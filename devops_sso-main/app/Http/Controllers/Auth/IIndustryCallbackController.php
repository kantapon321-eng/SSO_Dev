<?php
//Kantapon 29/9/2568
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Http; // อนุญาตให้คงไว้ได้ แต่จะไม่เรียกตรง ๆ
use Carbon\Carbon;
use App\User;

class IIndustryCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // 0) บันทึก path/prefix
        $path   = $request->path();
        $prefix = strtolower(explode('/', trim($path, '/'))[0] ?? '');
        Log::info('[iindustry] inbound', ['path' => $path, 'prefix' => $prefix, 'query' => $request->getQueryString()]);

        // 1) ดึง UID/AID จาก cookie → (fallback) body XML (ไม่อ่าน query)
        $raw  = $request->getContent();
        $root = null;

        if ($raw) {
            $dom = new \DOMDocument();
            $ok  = @$dom->loadXML($raw, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            if ($ok && $dom->documentElement && strtolower($dom->documentElement->nodeName) === 'i-industry') {
                $root = $dom->documentElement;
            } else {
                Log::warning('[iindustry] Body is not <i-industry> XML; ignoring body for uid/aid', [
                    'body_head' => mb_substr($raw, 0, 200, 'UTF-8'),
                ]);
            }
        }

        [$uid, $aid] = $this->resolveUidAid($request, $root);
        Log::debug('[iindustry] UID/AID resolved', ['uid' => $uid, 'aid' => $aid]);

        if (!$uid || !$aid) {
            Log::error('[iindustry] Missing UID/AID after cookie/body resolution');
            return response()->json(['error' => 'Missing UID/AID cookie.'], 400);
        }

        // 2) สร้าง URL LoginIndust.asp ตาม prefix (/moiapi → www3, /moiapitest → www4)
        //dd($this->buildTisiLoginUrl($request, 'abc123', 'xyz789'));
        $url = $this->buildTisiLoginUrl($request, $uid, $aid);
        Log::debug('[iindustry] buildTisiLoginUrl', ['prefix' => $prefix, 'url' => $url]);

        // 3) เรียก upstream (มี fallback: Http → Guzzle → cURL) + header ที่จำเป็น
        $u = $this->fetchUpstream($request, $url);

        Log::info('[iindustry] Upstream status', [
            'status'   => $u['status'],
            'ok'       => $u['ok'],
            'ctype'    => $u['ctype'],
            'body_len' => strlen($u['body']),
        ]);
        Log::debug('[iindustry] Upstream body head', [
            'head' => mb_substr($u['body'], 0, 300, 'UTF-8'),
        ]);

        if (trim($u['body']) === '') {
            Log::error('[iindustry] Upstream empty response');
            return response()->json(['error' => 'Upstream empty response.'], 502);
        }

        // 4) พาร์ส XML จาก LoginIndust → ต้องได้ BID/JuristicType/Progid
        $parsed = $this->parseTisiXmlString($u['body']);
        $bid    = $parsed['bid'] ?? null;
        $jt     = $parsed['juristic_type'] ?? null;
        $progid = $parsed['progid'] ?? null;

        Log::info('[iindustry] Parsed from LoginIndust', [
            'uid' => $parsed['uid'] ?? null,
            'aid' => $parsed['aid'] ?? null,
            'bid' => $bid,
            'jt'  => $jt,
            'progid' => $progid,
            'has_iOwner' => !empty($parsed['iOwner']),
        ]);

        if (!$bid || !$jt) {
            Log::error('[iindustry] Missing required fields from LoginIndust', compact('bid','jt','progid'));
            return response()->json(['error' => 'Missing required fields from upstream (JuristicType and BID are required).'], 502);
        }

        // 5) flow เดิม
        $taxNumber = $this->normalize13($bid) ?: $this->normalize13($uid);
        $user = null;
        if ($taxNumber && strlen($taxNumber) === 13) {
            $user = User::where('tax_number', $taxNumber)->first();
        }

        if ($user) {
            if (empty($user->juristic_status)) {
                $user->juristic_status = $jt;
                $user->save();
            }
            Auth::login($user);
            $request->session()->regenerate();

            $dest = $this->routeByProgid($progid);
            Log::info('[iindustry] login success', ['user_id' => $user->id, 'dest' => $dest]);
            return redirect($dest);
        }

        // 6) ไม่เจอ user → snapshot แล้วส่งไป register
        return $this->redirectToRegisterSnapshot(
            $uid,
            $bid,
            $jt,
            $progid,
            $taxNumber,
            $parsed['iCustomer'] ?? null
        );
    }

    /**
     * เอา UID/AID จาก cookie (หรือ body XML เป็น fallback) เท่านั้น
     */
    private function resolveUidAid(Request $request, ?\DOMElement $root): array
    {
        $uid = null; $aid = null;

        $cookieHeader = (string) $request->headers->get('cookie', '');
        Log::debug('[iindustry] Cookie header (raw)', ['cookie' => mb_substr($cookieHeader, 0, 500, 'UTF-8')]);

        $rawCookie = $request->cookie('i-industry') ?? $request->cookie('i%2Dindustry');
        if (!$rawCookie && $cookieHeader) {
            if (preg_match('/(?:^|;\s*)(i(?:%2D|-)?industry)\s*=\s*([^;]+)/i', $cookieHeader, $m)) {
                $rawCookie = $m[2];
                Log::debug('[iindustry] Cookie picked from header', ['name' => $m[1], 'value_head' => mb_substr($rawCookie, 0, 80, 'UTF-8')]);
            }
        }

        if ($rawCookie !== null && $rawCookie !== '') {
            $decoded = urldecode($rawCookie);
            if (strpos($decoded, '%') !== false) $decoded = urldecode($decoded);
            Log::debug('[iindustry] Cookie decoded', ['decoded' => $decoded]);

            $parts = explode('/', $decoded, 2);
            if (count($parts) === 2) {
                $uid = $this->normalize13($parts[0]) ?: null;
                $aid = preg_replace('/\D/', '', (string) $parts[1]) ?: null;
                Log::debug('[iindustry] UID/AID from cookie', ['uid' => $uid, 'aid' => $aid]);
            }
        }

        if ((!$uid || !$aid) && $root instanceof \DOMElement) {
            $get = function($tag) use ($root) {
                $node = $root->getElementsByTagName($tag)->item(0);
                return $node ? trim($node->nodeValue) : '';
            };
            $uidBody = $this->normalize13($get('uid')) ?: null;
            $aidBody = preg_replace('/\D/', '', $get('SessionID')) ?: null;

            if (!$uid && $uidBody) $uid = $uidBody;
            if (!$aid && $aidBody) $aid = $aidBody;

            Log::debug('[iindustry] UID/AID from body fallback', ['uid' => $uidBody, 'aid' => $aidBody]);
        }

        return [$uid, $aid];
    }

    /**
     * เรียก upstream แบบมี fallback: Http → Guzzle → cURL
     */
    private function fetchUpstream(Request $request, string $url): array
    {
        $base    = $this->tisiBaseByPath($request);
        $referer = $this->buildTisiReferer($request, $base);

        $headers = [
            'User-Agent'      => 'Mozilla/5.0',
            'Accept'          => 'text/xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'th,en-US;q=0.7,en;q=0.3',
            'Origin'          => $base,
            'Referer'         => $referer,
            'Connection'      => 'close',
        ];

        // (1) Laravel Http (ถ้ามี)
        try {
            if (class_exists(\Illuminate\Support\Facades\Http::class)) {
                $resp = \Illuminate\Support\Facades\Http::withHeaders($headers)
                    ->retry(2, 250)
                    ->timeout(12)
                    ->get($url);

                return [
                    'status' => $resp->status(),
                    'ok'     => $resp->successful(),
                    'ctype'  => $resp->header('Content-Type'),
                    'body'   => (string) $resp->body(),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('[iindustry] Http facade fetch fail: '.$e->getMessage());
        }

        // (2) Guzzle (ส่วนใหญ่มีใน Laravel อยู่แล้ว)
        try {
            if (class_exists(\GuzzleHttp\Client::class)) {
                $client = new \GuzzleHttp\Client([
                    'headers'     => $headers,
                    'http_errors' => false,
                    'timeout'     => 12,
                    'verify'      => true,
                ]);
                $resp = $client->get($url);
                return [
                    'status' => $resp->getStatusCode(),
                    'ok'     => ($resp->getStatusCode() >= 200 && $resp->getStatusCode() < 300),
                    'ctype'  => $resp->getHeaderLine('Content-Type'),
                    'body'   => (string) $resp->getBody(),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('[iindustry] Guzzle fetch fail: '.$e->getMessage());
        }

        // (3) cURL ติดดิน
        try {
            $ch = curl_init($url);
            $h  = [];
            foreach ($headers as $k => $v) { $h[] = $k.': '.$v; }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 12,
                CURLOPT_HTTPHEADER     => $h,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 3,
            ]);
            $body   = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $ctype  = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            if ($body === false) {
                Log::warning('[iindustry] cURL error: '.curl_error($ch));
            }
            curl_close($ch);
            return [
                'status' => $status ?: 0,
                'ok'     => ($status >= 200 && $status < 300),
                'ctype'  => $ctype ?: null,
                'body'   => (string) $body,
            ];
        } catch (\Throwable $e) {
            Log::error('[iindustry] cURL fetch fail: '.$e->getMessage());
        }

        return ['status' => 0, 'ok' => false, 'ctype' => null, 'body' => ''];
    }

    private function tisiBaseByPath(Request $request): string
    {
        $prefix = strtolower(explode('/', trim($request->path(), '/'))[0] ?? '');
        return ($prefix === 'moiapitest') ? 'https://www4.tisi.go.th' : 'https://www3.tisi.go.th';
    }

    private function buildTisiReferer(Request $request, string $base): string
    {
        $prefix = strtolower(explode('/', trim($request->path(), '/'))[0] ?? '');
        $path2  = ($prefix === 'moiapitest') ? '/moiapitest/ind_chk.asp?prog=3' : '/moiapi/ind_chk.asp?prog=3';
        return $base.$path2;
    }

    /**
     * แปลง/พาร์ส XML จาก LoginIndust.asp → คืน uid/aid/bid/progid/juristic_type(+owner)
     */
    private function parseTisiXmlString(string $xml): array
    {
        $out = [
            'uid' => null, 'aid' => null, 'bid' => null, 'progid' => null,
            'juristic_type' => null, 'iOwner' => null, 'iCustomer' => null,
            'return' => null, 'st' => null, 'entry' => null, 'ipAddr' => null,
        ];

        if (trim($xml) === '') return $out;

        $xml = preg_replace('/^\xEF\xBB\xBF/u', '', $xml);

        $dom = new \DOMDocument();
        $ok  = @$dom->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NOCDATA);
        if (!$ok) {
            Log::warning('[iindustry] parseXML fail', ['xml_head' => mb_substr($xml, 0, 300, 'UTF-8')]);
            return $out;
        }

        $root = $dom->documentElement;
        $get = function($tag) use ($root) {
            $node = $root->getElementsByTagName($tag)->item(0);
            return $node ? trim($node->nodeValue) : '';
        };

        $out['aid']    = preg_replace('/\D/', '', $get('SessionID')) ?: null;
        $out['uid']    = preg_replace('/\D/', '', $get('uid')) ?: null;
        $out['bid']    = preg_replace('/\D/', '', $get('bid')) ?: null;
        $out['progid'] = $get('progid') ?: null;

        $ret           = $get('Return');
        $out['return'] = ($ret !== '' ? (strtolower($ret) === 'true' || $ret === '1') : null);

        $out['st']      = $get('st') ?: null;
        $out['entry']   = $get('entry') ?: null;
        $out['ipAddr']  = $get('ipAddr') ?: null;
        $out['iOwner']  = $get('iOwner') ?: null;
        $out['iCustomer'] = $get('iCustomer') ?: null;

        // ดึง jt/bid จาก iOwner JSON
        if ($out['iOwner']) {
            $json = html_entity_decode($out['iOwner'], ENT_QUOTES | ENT_XML1, 'UTF-8');
            $tmp  = json_decode($json, true);
            if (!is_array($tmp) && strpos($json, '\\') !== false) {
                $tmp = json_decode(stripslashes($json), true);
            }
            if (is_array($tmp)) {
                if (isset($tmp['JuristicList'][0]['JuristicType'])) {
                    $out['juristic_type'] = (string) $tmp['JuristicList'][0]['JuristicType'];
                } elseif (isset($tmp['JuristicType'])) {
                    $out['juristic_type'] = (string) $tmp['JuristicType'];
                }
                if (!$out['bid'] && isset($tmp['JuristicList'][0]['JuristicID'])) {
                    $out['bid'] = preg_replace('/\D/', '', (string) $tmp['JuristicList'][0]['JuristicID']);
                }
            }
        }

        Log::debug('[iindustry] parseXML result', [
            'uid' => $out['uid'], 'aid' => $out['aid'], 'bid' => $out['bid'],
            'jt'  => $out['juristic_type'], 'progid' => $out['progid'],
        ]);

        return $out;
    }

    private function buildTisiLoginUrl(Request $request, string $uid, string $aid): string
    {
        $prefix = strtolower(explode('/', trim($request->path(), '/'))[0] ?? '');
        $base   = ($prefix === 'moiapitest')
            ? 'https://www4.tisi.go.th/moiapitest/LoginIndust.asp'
            : 'https://www3.tisi.go.th/moiapi/LoginIndust.asp';

        Log::info($base.'?uid='.urlencode($uid).'&aid='.urlencode($aid));
        return $base.'?uid='.urlencode($uid).'&aid='.urlencode($aid);
    }
/*
    private function redirectToRegisterSnapshot($uid, $bid, $jt, $progid, $taxNumber = null, $iCustomer)
    {
        $token = $this->randomToken();
        session()->put('prereg:' . $token, [
            'source'     => 'i-industry',
            'jt'         => $jt,
            'tax_number' => $taxNumber,
            'uid'        => $uid,
            'bid'        => $bid,
            'progid'     => $progid,
            'iCustomer'  => $iCustomer,
        ]);

        session()->save(); //save session

        Log::info('[iindustry] prereg snapshot', compact('token','uid','bid','jt','progid','taxNumber'));

        return redirect()->route('register', [
            'token'  => $token,
            'source' => 'i-industry',
        ]);
    }
*/

private function redirectToRegisterSnapshot($uid, $bid, $jt, $progid, $taxNumber = null, $iCustomer)
{
    $token = $this->randomToken();

    // ---- build & sanitize payload (UTF-8 safe) ----
    $payload = [
        'source'     => 'i-industry',
        'jt'         => (string) $jt,
        'tax_number' => $taxNumber !== null ? (string) $taxNumber : null,
        'uid'        => $uid !== null ? (string) $uid : null,
        'bid'        => $bid !== null ? (string) $bid : null,
        'progid'     => $progid !== null ? (string) $progid : null,
        'iCustomer'  => $iCustomer, // may be array/string; sanitized below
    ];

    $sanitize = function ($v) use (&$sanitize) {
        if ($v === null || is_bool($v) || is_int($v) || is_float($v)) return $v;
        if (is_string($v)) {
            return mb_check_encoding($v, 'UTF-8') ? $v : mb_convert_encoding($v, 'UTF-8', 'auto');
        }
        if (is_array($v)) {
            foreach ($v as $k => $vv) $v[$k] = $sanitize($vv);
            return $v;
        }
        // objects/resources → stringify safely
        return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    };
    foreach ($payload as $k => $v) $payload[$k] = $sanitize($v);

    // ---- write to SESSION (tokened key + stable "latest") ----
    // (Colons in keys are fine, but keep the "latest" keys simple)
    $session = session();
    $session->put('prereg:' . $token, $payload);
    $session->put('prereg_latest', $payload);
    $session->put('prereg_token_latest', $token);
    $session->save(); // force write before the 302

    // ---- redirect RELATIVE to avoid host flips; also flash a plain key for the very next request ----
    // ->with() uses Laravel flash session (still session-only) and guarantees availability on the next page load
    $to = route('register', ['token' => $token, 'source' => 'i-industry'], false);

    return redirect()->to($to)
        ->with('prereg', $payload)          // flash for immediate /register render
        ->with('prereg_token', $token);     // optional: flash token too
}


    // ===== helpers =====

    private function normalize13($v)
    {
        $n = preg_replace('/\D/', '', (string) $v);
        return $n ?: null;
    }

    private function routeByProgid($progid)
    {
        $map     = (array) config('progid.routes', []);
        $default = (string) config('progid.default', '/');
        return $map[(string)$progid] ?? $default;
    }

    private function randomToken()
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            return str_replace('.', '', uniqid('', true));
        }
    }
}
