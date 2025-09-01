<?php

namespace App\Http\Controllers\Section5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;
use HP;

use App\Models\Section5\ApplicationIbcb;
use App\Models\Section5\ApplicationIbcbCertify;
use App\Models\Section5\ApplicationIbcbScope;
use App\Models\Section5\ApplicationIbcbScopeTis;
use App\Models\Section5\ApplicationIbcbScopeDetail;
use App\Models\Section5\ApplicationIbcbInspectors;
use App\Models\Section5\ApplicationIbcbInspectorsScope;
use App\Models\Section5\Section5ApplicationInspectorsScopeTis;

use App\Models\Certify\ApplicantIB\CertiIBExport;
use App\Models\Certify\ApplicantIB\CertiIb;
use App\Models\Certify\ApplicantCB\CertiCBExport;
use App\Models\Certify\ApplicantCB\CertiCb;

use App\Models\Basic\BranchTis;
use App\Models\Basic\BranchGroup;
use App\Models\Basic\Branch;
use App\Models\Basic\Tis;

use App\Models\Bsection5\Standard AS BStandard;

use App\Models\Section5\Inspectors;
use App\Models\Section5\InspectorsScope;
use App\Models\Section5\InspectorsScopeTis;
use App\Models\Section5\Section5ApplicationInspector;
use App\Models\Section5\Ibcbs;
use Illuminate\Support\Facades\File;

