<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\User; // หรือ App\Models\User

class IIndustryRegistrationController extends Controller
{
    public function showCompleteProfile(Request $request)
    {
        $token = (string) $request->query('token', '');
        $snap = \Illuminate\Support\Facades\Cache::get('prereg:'.$token);
        if (!$token || !$snap) {
            return response('Invalid or expired token', 400);
        }
        // เดิม: return view('register.complete_profile', [...]);
        return view('auth.complete_profile', [
            'token' => $token,
            'snap'  => $snap,
        ]);
    }
    

    public function storeCompleteProfile(Request $request)
    {
        $request->validate(array(
            'token' => 'required',
            // เพิ่มฟิลด์ที่เปิดให้กรอกเอง เช่น address ฯลฯ
        ));

        $token = (string) $request->input('token');
        $snap = Cache::pull('prereg:'.$token); // ดึงแล้วลบทิ้ง
        if (!$snap) {
            return response('Expired token', 400);
        }

        // ถ้ามีอยู่แล้วไม่ต้องสร้าง
        $user->tax_number = $snap['tax_number'];
        $user->firstname  = data_get($snap,'iCustomer.UserFirstName');
        $user->lastname   = data_get($snap,'iCustomer.UserLastName');
        $user->email      = data_get($snap,'iCustomer.UserEmail');
        $user->phone      = data_get($snap,'iCustomer.UserPhone');
        

        Auth::login($user);
        $request->session()->regenerate();

        // ไปปลายทางตาม progid
        //$progid = isset($snap['progid']) ? $snap['progid'] : null;
        //$route = app('App\Http\Controllers\SSO\IIndustryCallbackController')->routeByProgid($progid);
        //return redirect($route);
        $progid = $snap['progid'] ?? null;
        return redirect( config("progid.routes.$progid", config('progid.default','/')) );

    }

    public function routeByProgid(?string $progid): string
    {
        $map = config('progid.routes', []);
        return $map[$progid] ?? config('progid.default', '/');
    }

    public function handle(Request $request)
    {
        // ใส่โค้ดจริงภายหลังได้; ตอนนี้ทดสอบ route ให้เข้าแน่ ๆ ก่อน
        return response()->json(['ok' => true, 'from' => 'Auth\\IIndustryCallbackController']);
    }
}
