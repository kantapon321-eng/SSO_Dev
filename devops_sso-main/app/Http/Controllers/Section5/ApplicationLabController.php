<?php

namespace App\Http\Controllers\Section5;

use App\Http\Controllers\Controller;
use App\Models\Basic\Tis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;
use HP;
use App\User;

use App\Models\Section5\ApplicationLab;
use App\Models\Section5\ApplicationLabCertificate;
use App\Models\Section5\ApplicationLabScope;

use App\Models\Bsection5\TestMethod;
use App\Models\Bsection5\Unit;
use App\Models\Bsection5\TestTool;
use App\Models\Bsection5\TestItemTools;
use App\Models\Bsection5\TestItem;

use App\Models\Certificate\CertificateExport;
use App\Models\Certificate\CertiLab;

use stdClass;
use App\Models\Section5\Labs;

use App\Models\Section5\ApplicationLabAccept;

class ApplicationLabController extends Controller
{
    private $attach_path;//ที่เก็บไฟล์แนบ
    public function __construct()
    {
        set_time_limit(0);
        $this->middleware('auth');
        $this->attach_path = 'files/sso';
    }

    public function data_list(Request $request)
    {

        $user = auth()->user();

        $filter_search = $request->get('filter_search');
        $filter_state = $request->get('filter_state');

        $query = ApplicationLab::query()->when( $filter_search , function ($query, $filter_search){
                                            $search_full = str_replace(' ', '', $filter_search);

                                            if( strpos( $search_full , 'LAB-' ) !== false){
                                                return $query->where('application_no',  'LIKE', "%$search_full%");
                                            }else{
                                                return  $query->where(function ($query2) use($search_full) {
                                                                    $query2->Where(DB::raw("REPLACE(applicant_name,' ','')"), 'LIKE', "%".$search_full."%")
                                                                            ->OrWhere(DB::raw("REPLACE(applicant_taxid,' ','')"), 'LIKE', "%".$search_full."%")
                                                                            ->OrWhere(DB::raw("REPLACE(lab_name,' ','')"), 'LIKE', "%".$search_full."%")
                                                                            ->Orwhere('application_no',  'LIKE', "%$search_full%");

                                                                });
                                            }
                                        })
                                        ->when($filter_state, function ($query, $filter_state){
                                            return $query->where('application_status', $filter_state );
                                        })
                                        ->when($user, function($query, $user){//ผปก.ยื่นเอง หรือไปยื่นแทนคนอื่น

                                            $user_act_instead = $user->ActInstead;//ดำเนินการแทนผปก.คนไหน null=ยื่นของตัวเอง
                                            if(is_null($user_act_instead)){//อยู่ในฐานะตัวเอง แสดงคำขอของตัวเอง
                                                $query->where('created_by', $user->getKey());
                                            }else{//อยู่ในฐานะตัวแทน แสดงที่ตัวเองยื่นในฐานนะตัวแทน และเป็นของผปก.ที่กำลังเป็นตัวแทนอยู่ขณะนี้
                                                $query->where('agent_id', $user->getKey())
                                                      ->where('created_by', $user_act_instead->getKey());
                                            }

                                        });

        return Datatables::of($query)
                            ->addIndexColumn()
                            ->addColumn('application_no', function ($item) {

                                $application_type_arr = [ 1 => 'ขอขึ้นทะเบียนใหม่', 2 => 'ขอเพิ่มเติมขอบข่าย', 3 => 'ขอลดขอบข่าย', 4 => 'ขอแก้ไขข้อมูล'];
                                $application_type = array_key_exists( $item->applicant_type,  $application_type_arr )?$application_type_arr [ $item->applicant_type ]:'-';

                                return '<div>'.(!empty($item->application_no)?$item->application_no:'-').'</div>'.(!empty($application_type)?'('.$application_type.')':'-');

                            })
                            ->addColumn('applicant_name', function ($item) {
                                return '<div>'.(!empty($item->lab_name)?$item->lab_name:'-').'</div>'.(!empty($item->applicant_name)?'('.$item->applicant_name.')':'-');
                            })
                            ->addColumn('applicant_taxid', function ($item) {
                                return !empty($item->applicant_taxid)?$item->applicant_taxid:'-';
                            })
                            ->addColumn('standards', function ($item) {
                                return !empty($item->ScopeStandard)?$item->ScopeStandard:'-';
                            })
                            ->addColumn('creater', function ($item) {
                                return $item->CreaterName;
                            })
                            ->addColumn('application_date', function ($item) {
                                return !empty($item->application_date)?HP::DateThai($item->application_date):'-';
                            })
                            ->addColumn('status_application', function ($item) {
                                if( !empty($item->delete_state) ){
                                    return (!empty($item->StatusTitle)?'<div class="text-danger">'.$item->StatusTitle.'<div>':'-').'<div><em>'.(!empty($item->delete_at)?HP::DateThai($item->delete_at):null).'</em><div>';
                                }else{
                                    return !empty($item->StatusTitle)?$item->StatusTitle:'ฉบับร่าง';
                                }
                            })
                            ->addColumn('action', function ($item) {
                                $edit = true;
                                $disabled = [];
                                if(!in_array($item->application_status, [2, 0])){
                                    $edit = false;
                                    array_push($disabled, 'edit');
                                }

                                $created_at = !empty($item->created_at) ? HP::DateTimeThai($item->created_at) : null;

                                $button = HP::buttonAction($item->id, 'request-section-5/application-lab', 'Section5\\ApplicationLabController@destroy', 'application-lab',  true, $edit, false, $disabled);
                                if( $item->application_status <= 13){
                                    $button .= " <button type='button' class='btn btn-danger btn-xs btn_delete' title='Delete application_delete'
                                                        data-id='{$item->id}'
                                                        data-application_no='{$item->application_no}'
                                                        data-applicant_name='{$item->applicant_name}'
                                                        data-applicant_taxid='{$item->applicant_taxid}'
                                                        data-created_at='{$created_at}'
                                                    ><i class='fa fa-trash-o' aria-hidden='true'></i>
                                                </button>";
                                }else{
                                    $button .= " <button type='button' class='btn btn-danger btn-xs btn_delete' disabled><i class='fa fa-trash-o' aria-hidden='true'></i></button>";
                                }

                                return $button;
                            })
                            ->order(function ($query) {
                                $query->orderBy('id', 'DESC');
                            })
                            ->rawColumns(['checkbox', 'action', 'status_application','applicant_name','application_no'])
                            ->make(true);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('section5/application-lab.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('section5/application-lab.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $requestData = $request->all();

            $user_act_instead = auth()->user()->ActInstead;//ดำเนินการแทนผปก.คนไหน null=ยื่นของตัวเอง

            $gen_number =  HP::ConfigFormat( 'APP-LAB' , (new ApplicationLab)->getTable()  , 'application_no', null , null,null );
            $application_check = ApplicationLab::where('application_no', $gen_number)->first();
            if(!is_null($application_check)){
                $gen_number =  HP::ConfigFormat( 'APP-LAB' , (new ApplicationLab)->getTable()  , 'application_no', null , null,null );
            }

            $requestData['application_no'] = $gen_number;
            $requestData['created_by'] = is_null($user_act_instead) ? auth()->user()->getKey() : $user_act_instead->getKey();
            $requestData['agent_id']   = is_null($user_act_instead) ? null : auth()->user()->getKey();//ผู้ดำเนินการแทน

            if( !empty( $requestData['type_save'] ) && $requestData['type_save'] == "save" ){ //ยื่นคำขอ อยู่ระหว่างการตรวจสอบ
                $requestData['application_status'] = 1;
                $requestData['application_date'] = date('Y-m-d');
            }else{ //ฉบับร่าง
                $requestData['application_status'] = 0;
            }

            $requestData['applicant_date_niti'] = !empty($requestData['applicant_date_niti'])?$requestData['applicant_date_niti']:null;

            $requestData['config_evidencce']  = (count(HP::ConfigEvidence(3)) > 0)?json_encode(HP::ConfigEvidence(3), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE):null;

            //กรณีมี ID section5_labs id 
            if( !empty( $requestData['lab_id'] ) ){
                $labs = Labs::where('id', $requestData['lab_id'] )->first();
                $requestData['lab_id']   = !empty($labs->id)?$labs->id:null;
                $requestData['lab_code'] = !empty($labs->lab_code)?$labs->lab_code:null;
            }else{
                $requestData['lab_id']   = null;
                $requestData['lab_code'] = null; 
            }

            $application = ApplicationLab::create($requestData);

            $LabAcceptData                          = [];
            $LabAcceptData['application_lab_id']    = $application->id;
            $LabAcceptData['application_no']        = $requestData['application_no'];
            $LabAcceptData['application_status']    = $requestData['application_status'];
            $LabAcceptData['description']           = !empty($requestData['edit_detail']) ? $requestData['edit_detail'] : null;
            $LabAcceptData['appointment_date']      = 'edit_page';
            $LabAcceptData['created_by']            = auth()->user()->getKey();
            $LabAcceptData['created_at']            = date('Y-m-d H:i:s');

            ApplicationLabAccept::create($LabAcceptData);

            $this->SaveScope( $application, $requestData  );
            $this->SaveAudit( $application, $requestData  );

            $this->SaveFile( $application, $request );

            return redirect('request-section-5/application-lab')->with('flash_message', 'เรียบร้อยแล้ว!');

        } catch (\Exception $e) {

            echo $e->getMessage();
            exit;
            return redirect('request-section-5/application-lab')->with('message_error', 'เกิดข้อผิดพลาดกรุณาบันทึกใหม่');
        }
    }

