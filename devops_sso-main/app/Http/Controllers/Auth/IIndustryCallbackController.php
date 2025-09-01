<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;   // ✅ เช็คคอลัมน์ก่อนอัปเดต
use Carbon\Carbon;
use App\User;

class IIndustryCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // ---------- 0) รับ/ตรวจ payload ----------
        $uid = (string) $request->query('uid', '');
        $aid = (string) $request->query('aid', '');

        $raw = $request->getContent();
        if (!$raw && $request->has('xml')) {
            $raw = (string) $request->input('xml');
        }
        if (!$raw) {
            return response('Empty payload', 400);
        }
        if (stripos($raw, '<!DOCTYPE') !== false) {
            return response('Invalid XML', 400);
        }

        $dom = new \DOMDocument();
        $ok  = @$dom->loadXML($raw, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        if (!$ok) {
            return response('Malformed XML', 400);
        }
        $root = $dom->documentElement;
        if (!$root || strtolower($root->nodeName) !== 'i-industry') {
            return response('Root element not i-industry', 400);
        }

        $get = function ($tag) use ($root) {
            $node = $root->getElementsByTagName($tag)->item(0);
            return $node ? trim($node->nodeValue) : '';
        };

        $Return    = $get('Return');
        $SessionID = $get('SessionID');
        $bid       = $get('bid');
        $st        = $get('st');
        $entry     = $get('entry');
        $ipAddr    = $get('ipAddr');
        $progid    = $get('progid');
        $iCustomer = $get('iCustomer');
        $iOwner    = $get('iOwner');

        // Return ต้องเป็น True (ไม่สนเคสตัวพิมพ์)
        if (strcasecmp($Return, 'True') !== 0) {
            return response('Return != True', 400);
        }
        // SessionID ตรวจเมื่อทั้งคู่ถูกส่งมาเท่านั้น (กันกรณีฝั่งใดว่าง)
        if ($aid !== '' && $SessionID !== '' && $SessionID !== $aid) {
            return response('SessionID mismatch', 400);
        }

        // IP allowlist (ปิดไว้ตามค่า config)
        if (config('iindustry.ip_allowlist_enabled', false)) {
            $okIp = in_array($request->ip(), (array) config('iindustry.ip_allowlist', []), true);
            if (!$okIp) {
                return response('IP not allowed', 403);
            }
        }

        // freshness: ตรวจเฉพาะเมื่อมีค่า entry
        if ($entry !== '') {
            try {
                $tz      = 'Asia/Bangkok';
                $enTs    = Carbon::createFromFormat('Y-m-d H:i:s', $entry, $tz);
                $now     = Carbon::now($tz);
                $maxSkew = (int) config('iindustry.max_skew_minutes', 10);
                if (abs($now->diffInMinutes($enTs, false)) > $maxSkew) {
                    return response('Entry expired', 400);
                }
            } catch (\Throwable $e) {
                return response('Invalid timestamp', 400);
            }
        }

        // ---------- 1) แปลง JSON ภายใน ----------
        $owner = [];
        $cust  = [];
        if ($iOwner)    { $tmp = json_decode($iOwner, true);    if (is_array($tmp)) $owner = $tmp; }
        if ($iCustomer) { $tmp = json_decode($iCustomer, true); if (is_array($tmp)) $cust  = $tmp; }

        // ---------- 2) หา JT และ tax_number ----------
        $jt        = $this->pickJuristicType($owner); // '1' บุคคล / '2' นิติ
        $mapStatus = (array) config('iindustry.status_map', ['1' => '1', '2' => '2']);

        try {
            $resolved = $this->resolveTaxBySource('i-industry', $jt, $uid, null, $bid, $owner);
        } catch (\Throwable $e) {
            Log::warning('iindustry.resolveTaxBySource failed', ['jt' => $jt, 'uid' => $uid, 'bid' => $bid, 'err' => $e->getMessage()]);
            return response('Invalid tax number', 400);
        }

        $taxNumber    = $resolved['tax_number'];     // 13 หลัก
        $externalUid  = $resolved['external_uid'];   // สำหรับเก็บอ้างอิงคนที่ล็อกอิน (ถ้ามีคอลัมน์)
        $juristicStat = $mapStatus[$jt] ?? $jt;

        // ---------- 3) ค้นผู้ใช้ ----------
        $user = User::where('tax_number', $taxNumber)->first();

        // blocklist แบบ config (ถ้าต้องการ)
        $blockedList = (array) config('iindustry.blocklist_tax', []);
        if ($user && in_array($taxNumber, $blockedList, true)) {
            return response()->json(['message' => 'บัญชีนี้ถูกระงับ โปรดติดต่อแอดมิน'], 403);
        }

        // ---------- 4) เจอผู้ใช้ → login + route ----------
        if ($user) {
            // (เดิม) อัปเดตคอลัมน์ถ้ามี → login
            Auth::login($user);
            $request->session()->regenerate();
        
            // ✅ คนที่มีบัญชี → ใช้ progid
            return redirect($this->routeByProgid($progid));
        }
        
        // ---------- 5) ไม่เจอ → flash ข้อมูลเพื่อ Prefill แล้วพาไปหน้า /register (ฟอร์มเดิม)
        session()->flash('reg_prefill', [
            'source'          => 'i-industry',
            'jt'              => $jt,                   // '1' = บุคคลธรรมดา, '2' = นิติบุคคล
            'juristic_status' => $juristicStat,
            'tax_number'      => $taxNumber,
            'uid'             => $uid,
            'bid'             => $bid,
            'iCustomer'       => $cust,                 // {UserFirstName, UserLastName, UserEmail, UserPhone, ...}
            'iOwner'          => $owner,
            'progid'          => $progid,               // (ยังไม่ใช้ตอนสมัคร แต่เก็บไว้)
            // 'raw_xml'      => $raw,                  // เก็บก็ได้ ไม่เก็บก็ได้
        ]);

        return redirect('/register');

    }

    // ----- route by progid (อ่านจาก config/progid.php) -----
    public function routeByProgid(?string $progid): string
    {
        $map = config('progid.routes', []);
        return $map[$progid] ?? config('progid.default', '/');
    }

    // ----- helpers -----
    private function pickJuristicType(array $owner)
    {
        $jt = null;
        if (isset($owner['JuristicList'][0]['JuristicType'])) {
            $jt = (string) $owner['JuristicList'][0]['JuristicType'];
        } elseif (isset($owner['JuristicType'])) {
            $jt = (string) $owner['JuristicType'];
        } elseif (!empty($owner['CitizenID'])) {
            // เดาว่าเป็นบุคคลถ้ามี CitizenID
            $jt = '1';
        }
        return $jt ?: '1';
    }

    private function normalize13($v)
    {
        $n = preg_replace('/\D/', '', (string) $v);
        return $n ?: null;
    }

    /**
     * source = 'i-industry'
     * JT=1 → tax_number = uid (= pid ของ i-industry)
     * JT=2 → tax_number = bid (fallback: JuristicID)
     */
    private function resolveTaxBySource($source, $jt, $uid, $pid, $bid, array $owner)
    {
        $out = ['tax_number' => null, 'external_uid' => null];
        $jt  = (string) $jt;

        if ($source === 'i-industry') {
            if ($jt === '2') {
                $tn = $this->normalize13($bid);
                if (!$tn && isset($owner['JuristicList'][0]['JuristicID'])) {
                    $tn = $this->normalize13($owner['JuristicList'][0]['JuristicID']);
                }
                $out['tax_number']   = $tn;
                $out['external_uid'] = $this->normalize13($uid);
            } else {
                // บุคคลธรรมดา: uid == pid
                $tn = $this->normalize13($uid);
                if (!$tn && isset($owner['CitizenID'])) {
                    $tn = $this->normalize13($owner['CitizenID']);
                }
                $out['tax_number']   = $tn;
                $out['external_uid'] = $tn;
            }
        } else {
            // เผื่อในอนาคตใช้กับ TISI (ไม่ได้ใช้ใน flow นี้)
            $out['tax_number'] = $jt === '2'
                ? $this->normalize13($bid)
                : $this->normalize13($pid);
        }

        if (!$out['tax_number'] || strlen($out['tax_number']) !== 13) {
            throw new \RuntimeException('Invalid tax number');
        }
        return $out;
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
