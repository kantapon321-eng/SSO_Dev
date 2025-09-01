<?php

use Illuminate\Support\Facades\DB;
use App\Models\Setting\SettingUrl;
use App\Models\Setting\SettingSystem;
use App\Models\Basic\Config;
use App\Models\Basic\Province;
use App\Models\Basic\District;
use App\Models\Basic\Subdistrict;
use App\Models\Basic\Zipcode;
use App\Models\Agents\Agent;
use App\Models\Agents\AgentSystem;
use App\User;
use App\AttachFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Config\ConfigsEvidence;
use App\Models\Config\ConfigsFormatCode;
use App\Models\Config\ConfigsFormatCodeSub;
use App\Models\Config\ConfigsFormatCodeLog;
use App\Models\Log\LogNotification;
use App\Models\WS\MOILog;

class HP
{
    static function GenerateToken()
    {
        $tokens = array();
        $tokens[] = bin2hex(random_bytes(4));
        $tokens[] = bin2hex(random_bytes(2));
        $tokens[] = bin2hex(random_bytes(2));
        $tokens[] = bin2hex(random_bytes(2));
        $tokens[] = bin2hex(random_bytes(6));
        return implode('-', $tokens);
    }

    static function DateThai($strDate)
    {
        if (is_null($strDate)) {
            return '';
        }
        $strYear = date("Y", strtotime($strDate))+543;
        $strMonth= date("n", strtotime($strDate));
        $strDay= date("j", strtotime($strDate));

        $strMonthCut = array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
        $strMonthThai=$strMonthCut[$strMonth];

        return "$strDay $strMonthThai $strYear";
    }

    static function DateTimeThai($strDate)
    {
        $strYear = date("Y", strtotime($strDate))+543;
        $strMonth= date("n", strtotime($strDate));
        $strDay= date("j", strtotime($strDate));
        $strHour= date("H", strtotime($strDate));
        $strMinute= date("i", strtotime($strDate));
        $strSeconds= date("s", strtotime($strDate));
        $strMonthCut = array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
        $strMonthThai=$strMonthCut[$strMonth];
        return "$strDay $strMonthThai $strYear, $strHour:$strMinute น.";
    }

    static function DateTimeThaiTormat_1($strDate)
    {
        $strYear = date("Y", strtotime($strDate))+543;
        $strMonth= date("n", strtotime($strDate));
        $strDay= date("j", strtotime($strDate));
        $strHour= date("H", strtotime($strDate));
        $strMinute= date("i", strtotime($strDate));
        $strSeconds= date("s", strtotime($strDate));
        $strMonthCut = array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
        $strMonthThai=$strMonthCut[$strMonth];
        return "$strDay $strMonthThai $strYear เวลา $strHour:$strMinute:$strSeconds";
    }

    static function DateThaiFull($strDate)
    {
        if ($strDate != '') {

            $strYear = date("Y", strtotime($strDate)) + 543;
            $strMonth = date("m", strtotime($strDate));
            $strDay = date("j", strtotime($strDate));

            $strMonthCut = self::MonthList();
            $strMonthThai = $strMonthCut[$strMonth];
            return "$strDay $strMonthThai $strYear";
        } else {
            return "";
        }
    }

    static function MonthList()
    {
        $month = ['01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'];
        return $month;
    }

    //แปลงวันที่รูปแบบ 31/01/2018 เป็น 2018-01-31
    static function convertDate($date, $minus=true)
    {
        $negative = $minus===true?543:0;
        $dates = explode('/', $date);
        return ($dates['2']-$negative).'-'.$dates[1].'-'.$dates[0];
    }

    //แปลงวันที่รูปแบบ 2018-01-31 เป็น 31/01/2018
    static function revertDate($date, $plus=true)
    {
        $date = \Carbon\Carbon::parse($date)->format('Y-m-d');
        $positive = $plus===true?543:0;
        $dates = explode('-', $date);
        return (count($dates)=='3')?$dates['2'].'/'.$dates[1].'/'.($dates[0]+$positive):'';
    }


    static function formatDateThai($strDate) {

        if(is_null($strDate) || $strDate == '' || $strDate == '-' ){
            return '-';
        }
        $strYear = date("Y", strtotime($strDate)) + 543;
        $strMonth = date("m", strtotime($strDate));
        $strDay = date("j", strtotime($strDate));
        $month = ['01'=>'มกราคม', '02'=>'กุมภาพันธ์', '03'=>'มีนาคม', '04'=>'เมษายน', '05'=>'พฤษภาคม', '06'=>'มิถุนายน', '07'=>'กรกฎาคม', '08'=>'สิงหาคม', '09'=>'กันยายน', '10'=>'ตุลาคม', '11'=>'พฤศจิกายน', '12'=>'ธันวาคม'];
        $strMonthThai = $month[$strMonth];
        return "วันที่ $strDay เดือน $strMonthThai พ.ศ. $strYear";
      }

    static function ConvertCertifyFileName($name){
        $name = str_replace('#', '', $name);
        $name = str_replace('/', '', $name);
        return $name;
    }

    static function getSettingUrl()
    {
        $datas = SettingUrl::get();
        foreach ($datas as $item) {
            $column_name = $item->column_name;
            @$result->$column_name  = $item->data;
        }
        return $result;
    }

    static function getZipcode($sub = '',$dis = '',$pro = '')
    {
        $result = '';
        $address_data  =  DB::table((new Subdistrict)->getTable().' AS sub') // อำเภอ
                                    ->leftJoin((new District)->getTable().' AS dis', 'dis.AMPHUR_ID', '=', 'sub.AMPHUR_ID') // ตำบล
                                    ->leftJoin((new Province)->getTable().' AS pro', 'pro.PROVINCE_ID', '=', 'sub.PROVINCE_ID')  // จังหวัด
                                    ->leftJoin((new Zipcode)->getTable().' AS code', 'code.district_code', '=', 'sub.DISTRICT_CODE')  // รหัสไปรษณีย์
                                    ->where(DB::raw("REPLACE(sub.DISTRICT_NAME,' ','')"),  '=',  str_replace(' ', '', $sub ))
                                    ->where(DB::raw("REPLACE(dis.AMPHUR_NAME,' ','')"),  '=',   str_replace(' ', '', $dis ))
                                    ->where(DB::raw("REPLACE(pro.PROVINCE_NAME,' ','')"),  '=',  str_replace(' ', '', $pro ))
                                    ->selectRaw('code.zipcode AS zipcode')
                                    ->first();

        if(!is_null($address_data)){
            $result =  $address_data->zipcode ?? null;
        }

        return $result;
    }


    static function AuthUserSSO($id = null)
    {
        if( is_null($id) ){
            return Auth::user();
        }else{
            return User::where('id', $id )->first();
        }
    }

     //Config ระบบ
     static function getConfig()
     {
         $result = new stdClass();
         $generalList = Config::get();
         foreach ($generalList as $general) {
             $variable = $general->variable;
             $result->$variable = $general->data;
         }
         return $result;
     }

    static function checkFileStorage($file_path)
    {//get file from storage

        $result = false;
        $public = Storage::disk('uploads')->getDriver()->getAdapter()->getPathPrefix();

        if (is_file($public . $file_path)) {//ถ้ามีไฟล์ที่พร้อมแสดงอยู่แล้ว
            $result = true;
        } else {

            $exists = Storage::exists($file_path);
            if ($exists) {//ถ้ามีไฟล์ใน storage
                $result = true;
            }
        }

        return $result;

    }