    public function SaveScope( $application ,  $requestData )
    {

        if( isset($requestData['section_box_tis']) ){

            $section_box_tis = $requestData['section_box_tis'];

            $section_ids = [];
            foreach( $section_box_tis AS $box ){
                $section_ids[$box] = $box;
            }

            $section_id = array_diff($section_ids, [null]);

            ApplicationLabScope::where('application_lab_id', $application->id)
                            ->whereNotNull('tis_id')
                            ->when($section_id, function ($query, $section_id){
                                return $query->whereNotIn('tis_id', $section_id);
                            })
                            ->delete();

            foreach( $section_box_tis AS $box ){

                if(  isset($requestData['repeater-group-'.$box ]) ){

                    $repeater_scope = $requestData['repeater-group-'.$box];

                    $list_scope_id = [];
                    foreach($repeater_scope as $scope){
                        if( isset( $scope['scope_id']) ){
                            $list_scope_id[] = $scope['scope_id'];
                        }
                    }
                    $list_ids = array_diff($list_scope_id, [null]);

                    ApplicationLabScope::where('application_lab_id', $application->id)
                                        ->when($list_ids, function ($query, $list_ids){
                                            return $query->whereNotIn('id', $list_ids);
                                        })
                                        ->where('tis_id', $box )
                                        ->delete();

                    foreach( $repeater_scope as $scope ){

                        if( array_key_exists('test_item_id', $scope ) && !empty($scope['test_item_id'])){
                            $scopes =  array_key_exists('scope_id', $scope) ? ApplicationLabScope::where('id',  $scope["scope_id"] )->first() : null ;
                            if(is_null($scopes)){
                                $scopes = new ApplicationLabScope;
                            }
                            $scopes->application_lab_id = $application->id;
                            $scopes->application_no     = $application->application_no;
                            $scopes->tis_id             = !empty($scope['tis_id'])?$scope['tis_id']:null;
                            $scopes->tis_tisno          = !empty($scope['tis_tisno'])?$scope['tis_tisno']:null;
                            $scopes->test_item_id       = !empty($scope['test_item_id'])?$scope['test_item_id']:null;
                            $scopes->test_tools_id      = !empty($scope['test_tools_id'])?$scope['test_tools_id']:null;
                            $scopes->test_tools_no      = !empty($scope['test_tools_no'])?$scope['test_tools_no']:null;
                            $scopes->capacity           = !empty($scope['capacity'])?$scope['capacity']:null;
                            $scopes->range              = !empty($scope['range'])?$scope['range']:null;
                            $scopes->true_value         = !empty($scope['true_value'])?$scope['true_value']:null;
                            $scopes->fault_value        = !empty($scope['fault_value'])?$scope['fault_value']:null;
                            $scopes->test_duration      = !empty($scope['test_duration'])?$scope['test_duration']:null;
                            $scopes->test_price         = !empty($scope['test_price'])?$scope['test_price']:null;
    
                            //กรณีมี ID section5_labs id 
                            $scopes->lab_id             = !empty($application->lab_id)?$application->lab_id:null;
                            $scopes->lab_code           = !empty($application->lab_code)?$application->lab_code:null;
    
                            $scopes->save();
                        }

                    }

                }

            }

        }

    }

