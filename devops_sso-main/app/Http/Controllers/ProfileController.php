<?php

namespace App\Http\Controllers;

use App\Models\Basic\Prefix;
use App\User;
use App\UserHistory;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Session;
use Illuminate\Support\Facades\Hash;

/* Google Authen */
use Crypt;
use Google2FA;

use Storage;
use HP;

class ProfileController extends Controller
{

    //แสดง profile
    public function show(){
        return view('profile/show');
    }

    //อัพเดทข้อมูลโปรไฟล์
    public function update(Request $request){

        $data = $request->all();

        $prefix = Prefix::find($data['contact_prefix_name']);//คำนำหน้าชื่อ

        if(!empty($data['contact_first_name']) && !empty($data['contact_last_name'])){
            $data['contact_name'] = $data['contact_first_name'].' '.$data['contact_last_name'];
        }
        $data['contact_tax_id']       = isset($data['contact_tax_id']) ? str_replace('-', '', $data['contact_tax_id']) : null ;
        $data['contact_phone_number'] = isset($data['contact_phone_number']) ? str_replace('-', '', $data['contact_phone_number']) : null ;
        $data['contact_prefix_text']  = !is_null($prefix) ? $prefix->title : null ;
        //$data['contact_name']         = $data['contact_prefix_text'].$data['contact_first_name'].' '.$data['contact_last_name']; //ชื่อเต็มผู้ติดต่อ

        $user = User::findOrFail(auth()->user()->id);
        $user_array = $user->toArray();

        //เก็บ Log และลบอักขระบางตัวออก
        foreach ($data as $key => $value) {

            $value = HP::replace_html($value);
            $data[$key] = $value;

            if(array_key_exists($key, $user_array) ){
                if($user_array[$key]!=$value){
                    UserHistory::Add($user->id,
                                    $key,
                                    $user_array[$key],
                                    $value,
                                    null
                                );
                }
            }
        }

        $user->update($data);

        Session::flash('message', 'อัพเดทข้อมูลเรียบร้อยแล้ว');
        return redirect('profile/show');

    }

    //หน้าตั้งค่า 2fa
    public function google2fa(){
        return view('profile/google2fa');
    }

    //บันทึกการตั้งค่า 2fa ปิด
    public function google2fa_disabled(Request $request){

        $user = User::find(auth()->user()->id);
        $user->google2fa_status = 0 ;
        $user->save();

        //บันทึก Log
        UserHistory::Add($user->id, 'google2fa_status', 1, 0, null);

        Session::flash('save_success', 'ปิดใช้งาน 2FA เรียบร้อยแล้ว');
        return redirect('profile/google2fa');
    }

    //บันทึกการตั้งค่า 2fa เปิด
    public function google2fa_enabled(Request $request){

        $one_time_password = implode('', $request->get('one_time_password'));

        $user = User::find(auth()->user()->id);

        $secret = Crypt::decrypt($user->google2fa_secret);

        $result = Google2FA::verifyKey($secret, $one_time_password);

        if($result){//รหัสถูกต้อง

            //อัพเดทผูก google 2fa สำเร็จ
            $user->google2fa_status = 1;
            $user->save();

            //บันทึก Log
            UserHistory::Add($user->id, 'google2fa_status', 0, 1, null);

            Session::flash('save_success', 'เปิดใช้งาน 2FA เรียบร้อยแล้ว');
            return redirect('profile/google2fa');
        }else{
            Session::flash('one_time_password_error', 'รหัส Google Authenticator ไม่ถูกต้อง');
            return redirect('profile/google2fa');
        }

        $user->google2fa_status = 0 ;
        $user->save();

        return redirect('profile/google2fa');
    }

    //หน้าเปลี่ยนรหัสผ่าน
    public function password(){
        return view('profile/password');
    }

    //บันทึกรหัสผ่าน
    public function password_save(Request $request){

        $this->validate($request,[
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8'
        ]);

        $user = User::findOrFail(auth()->user()->id);
        $user->password = Hash::make($request->password);
        $user->lastResetTime = date('Y-m-d H:i:s');
        $user->resetCount++;
        $user->save();

        Session::flash('save_success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว!');
        return redirect('profile/password');
    }

    //วิวหน้า Crop ภาพโปรไฟล์
    public function imageCrop()
    {
        return view('profile/image-crop');
    }

    //บันทึกภาพ จาก ajax request
    public function imageCropPost(Request $request)
    {

        $status = false;
        $image_url = '';

        if($request->has('image')){

            $profile = auth()->user();

            $data = $request->image;

            list($type, $data) = explode(';', $data);

            list(, $data) = explode(',', $data);

            $data = base64_decode($data);

            $image_name = uniqid() . '.png';

            //Upload File
            $result = Storage::put('sso_users/' . $image_name, $data);
            if($result){

                if(!is_null($profile->picture) && $profile->picture!=''){//มีไฟล์เดิม
                    Storage::delete('sso_users/' . $profile->picture);//ลบไฟล์เดิม
                }

                //Save Profile
                $profile->picture = $image_name;
                $profile->save();

                //ดึงไฟล์มา
                $image_url = HP::getFileStorage('sso_users/'.$image_name);
                $status = true;

            }

        }

        return response()->json(['status' => $status, 'image' => $image_url]);

    }

}