    static function getFileStorage($file_path)
    {//get file from storage

        $result = '';
        $public = Storage::disk('uploads')->getDriver()->getAdapter()->getPathPrefix();

        if (is_file($public.$file_path)) {//ถ้ามีไฟล์ที่พร้อมแสดงอยู่แล้ว
            $result = Storage::disk('uploads')->url($file_path);
        } else {

            $exists = Storage::exists($file_path);
            if ($exists) {//ถ้ามีไฟล์ใน storage

                $stream = Storage::getDriver()->readStream($file_path);

                $attach =  str_replace(basename($file_path),"",$file_path);

                if(!Storage::disk('uploads')->has($attach)){
                    Storage::disk('uploads')->makeDirectory($attach) ;
                }
                $byte_put = file_put_contents($public.$file_path, stream_get_contents($stream), FILE_APPEND);
                if ($byte_put !== false) {
                    $result = Storage::disk('uploads')->url($file_path);
                }
            }
        }

        return $result;

    }

    // icon สกุลไฟล์แนบต่างๆ
    static function FileExtension($file) {
        $result = '';
        if(!is_null($file) && $file != ''){
            $type = strrchr(basename($file),".");
            if($type == '.pdf'    || $type ==  '.PDF'){
                $result =  '<i class="fa fa-file-pdf-o" style="font-size:20px; color:red" aria-hidden="true"></i>';
            }elseif($type == '.xlsx'){
                $result =  '<i class="fa  fa-file-excel-o" style="font-size:20px; color:#00b300" aria-hidden="true"></i>';
            }elseif($type == '.doc' || $type == '.docx'){
                $result =  '<i  class="fa fa-file-word-o"  style="font-size:20px; color:#0000ff" aria-hidden="true"></i>';
            }elseif($type == '.png' || $type == '.jpg'  || $type == '.jpeg'){
                $result =  '<i class="fa  fa-file-photo-o" style="font-size:20px; color:#ff9900" aria-hidden="true"></i>';
            }elseif($type == '.zip' || $type == '.7z' ){
                $result =  '<i class="fa fa-file-zip-o" style="font-size:20px; color:#ff0000" aria-hidden="true"></i>';
            }else{
                $result =  '<i class="fa  fa-file-text" style="font-size:20px; color:#92b9b9" aria-hidden="true"></i>';
            }
        }else{
            $result =  '<i class="fa  fa-file-text" style="font-size:20px; color:#92b9b9" aria-hidden="true"></i>';
        }
        return $result;
    }


    static function singleFileUpload($request_file, $attach_path = '', $tax_number='0000000000000', $username='0000000000000', $systems = "SSO", $table_name = null , $ref_id = null, $section = null, $attach_text = null, $setting_file_id = null){

        $attach             = $request_file;
        $file_size          = (method_exists($attach, 'getSize')) ? $attach->getSize() : 0;
        $file_extension     = $attach->getClientOriginalExtension();
        $fullFileName       = str_random(10).'-date_time'.date('Ymd_hms') . '.' .$file_extension ;

        $path               = Storage::putFileAs($attach_path.'/'.$tax_number, $attach,  str_replace(" ","",$fullFileName) );
        $file_name          = self::ConvertCertifyFileName($attach->getClientOriginalName());

        AttachFile::create([
                            'tax_number'        => $tax_number,
                            'username'          => $username,
                            'systems'           => $systems,
                            'ref_table'         => $table_name,
                            'ref_id'            => $ref_id,
                            'url'               => $path,
                            'filename'          => $file_name,
                            'new_filename'      => $fullFileName,
                            'caption'           => $attach_text,
                            'size'              => $file_size,
                            'file_properties'   => $file_extension,
                            'section'           => $section,
                            'setting_file_id'   => $setting_file_id,
                            'created_by'        => auth()->user()->getKey(),
                            'created_at'        => date('Y-m-d H:i:s')
                          ]);

    }

    static function ConfigEvidence($evidence_group_id){
        $configs_evidences = ConfigsEvidence::where('evidence_group_id', $evidence_group_id)->where('state', 1)->orderBy('ordering')->get();
        return $configs_evidences;
    }

    //ดึงรายชื่อผู้ที่มอบอำนาจให้ user นี้ทั้งหมด
    /*
        $agent_id = sso_users.id
        return Collection ([user.id => [user data]])
    */
    static function getAuthoritys($agent_id, $state=2){ //$state=2 สถานะดำเนินการตามรับมอบ
        $table_user = (new User)->getTable();
        $table_agent = (new Agent)->getTable();
        $agents =  Agent::where($table_agent.'.agent_id', $agent_id)
                        ->where(function($query){
                            $query->where('issue_type', 1)
                                  ->orWhere(function($query){
                                    $now = date('Y-m-d');
                                    $query->where('issue_type', 2)
                                          ->whereDate('start_date', '<=', $now)
                                          ->whereDate('end_date', '>=', $now);
                                  });
                        })
                        ->where($table_agent.'.state', $state)//สถานะดำเนินการตามรับมอบ
                        ->where($table_user.'.block','!=',1)//สถานะการใช้งาน
                        ->leftJoin($table_user, $table_user.'.id', '=', $table_agent.'.user_id')
                        ->select($table_user.'.id', $table_user.'.name', $table_user.'.branch_type', $table_user.'.branch_code', $table_user.'.applicanttype_id')
                        ->get();

        $agents = $agents->keyBy('id');

        return $agents;
    }

    //ดึงระบบที่ได้รับมอบอำนาจมาจาก user
    /*
        $agent_id = sso_agent.agent_id ผู้รับมอบอำนาจ
        $user_id = sso_agent.user_id ผู้มอบอำนาจ
        return Collection ([SettingSystem])
    */
    static function getAgentSystems($agent_id, $user_id){
        $agents =  Agent::where('agent_id', $agent_id)
                        ->where('user_id', $user_id)
                        ->where(function($query){
                            $query->where('issue_type', 1)
                                  ->orWhere(function($query){
                                    $now = date('Y-m-d');
                                    $query->where('issue_type', 2)
                                          ->whereDate('start_date', '<=', $now)
                                          ->whereDate('end_date', '>=', $now);
                                  });
                        })
                        ->where('state', 2)//ดำเนินการตามรับมอบ
                        ->orderby('select_all', 'desc')
                        ->select('id', 'select_all')
                        ->get();

        $setting_systems = SettingSystem::get()->keyBy('id');
        $allows = collect([]);
        foreach ($agents as $agent) {
            if($agent->select_all==1){//มอบทุกระบบ
                $allows = $setting_systems;
                break;
            }else{
                $system_ids = AgentSystem::where('sso_agent_id', $agent->id)->pluck('setting_systems_id');//ไอดี ระบบที่มอบอำนาจให้
                $allow_tmps = $setting_systems->whereIn('id', $system_ids);//get จากระบบที่มีอยู่เพื่อดึงชุดข้อมูลและเช็ค
                $allows = $allows->merge($allow_tmps)->keyBy('id');//นำมารวมกับของรายการก่อนหน้า
            }
        }

        return $allows;
    }

    static function UserAgentExpire()
    {
        $agent = Agent::whereNotIn('state', [99])->Where('issue_type', 2)->Where('state', 2)->whereDate('end_date', '<', date('Y-m-d') )->get();

        foreach( $agent as $item ){
            if( !empty($item->end_date) ){
                $item->state = 4;
                $item->save();
            }
        }
    }

    /* ขนาดไฟล์ที่เซิร์ฟเวอร์อนุญาตให้อัพโหลด
       return (object)['size' => ขนาดไฟล์หน่วยเป็น KB, 'text' => ข้อความขนาดไฟล์ MB]
    */
    static function get_upload_max_filesize(){

        $max  = ini_get('upload_max_filesize');
        $size = (int)$max * 1024;
        return (object)['size' => $size, 'text' => $max.'B'];

    }