    public function SaveAudit($application ,  $requestData)
    {
        $audit_types = !empty($requestData['audit_type'])?$requestData['audit_type']:null;

        if( $audit_types  == 2 ){

            if( isset($requestData['repeater-audit-2']) ){

                $list_audit = [];

                foreach( $requestData['repeater-audit-2'] as $item ){
                    $data = new stdClass;
                    $data->audit_date_start =  !empty($item['audit_date_start'])?HP::convertDate($item['audit_date_start']):null;
                    $data->audit_date_end   =  !empty($item['audit_date_end'])?HP::convertDate($item['audit_date_end']):null;
                    $list_audit[] = $data;
                }

                $application->audit_date  =  (count($list_audit) > 0 )? json_encode($list_audit,JSON_UNESCAPED_UNICODE):null;
                $application->save();
            }else{
                $application->audit_date = null;
                $application->save();
            }

            ApplicationLabCertificate::where('application_lab_id', $application->id)->delete();


        }else{

            if( isset($requestData['repeater-audit-1']) ){

                
                $attach_path =  $this->attach_path.'/Section5/ApplicationLab/'.$application->application_no;

                $application->audit_date = null;
                $application->save();

                $repeater_audit_1 = $requestData['repeater-audit-1'];

                $list_id = [];
                foreach($repeater_audit_1 as $item){
                    if( isset( $item['cer_id']) ){
                        $list_id[] = $item['cer_id'];
                    }
                }
                $list_ids = array_diff($list_id, [null]);

                ApplicationLabCertificate::where('application_lab_id', $application->id)
                                            ->when($list_ids, function ($query, $list_ids){
                                                return $query->whereNotIn('id', $list_ids);
                                            })
                                            ->delete();

                foreach( $repeater_audit_1 as $item ){

                    $cer = array_key_exists('cer_id', $item) ? ApplicationLabCertificate::where('id', $item["cer_id"] )->first() : null ;
                    if(is_null($cer)){
                        $cer = new ApplicationLabCertificate;
                    }
                    $cer->application_lab_id     = $application->id;
                    $cer->application_no         = $application->application_no;
                    $cer->certificate_id         = !empty($item['certificate_id'])?$item['certificate_id']:null;
                    $cer->certificate_no         = !empty($item['certificate_no'])?$item['certificate_no']:null;
                    $cer->certificate_start_date =  !empty($item['certificate_start_date'])?HP::convertDate($item['certificate_start_date']):null;
                    $cer->certificate_end_date   =  !empty($item['certificate_end_date'])?HP::convertDate($item['certificate_end_date']):null;
                    $cer->issued_by              = isset($item['issued_by'])?1:2;
                    $cer->accereditatio_no       = !empty($item['accereditatio_no'])?$item['accereditatio_no']:null;
                    $cer->save();
                    
                    if( isset( $item['certificate_file'] ) && !empty($item['certificate_file']) ){

                        HP::singleFileUpload(

                            $item['certificate_file'],
                            $attach_path,
                            (auth()->user()->tax_number ?? null),
                            (auth()->user()->username ?? null),
                            'SSO',
                            (  (new ApplicationLabCertificate)->getTable() ),
                            $cer->id,
                            'audit_certificate_file',
                            null,
                            null

                        );

                    }


                }

            }

        }

    }


