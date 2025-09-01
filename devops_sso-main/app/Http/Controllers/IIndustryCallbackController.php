<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use App\User; // หรือ App\Models\User ตามโปรเจ็กต์

class IIndustryCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // ===== 0) Security gate =====
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
        $ok = @$dom->loadXML($raw, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        if (!$ok) {
            return response('Malformed XML', 400);
        }
        $root = $dom->documentElement;
        if (!$root || strtolower($root->nodeName) !== 'i-industry') {
            return response('Root element not i-industry', 400);
        }

        $get = function($tag) use ($root) {
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

        if ($Return !== 'True')  return response('Return != True', 400);
        if ($SessionID !== $aid) return response('SessionID mismatch', 400);

        // IP allowlist (ปิดไว้ก่อน)
        if (config('iindustry.ip_allowlist_enabled')) {
            $okIp = in_array($request->ip(), (array) config('iindustry.ip_allowlist'), true);
            if (!$okIp) return response('IP not allowed', 403);
        }

        // freshness st/entry
        try {
            $tz = 'Asia/Bangkok';
            $enTs = Carbon::createFromFormat('Y-m-d H:i:s', $entry, $tz);
            $now  = Carbon::now($tz);
            $maxSkew = (int) config('iindustry.max_skew_minutes', 10);
            if ($now->diffInMinutes($enTs, false) < -$maxSkew) {
                return response('Entry expired', 400);
            }
        } catch (\Exception $e) {
            return response('Invalid timestamp', 400);
        }

        // parse inner JSON
        $owner = array();
        $cust  = array();
        if ($iOwner) { $tmp = json_decode($iOwner, true); if (is_array($tmp)) $owner = $tmp; }
        if ($iCustomer) { $tmp = json_decode($iCustomer, true); if (is_array($tmp)) $cust = $tmp; }

        // ===== 1) Resolve juristic + tax_number =====
        $jt = $this->pickJuristicType($owner); // '1' or '2'
        $mapStatus = (array) config('iindustry.status_map', array('1'=>'1','2'=>'2'));

        $resolved = $this->resolveTaxBySource(
            'i-industry',
            $jt,
            $uid,
            null,
            $bid,
            $owner
        );

        $taxNumber = $resolved['tax_number'];     // 13 หลัก
        $externalUid = $resolved['external_uid']; // อาจเป็น null
        $juristicStatus = isset($mapStatus[$jt]) ? $mapStatus[$jt] : $jt;

        // ===== 2) Lookup user =====
        $user = User::where('tax_number', $taxNumber)->first();

        // (บล็อก: ยังไม่เปิดใช้ในเฟสนี้ — เผื่ออนาคต)
        $blockedList = (array) config('iindustry.blocklist_tax', array());
        if ($user && in_array($taxNumber, $blockedList, true)) {
            return response()->json(['message' => 'บัญชีนี้ถูกระงับ โปรดติดต่อแอดมิน'], 403);
        }

        // ===== 3) Found → login → route by progid =====
        if ($user) {
            // อัปเดต juristic_status ถ้าขาด
            if (empty($user->juristic_status)) {
                $user->juristic_status = $juristicStatus;
            }
            // จำคนที่ล็อกอินมา (optional, ไม่มีคอลัมน์ก็ข้าม)
            if (property_exists($user, 'external_uid') && empty($user->external_uid) && !empty($externalUid)) {
                $user->external_uid = $externalUid;
            }
            $user->save();

            Auth::login($user);
            $request->session()->regenerate();

            return redirect($this->routeByProgid($progid));
        }

        // ===== 4) Not found → send to register (cache snapshot, no migration) =====
        $token = $this->randomToken();

        $snapshot = array(
            'source'       => 'i-industry',
            'jt'           => $jt,
            'juristic_status' => $juristicStatus,
            'tax_number'   => $taxNumber,
            'uid'          => $uid,
            'bid'          => $bid,
            'iCustomer'    => $cust,
            'iOwner'       => $owner,
            'progid'       => $progid,
        );
        Cache::put('prereg:'.$token, $snapshot, Carbon::now()->addMinutes(30));

        return redirect()->route('register.complete-profile', array(
            'token'  => $token,
            'source' => 'i-industry',
        ));
    }

    private function routeByProgid($progid)
    {
        $map = (array) config('progid.routes', array());
        $default = (string) config('progid.default', '/');
        $k = (string) $progid;
        return isset($map[$k]) ? $map[$k] : $default;
    }

    private function pickJuristicType(array $owner)
    {
        $jt = null;
        if (isset($owner['JuristicList'][0]['JuristicType'])) {
            $jt = (string) $owner['JuristicList'][0]['JuristicType'];
        } elseif (isset($owner['JuristicType'])) {
            $jt = (string) $owner['JuristicType'];
        }
        return $jt ?: '1';
    }

    private function normalize13($v)
    {
        $n = preg_replace('/\D/', '', (string) $v);
        return $n ? $n : null;
    }

    // source: 'i-industry' => JT=1 use uid (pid), JT=2 use bid
    private function resolveTaxBySource($source, $jt, $uid, $pid, $bid, array $owner)
    {
        $out = array('tax_number' => null, 'external_uid' => null);
        $jt = (string) $jt;

        if ($source === 'i-industry') {
            if ($jt === '2') {
                $tn = $this->normalize13($bid);
                if (!$tn && isset($owner['JuristicList'][0]['JuristicID'])) {
                    $tn = $this->normalize13($owner['JuristicList'][0]['JuristicID']);
                }
                $out['tax_number']  = $tn;
                $out['external_uid'] = $this->normalize13($uid);
            } else {
                // บุคคลธรรมดา: uid == pid
                $tn = $this->normalize13($uid);
                if (!$tn && isset($owner['CitizenID'])) {
                    $tn = $this->normalize13($owner['CitizenID']);
                }
                $out['tax_number']  = $tn;
                $out['external_uid'] = $tn;
            }
        } else {
            // เผื่ออนาคตใช้กับ TISI
            if ($jt === '2') {
                $out['tax_number'] = $this->normalize13($bid);
            } else {
                $out['tax_number'] = $this->normalize13($pid);
            }
        }

        if (!$out['tax_number'] || strlen($out['tax_number']) !== 13) {
            throw new \RuntimeException('Invalid tax number');
        }
        return $out;
    }

    private function randomToken()
    {
        // ใช้ได้กับ PHP 7.1+
        try {
            return bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            return str_replace('.', '', uniqid('', true));
        }
    }
}