    private static $data_provinces_lsit;//จังหวัด
    private static $data_districts_list;//อำเภอ
    private static $data_sub_districts_lsit;//ตำบล
    private static $data_zipcode_lsit;//รหัสไปรษณีย์
    private static $data_district_groups_list;//Group อำเภอ By ID จังหวัด
    private static $data_sub_district_groups_list;// Group ตำบล By ID อำเภอ

    static function GetIDAddress( $txt_sub = null, $txt_dis = null, $txt_pro = null )
    {
        $data = new stdClass;

        if( !empty($txt_sub) && !empty($txt_dis) && !empty($txt_pro)  ){

            $txt_sub = trim($txt_sub);
            $txt_dis = trim($txt_dis);
            $txt_pro = trim($txt_pro);

            if( strpos( $txt_sub , "ตำบล" ) === 0 ){
                $txt_sub =  !empty($txt_sub)?str_replace('ตำบล','',$txt_sub):null;
            }

            if( strpos( $txt_dis , "อำเภอ/เขต" ) === 0 ){
                $txt_dis =  !empty($txt_dis)?str_replace('อำเภอ/เขต','',$txt_dis):null;
            }else if( strpos( $txt_dis , "เขต" ) === 0 ){
                $txt_dis =  !empty($txt_dis)?str_replace('เขต','',$txt_dis):null;
            }else if( strpos( $txt_dis , "อำเภอ" ) === 0 ){
                $txt_dis =  !empty($txt_dis)?str_replace('อำเภอ','',$txt_dis):null;
            }

            if( strpos( $txt_pro , "จังหวัด" ) === 0 ){
                $txt_pro =  !empty($txt_pro)?str_replace('จังหวัด','',$txt_pro):null;
            }

            //จัดหวัด
            if( !is_array(self::$data_provinces_lsit) ){
                self::$data_provinces_lsit =  Province::select(DB::raw("TRIM(`PROVINCE_NAME`) AS PROVINCE_NAME"), 'PROVINCE_ID')->pluck( 'PROVINCE_NAME', 'PROVINCE_ID')->toArray();
            }
            $provinces = self::$data_provinces_lsit;

            //อำเภอ
            if( !is_array(self::$data_districts_list) ){

                $districts = District::selectRaw("
                                                    AMPHUR_ID,
                                                    IF(POSITION('เขต' IN TRIM(`AMPHUR_NAME`)) = 1,
                                                        REPLACE(TRIM(`AMPHUR_NAME`), 'เขต', '')
                                                    ,
                                                        IF(POSITION('อำเภอ' IN TRIM(`AMPHUR_NAME`)) = 1,
                                                            REPLACE(TRIM(`AMPHUR_NAME`), 'อำเภอ', '')
                                                        ,
                                                            TRIM(`AMPHUR_NAME`)
                                                        )
                                                    ) AS AMPHUR_NAME,
                                                    PROVINCE_ID
                                                ")
                                                ->where(DB::raw("REPLACE(AMPHUR_NAME,' ','')"),  'NOT LIKE', "%*%")
                                                ->get();

                $district_group_tmps = $districts->groupBy('PROVINCE_ID')->toArray();

                $district_groups = [];
                foreach ($district_group_tmps as $key => $tmp) {
                    $district_groups[$key] = collect($tmp)->pluck('AMPHUR_NAME', 'AMPHUR_ID')->toArray();
                }
                self::$data_districts_list       = $districts->pluck('AMPHUR_NAME', 'AMPHUR_ID')->toArray();
                self::$data_district_groups_list = $district_groups;
            }
            $districts = self::$data_districts_list;
            $district_groups = self::$data_district_groups_list;

            //ตำบล
            if( !is_array(self::$data_sub_districts_lsit) ){
                $sub_districts = Subdistrict::select('DISTRICT_ID', DB::raw("TRIM(`DISTRICT_NAME`) AS DISTRICT_NAME"), 'AMPHUR_ID')->where(DB::raw("REPLACE(DISTRICT_NAME,' ','')"),  'NOT LIKE', "%*%")->get()->makeHidden(['districtname', 'provincename']);
                $sub_district_group_tmps = collect($sub_districts->toArray())->groupBy('AMPHUR_ID')->toArray();
                $sub_district_groups     = [];
                foreach ($sub_district_group_tmps as $key => $tmp) {
                    $sub_district_groups[$key] = collect($tmp)->pluck('DISTRICT_NAME', 'DISTRICT_ID')->toArray();
                }
                self::$data_sub_districts_lsit = $sub_districts->pluck('DISTRICT_NAME', 'DISTRICT_ID')->toArray();
                self::$data_sub_district_groups_list = $sub_district_groups;

            }
            $sub_districts = self::$data_sub_districts_lsit;
            $sub_district_groups = self::$data_sub_district_groups_list;

            //รหัสไปรษณีย์
            if( !is_array(self::$data_zipcode_lsit) ){
                self::$data_zipcode_lsit =  DB::table((new Subdistrict)->getTable().' AS sub') // ตำบล
                                                    ->leftJoin((new Zipcode)->getTable().' AS code', 'code.district_code', '=', 'sub.DISTRICT_CODE')  // รหัสไปรษณีย์
                                                    ->select('sub.DISTRICT_ID', 'code.zipcode' )
                                                    ->pluck( 'zipcode', 'DISTRICT_ID')
                                                    ->toArray();
            }
            $zipcode = self::$data_zipcode_lsit;

            $province_ids = array_search($txt_pro, $provinces);
            $district_ids = array_search($txt_dis, $districts);
            $subdistrict_ids = array_search( $txt_sub , $sub_districts);

            if($province_ids!==false){
                $data->province_id = $province_ids;
            }else{
                $data->province_id = null;
            }

            if($province_ids!==false && $district_ids!==false){
                $district_ids = array_key_exists($province_ids, $district_groups) ? array_search( $txt_dis , $district_groups[ $province_ids ]) : false;
                $data->district_id = ( $district_ids!==false ? $district_ids : null );
            }else{
                $data->district_id = null;
            }

            if($district_ids!==false && $subdistrict_ids!==false){
                $subdistrict_ids = array_key_exists($district_ids, $sub_district_groups) ? array_search( $txt_sub , $sub_district_groups[ $district_ids ]) : false;
                $data->subdistrict_id = ( $subdistrict_ids!==false ? $subdistrict_ids : null );
            }else{
                $data->subdistrict_id = null;
            }

            if( $subdistrict_ids!==false ){
                $data->zipcode = array_key_exists(  $subdistrict_ids , $zipcode )?$zipcode[ $subdistrict_ids ]:null;
            }else{
                $data->zipcode = null;
            }

        }else{
            $data->province_id = null;
            $data->district_id = null;
            $data->subdistrict_id = null;
            $data->zipcode = null;
        }

        return $data;
    }

    //ประเภทการลงทะเบียน
    static function applicant_types(){
        return [
                '1' => 'นิติบุคคล',
                '2' => 'บุคคลธรรมดา',
                '3' => 'คณะบุคคล',
                '4' => 'ส่วนราชการ',
                '5' => 'อื่นๆ'
               ];
    }

    //ประเภทนิติบุคคล
    static function juristic_types(){
        return [
                '1' => 'บริษัทจำกัด',
                '2' => 'บริษัทมหาชนจำกัด',
                '3' => 'ห้างหุ้นส่วนจำกัด',
                '4' => 'ห้างหุ้นส่วนสามัญนิติบุคคล'
               ];
    }

    //ประเภทสาขา
    static function branch_types(){
        return [
                '1' => 'สำนักงานใหญ่',
                '2' => 'สาขา'
               ];
    }

    static function buttonAction($id, $action_url, $controller_action, $str_slug_name, $show_view = true, $show_edit = true, $show_delete = true, $disabled = [])
    {
        $form_action = '';

        if( $show_view == true ){
            $form_action .= '<a href="' . url('/' . $action_url . '/' . $id) . '"title="View ' . substr($str_slug_name, 0, -1) . '" class="btn btn-info btn-xs"><i class="fa fa-eye"></i></a>';
        }elseif( $show_view == false && in_array( 'view' , $disabled  ) ){
            $form_action .= '<button class="btn btn-info btn-xs" disabled><i class="fa fa-eye"></i></button>';
        }

        if( $show_edit == true ){
            $form_action .= ' <a href="' . url('/' . $action_url . '/' . $id . '/edit') . '" title="Edit ' . substr($str_slug_name, 0, -1) . '" class="btn btn-warning btn-xs"><i class="fa fa-pencil-square-o"></i></a>';
        }elseif( $show_edit == false && in_array( 'edit' , $disabled  ) ){
            $form_action .= ' <button class="btn btn-warning btn-xs" disabled><i class="fa fa-pencil-square-o"></i></button>';
        }

        if( $show_delete == true ){
            $form_action .= '<form action="' . action($controller_action, ['id' => $id]) . '" method="POST" style="display:inline">' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="btn btn-danger btn-xs" title="Delete ' . substr($str_slug_name, 0, -1) . '" onclick="return confirm_delete()"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                            </form>';
        }elseif( $show_delete == false && in_array( 'delete' , $disabled  ) ){
            $form_action .= ' <button class="btn btn-danger btn-xs" disabled><i class="fa fa-trash-o"></i></button>';
        }


        return $form_action;
    }

    static function  replace_address($address,$moo ='',$soi ='',$road ='')
    {
        $address =  str_replace("  "," ",$address);
        $address =  str_replace($moo,"",$address);
        $address =  str_replace("หมู่ที่","",$address);
        $address =  str_replace($soi,"",$address);
        $address =  str_replace("ซอย","",$address);
        $address =  str_replace($road,"",$address);
        $address =  str_replace("ถนน","",$address);
        return $address;
    }

    static function replace_multi_space($string){//ลบช่องว่างที่อยู่ติดกันให้เหลือเว้นแค่ 1 ช่อง

        replace_space:
        $string = str_replace('  ', ' ', trim($string));
        if(mb_strpos($string, '  ')!==false){//ยังมีการช่องว่างมากกว่า 1 ช่องติดกัน
            goto replace_space;
        }

        return $string;

    }

    //จัดข้อมูลที่อยู่จาก API นิติบุคคลจาก DBD
    //$address=Object ที่อยู่
    static function format_address_company_api($address)
    {

        $FullAddress = $address->FullAddress;

        $address_no = $building = $floor = $room_no = $village_name = $moo = $soi = $road = null;

        //ค้นหาคู่วงเล็บได้เป็นชุด array
        $brackets = self::search_brackets($FullAddress);

        //ค้นหาอาคาร
        $index_building = mb_strpos($FullAddress, 'อาคาร');

        //ค้นหาชั้นที่
        $index_floor = mb_strpos($FullAddress, 'ชั้นที่');

        //ค้นหาห้องเลขที่
        $index_room_no = mb_strpos($FullAddress, 'ห้องเลขที่');

        //ค้นหาหมู่บ้าน
        $index_village_name = mb_strpos($FullAddress, 'หมู่บ้าน');

        //ค้นหาหมู่
        $index_moo = mb_strpos($FullAddress, 'หมู่ที่');

        //ค้นหาซอย
        $index_soi = mb_strpos($FullAddress, 'ซอย');
        $index_soi = self::check_between($index_soi, $brackets)===false ? $index_soi : false ; //ถ้าซอยอยู่ในวงเล็บถือว่าไม่มี

        //ค้นหาถนน
        $index_road = mb_strpos($FullAddress, 'ถนน');
        $index_road = self::check_between($index_road, $brackets)===false ? $index_road : false ; //ถ้าถนนอยู่ในวงเล็บถือว่าไม่มี

        //หาเลขที่
        $address_no = self::cut_string($FullAddress, 0, [$index_moo, $index_soi, $index_road]);

        //หาซอยที่ไม่มีคำว่าซอยนำหน้า
        if(!empty($address->Soi)){
            $index_soi_name = mb_strpos($address_no, $address->Soi);
            if($index_soi===false && $index_soi_name!==false){
                if($index_building!==false && ($index_soi_name-($index_building+5) <= 1)){//ถ้ามีอาคารและตำแหน่งใกล้กับซอย ให้ไปค้นคำถัดไป
                    $index_soi_name = mb_strpos($address_no, $address->Soi, ($index_soi_name+1));
                }
                if($index_village_name!==false && ($index_soi_name-($index_village_name+7) <= 1)){//ถ้ามีหมู่บ้านและตำแหน่งใกล้กับซอย ให้ไปค้นคำถัดไป
                    $index_soi_name = mb_strpos($address_no, $address->Soi, ($index_soi_name+1));
                }
                $address_no = self::cut_string($address_no, 0, [$index_soi_name]);
            }
        }

        //หาถนนที่ไม่มีคำว่าถนนนำหน้า
        if(!empty($address->Road)){
            $index_road_name = mb_strpos($address_no, $address->Road);
            if($index_road===false && $index_road_name!==false){
                if($index_building!==false && ($index_road_name-($index_building+5) <= 1)){//ถ้ามีอาคารและตำแหน่งใกล้กับซอย ให้ไปค้นคำถัดไป
                    $index_road_name = mb_strpos($address_no, $address->Road, ($index_road_name+1));
                }
                if($index_village_name!==false && ($index_road_name-($index_village_name+7) <= 1)){//ถ้ามีหมู่บ้านและตำแหน่งใกล้กับซอย ให้ไปค้นคำถัดไป
                    $index_road_name = mb_strpos($address_no, $address->Road, ($index_road_name+1));
                }
                $address_no = self::cut_string($address_no, 0, [$index_road_name]);
            }
        }

        //หาชื่ออาคาร
        if($index_building!==false){
            $building = self::cut_string($FullAddress, $index_building, [$index_floor, $index_room_no, $index_village_name, $index_moo, $index_soi, $index_road]);
        }

        //หาชื่อชั้น
        if($index_floor!==false){
            $floor = self::cut_string($FullAddress, $index_floor, [$index_room_no, $index_village_name, $index_moo, $index_soi, $index_road]);
        }

        //หาชื่อห้อง
        if($index_room_no!==false){
            $room_no = self::cut_string($FullAddress, $index_room_no, [$index_village_name, $index_moo, $index_soi, $index_road]);
        }

        //หาชื่อหมู่บ้าน
        if($index_village_name!==false){
            $village_name = self::cut_string($FullAddress, $index_village_name, [$index_moo, $index_soi, $index_road]);
        }

        //หาชื่อหมู่ที่
        if($index_moo!==false){
            $moo = self::cut_string($FullAddress, $index_moo, [$index_soi, $index_road]);
            $moo = self::replace_multi_space(mb_substr($moo, mb_strlen('หมู่ที่')));

            //หาตัวแบ่งหมู่ที่เป็นตัวเลขกับข้อความหลังจากนั้น
            $index_moo_sub = 0;
            $moos = mb_str_split($moo);
            foreach ($moos as $key => $moo_str) {
                if(!is_numeric($moo_str)){
                    $index_moo_sub = $key;
                    break;
                }else{
                    $index_moo_sub = mb_strlen($moo);
                }
            }

            $moo_number = self::cut_string($moo, 0, [$index_moo_sub]);

            if($index_moo_sub!=0){//มีข้อความหลังหมู่
                $moo_after  = trim(mb_substr($moo, $index_moo_sub));//ข้อความหลังหมู่
                $address_no = $address_no.' '.$moo_after;//ต่อข้อความไปหลังเลขที่
            }

            $moo = $moo_number;//หมู่เอาแต่ตัวเลข

        }

        //หาชื่อซอย
        if($index_soi!==false){
            $soi = self::cut_string($FullAddress, $index_soi, [$index_road]);
        }

        //หาชื่อถนน
        if($index_road!==false){
            $road = self::cut_string($FullAddress, $index_road, [mb_strlen($FullAddress)]);
        }

        if((is_null($address->AddressNo) && !is_null($address_no)) || (strlen(trim($address->AddressNo)) > strlen(trim($address_no)) && !is_null($address_no))){//ถ้าเลขที่ในข้อมูลย่อยไม่มีให้เอาไปใส่แทน หรือข้อมูลย่อยยาวกว่า
            $address->AddressNo = self::replace_multi_space($address_no);
        }

        if(is_null($address->Building) && !is_null($building)){//ถ้าหมู่ที่ในข้อมูลย่อยไม่มีให้เอาไปใส่แทน
            $address->Building = self::replace_multi_space(mb_substr($building, mb_strlen('อาคาร')));
        }elseif(!is_null($address->Building)){
            $address->Building = trim($address->Building);
            $address->Building = !empty($address->Building) && mb_strpos($address->Building, 'อาคาร')===0 ? trim(mb_substr($address->Building, 5)) : $address->Building ; //ตัดคำว่าอาคาร คำแรกออก
        }

        if(!empty($moo)){//ถ้าหมู่ที่ในข้อมูลย่อยไม่มีให้เอาไปใส่แทน
            $address->Moo = $moo;
        }elseif(!is_null($address->Moo)){
            $address->Moo = trim($address->Moo);
            $address->Moo = !empty($address->Moo) && mb_strpos($address->Moo, 'หมู่ที่')===0 ? trim(mb_substr($address->Moo, 7)) : $address->Moo ; //ตัดคำว่าหมู่ที่ คำแรกออก
            $address->Moo = !empty($address->Moo) && mb_strpos($address->Moo, 'ซอย') !== false ? trim(mb_substr($address->Moo, 0, mb_strpos($address->Moo, 'ซอย'))) : $address->Moo; //ตัดซอยออกถ้ามีรวมอยู่ด้วย
        }

        if(is_null($address->Soi) && !is_null($soi)){//ถ้าซอยในข้อมูลย่อยไม่มีให้เอาไปใส่แทน
            $address->Soi = self::replace_multi_space(mb_substr($soi, mb_strlen('ซอย')));
        }elseif(!is_null($address->Soi)){
            $address->Soi = trim($address->Soi);
            $address->Soi = !empty($address->Soi) && mb_strpos($address->Soi, 'ซอย')===0 ? trim(mb_substr($address->Soi, 3)) : $address->Soi ; //ตัดคำว่าซอย คำแรกออก
        }

        if(is_null($address->Road) && !is_null($road)){//ถ้าถนนในข้อมูลย่อยไม่มีให้เอาไปใส่แทน
            $address->Road = self::replace_multi_space(mb_substr($road, mb_strlen('ถนน')));
        }elseif(!is_null($address->Road)){
            $address->Road = trim($address->Road);
            $address->Road = !empty($address->Road) && mb_strpos($address->Road, 'ถนน')===0 ? trim(mb_substr($address->Road, 3)) : $address->Road ; //ตัดคำว่าถนน คำแรกออก
        }

        $address->Tumbol = trim($address->Tumbol);
        $address->Tumbol = !empty($address->Tumbol) && (mb_strpos($address->Tumbol, 'แขวง')===0 || mb_strpos($address->Tumbol, 'ตำบล')===0) ? trim(mb_substr($address->Tumbol, 4)) : $address->Tumbol ; //ตัดคำว่าตำบล/แขวง คำแรกออก

        $address->Ampur = trim($address->Ampur);
        $address->Ampur = !empty($address->Ampur) && mb_strpos($address->Ampur, 'อำเภอ')===0 ? trim(mb_substr($address->Ampur, 5)) : $address->Ampur ; //ตัดคำว่าอำเภอ คำแรกออก
        $address->Ampur = !empty($address->Ampur) && mb_strpos($address->Ampur, 'เขต')===0 ? trim(mb_substr($address->Ampur, 3)) : $address->Ampur ; //ตัดคำว่าเขต คำแรกออก

        //เปลี่ยนข้อมูลเลขที่ใหม่
        $address->AddressNo = self::replace_multi_space($address_no);

        return $address;
    }

    static function cut_string($FullAddress, $index_source, $index_compares){

        sort($index_compares);

        $result = null;

        foreach ($index_compares as $key => $index_compare) {
            if($index_compare!==false){//ถ้าพบข้อมูล
                $result = mb_substr($FullAddress, $index_source, $index_compare-$index_source);
                break;
            }
        }

        if(is_null($result)){//ถ้าเป็น null แสดงว่าเป็นคำสุดท้ายของ FullAddress
            $result = mb_substr($FullAddress, $index_source);
        }

        return $result;

    }

    //หาตำแหน่งคู่วงเล็บในข้อความ ไม่รองรับวงเล็บที่ซ้อนกัน
    static function search_brackets($input){

        //เก็บตำแหน่งวงเล็บเปิดทั้งหมด
        $needle = "(";
        $lastPos = 0;
        $opens = array();

        while (($lastPos = mb_strpos($input, $needle, $lastPos))!== false) {
            $opens[] = $lastPos;
            $lastPos = $lastPos + mb_strlen($needle);
        }

        //เก็บตำแหน่งวงเล็บปิดทั้งหมด
        $needle = ")";
        $lastPos = 0;
        $closes = array();

        while (($lastPos = mb_strpos($input, $needle, $lastPos))!== false) {
            $closes[] = $lastPos;
            $lastPos  = $lastPos + mb_strlen($needle);
        }

        $results = [];
        foreach ($opens as $key => $open) {
            $results[] = (object)['open' => $open, 'close' => $closes[$key]];
        }

        return $results;

    }

    //เช็ค $index ว่ามีค่าอยู่ระหว่าง $brackets หรือไม่
    static function check_between($index, $brackets){
        $result = false;
        foreach ($brackets as $bracket) {
            if($index > $bracket->open && $index < $bracket->close){
                $result = true;
                break;
            }
        }
        return $result;
    }

    //จัดข้อมูลที่อยู่จาก API ผู้เสียภาษีจาก RD
    //$rd=Object ที่ได้จาก API
    static function format_address_rd_api($rd)
    {
        $rd->vName         = $rd->vName=='-'         ? null : self::replace_multi_space($rd->vName) ;
        $rd->vTitleName    = $rd->vTitleName=='-'    ? null : self::replace_multi_space($rd->vTitleName) ;
        $rd->vHouseNumber  = $rd->vHouseNumber=='-'  ? null : self::replace_multi_space($rd->vHouseNumber) ;
        $rd->vBuildingName = $rd->vBuildingName=='-' ? null : self::replace_multi_space($rd->vBuildingName) ;
        $rd->vFloorNumber  = $rd->vFloorNumber=='-'  ? null : self::replace_multi_space($rd->vFloorNumber) ;
        $rd->vRoomNumber   = $rd->vRoomNumber=='-'   ? null : self::replace_multi_space($rd->vRoomNumber) ;
        $rd->vVillageName  = $rd->vVillageName=='-'  ? null : self::replace_multi_space($rd->vVillageName) ;
        $rd->vMooNumber    = $rd->vMooNumber=='-'    ? null : self::replace_multi_space($rd->vMooNumber) ;
        $rd->vSoiName      = $rd->vSoiName=='-'      ? null : self::replace_multi_space($rd->vSoiName) ;
        $rd->vStreetName   = $rd->vStreetName=='-'   ? null : self::replace_multi_space($rd->vStreetName) ;
        $rd->vThambol      = $rd->vThambol=='-'      ? null : self::replace_multi_space($rd->vThambol) ;
        $rd->vAmphur       = $rd->vAmphur=='-'       ? null : self::replace_multi_space($rd->vAmphur) ;
        $rd->vProvince     = $rd->vProvince=='-'     ? null : self::replace_multi_space($rd->vProvince) ;

        //วันที่จดทะเบียนนิติบุคคล
        $rd->vBusinessFirstDate = str_replace('/', '-', $rd->vBusinessFirstDate);
        $vBusinessFirstDates    = explode('-', $rd->vBusinessFirstDate);
        if(count($vBusinessFirstDates)===3){
            if(strlen($vBusinessFirstDates['0'])===4){ //แบบ ปี-เดือน-วัน
                $rd->vBusinessFirstDate = $rd->vBusinessFirstDate ;
            }elseif(strlen($vBusinessFirstDates['2'])===4){ //แบบ วัน-เดือน-ปี หรือ เดือน-วัน-ปี
                if(in_array($rd->vBranchTitleName, ['ห้างหุ้นส่วนสามัญ', 'สหกรณ์', 'มหาวิทยาลัย', 'โรงเรียน', 'กิจการร่วมค้า']) || (int)$vBusinessFirstDates['1']>12){ //เดือน-วัน-ปี กรณี ห้างหุ้นส่วนสามัญ หรือวัน > 12 คือเดือน
                    $rd->vBusinessFirstDate = $vBusinessFirstDates['2'].'-'.str_pad($vBusinessFirstDates['0'], 2, "0", STR_PAD_LEFT).'-'.str_pad($vBusinessFirstDates['1'], 2, "0", STR_PAD_LEFT);
                }else{ //วัน-เดือน-ปี
                    $rd->vBusinessFirstDate = $vBusinessFirstDates['2'].'-'.$vBusinessFirstDates['1'].'-'.$vBusinessFirstDates['0'];
                }
            }
        }else{
            $rd->vBusinessFirstDate = null ;
        }

        if((!is_null($rd->vTitleName) && !is_null($rd->vName)) && mb_strpos($rd->vName, $rd->vTitleName)===false){//ไม่มีคำนำหน้าเติมให้
            $rd->vName = $rd->vTitleName.$rd->vName;
        }

        $rd->vHouseNumber = $rd->vHouseNumber.' '.$rd->vBuildingName.' '.$rd->vFloorNumber.' '.$rd->vRoomNumber.' '.$rd->vVillageName;//รวมเป็นฟิลด์เดียว
        $rd->vHouseNumber = self::replace_multi_space($rd->vHouseNumber);//รวม เลขที่ อาคาร ชั้น ห้อง หมู่บ้าน

        return $rd;
    }


    static function ApplicationStatusIBCB(){

        return [
                    1 => 'อยู่ระหว่างการตรวจสอบ',
                    2 => 'เอกสารไม่ครบถ้วน',
                    3 => 'เอกสารครบถ้วน อยู่ระหว่างตรวจประเมิน',
                    4 => 'เอกสารครบถ้วน อยู่ระหว่างสรุปรายงาน',
                    5 => 'ตรวจสอบเอกสารอีกครั้ง',
                    6 => 'ไม่รับคำขอ/Reject',
                    7 => 'ไม่ผ่านการตรวจประเมิน',
                    8 => 'อยู่ระหว่างการพิจารณาอนุมัติ',
                    9 => 'อนุมัติ อยู่ระหว่างเสนอคณะอนุกรรมการ',
                    10 => 'ไม่อนุมัติ ตรวจสอบอีกครั้ง',
                    11 => 'อยู่ระหว่างเสนอ กมอ. และจัดทำประกาศ',
                    12 => 'ไม่ผ่าน มติคณะอนุกรรมการ',
                    13 => 'ประกาศราชกิจจาฯ'
        ];

    }

    static function blur_email($email){//ใส่ * ไปในแทรกไปใน email
        $result = null;
        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $emails = explode('@', $email);

            $name_length = ceil(strlen($emails[0])/4);
            $name_text   = substr($emails[0], 0, strlen($emails[0])-$name_length).str_repeat('*', $name_length);

            $domain_text = $emails[1];

            $result = $name_text.'@'.$domain_text;
        }
        return $result;
    }