    public function SaveFile( $application ,  $request)
    {

        $requestData = $request->all();
        $applicant_taxid = auth()->user()->tax_number ?? $application->applicant_taxid;

        $attach_path =  $this->attach_path.'/Section5/ApplicationLab/'.$application->application_no;

        if( isset( $requestData['evidences'] ) && !empty($applicant_taxid) && is_numeric($applicant_taxid) ){

            $evidences = $requestData['evidences'];

            foreach( $evidences as $evidence ){

                if( isset( $evidence['evidence_file_config'] ) && !empty($evidence['evidence_file_config']) ){

                    HP::singleFileUpload(

                        $evidence['evidence_file_config'],
                        $attach_path,
                        (auth()->user()->tax_number ?? null),
                        (auth()->user()->username ?? null),
                        'SSO',
                        (  (new ApplicationLab)->getTable() ),
                        $application->id,
                        'evidence_file_config',
                        !empty($evidence['setting_title'])?$evidence['setting_title']:null,
                        !empty($evidence['setting_id'])?$evidence['setting_id']:null

                    );

                }

            }

        }

        if( !empty( $requestData['repeater-file-other'] ) ){

            $repeater_file = $requestData['repeater-file-other'];

            foreach( $repeater_file as $key=>$file ){

                if($request->hasFile("repeater-file-other.{$key}.evidence_file_other")){
                    HP::singleFileUpload(
                        $request->file("repeater-file-other.{$key}.evidence_file_other"),
                        $attach_path,
                        (auth()->user()->tax_number ?? null),
                        (auth()->user()->username ?? null),
                        'SSO',
                        (  (new ApplicationLab)->getTable() ),
                        $application->id,
                        'evidence_file_other',
                        $request->input("repeater-file-other.{$key}.file_documents")
                    );
                }

            }

        }

    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $applicationlab = ApplicationLab::findOrFail($id);
        $applicationlab->edited = true;
        return view('section5/application-lab.show', compact('applicationlab'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $applicationlab = ApplicationLab::findOrFail($id);
        $applicationlab->show = true;
        $applicationlab->edit_page = true;
        return view('section5/application-lab.edit', compact('applicationlab'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $application = ApplicationLab::findOrFail($id);

            $requestData = $request->all();

            if( empty($application->application_no) ){
                $gen_number =  HP::ConfigFormat( 'APP-LAB' , (new ApplicationLab)->getTable()  , 'application_no', null , null,null );
                $application_check = ApplicationLab::where('application_no', $gen_number)->first();
                if(!is_null($application_check)){
                    $gen_number =  HP::ConfigFormat( 'APP-LAB' , (new ApplicationLab)->getTable()  , 'application_no', null , null,null );
                }
                $requestData['application_no'] = $gen_number;
                $requestData['created_by'] = auth()->user()->getKey();
                if( !empty( $requestData['type_save'] ) && $requestData['type_save'] == "save" ){
                    $requestData['application_status'] = 1;
                    if(is_null($application->application_date)){
                        $requestData['application_date'] = date('Y-m-d');
                    }
                }else{
                    $requestData['application_status'] = 0;
                }
            }else{
                if( !empty( $requestData['type_save'] ) && $requestData['type_save'] == "save" ){
                    $requestData['application_status'] = 1;
                    if(is_null($application->application_date)){
                        $requestData['application_date'] = date('Y-m-d');
                    }
                }else{
                    $requestData['application_status'] = 0;
                }    
                $requestData['updated_by'] = auth()->user()->getKey();
                $requestData['updated_at'] = date('Y-m-d H:i:s');
            }

            $requestData['applicant_date_niti'] = !empty($requestData['applicant_date_niti'])?$requestData['applicant_date_niti']:null;

            if( empty($application->config_evidencce) ){
                $requestData['config_evidencce']  = (count(HP::ConfigEvidence(3)) > 0)?json_encode(HP::ConfigEvidence(3), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE):null;
            }

             //กรณีมี ID section5_labs id 
            if( !empty( $requestData['lab_id'] ) ){
                $labs = Labs::where('id', $requestData['lab_id'] )->first();
                $requestData['lab_id']   = !empty($labs->id)?$labs->id:null;
                $requestData['lab_code'] = !empty($labs->lab_code)?$labs->lab_code:null;
            }else{
                $requestData['lab_id']   = null;
                $requestData['lab_code'] = null; 
            }

            $application->update( $requestData );

            $LabAcceptData['application_lab_id']    = $requestData['application_lab_id'];
            $LabAcceptData['application_no']        = $requestData['application_no'];
            $LabAcceptData['application_status']    = $requestData['application_status'];
            $LabAcceptData['description']           = !empty($requestData['edit_detail'])?$requestData['edit_detail']:null;
            $LabAcceptData['appointment_date']      = 'edit_page';
            $LabAcceptData['created_by']            = auth()->user()->getKey();
            $LabAcceptData['created_at']            = date('Y-m-d H:i:s');

            ApplicationLabAccept::create($LabAcceptData);

            $this->SaveScope( $application, $requestData  );

            $this->SaveAudit( $application, $requestData  );

            $this->SaveFile( $application, $request );

            return redirect('request-section-5/application-lab')->with('flash_message', 'เรียบร้อยแล้ว!');

        } catch (\Exception $e) {

            echo $e->getMessage();
            exit;
            return redirect('request-section-5/application-lab')->with('message_error', 'เกิดข้อผิดพลาดกรุณาบันทึกใหม่');
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ApplicationLab::destroy($id);
        return redirect('request-section-5/application-lab')->with('flash_message_delete', 'ลบข้อมูลเรียบร้อยแล้ว!');
    }


    public function GetTestItem($tis_id)
    {

        if(  !empty($tis_id) && is_numeric($tis_id) ){

            $orderby = "CAST(SUBSTRING_INDEX(no,'.',1) as UNSIGNED),";
            $orderby .= "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(no,'.',2),'.',-1) as UNSIGNED),";
            $orderby .= "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(no,'.',3),'.',-1) as UNSIGNED),";
            $orderby .= "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(no,'.',4),'.',-1) as UNSIGNED),";
            $orderby .= "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(no,'.',5),'.',-1) as UNSIGNED)";

            $main = TestItem::where('tis_id', $tis_id)
                            ->where('type', 1)
                            ->with('main_test_item_parent_data')
                            ->orderby(DB::raw( $orderby ))
                            ->get();

            $list = [];
            foreach( $main AS $mains ){

                $parent = $mains->main_test_item_parent_data()
                                    ->where(function($query){
                                        $query->where('input_result', 1)->Orwhere('test_summary', 1);
                                    })
                                    ->orderby(DB::raw( $orderby ))
                                    ->get();

                if( count( $parent ) >= 1 ){

                    foreach( $parent AS $parents ){
                        $data = new stdClass;
                        $data->id = $parents->id;
                        $data->title = ( !empty( $parents->no )?$parents->no.' ' :'' ).$parents->title.' <em>(ภายใต้หัวข้อทดสอบ '.(  ( !empty( $mains->no )?$mains->no.' ' :null ).$mains->title ).')</em>';
                        $list[] =  $data;
                    }

                }
            }

            return response()->json($list);

        }

    }


    public function GetTestItemTools($id)
    {

        if(  !empty($id) && is_numeric($id) ){

            $data = TestItemTools::with('test_tool')
                                    ->whereHas('test_tool', function ($query) {
                                        $query->whereNotNull('id');
                                    })
                                    ->where( function($query) use($id ) {
                                        $query->where('bsection5_test_item_id',  $id);
                                    })
                                    ->select('test_tools_id')
                                    ->groupBy('test_tools_id')
                                    ->get();
            foreach( $data AS $item ){
                $item->id = $item->test_tool->id;
                $item->title = $item->test_tool->title;
            }      

            return response()->json($data);

        }

    }

    public function GetTestItemToolsStd($id)
    {

        if(  !empty($id) && is_numeric($id) ){

            $data = TestItemTools::with('test_tool')
                                    ->whereHas('test_tool', function ($query) {
                                        $query->whereNotNull('id');
                                    })
                                    ->whereHas('test_item', function($query) use($id ) {
                                        $query->where('tis_id',  $id);
                                    })
                                    ->select('test_tools_id')
                                    ->groupBy('test_tools_id')
                                    ->get();
            foreach( $data AS $item ){
                $item->id = $item->test_tool->id;
                $item->title = $item->test_tool->title;
            }      

            return response()->json($data);

        }

    }


    public function GetTisName($id)
    {
        $data = Tis::find($id);

        return response()->json($data);
    }

    public function AutoRunRefApplication()
    {
        $today = date('Y-m-d');
        $dates = explode('-', $today);
        $year = ( date('y')  + 43);
        $ref = 'LAB';

        $query_check = ApplicationLab::select('application_no')->whereYear('created_at',$dates[0])->orderBy('application_no')->get();
        $query = 0;
        if(count($query_check) != 0){

            $last_data = $query_check->last();
            if(!empty($last_data->application_no)){
                $application_no =  $last_data->application_no;
                $cut = explode('-', $application_no);
                $query = (int)($cut[2]);
            }

            $Seq = substr("000".((string)$query + 1),-4,4);
            $strNextSeq = $ref.'-'.$year ."-".$Seq;

            $no_check = ApplicationLab::where('application_no', $strNextSeq )->first();
            if(is_null($no_check)){
                return $strNextSeq;
            }else{
                $Seq = substr("000".((string)$query + 2),-4,4);
                $strNextSeq = $ref.'-'.$year ."-".$Seq;
                return $strNextSeq;
            }
        }else{
            $Seq = substr("000".((string)$query + 1),-4,4);
            $strNextSeq = $ref.'-'.$year ."-".$Seq;
            return $strNextSeq;
        }

    }

    public function data_list_cer(Request $request)
    {

        $tax_id =  $request->get('tax_id');
        $filter_search =  $request->get('filter_search');


        $query = CertificateExport::query()->when( $filter_search , function ($query, $filter_search){
                                                $search_full = str_replace(' ', '', $filter_search);
                                                return  $query->where(function ($query2) use($search_full) {

                                                    $ids = CertiLab::Where(DB::raw("REPLACE(lab_name,' ','')"), 'LIKE', "%".$search_full."%")->select('id');
                                                    $query2->Where(DB::raw("REPLACE(certificate_no,' ','')"), 'LIKE', "%".$search_full."%")
                                                            ->orWhereIn('certificate_for',  $ids  );
                                                    //         ->OrWhere(DB::raw("REPLACE(applicant_taxid,' ','')"), 'LIKE', "%".$search_full."%");
                                                });
                                            })
                                            ->where(function($query) use( $tax_id ){
                                                $ids = CertiLab::where('tax_id', $tax_id )->select('id');
                                                $query->whereIN('certificate_for',  $ids  );
                                            })
                                            ->whereHas('certificate_lab_export_mapreq', function ($query)  {
                                                   $query->whereNotNull('app_certi_lab_id' );
                                             });

        $DT    = Datatables::of($query);
        $DT->addIndexColumn();     
        $DT->addColumn('lab_name', function ($item) {
              $CertiLabTo = $item->CertiLabTo;
            return !is_null($CertiLabTo)?$CertiLabTo->lab_name:null;
             })
             ->addColumn('certificate_no', function ($item) {
                 return !is_null($item->certificate_no)?$item->certificate_no:null;
             })
             ->addColumn('accereditatio_no', function ($item) {
                 return !is_null($item->accereditatio_no)?$item->accereditatio_no:null;
             }); 
     $DT->addColumn('certificate_date_start', function ($item) {
                return !empty($item->CertiLabFileAll->start_date)?HP::revertDate($item->CertiLabFileAll->start_date):null;
            })
            ->addColumn('certificate_date_end', function ($item) {
                return !empty($item->CertiLabFileAll->end_date)?HP::revertDate($item->CertiLabFileAll->end_date):null;
            })
            ->addColumn('status', function ($item) {
                $certificate_date_end = !empty($item->CertiLabFileAll->end_date)?$item->CertiLabFileAll->end_date:null;
                if( $certificate_date_end >= date('Y-m-d') ){
                    return 'ใช้งาน';
                }else{
                    return 'หมดอายุ';
                }
            })
            ->addColumn('action', function ($item) {
                $certificate_date_end = !empty($item->CertiLabFileAll->end_date)?$item->CertiLabFileAll->end_date:null;
                if( $certificate_date_end >= date('Y-m-d') ){
                    return '<button class="btn btn-info btn_select_cer" type="button" data-accereditatio_no="'.($item->accereditatio_no).'" data-id="'.($item->id).'" data-table="'.((new CertificateExport)->getTable() ).'" data-certificate_no="'.(!is_null($item->certificate_no)?$item->certificate_no:null).'" data-date_end="'.( !empty($item->CertiLabFileAll->end_date)?HP::revertDate($item->CertiLabFileAll->end_date):null ).'" data-date_start="'.( !empty($item->CertiLabFileAll->start_date)?HP::revertDate($item->CertiLabFileAll->start_date):null ).'"> เลือก </button>';
                }else{
                    return '<button class="btn btn-info" type="button" disabled> เลือก </button>';
                }

            });
   return $DT->rawColumns([ 'action'])
              ->make(true);

 

        // $query = CertificateExport::query()->when( $filter_search , function ($query, $filter_search){
        //                                         $search_full = str_replace(' ', '', $filter_search);
        //                                         return  $query->where(function ($query2) use($search_full) {

        //                                             $ids = CertiLab::Where(DB::raw("REPLACE(lab_name,' ','')"), 'LIKE', "%".$search_full."%")->select('id');
        //                                             $query2->Where(DB::raw("REPLACE(certificate_no,' ','')"), 'LIKE', "%".$search_full."%")
        //                                                     ->orWhereIn('certificate_for',  $ids  );
        //                                             //         ->OrWhere(DB::raw("REPLACE(applicant_taxid,' ','')"), 'LIKE', "%".$search_full."%");
        //                                         });
        //                                     })
        //                                     ->where(function($query) use( $tax_id ){
        //                                         $ids = CertiLab::where('tax_id', $tax_id )->select('id');
        //                                         $query->whereIN('certificate_for',  $ids  );
        //                                     })
        //                                     ->whereHas('cert_labs_file_all', function ($query)  {
        //                                         $query->where('state',  1  );
        //                                     });
        // return Datatables::of($query)
        //                     ->addIndexColumn()
        //                     ->addColumn('lab_name', function ($item) {
        //                         $CertiLabTo = $item->CertiLabTo;
        //                         return !is_null($CertiLabTo)?$CertiLabTo->lab_name:null;
        //                     })
        //                     ->addColumn('certificate_no', function ($item) {
        //                         return !is_null($item->certificate_no)?$item->certificate_no:null;
        //                     })
        //                     ->addColumn('accereditatio_no', function ($item) {
        //                         return !is_null($item->accereditatio_no)?$item->accereditatio_no:null;
        //                     })
        //                     ->addColumn('certificate_date_start', function ($item) {

        //                         $cert_labs_file_all =  $item->cert_labs_file_all()->where('state', 1)->get()->last();
        //                         return !empty($cert_labs_file_all->start_date)?HP::revertDate($cert_labs_file_all->start_date):null;
        //                     })
        //                     ->addColumn('certificate_date_end', function ($item) {
        //                         $cert_labs_file_all =  $item->cert_labs_file_all()->where('state', 1)->get()->last();
        //                         return !empty($cert_labs_file_all->end_date)?HP::revertDate($cert_labs_file_all->end_date):null;
        //                     })
        //                     ->addColumn('status', function ($item) {
        //                         $cert_labs_file_all =  $item->cert_labs_file_all()->where('state', 1)->get()->last();
        //                         $certificate_date_end = !empty($cert_labs_file_all->end_date)?$cert_labs_file_all->end_date:null;
        //                         if( $certificate_date_end >= date('Y-m-d') ){
        //                             return 'ใช้งาน';
        //                         }else{
        //                             return 'หมดอายุ';
        //                         }
        //                     })
        //                     ->addColumn('action', function ($item) {
        //                         $cert_labs_file_all =  $item->cert_labs_file_all()->where('state', 1)->get()->last();
        //                         $certificate_date_end = !is_null($cert_labs_file_all->end_date)?$cert_labs_file_all->end_date:null;
        //                         if( $certificate_date_end >= date('Y-m-d') ){
        //                             return '<button class="btn btn-info btn_select_cer" type="button" data-accereditatio_no="'.($item->accereditatio_no).'" data-id="'.($item->id).'" data-table="'.((new CertificateExport)->getTable() ).'" data-certificate_no="'.(!is_null($item->certificate_no)?$item->certificate_no:null).'" data-date_end="'.( !empty($cert_labs_file_all->end_date)?HP::revertDate($cert_labs_file_all->end_date):null ).'" data-date_start="'.( !empty($cert_labs_file_all->start_date)?HP::revertDate($cert_labs_file_all->start_date):null ).'"> เลือก </button>';
        //                         }else{
        //                             return '<button class="btn btn-info" type="button" disabled> เลือก </button>';
        //                         }

        //                     })
        //                     ->rawColumns(['checkbox', 'action'])
        //                     ->make(true);
    }

    public function save_test_tools(Request $request)
    {

        $test_item =  $request->get('test_item');
        $test_tool =  $request->get('test_tool');
        $test_tool_id =  $request->get('test_tool_id');
        $type =  $request->get('type');

        if( $type == 1){
            $check = TestTool::where(DB::raw("REPLACE(title,' ','')"), $test_tool )->first();
        }else{
            $check = TestTool::where( 'id', $test_tool_id )->first();
        }

        $tools_id = null;

        if( !is_null($test_item) ){

            if( is_null($check) ){

                $newtools['title'] = $test_tool;
                $newtools['state'] = 1;
                $newtools['created_by'] = 0;

                $tools = TestTool::create($newtools);
                $tools_id = $tools->id;
                $item_tools = TestItemTools::Where('bsection5_test_item_id', $test_item )->where( 'test_tools_id', $tools->id  )->first();

                if( is_null($item_tools) ){

                    $toolsT = new TestItemTools;
                    $toolsT->bsection5_test_item_id = $test_item;
                    $toolsT->test_tools_id = $tools->id;
                    $toolsT->save();
                }

                $mgs = 'success';

            }else{

                $tools = $check;
                $tools_id = $tools->id;
                $item_tools = TestItemTools::Where('bsection5_test_item_id', $test_item )->where( 'test_tools_id', $tools->id  )->first();

                if( is_null($item_tools) ){

                    $toolsT = new TestItemTools;
                    $toolsT->bsection5_test_item_id = $test_item;
                    $toolsT->test_tools_id = $tools->id;
                    $toolsT->save();

                }

                $mgs = 'success';
            }

        }else{
            $mgs = "not success";
        }

        $data = new stdClass;
        $data->mgs = $mgs;
        $data->tools_id = $tools_id;

        return response()->json($data);


    }

    public function GetBasicTools($test_item_id)
    {
        $data = TestTool::where(function($query) use($test_item_id){
                                $ids = DB::table((new TestItemTools)->getTable().' AS item')
                                            ->leftJoin((new TestTool)->getTable().' AS tools', 'tools.id', '=', 'item.test_tools_id')
                                            ->where( function($query) use($test_item_id ) {
                                                $query->where('item.bsection5_test_item_id',  $test_item_id);
                                            })
                                            ->select('tools.id');

                                $query->whereNotIn('id',  $ids);
                            })
                            ->select('title', 'id')
                            ->get();

        return response()->json($data);

    }

    public function delete_update(Request $request)
    {
        try {

            $id = $request->get('id');
            $application = ApplicationLab::findOrFail($id);

            $requestData = $request->all();
            $requestData['application_status'] = 100;
            $requestData['delete_by'] = auth()->user()->getKey();
            $requestData['delete_at'] = date('Y-m-d h:i:s');
            $requestData['delete_state'] = 1;

            $application->update($requestData);

            if( $application ){
                return 'success';
            }else{
                return 'error';
            }

        } catch (\Exception $e) {

            return 'error';

        }
    }
}