use function GuzzleHttp\json_encode;
use stdClass;
class ApplicationIbcbController extends Controller
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

        $filter_search =  $request->get('filter_search');
        $filter_state =  $request->get('filter_state');
        $user = auth()->user();

        $query = ApplicationIbcb::query()->when( $filter_search , function ($query, $filter_search){
                                            $search_full = str_replace(' ', '', $filter_search);

                                            if( strpos( $search_full , 'IB-' ) !== false || strpos( $search_full , 'CB-' ) !== false ){
                                                return $query->where('application_no',  'LIKE', "%$search_full%");
                                            }else{
                                                return  $query->where(function ($query2) use($search_full) {
                                                                    $query2->Where(DB::raw("REPLACE(applicant_name,' ','')"), 'LIKE', "%".$search_full."%")
                                                                            ->OrWhere(DB::raw("REPLACE(applicant_taxid,' ','')"), 'LIKE', "%".$search_full."%")
                                                                            ->Orwhere('application_no',  'LIKE', "%$search_full%");
                                                                });
                                                                
                                            }
                                        })
                                        ->when( $filter_state , function ($query, $filter_state){
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
                                return $item->application_no;
                            })
                            ->addColumn('applicant_name', function ($item) {
                                return $item->FullNameTaxId;
                            })
                            ->addColumn('application_type', function ($item) {

                                $application_type_arr = [1 => 'IB', 2 => 'CB'];
                                return array_key_exists( $item->application_type,  $application_type_arr )?$application_type_arr [ $item->application_type ]:'-';
                            })
                            ->addColumn('scope', function ($item) {
                                return !empty($item->ScopeGroup)?$item->ScopeGroup:'-';
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
                            ->addColumn('creater', function ($item) {
                                return $item->CreaterName;
                            })
                            ->addColumn('action', function ($item) {
                                $edit = true;
                                $disabled = [];
                                if(!in_array($item->application_status, [2,0])){
                                    $edit = false;
                                    array_push($disabled, 'edit');
                                }

                                $created_at = !empty($item->created_at) ? HP::DateTimeThai($item->created_at) : null;

                                $button = HP::buttonAction($item->id, 'request-section-5/application-ibcb', 'Section5\\ApplicationIbcbController@destroy', 'application-ibcb',  true, $edit, false, $disabled);
                                if( $item->application_status <= 13){
                                    $button .= " <button type='button' class='btn btn-danger btn-xs btn_delete' title='Delete application_delete'
                                                        data-id='{$item->id}'
                                                        data-application_no='{$item->application_no}'
                                                        data-applicant_name='{$item->applicant_name}'
                                                        data-applicant_taxid='{$item->applicant_taxid}'
                                                        data-created_at='{$created_at}'
                                                    ><i class='fa fa-trash-o' aria-hidden='true'></i>
                                                </button>";
                                }

                                return $button;
                            })
                            ->order(function ($query) {
                                $query->orderBy('id', 'DESC');
                            })
                            ->rawColumns(['checkbox', 'action', 'status_application', 'applicant_name'])
                            ->make(true);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('section5/application-ib-cb.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // dd(Auth::check() );
        return view('section5/application-ib-cb.create');
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

            $application_type  =  !empty($requestData['application_type'])?$requestData['application_type']:null;
            $ref =( $application_type == 1)? 'IB':'CB';

            $gen_number =  HP::ConfigFormat( 'APP-IB-CB' , (new ApplicationIbcb)->getTable()  , 'application_no', $ref , null,null );
            $application_check = ApplicationIbcb::where('application_no', $gen_number)->first();
            if(!is_null($application_check)){
                $gen_number =  HP::ConfigFormat( 'APP-IB-CB' , (new ApplicationIbcb)->getTable()  , 'application_no', $ref , null,null );
            }
            $requestData['application_no'] = $gen_number;
            $requestData['created_by'] = is_null($user_act_instead) ? auth()->user()->getKey() : $user_act_instead->getKey();
            $requestData['agent_id']   = is_null($user_act_instead) ? null : auth()->user()->getKey();//ผู้ดำเนินการแทน
            if( !empty( $requestData['type_save'] ) && $requestData['type_save'] == "save" ){
                $requestData['application_status'] = 1;
                $requestData['application_date'] = date('Y-m-d');
            }else{
                $requestData['application_status'] = 0;
            }

            $requestData['applicant_date_niti'] = !empty($requestData['applicant_date_niti'])?$requestData['applicant_date_niti']:null;

            $requestData['config_evidencce']  = (count(HP::ConfigEvidence(1)) > 0)?json_encode(HP::ConfigEvidence(1), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE):null;

            //กรณีมี ID section5_ibcbs id
            if( !empty( $requestData['ibcb_id'] ) ){
                $ibcbs = Ibcbs::where('id', $requestData['ibcb_id'] )->first();
                $requestData['ibcb_id']   = !empty($ibcbs->id)?$ibcbs->id:null;
                $requestData['ibcb_code'] = !empty($ibcbs->ibcb_code)?$ibcbs->ibcb_code:null;
            }else{
                $requestData['lab_id']   = null;
                $requestData['ibcb_code'] = null;
            }

            $application = ApplicationIbcb::create( $requestData );

            $this->SaveCertify( $application, $requestData  );
            $this->SaveScope( $application, $requestData  );
            $this->SaveInspectors( $application, $requestData  );

            $this->SaveFile( $application, $request );

            return redirect('request-section-5/application-ibcb')->with('flash_message', 'เรียบร้อยแล้ว!');

        } catch (\Exception $e) {

            echo $e->getMessage();
            exit;
            return redirect('request-section-5/application-ibcb/create')->with('message_error', 'เกิดข้อผิดพลาดกรุณาบันทึกใหม่');
        }
    }

    public function SaveInspectors( $application ,  $requestData )
    {
        if( isset($requestData['repeater-inspectors']) ){

            $inspectors_list = $requestData['repeater-inspectors'];

            foreach( $inspectors_list  AS $Iinspectors ){

                $id = isset($Iinspectors['insp_id'])?$Iinspectors['insp_id']:null;

                $inspector = ApplicationIbcbInspectors::where('id', $id )->first();

                if( is_null( $inspector ) ){
                    $inspector = new ApplicationIbcbInspectors;
                }

                $inspector->application_id = $application->id;
                $inspector->application_no = $application->application_no;
                $inspector->inspector_id = !empty($Iinspectors['inspector_id'])?$Iinspectors['inspector_id']:null;
                $inspector->inspector_prefix = !empty($Iinspectors['inspector_prefix'])?$Iinspectors['inspector_prefix']:null;
                $inspector->inspector_first_name = !empty($Iinspectors['inspector_first_name'])?$Iinspectors['inspector_first_name']:null;
                $inspector->inspector_last_name = !empty($Iinspectors['inspector_last_name'])?$Iinspectors['inspector_last_name']:null;
                $inspector->inspector_taxid = !empty($Iinspectors['inspector_taxid'])?$Iinspectors['inspector_taxid']:null;
                $inspector->inspector_type = !empty($Iinspectors['inspector_type'])?$Iinspectors['inspector_type']:null;
                $inspector->save();

                if( !empty($Iinspectors['branch_group_id']) ){

                    $branch_group_lits = explode(',', $Iinspectors['branch_group_id'] );

                    ApplicationIbcbInspectorsScope::whereNotIn('branch_group_id', $branch_group_lits )->where('ibcb_inspector_id', $inspector->id )->delete();

                    foreach( $branch_group_lits as $Bgroup ){


                        if( isset($Iinspectors['branch_id_'.$Bgroup]) && !empty($Iinspectors['branch_id_'.$Bgroup]) ){

                            $branch_lits = explode(',', $Iinspectors['branch_id_'.$Bgroup] );

                            ApplicationIbcbInspectorsScope::whereNotIn('branch_id', $branch_lits )->where('branch_group_id', $Bgroup )->where('ibcb_inspector_id', $inspector->id )->delete();

                            foreach( $branch_lits  AS $Ibranch ){

                                $branch = ApplicationIbcbInspectorsScope::where('branch_id', $Ibranch )->where('ibcb_inspector_id', $inspector->id )->first();

                                if(  is_null( $branch )  ){
                                    $branch = new ApplicationIbcbInspectorsScope;
                                }

                                $branch->ibcb_inspector_id = $inspector->id;
                                $branch->application_no = $application->application_no;
                                $branch->branch_group_id = $Bgroup;
                                $branch->branch_id = $Ibranch;
                                $branch->Save();

                            }

                        }


                    }

                }

            }

        }else{
            ApplicationIbcbInspectors::where('application_id', $application->id )->delete();
        }
    }


    function SaveScope($application, $requestData){

        if( isset($requestData['repeater-scope']) ){

            $scope_list = $requestData['repeater-scope'];

            $list_ids = array_diff(array_column($scope_list, 'scope_id'), [null]);

            ApplicationIbcbScope::where('application_id', $application->id)
                                        ->when($list_ids, function ($query, $list_ids){
                                            return $query->whereNotIn('id', $list_ids);
                                        })
                                        ->delete();

            foreach( $scope_list  AS $Iscope ){

                $id = isset($Iscope['scope_id'])?$Iscope['scope_id']:null;

                $group = ApplicationIbcbScope::firstOrNew(
                    ['id' => $id],
                    [
                       'application_id'  => $application->id,
                       'branch_group_id' => @$Iscope['branch_group_id'],
                       'isic_no'         => @$Iscope['isic_no'],
                       'created_by'      => auth()->user()->getKey(),
                       'ibcb_id'         => !empty($application->ibcb_id)?$application->ibcb_id:null,
                       'ibcb_code'       => !empty($application->ibcb_code)?$application->ibcb_code:null,
                    ]
                );
                $group->application_no = $application->application_no;
                $group->updated_by = auth()->user()->getKey();
                $group->save();


                if( !empty($Iscope['branch_id']) ){

                    $branch_lits = $Iscope['branch_id'];

                    ApplicationIbcbScopeDetail::whereNotIn('branch_id', $branch_lits )->where('ibcb_scope_id', $group->id )->delete();

                    foreach( $branch_lits as $Ibranch ){

                        ApplicationIbcbScopeDetail::updateOrCreate(
                            [
                                'ibcb_scope_id' => $group->id,
                                'branch_id' => $Ibranch
                            ],
                            [
                                'ibcb_scope_id'  => $group->id,
                                'application_no' => $application->application_no,
                                'branch_id'      => $Ibranch,
                                'ibcb_id'        => !empty($application->ibcb_id)?$application->ibcb_id:null,
                                'ibcb_code'      => !empty($application->ibcb_code)?$application->ibcb_code:null,
                            ]
                        );

                    }

                }

                if( !empty($Iscope['tis_id']) ){

                    $tis_ids = $Iscope['tis_id'];

                    ApplicationIbcbScopeTis::whereNotIn('tis_id', $tis_ids )->where('ibcb_scope_id', $group->id )->delete();

                    $standards = Tis::select('tb3_TisAutono', 'tb3_TisThainame', 'tb3_Tisno')->whereIn('tb3_TisAutono', $tis_ids)->get()->keyBy('tb3_TisAutono')->toArray();

                    foreach( $tis_ids as $tis_id ){

                        $standard = array_key_exists($tis_id, $standards)?$standards[$tis_id]:null;

                        //id รายละเอียด
                        $branch_tis = BranchTis::where('tis_id', $tis_id)->first();
                        $detail     = !is_null($branch_tis) ? ApplicationIbcbScopeDetail::where('ibcb_scope_id', $group->id)->where('branch_id', $branch_tis->branch_id)->first() : null ;
                        $detail_id  = !is_null($detail) ? $detail->id : null ;

                        ApplicationIbcbScopeTis::updateOrCreate(
                            [
                                'ibcb_scope_detail_id' => $detail_id,
                                'tis_id' => $tis_id
                            ],
                            [
                                'ibcb_scope_id'        => $group->id,
                                'ibcb_scope_detail_id' => $detail_id,
                                'tis_id'               => $tis_id,
                                'tis_no'               => @$standard['tb3_Tisno'],
                                'tis_name'             => @$standard['tb3_TisThainame'],
                                'ibcb_id'              => !empty($application->ibcb_id)?$application->ibcb_id:null,
                                'ibcb_code'            => !empty($application->ibcb_code)?$application->ibcb_code:null,
                            ]
                        );

                    }

                }


            }

        }else{
            ApplicationIbcbScope::where('application_id', $application->id )->delete();
        }

    }

    function SaveCertify( $application ,  $requestData ){

        if( isset($requestData['repeater-certificate'])  ){

            $certificate = $requestData['repeater-certificate'];

            $list_id = [];
            foreach($certificate as $item){
                if( isset( $item['id']) ){
                    $list_id[] = $item['id'];
                }
            }
            $list_ids = array_diff($list_id, [null]);

            ApplicationIbcbCertify::where('application_id', $application->id)
                                        ->when($list_ids, function ($query, $list_ids){
                                            return $query->whereNotIn('id', $list_ids);
                                        })
                                        ->delete();

            foreach( $certificate  AS $certify ){

                $id = isset($certify['cer_id'])?$certify['cer_id']:null;
                $cer = ApplicationIbcbCertify::where('id', $id )->first();

                if( is_null( $cer ) ){
                    $cer = new ApplicationIbcbCertify;
                }
                $cer->application_id = $application->id;
                $cer->application_no = $application->application_no;
                $cer->certificate_std_id = !empty($certify['certificate_std_id'])?$certify['certificate_std_id']:null;
                $cer->certificate_id = !empty($certify['certificate_id'])?$certify['certificate_id']:null;
                $cer->certificate_no = !empty($certify['certificate_no'])?$certify['certificate_no']:null;
                $cer->certificate_table = !empty($certify['certificate_table'])?$certify['certificate_table']:null;
                $cer->certificate_start_date = !empty($certify['certificate_start_date'])?HP::convertDate($certify['certificate_start_date']):null;
                $cer->certificate_end_date = !empty($certify['certificate_end_date'])?HP::convertDate($certify['certificate_end_date']):null;
                $cer->issued_by = !empty($certify['certificate_id'])?1:2;
                $cer->save();

            }

        }else{
            ApplicationIbcbCertify::where('application_id', $application->id )->delete();

        }

    }

    public function SaveFile( $application ,  $request)
    {

        $requestData = $request->all();
        $applicant_taxid = auth()->user()->tax_number ?? $application->applicant_taxid;

        $attach_path =  $this->attach_path.'/Section5/ApplicationIbcb/'.$application->application_no;

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
                        (  (new ApplicationIbcb)->getTable() ),
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
                        (  (new ApplicationIbcb)->getTable() ),
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
        $applicationibcb = ApplicationIbcb::findOrFail($id);
        $applicationibcb->edited = true;

        // if($id==22){

        //     var_dump(File::makeDirectory(public_path('uploads/files/sso/Section5/ApplicationIbcb/'), 0777, true, true));
        //     var_dump(File::isDirectory(public_path('uploads/files/sso/Section5/ApplicationIbcb/')));
        //     echo public_path('uploads/files/sso/Section5/ApplicationIbcb/IB-66-0007/');
        //     var_dump(File::makeDirectory(public_path('uploads/files/sso/Section5/ApplicationIbcb/IB-66-0007/'), 0777, true, true));
        //     var_dump(File::isDirectory(public_path('uploads/files/sso/Section5/ApplicationIbcb/IB-66-0007/')));
            
        //     var_dump(File::makeDirectory(public_path('uploads/files/sso/Section5/ApplicationIbcb/IB-66-0007/0994000005563/'), 0777, true, true));
        //     var_dump(File::isDirectory(public_path('uploads/files/sso/Section5/ApplicationIbcb/IB-66-0007/0994000005563/')));

        //     //File::append($path.'/test.txt', 'Test');
        //     return 'OK';
        // }

        return view('section5/application-ib-cb.show', compact('applicationibcb'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $applicationibcb = ApplicationIbcb::findOrFail($id);
        $applicationibcb->show = true;
        
        return view('section5/application-ib-cb.edit', compact('applicationibcb'));
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

            $application = ApplicationIbcb::findOrFail($id);

            $requestData = $request->all();

            if(!empty($requestData['type_save']) && $requestData['type_save'] == "save" ){ //ยื่นคำขอ อยู่ระหว่างการตรวจสอบ
                $requestData['application_status'] = 1;
                if(is_null($application->application_date)){
                    $requestData['application_date'] = date('Y-m-d');
                }
            }else{ //ฉบับร่าง
                $requestData['application_status'] = 0;
            }

            if( empty($application->application_no) ){
                $application_type  =  !empty($requestData['application_type'])?$requestData['application_type']:null;
                $ref =( $application_type == 1)? 'IB':'CB';

                $gen_number =  HP::ConfigFormat( 'APP-IB-CB' , (new ApplicationIbcb)->getTable()  , 'application_no', $ref , null,null );
                $application_check = ApplicationIbcb::where('application_no', $gen_number)->first();
                if(!is_null($application_check)){
                    $gen_number =  HP::ConfigFormat( 'APP-IB-CB' , (new ApplicationIbcb)->getTable()  , 'application_no', $ref , null,null );
                }
                $requestData['application_no'] = $gen_number;
                $requestData['created_by'] = auth()->user()->getKey();
            }else{
                $requestData['updated_by'] = auth()->user()->getKey();
                $requestData['updated_at'] = date('Y-m-d H:i:s');
            }

            $requestData['applicant_date_niti'] = !empty($requestData['applicant_date_niti'])?$requestData['applicant_date_niti']:null;

            if( empty($application->config_evidencce) ){
                $requestData['config_evidencce']  = (count(HP::ConfigEvidence(1)) > 0)?json_encode(HP::ConfigEvidence(1), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE):null;
            }

            if(@$application->application_status == 2 || (!empty($requestData['type_save']) && $requestData['type_save'] == "save")){
                $requestData['application_status'] = 1;
            }

            //กรณีมี ID section5_ibcbs id
            if( !empty( $requestData['ibcb_id'] ) ){
                $ibcbs = Ibcbs::where('id', $requestData['ibcb_id'] )->first();
                $requestData['ibcb_id']   = !empty($ibcbs->id)?$ibcbs->id:null;
                $requestData['ibcb_code'] = !empty($ibcbs->ibcb_code)?$ibcbs->ibcb_code:null;
            }else{
                $requestData['lab_id']   = null;
                $requestData['ibcb_code'] = null;
            }

            $application->update($requestData);

            $this->SaveCertify( $application, $requestData  );
            $this->SaveScope( $application, $requestData  );
            $this->SaveInspectors( $application, $requestData  );
            $this->SaveFile( $application, $request );

            return redirect('request-section-5/application-ibcb')->with('flash_message', 'เรียบร้อยแล้ว!');

        } catch (\Exception $e) {

            echo $e->getMessage();
            exit;
            return redirect('request-section-5/application-ibcb/'.$id.'/edit')->with('message_error', 'เกิดข้อผิดพลาดกรุณาบันทึกใหม่');
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
        ApplicationIbcb::destroy($id);
        return redirect('request-section-5/application-ibcb')->with('flash_message', 'ลบข้อมูลเรียบร้อยแล้ว!');
    }

    public function getDataBrancheTis($branch_ids)
    {
        $branch_ids = explode(',', $branch_ids);
        $data = DB::table((new BranchTis)->getTable().' AS branch')
                    ->leftJoin((new Tis)->getTable().' AS std', 'std.tb3_TisAutono', '=', 'branch.tis_id')
                    ->leftJoin((new Branch)->getTable().' AS b', 'b.id', '=', 'branch.branch_id')
                    ->when(count($branch_ids) > 0, function($query) use ($branch_ids) {
                        $query->whereIn('branch.branch_id', $branch_ids);
                    })
                    ->selectRaw('CONCAT_WS(" : ", std.tb3_Tisno, std.tb3_TisThainame) AS title, std.tb3_TisAutono AS id, b.title AS branch_title')
                    ->get();

        return response()->json($data);
    }

    public function getDataBranche($branch_group)
    {
        $data = Branch::where('branch_group_id', $branch_group)->get();
        return response()->json($data);
    }

    public function getDataCertificate(Request $request)
    {

        $table = $request->get('table');
        $taxid = $request->get('applicant_taxid');
        $search = $request->get('search');
   
        if( $table == ( new CertiIBExport )->getTable() || is_null( $table ) ){
            $query = CertiIBExport::query()->where(function($query) use($taxid){
                                                $ids = CertiIb::where('tax_id',  $taxid)->select('id');
                                                $query->whereIn('app_certi_ib_id',  $ids );
                                            })
                                            ->when( $search , function ($query, $search){
                                                $search_full = str_replace(' ', '', $search);
                                                return  $query->where(function ($query2) use($search_full) {
                                                                    $query2->Where(DB::raw("REPLACE(org_name,' ','')"), 'LIKE', "%".$search_full."%")
                                                                            ->OrWhere(DB::raw("REPLACE(certificate,' ','')"), 'LIKE', "%".$search_full."%");
                                                                });
                                            })
                                            ->select('id','certificate', DB::raw('org_name AS cb_name'), 'date_start', 'date_end', 'formula', 'status');

        }else if( $table == ( new CertiCBExport )->getTable() ){

            $query = CertiCBExport::query()->where(function($query) use($taxid){
                                                $ids = CertiCb::where('tax_id',  $taxid)->select('id');
                                                $query->whereIn('app_certi_cb_id',  $ids );
                                            })
                                            ->when( $search , function ($query, $search){
                                                $search_full = str_replace(' ', '', $search);
                                                return  $query->where(function ($query2) use($search_full) {
                                                                    $query2->Where(DB::raw("REPLACE(name_standard,' ','')"), 'LIKE', "%".$search_full."%")
                                                                            ->OrWhere(DB::raw("REPLACE(certificate,' ','')"), 'LIKE', "%".$search_full."%");
                                                                });
                                            })
                                            ->select('id','certificate', DB::raw('name_standard AS cb_name') , 'date_start', 'date_end', 'formula', 'status')->get();
                                        
        }
        $DT    = Datatables::of($query);
        $DT->addIndexColumn();      
        $DT->addColumn('certificate', function ($item) {
               return $item->certificate;
            })
            ->addColumn('cb_name', function ($item) {
                return $item->cb_name;
            })
            ->addColumn('formula', function ($item) {
                return $item->formula;
            })

            ->addColumn('table', function ($item) use( $table ) {
                return $table;
            }); 



        if( $table == ( new CertiIBExport )->getTable() || is_null( $table ) ){
          
            $DT->addColumn('date_start', function ($item) {
                return  !empty($item->CertiIBFileAll->start_date) ? HP::revertDate($item->CertiIBFileAll->start_date,true) : '';
            })
            ->addColumn('date_end', function ($item) {
                return  !empty($item->CertiIBFileAll->end_date) ?  HP::revertDate($item->CertiIBFileAll->end_date,true) : '';
            })
            ->addColumn('status', function ($item) {
                $certificate_date_end = !empty($item->CertiIBFileAll->end_date)?$item->CertiIBFileAll->end_date:null;
                if( $certificate_date_end >= date('Y-m-d') ){
                    return 'ใช้งาน';
                }else{
                    return 'หมดอายุ';
                }
            })->addColumn('action', function ($item) use( $table ) {

                $date_end =  !empty($item->CertiIBFileAll->end_date)?$item->CertiIBFileAll->end_date:null;
                if( $date_end >= date('Y-m-d') ){
                    return '<button class="btn btn-info btn_select_cer" type="button" data-id="'.($item->id).'" data-table="'.($table).'" data-certificate_no="'.(!is_null($item->certificate)?$item->certificate:null).'" data-date_end="'.( !empty($item->CertiIBFileAll->end_date)?HP::revertDate($item->CertiIBFileAll->end_date):null ).'" data-date_start="'.( !empty($item->CertiIBFileAll->start_date)?HP::revertDate($item->CertiIBFileAll->start_date):null ).'"> เลือก </button>';
                }else{
                    return '<button class="btn btn-info" type="button" disabled> เลือก </button>';
                }
            }) ;  
               

        }else if( $table == ( new CertiCBExport )->getTable() ){
 
            $DT->addColumn('date_start', function ($item) {

                return  !empty($item->CertiCBFileAll->start_date) ? HP::revertDate($item->CertiCBFileAll->start_date,true) : '';
            })
            ->addColumn('date_end', function ($item) {
                return  !empty($item->CertiCBFileAll->end_date) ?  HP::revertDate($item->CertiCBFileAll->end_date,true) : '';
            })
            ->addColumn('status', function ($item) {
                $certificate_date_end = !empty($item->CertiCBFileAll->end_date)?$item->CertiCBFileAll->end_date:null;
                if( $certificate_date_end >= date('Y-m-d') ){
                    return 'ใช้งาน';
                }else{
                    return 'หมดอายุ';
                }
            })->addColumn('action', function ($item) use( $table ) {

                $date_end = !empty($item->CertiCBFileAll->end_date)?$item->CertiCBFileAll->end_date:null;
                if( $date_end >= date('Y-m-d') ){
                    return '<button class="btn btn-info btn_select_cer" type="button" data-id="'.($item->id).'" data-table="'.($table).'" data-certificate_no="'.(!is_null($item->certificate)?$item->certificate:null).'" data-date_end="'.( !empty($item->CertiCBFileAll->end_date)?HP::revertDate($item->CertiCBFileAll->end_date):null ).'" data-date_start="'.( !empty($item->CertiCBFileAll->start_date)?HP::revertDate($item->CertiCBFileAll->start_date):null ).'"> เลือก </button>';
                }else{
                    return '<button class="btn btn-info" type="button" disabled> เลือก </button>';
                }
            }) ;  
                

        }


        return $DT->order(function ($query) {
                //    $query->orderBy('id', 'DESC');
              })
              ->rawColumns([ 'action'])
              ->make(true);
        // return Datatables::of($query)
        //                     ->addIndexColumn()
        //                     ->addColumn('certificate', function ($item) {
        //                         return $item->certificate;
        //                     })
        //                     ->addColumn('cb_name', function ($item) {
        //                         return $item->cb_name;
        //                     })
        //                     ->addColumn('formula', function ($item) {
        //                         return $item->formula;
        //                     })
        //                     ->addColumn('date_start', function ($item) {
        //                         return HP::revertDate($item->date_start,true);
        //                     })
        //                     ->addColumn('date_end', function ($item) {
        //                         return HP::revertDate($item->date_end,true);
        //                     })
        //                     ->addColumn('table', function ($item) use( $table ) {
        //                         return $table;
        //                     })
        //                     ->addColumn('status', function ($item) {
        //                         $certificate_date_end = !is_null($item->date_end)?$item->date_end:null;
        //                         if( $certificate_date_end >= date('Y-m-d') ){
        //                             return 'ใช้งาน';
        //                         }else{
        //                             return 'หมดอายุ';
        //                         }
        //                     })
        //                     ->order(function ($query) {
        //                         $query->orderBy('id', 'DESC');
        //                     })
        //                     ->addColumn('action', function ($item) use( $table ) {

        //                         $date_end = !is_null($item->date_end)?$item->date_end:null;
        //                         if( $date_end >= date('Y-m-d') ){
        //                             return '<button class="btn btn-info btn_select_cer" type="button" data-id="'.($item->id).'" data-table="'.($table).'" data-certificate_no="'.(!is_null($item->certificate)?$item->certificate:null).'" data-date_end="'.( !is_null($item->date_end)?HP::revertDate($item->date_end):null ).'" data-date_start="'.( !is_null($item->date_start)?HP::revertDate($item->date_start):null ).'"> เลือก </button>';
        //                         }else{
        //                             return '<button class="btn btn-info" type="button" disabled> เลือก </button>';
        //                         }
        //                     })
        //                     ->rawColumns([ 'action'])
        //                     ->make(true);

    }


    public function getDataInspectors(Request $request)
    {
        $filter_search = $request->get('search');
        $filter_branch_group = $request->input('branch_group');
        $filter_branch = $request->input('branch');
        $filter_freelance = $request->input('freelance');
        $agency_taxid = $request->input('agency_taxid');
        $request_branch_group_id = $request->input('branch_group_id');
        $request_branch_ids = $request->input('branch_id', [null]);//ไอดีสาขาจากคำขอ
        $request_tis_nos    = $request->input('tis_nos', [null]);//เลขมอก.จากคำขอ

        $query = Inspectors::query()->where(function($query) use($filter_freelance) {
                                        // if($filter_freelance != 'All'){
                                        //     $query->whereIn('agency_taxid', Inspectors::select('inspectors_taxid'));
                                        // }
                                    })
                                    ->where('state', 1)
                                    ->when( $filter_branch_group , function ($query, $filter_branch_group){
                                        $query->whereHas('scopes', function ($query) use($filter_branch_group) {
                                                    $query->where('branch_group_id', $filter_branch_group );
                                                });
                                    })
                                    ->when( $filter_branch , function ($query, $filter_branch){
                                        $query->whereHas('scopes', function ($query) use($filter_branch) {
                                                    $query->where('branch_id', $filter_branch );
                                                });
                                    })
                                    ->when( $filter_search , function ($query, $filter_search){
                                        $search_full = str_replace(' ', '', $filter_search);
                                        return  $query->where(function ($query2) use($search_full) {
                                                            $query2->Where(DB::raw("REPLACE(inspectors_taxid,' ','')"), 'LIKE', "%".$search_full."%")
                                                                    ->OrWhere(DB::raw("CONCAT(REPLACE(inspectors_prefix,' ',''),'', REPLACE(inspectors_first_name,' ',''),'', REPLACE(inspectors_last_name,' ',''))"), 'LIKE', "%".$search_full."%");
                                                        });
                                    })
                                    ->when($request_branch_ids, function ($query, $request_branch_ids){//กรองตามสาขาตามคำขอ
                                        $query_branch = InspectorsScope::query()->whereIn('branch_id', $request_branch_ids)->select('inspectors_id');
                                        $query->whereIn('id', $query_branch);
                                    })->when($request_tis_nos, function ($query, $request_tis_nos){//กรองตามเลขมอก.จากคำขอ
                                        $query_tis    = InspectorsScopeTis::whereIn('tis_no', $request_tis_nos)->select('inspector_scope_id');
                                        $query_branch = InspectorsScope::query()->whereIn('id', $query_tis)->select('inspectors_id');
                                        $query->whereIn('id', $query_branch);
                                    });



        return Datatables::of($query)
                        ->addIndexColumn()
                        ->addColumn('checkbox', function ($item) use ($request_branch_ids, $agency_taxid) {

                            $ScopeDataSet = $this->ScopeDataSet($item, $request_branch_ids);
                            $json_scope = !empty($ScopeDataSet) ? json_encode($ScopeDataSet, JSON_UNESCAPED_UNICODE) : null ;
                            $scope = !empty($json_scope) ? str_replace('"', "'", $json_scope) : '';

                            $data = 'data-full_name="'.($item->AgencyFullName).'"';
                            $data .= 'data-taxid="'.($item->inspectors_taxid).'"';
                            $data .= 'data-scope="'.($scope).'"';
                            $data .= 'data-id="'.($item->id).'"';
                            $data .= 'data-inspectors_prefix="'.($item->inspectors_prefix).'"';
                            $data .= 'data-inspectors_first_name="'.($item->inspectors_first_name).'"';
                            $data .= 'data-inspectors_last_name="'.($item->inspectors_last_name).'"';

                            $data .= 'data-inspector_type="'.( ($item->agency_taxid == $agency_taxid)?1:2 ).'"';


                            return '<input type="checkbox" name="item_checkbox[]" class="item_checkbox" '.($data).'  value="'.$item->id.'">';
                        })
                        ->addColumn('full_name', function ($item) {
                            return $item->AgencyFullName;
                        })
                        ->addColumn('inspectors_taxid', function ($item) {
                            return $item->inspectors_taxid;
                        })
                        ->addColumn('inspector_type', function ($item) use ($agency_taxid) {
                            return $item->agency_taxid == $agency_taxid ? 'ผู้ตรวจของหน่วยตรวจ' : 'ผู้ตรวจอิสระ' ;
                        })
                        ->addColumn('scope', function ($item) use ($request_branch_ids){
                            $ScopeShow = $this->ScopeShow($item, $request_branch_ids);
                            return $ScopeShow;
                        })
                        ->rawColumns([ 'checkbox', 'scope'])
                        ->make(true);
    }

    public function ScopeShow($Inspectors, $branch_ids){

        $app_scope = InspectorsScope::whereIn('branch_id', is_array($branch_ids) ? $branch_ids : [])
                                    ->where('inspectors_id', $Inspectors->id)
                                    ->select('branch_group_id')
                                    ->groupBy('branch_group_id')
                                    ->get();

        $html = '<ul  class="list-unstyled">';
        foreach($app_scope AS $item){
            $bs_branch_group = $item->bs_branch_group;

            if( !is_null($bs_branch_group) ){

                $html .= '<li>'.($bs_branch_group->title).'</li>';
                $scope = InspectorsScope::where('branch_group_id', $bs_branch_group->id)
                                        ->whereIn('branch_id', $branch_ids)
                                        ->where('inspectors_id', $Inspectors->id)
                                        ->select('branch_id')
                                        ->get();
                $html .= '<li>';
                $html .= '<ul>';
                $list = [];
                foreach( $scope as $branch ){
                    $bs_branch = $branch->bs_branch;
                    $list[] = $bs_branch->title;
                }
                $html .= '<li>'.( implode( ' ,',  $list ) ).'</li>';
                $html .= '</ul>';
                $html .= '</li>';
            }

        }

        $html .= '</ul>';

        return $html;
    }

    public function ScopeDataSet($Inspectors, $branch_ids)
    {

        $app_scope = InspectorsScope::whereIn('branch_id', is_array($branch_ids) ? $branch_ids : [] )
                                    ->where('inspectors_id', $Inspectors->id)
                                    ->select('branch_group_id')
                                    ->groupBy('branch_group_id')
                                    ->get();

        $list = [];
        foreach( $app_scope AS $item ){
            $bs_branch_group = $item->bs_branch_group;

            if(!is_null($bs_branch_group)){
                $scope = InspectorsScope::where('branch_group_id', $bs_branch_group->id)
                                        ->whereIn('branch_id', $branch_ids)
                                        ->where('inspectors_id', $Inspectors->id)
                                        ->select('branch_id')
                                        ->get();
                $list_branch = [];
                foreach( $scope as $branch ){
                    $bs_branch =  $branch->bs_branch;
                    if( !is_null($bs_branch) ){
                        $dataB = new stdClass;
                        $dataB->branch_title = (string)$bs_branch->title;
                        $dataB->branch_id = (string)$bs_branch->id;
                        $list_branch[$bs_branch->id] = $dataB;
                    }
                }

                $data = new stdClass;
                $data->branch_group_title = (string)$bs_branch_group->title;
                $data->branch_group_id = (string)$bs_branch_group->id;
                $data->branch = $list_branch;
                $list[$bs_branch_group->id] = $data;
            }

        }
        return $list;
    }

    public function AutoRunRefApplication($type)
    {
        $today = date('Y-m-d');
        $dates = explode('-', $today);
        $year = ( date('y')  + 43);
        $ref =( $type == 1)? 'IB':'CB';

        $query_check = ApplicationIbcb::select('application_no')->whereYear('created_at',$dates[0])->whereMonth('created_at',$dates[1])->orderBy('application_no')->get();
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

            $no_check = ApplicationIbcb::where('application_no', $strNextSeq )->first();
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

    public function delete_update(Request $request)
    {
        try {

            $id = $request->get('id');
            $application = ApplicationIbcb::findOrFail($id);

            $requestData = $request->all();
            $requestData['application_status'] = 99;
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

    public function getStandards($type)
    {
        $data = BStandard::whereJsonContains('standard_type', $type)->get();

        return response()->json($data);
    }
}