    static function ConfigFormat( $systems , $table , $column, $application_type = null , $tisi_shortnumber = null, $tisi_number = null   )
    {
        $config = ConfigsFormatCode::where( 'system', $systems)->where('state', 1)->first();

        $today = date('Y-m-d');
        $dates = explode('-', $today);

        $ref_no_run = null;

        $Item_format = [];
        $year_bf_check = false;

        if( !is_null($config) ){

            $sub = ConfigsFormatCodeSub::where('format_id', $config->id )->select( 'format','data','sub_data' )->get();

            //ข้อมูลเลขรัน
            $datas_set_no = null;
            $datas_key_no = null;

            $array_format = [];
            $strNextSeq = '';

            $json =  (count( $sub ) > 0 )?json_encode( $sub, JSON_UNESCAPED_UNICODE ):null;

            $right_no = false;
            $right_arr = [];
            $left_arr = [];

            //วนรูปแบบ
            foreach( $sub AS $key => $item ){

                $format = $item->format;

                $dataSet = null;

                if( $format == 'character' ){ //อักษรนำ
                    $dataSet .= !empty($item->data)?$item->data:null;
                }else if( $format == 'separator' ){ //อักษรคั่น
                    $dataSet .= !empty($item->data)?$item->data:null;
                }else if( $format == 'month' ){ //เดือน
                    $dataSet .= $dates[1];
                }else if( $format == 'year-be' ){ //ปี พ.ศ.

                    if( $item->data == '4'){
                        $dataSet = $dates[0] + 543;
                    }else{
                        $dataSet = (substr( ($dates[0] + 543) , 2) );
                    }

                }else if( $format == 'year-bf' ){ //ปี พ.ศ.ตามปีงบประมาณ

                    $yaer  = ( $dates[0] >= 10 )?($dates[0] + 544):($dates[0] + 543);

                    if( $item->data == '4'){
                        $dataSet = $yaer;
                    }else{
                        $dataSet = (substr( $yaer , 2) );
                    }
                    $year_bf_check = true;
                }else if( $format == 'year-ac' ){ //ปี ค.ศ.

                    if( $item->data == '4'){
                        $dataSet = $dates[0];
                    }else{
                        $dataSet = (substr( ($dates[0]) , 2) );
                    }

                }else if( $format == 'no' ){ //เลขรัน

                    $numbers = !empty($item->data) ?($item->data):0;

                    $number_set = !empty($item->data) && ($item->data >= 2) ?($item->data - 1):0;

                    $zero = str_repeat( '0',  $number_set  );

                    $datas_set_no = $item;
                    $datas_key_no = $key;

                    $dataSet =  substr( $zero .(1),- $numbers,  $numbers );

                }

                if(  $right_no === true ){
                    $right_arr[ $format ] = strlen( $dataSet );
                }

                if( $format == 'tisi_shortnumber' ){ // เลขที่มอก
                    $dataSet = !empty($tisi_shortnumber)?$tisi_shortnumber:null;

                    $right_no = true;

                }else if( $format == 'tisi_number' ){ //รหัสมาตรฐาน
                    $dataSet = !empty($tisi_number)?$tisi_number:null;

                    $right_no = true;

                }else if( $format == 'application_type' ){ //ประเภทใบสมัคร
                    $dataSet = !empty($application_type)?$application_type:null;
                }

                if( $right_no === false ){
                    $left_arr[ $format ] = strlen( $dataSet );
                }

                $dataSet = !empty($dataSet)?str_replace(' ', '', $dataSet):null;

                $item->data_set = $dataSet;

                $array_format[ $key ] = $item;

                $Item_format[ $key ] = (string)$dataSet;

                $strNextSeq .= $dataSet;

            }

            //ถ้ามีเลขรัน format = no
            if( !is_null($datas_set_no) ){

                $font_search = null;
                $back_search = null;

                //หาข้อความก่อน เลขรัน
                foreach( $array_format AS $kf => $Fitem ){

                    if( $Fitem->format != 'tisi_shortnumber' && $Fitem->format != 'tisi_number' ){

                        if( $kf < $datas_key_no ){
                            $font_search .= $Fitem->data_set;
                            if( $Fitem->format != 'separator' ){
                                break;
                            }
                        }

                    }

                }

                //หาข้อความหลัง เลขรัน
                foreach( $array_format AS $kf => $Fitem ){

                    if( $Fitem->format != 'tisi_shortnumber' && $Fitem->format != 'tisi_number' ){

                        if( $kf > $datas_key_no ){
                            $back_search .= $Fitem->data_set;
                            if( $Fitem->format != 'separator' ){
                                break;
                            }

                        }

                    }

                }

                // หาช่วงการรันต่อ
                $sub_data = $datas_set_no->sub_data;

                if( $sub_data == 'o'){
                    $query_check = DB::table( $table )
                                        ->where(function($query) use($column, $font_search, $back_search ){
                                            if( !empty($font_search) ){
                                                $query->where($column, 'LIKE', "%$font_search%");
                                            }else if( empty($font_search) && !empty($back_search) ){
                                                $query->where($column, 'LIKE', "%$back_search%");
                                            }
                                        })
                                        ->select($column, 'created_at' )
                                        ->orderBy($column)
                                        ->get();
                }else if( $sub_data == 'm'){
                    $query_check = DB::table( $table )
                                        ->select($column, 'created_at' )
                                        ->where(function($query) use($column, $font_search, $back_search ){
                                            if( !empty($font_search) ){
                                                $query->where($column, 'LIKE', "%$font_search%");
                                            }else if( empty($font_search) && !empty($back_search) ){
                                                $query->where($column, 'LIKE', "%$back_search%");
                                            }
                                        })
                                        ->where(function($query) use($dates){
                                            $query->whereYear('created_at',$dates[0])->whereMonth('created_at', $dates[1] );
                                        })
                                        ->orderBy($column)
                                        ->get();
                }else if( $sub_data == 'y'){
                    $query_check = DB::table( $table )
                                        ->select($column, 'created_at' )
                                        ->where(function($query) use($column, $font_search, $back_search ){
                                            if( !empty($font_search) ){
                                                $query->where($column, 'LIKE', "%$font_search%");
                                            }else if( empty($font_search) && !empty($back_search) ){
                                                $query->where($column, 'LIKE', "%$back_search%");
                                            }
                                        })
                                        ->where(function($query) use($dates, $year_bf_check){
                                            if( $year_bf_check == false ){ // ตามปี
                                                $query->whereYear('created_at',$dates[0]);
                                            }else{ // ตามปีงบ
                                                $startDate = \Carbon\Carbon::parse( $dates[0].'-10-01' )->format('Y-m-d');
                                                $endDate   = \Carbon\Carbon::parse( $dates[0].'-09-30' )->addYears(1)->format('Y-m-d');
                                                $query->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
                                            }
                                        })
                                        ->orderBy($column)
                                        ->get();
                }

                $numbers = !empty($datas_set_no->data) ?($datas_set_no->data):0;

                $number_set = !empty($datas_set_no->data) && ($datas_set_no->data >= 2) ?($datas_set_no->data - 1):0; //จำนวนหลักเฉพาะเลขรัน ที่ -1

                $number_search = !empty($datas_set_no->data)?$datas_set_no->data:0; //จำนวนหลักเฉพาะเลขรัน

                $zero = str_repeat( '0',  $number_set  );

                $query = 0;
                if(count($query_check) != 0){

                    $last_data = $query_check->last();

                    $checks_state = false;

                    $log_old = ConfigsFormatCodeLog::where('format_id', $config->id )
                                                    ->where( 'system', $systems)
                                                    ->select('data', 'start_date', 'end_date', 'state')
                                                    ->get();
                    foreach( $log_old AS $old ){

                        //หา Format ที่ใช้งาน ก่อนหน้าว่ารูปแบบตรงกันไหม
                        if( mb_strpos($old->data, $json ) !== false  ){

                            //หาจากช่วงวันที่ จากใบก่อนหน้า
                            if( !is_null($old->end_date) && ( $last_data->created_at >= $old->start_date) && ( $last_data->created_at <= $old->end_date) ){
                                $checks_state = true;
                                break;
                            }else if( ( $last_data->created_at >= $old->start_date ) && is_null($old->end_date) ){ //หาจากช่วงวันที่ จากก่อนหน้า ที่ใช้งานปัจจุบัน
                                $checks_state = true;
                                break;
                            }
                        }
                    }

                    // Format ที่ใช้งาน
                    if( $checks_state === true ){

                        $_no =  $last_data->{$column};
                        $_no = !empty($_no)?str_replace(' ', '', $_no):null;

                        $_arr_format = [];

                        if( $right_no === true){
                            $sum = array_sum($right_arr);
                            $suc_string = mb_substr($_no,  - $sum ,  $sum );
                            $_arr_format = $right_arr;

                        }else{

                            $sum = array_sum($left_arr);
                            $suc_string = mb_substr($_no,  - $sum ,  $sum );
                            $_arr_format = $left_arr;
                        }

                        foreach(  $_arr_format AS $ka => $Atiem  ){

                            if( $ka == 'no' ){

                                $next = next( $_arr_format ) === false ? strlen($suc_string):$Atiem;

                                $suc_string = mb_substr( $suc_string , 0,  $next );
                                break;
                            }
                            $suc_string = mb_substr( $suc_string , $Atiem  );

                        }

                        $Max = str_repeat( '9',  $number_search  ); //ค่า MAX ของเลขรัน
                        $check_number_max = ($Max == $suc_string)?( $suc_string + 1 ):$suc_string; //เช็คว่าเกินค่า Max หรือยัง

                        if( mb_strlen( $check_number_max  ) > (int)$numbers ){ //กรณีที่รันเกินจำนวนหลักเลขรัน
                            $Seq_max = substr( $zero .( (int)$suc_string + 1 ),- $numbers,  $numbers );
                            $Seq = ( $check_number_max ) + (int)$Seq_max;

                        }else{
                            $Seq = substr( $zero .( (int)$suc_string + 1 ),- $numbers,  $numbers );
                        }

                    }else{
                        $Seq = substr( $zero .(1),- $numbers,  $numbers );
                    }

                }else{
                    $Seq = substr( $zero .(1),- $numbers,  $numbers );
                }

                $Item_format[ $datas_key_no ]=  $Seq;
                $check_run = implode('', $Item_format );

                $no_check = DB::table( $table )->where($column, $check_run )->first();
                if(is_null($no_check)){
                    $Item_format[ $datas_key_no ]=  $Seq;
                }else{
                    $Seq = substr( $zero .( (int)$Seq + 1 ),- $numbers,  $numbers );
                    $Item_format[ $datas_key_no ]=  $Seq;
                }
            }

        }

        return implode('', $Item_format );
    }

