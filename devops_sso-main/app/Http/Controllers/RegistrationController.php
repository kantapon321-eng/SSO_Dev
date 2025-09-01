<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PreRegisterSnapshot;
use App\Models\User;

class RegistrationController extends Controller
{
    public function showCompleteProfile(Request $request)
    {
        $token = $request->query('token');
        $snap  = PreRegisterSnapshot::where('token', $token)->firstOrFail();

        // เตรียมข้อมูลพรีฟิล (และ freeze ใน blade)
        return view('register.complete_profile', [
            'token'      => $token,
            'source'     => $snap->source,
            'progid'     => $snap->progid,
            'tax_number' => $snap->tax_number,
            'iOwner'     => $snap->i_owner,
            'iCustomer'  => $snap->i_customer,
        ]);
    }

    public function storeCompleteProfile(Request $request)
    {
        $request->validate([
            'token'      => 'required',
            // เพิ่มฟิลด์ที่ผู้ใช้ต้องกรอกเอง เช่น address ฯลฯ
        ]);

        $snap = PreRegisterSnapshot::where('token', $request->input('token'))->firstOrFail();

        // ป้องกันสมัครซ้ำ (หากมี latency)
        $exists = User::where('tax_number', $snap->tax_number)->exists();
        if ($exists) {
            $user = User::where('tax_number', $snap->tax_number)->first();
        } else {
            // สร้างผู้ใช้ใหม่ – ฟิลด์ freeze มาจาก snapshot
            $first = data_get($snap->i_customer, 'UserFirstName');
            $last  = data_get($snap->i_customer, 'UserLastName');

            $user = new User();
            $user->tax_number   = $snap->tax_number;
            $user->firstname    = $first;
            $user->lastname     = $last;
            $user->email        = data_get($snap->i_customer, 'UserEmail');
            $user->phone        = data_get($snap->i_customer, 'UserPhone');
            $user->source       = 'i-industry';
            $user->juristic_type= (string) data_get($snap->i_owner, 'JuristicList.0.JuristicType', data_get($snap->i_owner, 'JuristicType'));
            $user->external_uid = $snap->uid;
            $user->external_bid = $snap->bid;
            $user->progid_last  = $snap->progid;
            $user->is_blocked   = false;

            // TODO: เก็บฟิลด์เพิ่มเติมจากแบบฟอร์มผู้ใช้ (address ฯลฯ)
            $user->save();
        }

        // ลบ snapshot หรือเก็บไว้ตามนโยบาย
        $snap->delete();

        Auth::login($user);
        return redirect(app(\App\Http\Controllers\IIndustryCallbackController::class)->routeByProgid($user->progid_last));
    }
}