    static function getJuristic($JuristicID, $ip)
    {//รับค่า $JuristicID=เลขทะเบียนพาณิชน์ที่ต้องการทราบข้อมูล

        $response = (object)[];

        $config = HP::getConfig();

        $url = $config->tisi_api_corporation_url; //'https://www3.tisi.go.th/moiapi/srv.asp?pid=1';
        $data = array(
                'val' => $JuristicID,
                'IP' => $ip, // IP Address,
                'Refer' => 'sso.tisi.go.th'
                );
        $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                    'timeout' => 30
                )
        );
        if(strpos($url, 'https')===0){//ถ้าเป็น https
            $options["ssl"] = array(
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                              );
        }
        $context = stream_context_create($options);
        $request_start = date('Y-m-d H:i:s');
        $api = null ;

        try {
            $json_data = file_get_contents($url, false, $context);
            $api = json_decode($json_data);
            $response = $api;
            if(!empty($api->JuristicName_TH)){
                $response->status = 'success';
            }elseif(!empty($api->Message)){
                $response->Result = $api->Message;
            }else{
                $response->status = 'fail';
            }
        } catch (\Exception $e) {
            $response->status = 'no-connect';
        }

        //บันทึก Log
        MOILog::Add($JuristicID, $url, 'corporation', $request_start, @$http_response_header, ($response->status!='success' ? $api : null));

        return $response;

    }

    static function getPersonal($PersonalID, $ip)
    {//รับค่า $PersonalID=เลขประจำตัวประชาชนที่ต้องการทราบข้อมูล

        $person = null;

        $config = HP::getConfig();

        $url = $config->tisi_api_person_url; //'https://www3.tisi.go.th/moiapi/srv.asp?pid=2';
        $data = array(
                'val'   => $PersonalID,
                'IP'    => $ip,
                'Refer' => 'sso.tisi.go.th'
                );
        $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                    'timeout' => 30
                )
        );
        if(strpos($url, 'https')===0){//ถ้าเป็น https
            $options["ssl"] = array(
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                              );
        }
        $context  = stream_context_create($options);
        $request_start = date('Y-m-d H:i:s');
        $api = null ;

        try {
            $json_data = file_get_contents($url, false, $context);
            $api = json_decode($json_data);
            $person = $api;
            if(!empty($api->firstName)){
                $person->status = 'success';
            }else{
                $person->status = 'fail';
            }
        } catch (\Exception $e) {
            $person         = (object)[];
            $person->status = 'no-connect';
        }

        //บันทึก Log
        MOILog::Add($PersonalID, $url, 'person', $request_start, @$http_response_header, ($person->status!='success' ? $api : null));

        return $person;

    }

    //ดึงข้อมูลจากกรมสรรพากร
    static function getRdVat($JuristicID, $ip){//รับค่า $JuristicID=เลขประจำตัวผู้เสียภาษีที่ต้องการทราบข้อมูล

        $response = null;

        $config = HP::getConfig();

        $url = $config->tisi_api_faculty_url; //'https://www3.tisi.go.th/moiapi/srv.asp?pid=5';
        $data = array(
                'val' => $JuristicID,
                'IP' => $ip, // IP Address,
                'Refer' => 'sso.tisi.go.th'
                );
        $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                    'timeout' => 30
                )
        );
        if(strpos($url, 'https')===0){//ถ้าเป็น https
            $options["ssl"] = array(
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                              );
        }
        $context = stream_context_create($options);
        $request_start = date('Y-m-d H:i:s');
        $api = null ;

        try {
            $json_data = file_get_contents($url, false, $context);
            $api = json_decode($json_data);
            $response = $api;
            if(empty($api->vMessageErr)){
                $response->status = 'success';
            }else{
                $response->status = 'fail';
            }
        } catch (\Exception $e) {
            $response         = (object)[];
            $response->status = 'no-connect';
        }

        //บันทึก Log
        MOILog::Add($JuristicID, $url, 'rd', $request_start, @$http_response_header, ($response->status!='success' ? $api : null));

        return $response;

    }

    //ลบตัวอักษรบางตัวออก
    static function replace_html($input){
        $input = str_replace("'", '', $input);
        $input = str_replace('"', '', $input);
        $input = str_replace('<', '', $input);
        $input = str_replace('>', '', $input);
        $input = str_replace('[', '', $input);
        $input = str_replace(']', '', $input);
        return $input;
    }

    public static function dateTimeFormatN($date)
    {//$date format d/m/Y OR d/m/Y H.i น.

        $dates = explode('-', $date);
        if (count($dates) != 3) {
            return '-';
        }

        $time = explode(' ', $date);
        $times = isset($time[1])?$time[1]:null;
        if(!is_null($times)){
            $times       = explode(':', $times);
            $time_string = $times[0].'.'.$times[1].' น.';
        }else{
            $time_string = '';
        }

        $year = ($dates[0] + 543);

        return substr($dates[2], 0, 2) . '/' . $dates[1] . '/' . $year.' '.$time_string;
    }


    public static function LogInsertNotification( $ref_id, $ref_table, $raf_app_no, $status, $title, $detail, $url , $users_id , $type = 1 )
    {

        $check = LogNotification::where('ref_table', $ref_table )->where('ref_id', $ref_id )->where('ref_id', $ref_id )->select('status')->orderBy('id', 'desc')->first();

        if( $type == 1 ){

            if( is_null($check) || ( !is_null($check) && ( $check->status  != $status  )) ){

                $log = array();

                $log['title'] = !empty($title)?$title:null;
                $log['details'] = !empty($detail)?$detail:null;

                $log['ref_applition_no'] = !empty($raf_app_no)?$raf_app_no:null;
                $log['ref_table'] = !empty($ref_table)?$ref_table:null;
                $log['ref_id'] = !empty($ref_id)?$ref_id:null;
                $log['status'] = !empty($status)?$status:null;

                $log['site'] = 'center';
                $log['root_site'] = url('/');
                $log['url'] = !empty($url)?$url:null;

                $log['users_id'] = !empty($users_id)?$users_id:null;

                $log['type'] = !empty($type)?$type:null;

                $log['ref_table_user'] = (new User)->getTable() ;

                LogNotification::create($log);
            }

        }

    }

    //เช็คข้อมูลเป็นตัวเลขทั้งหมดหรือไม่ ครบ 13 หลักหรือไม่
    static function check_number_counter($input, $counter=13){
        $converted = preg_replace("/[^0-9]/", '', $input);
        return $converted===$input && strlen($input)===$counter ? true : false;
    }

    //แปลง 0 หรือ - หรือ empty เป็น null
    static function FormatToNull($input){
        return ($input==='0' || $input===0 || $input==='-' || $input==='') ? null : $input ;
    }
    
}
