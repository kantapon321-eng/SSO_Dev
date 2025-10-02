@extends('layouts.app')

@section('content')
@push('css')
    <link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('plugins/components/bootstrap-datepicker-thai/css/datepicker.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
    <style>
        .label-height{
            line-height: 10px;
        }
    </style>
@endpush

@php
    $config  = HP::getConfig();
    $skip = session()->pull('reg_skip', false);
    $prefill = session('reg_prefill'); // <-- ข้อมูลที่ callback ใส่มาให้
    $redirect_uri = request()->get('redirect_uri');
@endphp

{{-- @php
      $setting_system   = App\Models\Setting\SettingSystem::select('id','title')->where('state',1)->get();
@endphp --}}

<section id="wrapper" class="login-register">
    <div style="width:80%; display: block; margin: auto;">
        <div class="white-box">
            <form class="form-horizontal" id="register_form" method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                
                {{csrf_field()}}
                <input type="hidden" name="redirect_uri" value="{{ $redirect_uri }}">

                <div class="white-box">
                    <div class="row">
                        <div class="col-sm-12">
                            <legend><h3 class="box-title"> ลงทะเบียนเพื่อเข้าใช้งานระบบบริการอิเล็กทรอนิกส์ สมอ.</h3></legend>

                            @if($errors->any())
                                <div class="alert alert-danger"> {!! implode('', $errors->all()) !!}</div>
                            @endif

                            {!! Form::hidden('jform[juristic_status]', null, [ 'class' => '', 'id' => 'juristic_status' ] ) !!}
                            {!! Form::hidden('jform[check_api]', null, [ 'class' => '', 'id' => 'check_api' ] ) !!}
                            <div class="form-group {{ $errors->has('trader_type') ? 'has-error' : ''}}">
                                {!! HTML::decode(Form::label('jform[applicanttype_id]', 'ประเภทการลงทะเบียน'.' : <span class="text-danger">*</span>', ['class' => 'col-md-3   control-label'])) !!}
                                <div class="col-md-9" >
                                    {!! Form::radio('jform[applicanttype_id]', '1', true, ['class'=>'check applicanttype_id', 'data-radio'=>'iradio_square-green','id'=>'applicanttype_id1']) !!}
                                    <label for="applicanttype_id1">&nbsp;นิติบุคคล&nbsp;&nbsp;</label>
                                    {!! Form::radio('jform[applicanttype_id]', '2', false, ['class'=>'check applicanttype_id', 'data-radio'=>'iradio_square-green','id'=>'applicanttype_id2']) !!}
                                    <label for="applicanttype_id2">&nbsp;บุคคลธรรมดา&nbsp;&nbsp;</label>
                                    {!! Form::radio('jform[applicanttype_id]', '3', false, ['class'=>'check applicanttype_id', 'data-radio'=>'iradio_square-green','id'=>'applicanttype_id3']) !!}
                                    <label for="applicanttype_id3">&nbsp;คณะบุคคล&nbsp;&nbsp;</label>
                                    {!! Form::radio('jform[applicanttype_id]', '4', false, ['class'=>'check applicanttype_id', 'data-radio'=>'iradio_square-green','id'=>'applicanttype_id4']) !!}
                                    <label for="applicanttype_id4">&nbsp;ส่วนราชการ&nbsp;&nbsp;</label>
                                    {!! Form::radio('jform[applicanttype_id]', '5', false, ['class'=>'check applicanttype_id', 'data-radio'=>'iradio_square-green','id'=>'applicanttype_id5']) !!}
                                    <label for="applicanttype_id5">&nbsp;อื่นๆ&nbsp;&nbsp;</label>
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('person_type') ? 'has-error' : ''}}">
                                {{-- {!! HTML::decode(Form::label('jform[person_type]', 'ประเภทบุคคล'.' : <span class="text-danger">*</span>',  ['class' => 'col-md-3   control-label'])) !!} --}}
                                {!! HTML::decode(Form::label(' ',  ' ',  ['class' => 'col-md-3   control-label'])) !!}
                                <div class="col-md-3" >
                                    {!! Form::select('jform[person_type]',
                                                    ['1'=>'เลขประจำตัวผู้เสียภาษี','2'=>'เลขที่หนังสือเดินทาง','3'=>'เลขทะเบียนธุรกิจคนต่างด้าว'],
                                                    null,
                                                    ['class' => 'form-control', 'id'=>'person_type',
                                                    'placeholder' =>'- เลือกประเภทข้อมูลที่ใช้ลงทะเบียน -',
                                                    'required'=> true])
                                    !!}
                                </div>
                                <div class="col-md-4" >
                                    {!! Form::text('jform[tax_number]', null, ['class' => 'form-control check_format_en_and_number','id'=>'tax_number','required'=> true , 'maxlength' => '13','placeholder'=>'เลขนิติบุคคล']) !!}
                                    {!! $errors->first('jform[tax_number]', '<p class="help-block">:message</p>') !!}
                                </div>
                                <div class="col-md-2" >
                                    <button type="button" id="search" class="btn btn-primary"> ค้นหา </button>
                                </div>
                            </div>
    <div id="div_profile">
                            <div class="form-group {{ $errors->has('jform[date_birthday]') ? 'has-error' : ''}}">
                                {!! HTML::decode(Form::label('jform[date_birthday]', ' <span id="span_date_birthday">วันที่จดทะเบียน</span>'.' : <span class="text-danger">*</span>', ['class' => 'col-md-3 control-label'])) !!}
                                <div class="col-md-3" >
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="icon-calender"></i></span>
                                        {!! Form::text('jform[date_birthday]', null, ['class' => 'form-control datepicker','id'=>'date_of_birth','placeholder'=>'วันที่จดทะเบียน', 'data-date-end-date' =>'0d"', 'required'=> true]) !!}
                                        {!! $errors->first('jform[date_birthday]', '<p class="help-block">:message</p>') !!}
                                    </div>
                                </div>
                                {!! Form::hidden('jform[date_of_birth_encrypt]', null, ['id'=>'date_of_birth_encrypt']) !!}

                                {{-- {!! HTML::decode(Form::label('jform[branch_code]', 'รหัสสาขา'.' :', ['class' => 'col-md-2 control-label branch_code'])) !!}
                                <div class="col-md-4" >
                                    {!! Form::text('jform[branch_code]', null, ['class' => 'form-control branch_code','id'=>'branch_code','placeholder'=>'รหัสสาขา','required'=> false]) !!}
                                    {!! $errors->first('jform[branch_code]', '<p class="help-block">:message</p>') !!}
                                </div> --}}
                            </div>

                            <div class="form-group {{ $errors->has('jform[prefix_name]') ? 'has-error' : ''}}">
                                {!! HTML::decode(Form::label('jform[prefix_name]', 'ชื่อผู้ประกอบการ'.' : <span class="text-danger">*</span>', ['class' => 'col-md-3 control-label'])) !!}
                                <div class="col-md-3 div_legal_entity" >  <!-- นิติบุคคล    -->
                                    {!! Form::select('jform[prefix_name]',
                                    ['1'=>'บริษัทจำกัด','2'=>'บริษัทมหาชนจำกัด','3'=>'ห้างหุ้นส่วนจำกัด','4'=>'ห้างหุ้นส่วนสามัญนิติบุคคล'],
                                    null,
                                    ['class' => 'form-control', 'id'=>'prefix_name',
                                   'placeholder' =>'- เลือกประเภทการทะเบียน -',
                                   'required'=> true
                                   ]) !!}
                                </div>
                                <div class="col-md-6 div_legal_entity" >   <!-- นิติบุคคล    -->
                                    {!! Form::text('jform[name]', null, ['class' => 'form-control','id'=>'name','placeholder'=>'เช่น บริษัท XXX จำกัด', 'required'=> true, 'maxlength' => 350]) !!}
                                    {!! $errors->first('jform[name]', '<p class="help-block">:message</p>') !!}
                                </div>

                                <div class="col-md-3 div_natural_person" >   <!-- บุคคลธรรมดา     -->
                                    {!! Form::select('jform[person_prefix_name]',
                                    App\Models\Basic\Prefix::where('state',1)->pluck('initial', 'id')->all(),
                                    null,
                                    ['class' => 'form-control',
                                    'id'=>'person_prefix_name',
                                    'placeholder' =>'- เลือกคำนำหน้าชื่อ -',
                                    'required'=> true]) !!}
                                </div>
                                <div class="col-md-3 div_natural_person" >   <!-- บุคคลธรรมดา     -->
                                    {!! Form::text('jform[person_first_name]', null, ['class' => 'form-control','id'=>'person_first_name','placeholder'=>'ชื่อ','required' => false, 'maxlength' => 191]) !!}
                                    {!! $errors->first('jform[person_first_name]', '<p class="help-block">:message</p>') !!}
                                </div>
                                <div class="col-md-3 div_natural_person" >   <!-- บุคคลธรรมดา     -->
                                    {!! Form::text('jform[person_last_name]', null, ['class' => 'form-control','id'=>'person_last_name','placeholder'=>'นามสกุล','required' => false, 'maxlength' => 191]) !!}
                                    {!! $errors->first('jform[person_last_name]', '<p class="help-block">:message</p>') !!}
                                </div>


                                <div class="col-md-9 div_natural_faculty" >   <!-- คณะบุคคล    -->
                                    {!! Form::text('jform[faculty_name]', null, ['class' => 'form-control','id'=>'faculty_name','placeholder'=>'ชื่อคณะบุคคล','required'=> false, 'maxlength' => 350]) !!}
                                    {!! $errors->first('jform[faculty_name]', '<p class="help-block">:message</p>') !!}
                                </div>

                                <div class="col-md-9 div_natural_service" >   <!-- ส่วนราชการ     -->
                                    {!! Form::text('jform[service_name]', null, ['class' => 'form-control','id'=>'service_name','placeholder'=>'ชื่อส่วนราชการ','required'=> false, 'maxlength' => 350]) !!}
                                    {!! $errors->first('jform[service_name]', '<p class="help-block">:message</p>') !!}
                                </div>

                                <div class="col-md-9 div_natural_another" >   <!-- อื่นๆ     -->
                                    {!! Form::text('jform[another_name]', null, ['class' => 'form-control','id'=>'another_name','placeholder'=>'ชื่ออื่นๆ','required'=> false, 'maxlength' => 350]) !!}
                                    {!! $errors->first('jform[another_name]', '<p class="help-block">:message</p>') !!}
                                </div>

                            </div>


                            <div class="form-group div_legal_nationality {{ $errors->has('jform[nationality]') ? 'has-error' : ''}}">
                                    {!! HTML::decode(Form::label('jform[nationality]', 'สัญชาติ'.' :', ['class' => 'col-md-3 control-label'])) !!}
                                    <div class="col-md-3 " >  <!-- สัญชาติ    -->
                                        {!! Form::text('jform[nationality]', null, ['class' => 'form-control','id'=>'nationality','placeholder'=>'สัญชาติ','required'=> false, 'maxlength' => 30]) !!}
                                        {!! $errors->first('jform[nationality]', '<p class="help-block">:message</p>') !!}
                                    </div>
                            </div>

    <div id="div_branch_type">
                            <div class="form-group {{ $errors->has('jform[branch_type]') ? 'has-error' : ''}}">
                                {!! HTML::decode(Form::label('jform[branch_type]', 'ประเภทสาขา'.' : <span class="text-danger">*</span>', ['class' => 'col-md-3 control-label branch_code'])) !!}
                                <div class="col-md-4" >
                                    {!! Form::radio('jform[branch_type]', '1', true, ['class'=>'check branch_type', 'data-radio'=>'iradio_square-blue','id'=>'branch_type1']) !!}
                                    <label for="branch_type1">&nbsp;สำนักงานใหญ่&nbsp;&nbsp;</label>
                                    {!! Form::radio('jform[branch_type]', '2', false, ['class'=>'check branch_type', 'data-radio'=>'iradio_square-blue','id'=>'branch_type2']) !!}
                                    <label for="branch_type2">&nbsp;สาขา&nbsp;&nbsp;</label>
                                </div>
                            </div>

                            <div class="form-group div_branch_code{{ $errors->has('jform[branch_code]') ? 'has-error' : ''}}">
                                {!! HTML::decode(Form::label('jform[branch_code]', 'รหัสสาขา'.' : <span class="text-danger">*</span>', ['class' => 'col-md-3 control-label branch_code'])) !!}
                                <div class="col-md-4" >
                                    {!! Form::text('jform[branch_code]', null, ['class' => 'form-control check_format_en','id'=>'branch_code','placeholder'=>'รหัสสาขา','required'=> false, 'maxlength' => 10]) !!}
                                    {!! $errors->first('jform[branch_code]', '<p class="help-block">:message</p>') !!}
                                </div>
                            </div>
    </div>
                            <div class="white-box">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <legend><h4 id="label_address_no"> ที่ตั้งสำนักงานใหญ่</h4></legend>

                                        {{--  start  ที่ตั้งสำนักงานใหญ่ --}}
                                        <div class="form-group {{ $errors->has('jform[address_no]') ? 'has-error' : ''}}">
                                            <div class="col-md-8" >
                                                {!! HTML::decode(Form::label('jform[address_no]', 'เลขที่, อาคาร, ชั้น, เลขที่ห้อง, ชื่อหมู่บ้าน'.'  <span class="text-danger">*</span>', ['class' => 'col-md-12  '])) !!}
                                                <div class="col-md-12 " >
                                                    {!! Form::text('jform[address_no]', null, ['class' => 'form-control','id'=>'address_no','required'=> true, 'maxlength' => 150]) !!}
                                                    {!! $errors->first('jform[address_no]', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div>
                                            {{-- <div class="col-md-4" >
                                                {!! HTML::decode(Form::label('jform[building]', 'อาคาร/หมู่บ้าน', ['class' => 'col-md-12'])) !!}
                                                <div class="col-md-12 " >
                                                    {!! Form::text('jform[building]', null, ['class' => 'form-control','id'=>'building']) !!}
                                                    {!! $errors->first('jform[building]', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div> --}}
                                            <div class="col-md-4" >
                                                {!! HTML::decode(Form::label('jform[soi]', 'ตรอก/ซอย', ['class' => 'col-md-12 '])) !!}
                                                <div class="col-md-12 " >
                                                    {!! Form::text('jform[soi]', null, ['class' => 'form-control', 'id'=>'soi', 'maxlength' => 80]) !!}
                                                    {!! $errors->first('jform[soi]', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('jform[moo]') ? 'has-error' : ''}}">
                                              <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[moo]', 'หมู่', ['class' => 'col-md-12  '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[moo]', null, ['class' => 'form-control', 'id' => 'moo', 'maxlength' => 80]) !!}
                                                          {!! $errors->first('jform[moo]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                              <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[street]', 'ถนน', ['class' => 'col-md-12  '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[street]', null, ['class' => 'form-control', 'id' => 'street', 'required'=> false, 'maxlength' => 80]) !!}
                                                          {!! $errors->first('jform[street]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('address_search') ? 'has-error' : ''}}">
                                            <div class="col-md-8">
                                                {!! Form::label('address_search', 'ค้นหา', ['class' => 'col-md-12']) !!}
                                                <div class="col-md-12 ">
                                                    {!! Form::text('address_search', null, ['class' => 'form-control', 'autocomplete' => 'off', 'data-provide' => 'typeahead', 'placeholder' => 'ค้นหา:ตำบล/แขวง,อำเภอ/เขต,จังหวัด,รหัสไปรษณีย์', 'id'=>'address_search' ]) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-4" >
                                                {!! HTML::decode(Form::label('jform[subdistrict]', 'แขวง/ตำบล'.' <span class="text-danger">*</span>', ['class' => 'col-md-12 '])) !!}
                                                <div class="col-md-12 " >
                                                        {!! Form::text('jform[subdistrict]', null, ['class' => 'form-control', 'id'=>'subdistrict', 'required'=> true, 'maxlength' => 70, 'readonly'=>true]) !!}
                                                        {!! $errors->first('jform[subdistrict]', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('jform[district]') ? 'has-error' : ''}}">
                                              <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[district]', 'เขต/อำเภอ'.'  <span class="text-danger">*</span>', ['class' => 'col-md-12  '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[district]', null, ['class' => 'form-control', 'id'=>'district', 'required'=> true, 'maxlength' => 70, 'readonly'=>true]) !!}
                                                          {!! $errors->first('jform[district]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                              <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[province]', 'จังหวัด'.'  <span class="text-danger">*</span>', ['class' => 'col-md-12  '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[province]', null, ['class' => 'form-control', 'id'=>'province', 'required'=> true, 'maxlength' => 70, 'readonly'=>true]) !!}
                                                          {!! $errors->first('jform[province]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                              <div class="col-md-2" >
                                                    {!! HTML::decode(Form::label('jform[zipcode]', 'รหัสไปรษณีย์'.' <span class="text-danger">*</span>', ['class' => 'col-md-12 '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[zipcode]', null, ['class' => 'form-control ','id'=>'zipcode','required'=> true, 'maxlength' => 5]) !!}
                                                          {!! $errors->first('jform[zipcode]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                              {{-- <div class="col-md-2" >
                                                    {!! HTML::decode(Form::label('jform[country_code]', 'รหัสประเทศ'.' <span class="text-danger">*</span>', ['class' => 'col-md-12 '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[country_code]', null, ['class' => 'form-control','id'=>'country_code','required'=> true]) !!}
                                                          {!! $errors->first('jform[country_code]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div> --}}
                                        </div>
                                        <div class="form-group {{ $errors->has('jform[latitude]') ? 'has-error' : ''}}">
                                            <div class="col-md-4" >
                                                  {!! HTML::decode(Form::label('jform[latitude]', 'พิกัดที่ตั้ง (ละติจูด)'.'  <span class="text-danger">*</span>', ['class' => 'label_latitude col-md-12'])) !!}
                                                  <div class="col-md-12 " >
                                                        {!! Form::text('jform[latitude]', null, ['class' => 'form-control input_number','id'=>'latitude','required'=> true,'placeholder' =>'กรอกเฉพาะตัวเลข', 'maxlength' => 255]) !!}
                                                        {!! $errors->first('jform[latitude]', '<p class="help-block">:message</p>') !!}
                                                  </div>
                                            </div>
                                            <div class="col-md-4" >
                                                  {!! HTML::decode(Form::label('jform[longitude]', 'พิกัดที่ตั้ง (ลองจิจูด)'.'  <span class="text-danger">*</span>', ['class' => 'label_longitude col-md-12'])) !!}
                                                  <div class="col-md-12 " >
                                                        {!! Form::text('jform[longitude]', null, ['class' => 'form-control input_number','id'=>'longitude','required'=> true,'placeholder' =>'กรอกเฉพาะตัวเลข', 'maxlength' => 255]) !!}
                                                        {!! $errors->first('jform[longitude]', '<p class="help-block">:message</p>') !!}
                                                  </div>
                                            </div>
                                            <div class="col-md-4" >
                                                  {!! HTML::decode(Form::label(' ', "&nbsp;", ['class' => 'col-md-12 '])) !!}
                                                  <div class="col-md-12 " >
                                                            <a class="btn btn-default" id="show_map" onclick="return false">
                                                                ค้นหาจากแผนที่
                                                            </a>
                                                  </div>
                                            </div>
                                      </div>
                                        {{--  end  ที่ตั้งสำนักงานใหญ่ --}}

                                        <br>
                                        <legend><h4 > ที่อยู่ที่สามารถติดต่อได้</h4></legend>
                                        {{--  start  ที่อยู่ที่สามารถติดต่อได้ --}}
                                        <div class="form-group">
                                              <div class="col-md-12">
                                                  <div class="checkbox checkbox-success p-t-0">
                                                      <input   type="checkbox" id="checkbox_contact_address_no">
                                                            <label for="checkbox_contact_address_no" > &nbsp;&nbsp;<span class="checkbox_contact_address_no">ที่เดียวกับที่ตั้งสำนักงานใหญ่</span>
                                                      </label>
                                                  </div>
                                              </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('jform[contact_address_no]') ? 'has-error' : ''}}">
                                              <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[contact_address_no]', 'เลขที่'.'  <span class="text-danger">*</span>', ['class' => 'col-md-12  '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_address_no]', null, ['class' => 'form-control', 'id' => 'contact_address_no', 'required'=> true, 'maxlength' => 100]) !!}
                                                          {!! $errors->first('jform[contact_address_no]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                              <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[contact_building]', 'อาคาร/หมู่บ้าน', ['class' => 'col-md-12  '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_building]', null, ['class' => 'form-control', 'id' => 'contact_building', 'maxlength' => 191]) !!}
                                                          {!! $errors->first('jform[contact_building]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                              <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[contact_soi]', 'ตรอก/ซอย', ['class' => 'col-md-12 '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_soi]', null, ['class' => 'form-control','id'=>'contact_soi', 'maxlength' => 80]) !!}
                                                          {!! $errors->first('jform[contact_soi]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('jform[contact_moo]') ? 'has-error' : ''}}">
                                              <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[contact_moo]', 'หมู่', ['class' => 'col-md-12  '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_moo]', null, ['class' => 'form-control','id'=>'contact_moo', 'maxlength' => 80]) !!}
                                                          {!! $errors->first('jform[contact_moo]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                              <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[contact_street]', 'ถนน', ['class' => 'col-md-12  '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_street]', null, ['class' => 'form-control', 'id' => 'contact_street', 'required'=> false, 'maxlength' => 80]) !!}
                                                          {!! $errors->first('jform[contact_street]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                         
                                        </div>

                                        <div class="form-group {{ $errors->has('contact_address_search') ? 'has-error' : ''}}">
                                            <div class="col-md-8">
                                                {!! Form::label('contact_address_search', 'ค้นหา', ['class' => 'col-md-12']) !!}
                                                <div class="col-md-12 ">
                                                    {!! Form::text('contact_address_search', null, ['class' => 'form-control', 'autocomplete' => 'off', 'data-provide' => 'typeahead', 'placeholder' => 'ค้นหา:ตำบล/แขวง,อำเภอ/เขต,จังหวัด,รหัสไปรษณีย์', 'id'=>'contact_address_search' ]) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[contact_subdistrict]', 'แขวง/ตำบล'.' <span class="text-danger">*</span>', ['class' => 'col-md-12 '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_subdistrict]', null, ['class' => 'form-control', 'id' => 'contact_subdistrict', 'required' => true, 'maxlength' => 70, 'readonly'=>true]) !!}
                                                          {!! $errors->first('jform[contact_subdistrict]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                        </div>

                                        <div class="form-group {{ $errors->has('jform[contact_district]') ? 'has-error' : ''}}">
                                              <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[contact_district]', 'เขต/อำเภอ'.'  <span class="text-danger">*</span>', ['class' => 'col-md-12'])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_district]', null, ['class' => 'form-control', 'id' => 'contact_district', 'required' => true, 'maxlength' => 70, 'readonly'=>true]) !!}
                                                          {!! $errors->first('jform[contact_district]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                              <div class="col-md-4" >
                                                    {!! HTML::decode(Form::label('jform[contact_province]', 'จังหวัด'.'  <span class="text-danger">*</span>', ['class' => 'col-md-12  '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_province]', null, ['class' => 'form-control', 'id' => 'contact_province', 'required' => true, 'maxlength' => 70, 'readonly'=>true]) !!}
                                                          {!! $errors->first('jform[contact_province]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                              <div class="col-md-2" >
                                                    {!! HTML::decode(Form::label('jform[contact_zipcode]', 'รหัสไปรษณีย์'.' <span class="text-danger">*</span>', ['class' => 'col-md-12 '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_zipcode]', null, ['class' => 'form-control ','id'=>'contact_zipcode','required'=> true, 'maxlength' => 5]) !!}
                                                          {!! $errors->first('jform[contact_zipcode]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div>
                                              {{-- <div class="col-md-2" >
                                                    {!! HTML::decode(Form::label('jform[contact_country_code]', 'รหัสประเทศ'.' <span class="text-danger">*</span>', ['class' => 'col-md-12 '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_country_code]', null, ['class' => 'form-control','id'=>'contact_country_code','required'=> true]) !!}
                                                          {!! $errors->first('jform[contact_country_code]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                              </div> --}}
                                        </div>
                                        {{-- end  ที่อยู่ที่สามารถติดต่อได้ --}}

                                    </div>
                                </div>
                            </div>

                            <div class="white-box">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <legend><h4>ข้อมูลผู้ติดต่อ</h4></legend>
                                        {{--  start  ข้อมูลผู้ติดต่อ --}}
                                        <div class="form-group {{ $errors->has('jform[contact_tax_id]') ? 'has-error' : ''}}">
                                            <div class="col-md-3" >
                                                {!! HTML::decode(Form::label('jform[contact_tax_id]', 'เลขบัตรประจำตัวประชาชน'.'  <span class="text-danger">*</span>', ['class' => 'col-md-12'])) !!}
                                                <div class="col-md-12 " >
                                                      {!! Form::text('jform[contact_tax_id]', null, ['class' => 'form-control tax_id_format', 'id' => 'contact_tax_id', 'required' => true]) !!}
                                                      {!! $errors->first('jform[contact_tax_id]', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div>
                                            <div class="col-md-3" >
                                                {!! HTML::decode(Form::label('jform[contact_prefix_name]', 'ชื่อผู้ติดต่อ'.'  <span class="text-danger">*</span>', ['class' => 'col-md-12'])) !!}
                                                <div class="col-md-12 " >
                                                      {!! Form::select('jform[contact_prefix_name]',
                                                      App\Models\Basic\Prefix::where('state',1)->pluck('initial', 'id')->all(),
                                                      null,
                                                      ['class' => 'form-control', 'id'=>'contact_prefix_name',
                                                     'placeholder' =>'- เลือกคำนำหน้าชื่อ -',
                                                     'required'=> true]) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-3" >
                                                    {!! HTML::decode(Form::label('', '&nbsp;', ['class' => 'col-md-12 '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_first_name]', null, ['class' => 'form-control', 'id' => 'first_name', 'placeholder' => 'ชื่อ', 'required' => true, 'maxlength' => 191]) !!}
                                                          {!! $errors->first('jform[contact_first_name]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                            </div>
                                            <div class="col-md-3" >
                                                    {!! HTML::decode(Form::label('', '&nbsp;', ['class' => 'col-md-12 '])) !!}
                                                    <div class="col-md-12 " >
                                                          {!! Form::text('jform[contact_last_name]', null, ['class' => 'form-control', 'id' => 'last_name', 'placeholder' => 'นามสกุล', 'required' => true, 'maxlength' => 191]) !!}
                                                          {!! $errors->first('jform[contact_last_name]', '<p class="help-block">:message</p>') !!}
                                                    </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-4" >
                                                {!! HTML::decode(Form::label('jform[contact_position]', 'ตำแหน่ง', ['class' => 'col-md-12  '])) !!}
                                                <div class="col-md-12 " >
                                                    {!! Form::text('jform[contact_position]', null, ['class' => 'form-control', 'id' => 'contact_position', 'required' => false, 'maxlength' => 255]) !!}
                                                    {!! $errors->first('jform[contact_position]', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div>
                                            <div class="col-md-4" >
                                                {!! HTML::decode(Form::label('jform[contact_tel]', 'เบอร์โทรศัพท์', ['class' => 'col-md-12  '])) !!}
                                                <div class="col-md-12 " >
                                                    {!! Form::text('jform[contact_tel]', null, ['class' => 'form-control', 'id' => 'tel', 'required' => false, 'maxlength' => 30]) !!}
                                                    {!! $errors->first('jform[contact_tel]', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div>
                                            <div class="col-md-4" >
                                                {!! HTML::decode(Form::label('jform[contact_phone_number]', 'เบอร์โทรศัพท์มือถือ'.'  <span class="text-danger">*</span>', ['class' => 'col-md-12'])) !!}
                                                <div class="col-md-12 " >
                                                    {!! Form::text('jform[contact_phone_number]', null, ['class' => 'form-control phone_number_format', 'id' => 'phone_number', 'required' => true]) !!}
                                                    {!! $errors->first('jform[contact_phone_number]', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('jform[email]') ? 'has-error' : ''}}">
                                            <div class="col-md-4" >
                                                {!! HTML::decode(Form::label('jform[contact_fax]', 'โทรสาร', ['class' => 'col-md-12'])) !!}
                                                <div class="col-md-12 " >
                                                    {!! Form::text('jform[contact_fax]', null, ['class' => 'form-control', 'id' => 'fax', 'required' => false, 'maxlength' => 30]) !!}
                                                    {!! $errors->first('jform[contact_fax]', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div>
                                            <div class="col-md-6" >
                                                {!! HTML::decode(Form::label('jform[email]', 'e-Mail'.'  <span class="text-danger">*</span>', ['class' => 'col-md-12  '])) !!}
                                                <div class="col-md-12 " >
                                                    {!! Form::email('jform[email]', null, ['class' => 'form-control', 'id' => 'email', 'placeholder' => 'ระบุ E-mail ที่ใช้งานได้จริง เพื่อรับข่าวสารจาก สมอ.', 'required' => true, 'maxlength' => 100]) !!}
                                                    {!! $errors->first('jform[email]', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div>
                                        </div>
                                        {{-- end  ข้อมูลผู้ติดต่อ --}}

                                    </div>
                                </div>
                            </div>

                            <div class="white-box">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <legend><h4 >ข้อมูลสำหรับ Login เข้าใช้งานระบบ</h4></legend>
                                        {{--  start  ข้อมูลสำหรับ Login เข้าใช้งานระบบ --}}
                                        <div class="form-group {{ $errors->has('jform[username]') ? 'has-error' : ''}}">
                                            {!! HTML::decode(Form::label('jform[username]', 'Username'.' : <span class="text-danger">*</span>', ['class' => 'col-md-3 control-label'])) !!}
                                            <div class="col-md-5" >
                                                {!! Form::text('jform[username]', null, ['class' => 'form-control ','id'=>'username','placeholder'=>'','readonly'=> true]) !!}
                                                {!! $errors->first('jform[username]', '<p class="help-block">:message</p>') !!}
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('jform[password]') ? 'has-error' : ''}}">
                                            {!! HTML::decode(Form::label('jform[password]', 'Password'.' : <span class="text-danger">*</span>', ['class' => 'col-md-3 control-label'])) !!}
                                            <div class="col-md-8" >
                                                <div class="input-group" style="width:420px;">
                                                    <input id="password" type="password" title="กรอกรหัสผ่าน" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }} check_format_en"
                                                     minlength="8" name="password" required placeholder="Password" >
                                                    <span class="input-group-addon" id="eye_change"><i class="glyphicon glyphicon-eye-close"></i></span>
                                                </div>
                                                <span id="span-warn-password" class="text-danger"></span>
                                                <span class="text-warning">รูปแบบรหัสผ่าน ต้องเป็นตัวอักษรภาษาอังกฤษ ตัวพิมพ์ใหญ่หรือตัวพิมพ์เล็ก ตัวเลข สัญลักษณ์ และความยาวไม่น้อยกว่า 8 อักษร</span>
                                            </div>

                                        </div>
                                        <div class="form-group {{ $errors->has('jform[confirm_password]') ? 'has-error' : ''}}">
                                            {!! HTML::decode(Form::label('jform[confirm_password]', 'confirm Password'.' : <span class="text-danger">*</span>', ['class' => 'col-md-3 control-label'])) !!}
                                            <div class="col-md-8" >
                                                {{-- <div class="input-group"> --}}
                                                    <input id="password2" type="password" title="กรอกรหัสผ่าน" class="form-control {{ $errors->has('confirm_password') ? ' is-invalid' : '' }} check_format_en"
                                                     minlength="8" name="password_confirmation" required placeholder="confirm Password" style="width:420px;">
                                                     <span id="span-warn-password2" class="text-danger"></span>
                                                {{-- </div> --}}
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('jform[personfile]') ? 'has-error' : ''}}">
                                            {!! HTML::decode(
                                                Form::label('jform[personfile]', 'เอกสารแนบการยืนยันตัวตน'.' : <span class="text-danger personfile">*</span>' ,
                                                ['class' => 'label_personfile col-md-3 control-label label-height'])
                                            )!!}
                                            <div class="col-md-8" >
                                                <div  style="width:420px;">
                                                    <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                                        <div class="form-control" data-trigger="fileinput">
                                                            <i class="glyphicon glyphicon-file fileinput-exists"></i>
                                                            <span class="fileinput-filename"></span>
                                                        </div>
                                                        <span class="input-group-addon btn btn-default btn-file">
                                                            <span class="fileinput-new">เลือกไฟล์</span>
                                                            <span class="fileinput-exists">เปลี่ยน</span>
                                                            <input type="file" name="personfile"  id="personfile"   required accept="application/pdf"  >
                                                        </span>
                                                        <a href="#" class="input-group-addon btn btn-default fileinput-exists delete_personfile"  data-dismiss="fileinput">ลบ</a>
                                                    </div>
                                                </div>
                                                <p id="p_entity"> <span class="text-warning">สำเนาบัตรประจำตัวผู้เสียภาษี หรือสำเนาหนังสือรับรองการจดทะเบียนนิติบุคคล</span>   </p>
                                                <p id="p_another">
                                                    ระบุแนบเอกสาร ดังนี้ <span class="text-warning">{!! '(* รับเฉพาะ file PDF ขนาดไม่เกิน '.  str_replace("M","",ini_get('upload_max_filesize')) .'Mb เท่านั้น)'  !!} </span>
                                                    <br>
                                                    กรณี นิติบุคคล หรือ ธุรกิจคนต่างด้าว ตาม พรบ.การประกอบธุรกิจของคนต่างด้าว พ.ศ. 2542
                                                    <br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;- หนังสือรับรองบริษัท อายุไม่เกิน 6 เดือน ประทับตราบริษัทและให้คณะกรรมการเซ็นกำกับทุกแผ่น
                                                    <br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;- หนังสือมอบอำนาจ ติดอากรแสตมป์ 30 บาท พร้อมประทับตราบริษัท
                                                    <br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;- สำเนาบัตรประชาชน ของผู้มอบอำนาจและผู้รับมอบอำนาจ
                                                    <br>
                                                    กรณี เป็นชาวต่างชาติ
                                                    <br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;- สำเนาหนังสือเดินทางผู้มีอำนาจ
                                                    <br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;- หนังสือมอบอำนาจ ติดอากรแสตมป์ 30 บาท
                                                    <br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;- สำเนาบัตรประชาชน ของผู้รับมอบอำนาจ
                                                    <br>
                                                </p>
                                                {!! $errors->first('activity_file', '<p class="help-block">:message</p>') !!}
                                            </div>
                                        </div>



                                        {{-- <div class="form-group {{ $errors->has('jform[contact_name]') ? 'has-error' : ''}}">
                                              {!! HTML::decode(Form::label('jform[contact_name]', 'ลงทะเบียนเข้าใช้งานระบบ'.' : <span class="text-danger">*</span>', ['class' => 'col-md-3 control-label'])) !!}
                                              <div class="col-md-3" >
                                                    @if (count($setting_system) > 0)
                                                          @foreach ($setting_system as $system)
                                                          <div class="col-md-12">
                                                                <div class="checkbox checkbox-success p-t-0">
                                                                    <input   type="checkbox" class="system" name="jform[system][]" value="{{ $system->id }}" checked disabled>
                                                                    <label for="#"> &nbsp;&nbsp;{{ $system->title }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                          @endforeach
                                                    @endif
                                               </div>
                                         </div> --}}
                                        {{-- end  ข้อมูลสำหรับ Login เข้าใช้งานระบบ --}}
                                    </div>
                                </div>
                            </div>
     </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>เงื่อนไขการใช้งาน และ การลงทะเบียน : </label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-file-text-o"></i>
                                        </div>
                                        <textarea type="text" class="form-control" name="" id="" rows="8" readonly="" style="font-size: 16px;">
1. จะต้องกรอกข้อมูลให้ครบถ้วน
2. เมื่อระบบได้รับการบันทึกการลงทะเบียน ผู้ใช้งานจะต้อง ยืนยันการใช้งาน  ผ่าน e-mail ที่บันทึกข้อมูลไว้
3. ผู้ใช้งานจะต้องกำหนดรหัสผ่าน (Password) ที่มีความปลอดภัย โดยมีความยาวไม่น้อยกว่า 8 ตัวอักษร และผสมตัวอักษรภาษาอังกฤษ ตัวพิมพ์ใหญ่หรือตัวพิมพ์เล็ก ตัวเลข และสัญลักษณ์ ผู้ใช้งานจะต้องรักษาความลับของรหัสผ่านของตนเองไว้เป็นอย่างดี
4. ผู้ใช้งานจะต้องไม่กระทำการใดๆ ทั้งโดยตั้งใจหรือไม่ตั้งใจ ซึ่งเป็นเหตุทำให้ผู้อื่นเกิดความเสียหาย เสื่อมเสียชื่อเสียง ถูกดูหมิ่นเกลียดชัง และ/หรือ จะต้องไม่กระทำการใดๆ อันเป็นความผิดต่อพระราชบัญญัติว่าด้วยการกระทำความผิดเกี่ยวกับคอมพิวเตอร์ และ/หรือ กฎหมายอื่นใดที่กำหนดความผิดเกี่ยวกับการใช้งานคอมพิวเตอร์และการใช้งานเครือข่ายอินเทอร์เน็ต
5. การกรอกข้อมูล และ แนบเอกสารอันเป็นเท็จ หรือ จงใจแนบเอกสารไม่ครบถ้วน ถือเป็นการปกปิดข้อมูล มีความผิดตามกฎหมาย
6. สมอ. จะส่งข่าวสาร ข้อมูล การแจ้งเตือน หรืออื่น ๆ ผ่านทาง e-Mail ที่บันทึกข้อมูลไว้
                                        </textarea>
                                    </div>
                                </div>
                            </div>

    <div id="div_cancel">
                            <div class="form-group text-center m-t-20">
                                <div class="col-xs-12">
                                    {{-- disabled --}}
                                    <button class="btn btn-default btn-lg btn-block text-uppercase waves-effect waves-light btn_cancel" type="button">ยกเลิก</button>
                                </div>
                            </div>
   </div>
  
    <div id="div_sign_up">
                            <div class="form-group">
                                <div class="col-md-12">
                                    {{-- <div class="checkbox checkbox-primary p-t-0">
                                        <input id="checkbox-signup" type="checkbox">
                                        <label for="checkbox-signup"> &nbsp;&nbsp;ได้อ่านและยอมรับเงื่อนไขการใช้งาน การแนบเอกสารไม่ครบถ้วน ถือเป็นการจงใจปกปิดข้อมูล มีความผิดตามกฏหมาย</label>
                                    </div> --}}
                                    <div class="checkbox checkbox-primary p-t-0">
                                        <input id="checkbox-pdpa" type="checkbox" name="checkbox-pdpa" required>
                                        <label for="checkbox-pdpa"> &nbsp;&nbsp;ข้าพเจ้าขอรับรองว่าข้อมูลในใบสมัครฉบับนี้มีความครบถ้วน ถูกต้อง และตรงตามความเป็นจริงทุกประการ และยินยอมให้สำนักงานมาตรฐานผลิตภัณฑ์อุตสาหกรรมจัดเก็บข้อมูล เผยแพร่ และส่งต่อเพื่อใช้งานต่อไปตาม<a href="{!! asset('downloads/policy/1-TISI_Privacy_Policy.pdf') !!}" target="_blank">นโยบายการคุ้มครองข้อมูลส่วนบุคคล (Privacy Policy) สำนักงานมาตรฐานผลิตภัณฑ์อุตสาหกรรม</a></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group text-center m-t-20">
                                <div class="col-xs-6">
                                    {{-- disabled --}}
                                    <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="button" id="sign_applicant">ลงทะเบียน</button>
                                </div>
                                <div class="col-xs-6">
                                    {{-- disabled --}}
                                    <button class="btn btn-default btn-lg btn-block text-uppercase waves-effect waves-light btn_cancel" type="button">ยกเลิก</button>
                                </div>
                            </div>
   </div>




   <div class="modal fade" id="modal-default">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close"
                        data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times</span>
                </button>
            </div>
            <div class="modal-body">
                <style>
                    .controls {
                        margin-top: 10px;
                        border: 1px solid transparent;
                        border-radius: 2px 0 0 2px;
                        box-sizing: border-box;
                        -moz-box-sizing: border-box;
                        height: 32px;
                        outline: none;
                        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
                    }

                    #pac-input {
                        background-color: #fff;
                        font-size: 15px;
                        font-weight: 300;
                        margin-left: 12px;
                        padding: 0 11px 0 13px;
                        text-overflow: ellipsis;
                        width: 300px;
                    }

                    #pac-input:focus {
                        border-color: #4d90fe;
                    }

                </style>

                <input id="pac-input" class="controls"  type="text" placeholder="Search Box">
                <div id="map" style="height: 400px;"></div>
                <input id="lat1" class="controls" type="text"   placeholder="ละติจูด" disabled>
                <input id="lng1" class="controls" type="text"  placeholder="ลองติจูด" disabled>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-success"  id="button-modal-default">
                     <span aria-hidden="true">ยืนยัน</span>
                </button>
            </div>
        </div>
    </div>
</div>
</div>

                            {{-- <div class="form-group m-b-0">
                                  <div class="col-sm-12 text-center">
                                      <p>Already have an account? <a href="{{route('login')}}" class="text-primary m-l-5"><b>Sign In</b></a></p>
                                  </div>
                            </div> --}}
                        </div>
                    </div>

                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <footer class=" t-a-c">
                                © 2566 สมอ.
                            </footer>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

</section>

@endsection

@php
    // Server-side checks (no file/laravel logs — we just echo results into the page)
    $qToken = (string) request()->query('token', '');
    $cookieName = config('session.cookie');
    $appUrl = config('app.url');

    // Does the session (on the server) contain the expected keys right now?
    $hasTokenKey = $qToken !== '' ? session()->has('prereg:'.$qToken) : false;

    // Collect all session keys that start with 'prereg:' (helps spot token/key mismatches)
    $preregKeys = [];
    foreach (session()->all() as $k => $v) {
        if (strncmp($k, 'prereg:', 7) === 0) { $preregKeys[] = $k; }
    }
@endphp

<script>
// ======== PREREG DevTools Diagnostics (client-side only) ========
(function(){
  try {
    console.group('%c[PREREG DIAG]', 'color:#0bf;font-weight:bold;');

    // Browser context
    console.log('location.origin  =', location.origin);
    console.log('location.href    =', location.href);

    // From server (embedded as constants so you can see what PHP saw)
    console.log('APP_URL          =', @json($appUrl));
    console.log('query.token      =', @json($qToken));
    console.log('session.cookie   =', @json($cookieName));

    // Server-side session state at render time
    console.log('session.has(prereg:token) =', @json($hasTokenKey));
    console.log('session.prereg keys       =', @json($preregKeys));

    // Browser cookies actually present on this page load
    var cname = @json($cookieName) + '=';
    var hasSessCookie = document.cookie.indexOf(cname) !== -1;
    console.log('document.cookie has session cookie? =', hasSessCookie);

    // What the page will initialize for __PREREG (before init)
    console.log('window.__PREREG (pre-init) =', typeof window.__PREREG === 'undefined' ? 'UNDEFINED' : window.__PREREG);
    console.groupEnd();
  } catch(e) {
    console.warn('[PREREG DIAG] error:', e);
  }
})();
</script>

@push('scripts')
<script>
window.__PREREG = @json($prereg ?? null, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
console.log('[PREREG] init', window.__PREREG);
</script>
@endpush


<script>
(function() {
  var _pr = @json($prereg ?? null);
  window.__PREREG = _pr;
  
  console.log('[PREREG] blade boot', _pr ? 'OK' : 'EMPTY', _pr);
})();
</script>

@push('js')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAkwr5rmzY9btU08sQlU9N0qfmo8YmE91Y&libraries=places&callback=initAutocomplete"   async defer></script>
<script>
    // This example adds a search box to a map, using the Google Place Autocomplete
    // feature. People can enter geographical searches. The search box will return a
    // pick list containing a mix of places and predicted search terms.
    var markers = [];
    function initAutocomplete() {
        var map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: 13.7563309, lng: 100.50176510000006},
            zoom: 10,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        // Create the search box and link it to the UI element.
        var input = document.getElementById('pac-input');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function () {
            searchBox.setBounds(map.getBounds());
        });
        markers = new google.maps.Marker({
            position: {lat: 13.7563309, lng: 100.50176510000006},
            map: map,
        });

        google.maps.event.addListener(map, 'click', function (event) {
            markers.setMap(null);

            markers = new google.maps.Marker({
                position: { lat: event.latLng.lat(), lng: event.latLng.lng() },
                map: map,
            });

            $('#lat1').val(event.latLng.lat());
            $('#lng1').val(event.latLng.lng());
        });

        searchBox.addListener('places_changed', function () {
            markers.setMap(null);
            var places = searchBox.getPlaces();

            if (places.length == 0) {
                return;
            }

            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function (place) {
                 $('#lat1').val(place.geometry.location.lat());
                 $('#lng1').val(place.geometry.location.lng());

                var icon = {
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(25, 25)
                };

                // Create a marker for each place.
                markers = new google.maps.Marker({
                    position: { lat: place.geometry.location.lat(), lng: place.geometry.location.lng() },
                    map: map,
                });

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                } else {
                    // bounds.extend(place.geometry.location);
                }
            });
            map.fitBounds(bounds);
        });

    }
</script>


<script src="{{asset('plugins/components/icheck/icheck.min.js')}}"></script>
<script src="{{asset('plugins/components/icheck/icheck.init.js')}}"></script>
    <script src="{{asset('js/mask/jquery.inputmask.bundle.min.js')}}"></script>
    <script src="{{asset('js/jasny-bootstrap.js')}}"></script>
  <!-- input calendar thai -->
  <script src="{{ asset('plugins/components/bootstrap-datepicker-thai/js/bootstrap-datepicker.js') }}"></script>
  <!-- thai extension -->
  <script src="{{ asset('plugins/components/bootstrap-datepicker-thai/js/bootstrap-datepicker-thai.js') }}"></script>
  <script src="{{ asset('plugins/components/bootstrap-datepicker-thai/js/locales/bootstrap-datepicker.th.js') }}"></script>
  <script src="{{asset('js/mask/mask.init.js')}}"></script>
  <script src="{{asset('plugins/components/sweet-alert2/sweetalert2.all.min.js')}}"></script>
  <script src="{{asset('js/jasny-bootstrap.js')}}"></script>

<!-- ====== Loading ====== -->
<script src="{{ asset('plugins/components/loading-overlay/js/loadingoverlay.min.js') }}"></script>

@env('local')
<script>
(function () {
  if (!window.jQuery) return;

  // ---------- 1) Fallback-only Ajax shim (pass-through unless fail) ----------
  const realAjax = $.ajax;
  const once = Object.create(null); // per-endpoint guard

  function matchKey(url) {
    if (/\/auth\/register\/get_legal_entity$/.test(url)) return 'entity';     // JT 1/3
    if (/\/auth\/register\/get_tax_number$/.test(url))   return 'person';     // JT 2
    if (/\/auth\/register\/get_taxid$/.test(url))        return 'gov';        // JT 4
    if (/\/auth\/register\/datatype$/.test(url))         return 'datatype';   // contact lookup
    return '';
  }

  function payloadFor(key) {
    switch (key) {
      case 'entity':   // “not registered, normal status” -> proceed to data_pid
        return { check: false, juristic_status: '1' };
      case 'person':   // “not registered / not found”    -> continue branch
        return { check: false, person: 'not-found' };
      case 'gov':      // “not registered, no API data”   -> continue branch
        return { check: false, check_api: false };
      case 'datatype': // harmless stub so contact lookup doesn’t explode
        return { length: 0 };
      default:         // never used
        return {};
    }
  }

  $.ajax = function (opts) {
    const url = String(opts && opts.url || '');
    const key = matchKey(url);

    // no target → pure pass-through
    if (!key) return realAjax.apply(this, arguments);

    const jq = realAjax.apply(this, arguments);

    // if it succeeds, mark and do nothing else
    jq.done(function () { once[key] = true; });

    // if it fails and we haven't patched it for this page yet → synthesize success
    jq.fail(function () {
      if (once[key]) return;
      once[key] = true; // fire only once per endpoint per page load
      const data = payloadFor(key);
      try { opts.success && opts.success(data, 'success', {}); } catch(e) {}
      try { opts.complete && opts.complete({}, 'success'); } catch(e) {}
      // we deliberately DO NOT call opts.error so the flow continues
      console.warn('[fallback-only]', key, '→ provided minimal payload');
    });

    return jq;
  };

  // ---------- 2) Auto-confirm the first SweetAlert (no visual modal) ----------
  if (window.Swal && typeof Swal.fire === 'function') {
    const realFire = Swal.fire.bind(Swal);
    let first = true;
    Swal.fire = function () {
      const p = realFire.apply(this, arguments);
      if (first) {
        first = false;
        // return a promise that resolves immediately as if user clicked "ยืนยัน"
        return Promise.resolve({ value: true, isConfirmed: true });
      }
      return p;
    };
  }

  // ---------- 3) Kick their existing flow (fill → JT → click #search) ----------
  function setVal(sel, v) {
    const el = document.querySelector(sel);
    if (!el) return;
    const next = String(v == null ? '' : v).trim();
    if (!next) return;
    const setter = Object.getOwnPropertyDescriptor(el.__proto__, 'value')?.set;
    setter ? setter.call(el, next) : (el.value = next);
    el.dispatchEvent(new Event('input',  { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function pickJT(jt) {
    // keep your mapping exactly
    const map = { '1':'1', '2':'2', '3':'1', '4':'3', '5':'5' };
    const val = map[String(jt)] || '5';
    const r = document.querySelector('.applicanttype_id[value="'+val+'"]');
    if (r) { r.checked = true; r.dispatchEvent(new Event('change', { bubbles: true })); }
  }

  function kick() {
    if (!window.__PREREG) return;
    setVal('#tax_number', __PREREG.bid || __PREREG.uid || '');
    pickJT(__PREREG.jt);
    setVal('#person_type', '1'); // matches the page’s own defaulting
    const btn = document.getElementById('search');
    if (btn) btn.click();
  }

  // wait until their handlers are bound
  let tries = 120;
  (function wait() {
    if (document.readyState !== 'complete') return setTimeout(wait, 80);
    if (!document.getElementById('search')) return tries-- ? setTimeout(wait, 80) : null;
    setTimeout(kick, 60);
  })();
})();
</script>
@endenv


<script>

(function () {
      // ธงไว้ให้สคริปต์อื่นรู้ด้วย (ถ้าจะอ้างใช้ต่อ)
      window.__SKIP_REGISTER_SUBPAGE__ = true;

      // 1) ยิง CSS ปิดทุก element ที่มักใช้เป็นหน้า “คั่น”
      //    (ใส่ selector กว้าง ๆ + !important กัน re-show)
      var css = `
        .intro-step, .register-intro, .wizard-intro,
        [data-step="intro"], .step-intro, .subpage-intro,
        .onboarding, .pre-register, .choose-type,
        .modal-backdrop, .modal.in, .overlay, .page-mask {
          display:none !important; visibility:hidden !important; opacity:0 !important;
        }
      `;
      var style = document.createElement('style');
      style.type = 'text/css';
      style.appendChild(document.createTextNode(css));
      document.head.appendChild(style);

      // 2) ฟังก์ชัน “เผยฟอร์มจริง” แบบบังคับ
      function forceShowForm() {
        var s = function(sel){ try{ return document.querySelector(sel); }catch(e){ return null; } };
        var show = function(el){ if(!el) return; el.style.display = ''; el.hidden = false; el.classList.remove('hidden'); el.style.visibility = 'visible'; el.style.opacity = '1'; };

        // id/class ที่เจอในโปรเจกต์นี้บ่อย (ใส่หลายตัวไว้ได้)
        var targets = ['#div_profile', '#div_sign_up', '#div_register_form', '.register-form'];
        targets.forEach(function(sel){ var el = s(sel); if(el){ show(el); } });

        // บางที่ใช้ tab/wizard ซ่อนด้วย attr
        document.querySelectorAll('[data-step], [data-tab], [hidden]').forEach(function(el){
          el.hidden = false;
        });

        // ถ้ามีพวก fade/overlay ที่บังทั้งหน้า (fixed + z-index สูง) ให้ซ่อนทิ้ง
        document.querySelectorAll('body *').forEach(function(el){
          var cs = window.getComputedStyle(el);
          if (cs.position === 'fixed' && parseInt(cs.zIndex || '0', 10) >= 1000) {
            // ถ้าตัวนี้ไม่ใช่ toast/tooltip และใหญ่เกินครึ่งจอ ถือว่าเป็นตัวบัง
            var rect = el.getBoundingClientRect();
            var big = (rect.width * rect.height) > (window.innerWidth * window.innerHeight * 0.25);
            var looksLikeMask = /backdrop|overlay|modal|mask|intro|wizard|step/i.test(el.className + ' ' + (el.id || ''));
            if (big || looksLikeMask) {
              el.style.display = 'none';
              el.style.visibility = 'hidden';
              el.style.opacity = '0';
            }
          }
        });
      }

      // 3) กันกรณีสคริปต์อื่นทำงานทีหลังแล้วบังอีก: ใช้ MutationObserver
      var mo = new MutationObserver(function(muts){
        // ถ้ามี node ใหม่โผล่ ให้ forceShow ซ้ำ
        forceShowForm();
      });
      mo.observe(document.documentElement, {childList:true, subtree:true});

      // 4) ยิงหลายรอบ (สำหรับเคส lazy-loaded/wizard) แล้วค่อยหยุด
      var kicks = [0, 50, 120, 250, 500, 1000];
      kicks.forEach(function(t){ setTimeout(forceShowForm, t); });

      // 5) บังคับเลือกประเภทตามที่ตัดสินใจมาแล้ว (จาก payload)
      //    jt: '2' = นิติบุคคล -> applicanttype_id '1'
      //    jt: '1' = บุคคลธรรมดา -> applicanttype_id '2'
      try {
        var pf = @json($prefill ?? []);
        var applicant = (pf && pf.jt === '2') ? '1' : '2';
        // เช็คทั้ง input radio แบบเดิม/ICheck
        var el = document.querySelector('.applicanttype_id[value="'+ applicant +'"]');
        if (el) {
          el.checked = true;
          // ถ้ามี iCheck
          if (window.jQuery && jQuery.fn.iCheck) {
            jQuery('.applicanttype_id[value="'+ applicant +'"]').iCheck('check');
          }
        }
      } catch (e) {}

      // 6) เผยฟอร์มครั้งแรกทันที
      forceShowForm();
    })();

   
    $(document).ready(function () {
     /*

            // ===== Prefill จาก session('reg_prefill') =====
        @if(!empty($prefill))
        (function () {
            var pf = @json($prefill);

            // map JT -> applicanttype_id (JT '2' = นิติบุคคล(1), JT '1' = บุคคลธรรมดา(2))
            var suggestedApplicant = (pf.jt === '2') ? '1' : '2';

            // ตั้งประเภทสมัคร ถ้ายังไม่มีค่า/ยังไม่ได้เปลี่ยนเอง
            var currentApplicant = $('.applicanttype_id:checked').val();
            if (!currentApplicant || currentApplicant !== suggestedApplicant) {
                $('.applicanttype_id[value="'+ suggestedApplicant +'"]').prop('checked', true);
                $('.applicanttype_id').iCheck('update');
                if (typeof applicanttype === 'function') { applicanttype(); }
            }

            // helper: ใส่ค่าเฉพาะเมื่อช่องยังว่าง
            function setIfEmpty(sel, val) {
                if (val != null && val !== '' && $(sel).val() === '') {
                    $(sel).val(val);
                }
            }

            // tax_number / username
            if (pf.tax_number) {
                setIfEmpty('#tax_number', pf.tax_number);
                setIfEmpty('#username',   pf.tax_number);
            }

            // ข้อมูลติดต่อจาก iCustomer
            if (pf.iCustomer) {
                setIfEmpty('#email',        pf.iCustomer.UserEmail);
                setIfEmpty('#phone_number', pf.iCustomer.UserPhone);
            }

            // เติมชื่อ-นามสกุลตามประเภท
            if (suggestedApplicant === '2') {
                // บุคคลธรรมดา
                if (pf.iCustomer) {
                    setIfEmpty('#person_first_name', pf.iCustomer.UserFirstName);
                    setIfEmpty('#person_last_name',  pf.iCustomer.UserLastName);
                    setIfEmpty('#first_name',        pf.iCustomer.UserFirstName); // ผู้ติดต่อ
                    setIfEmpty('#last_name',         pf.iCustomer.UserLastName);
                }
                setIfEmpty('#contact_tax_id', pf.tax_number); // ผู้ติดต่อ = ผู้สมัคร
            } else {
                // นิติบุคคล
                if (pf.iCustomer) {
                    setIfEmpty('#first_name', pf.iCustomer.UserFirstName);
                    setIfEmpty('#last_name',  pf.iCustomer.UserLastName);
                }
                // ไม่กรอก prefix/company name เพราะ payload ไม่มี
            }

            // สถานะนิติ (ถ้ามี)
            if (pf.juristic_status) {
                $('#juristic_status').val(pf.juristic_status);
            }

            // ❌ ไม่แตะต้อง #check_api / ไม่โชว์/ซ่อนบล็อก / ไม่บังคับ branch_type
        })();
        @endif

*/
            $('#div_profile').hide();
            $('#div_cancel').show();
            $('#div_sign_up').hide();
            $('.tax_id_format').inputmask('9-9999-99999-99-9');
            $('.phone_number_format').inputmask('999-999-9999');

              input_number() ;
              check_format_en();

            $(".check_format_en_and_number").on("keypress",function(e){
                    var applicanttype_id  =  $('.applicanttype_id:checked').val();
                    if(applicanttype_id == 5){
                            var k = e.keyCode;/* เช็คตัวเลข 0-9 */
                            if (k>=48 && k<=57) {
                                return true;
                            }
                            /* เช็คคีย์อังกฤษ a-z, A-Z */
                            if ((k>=65 && k<=90) || (k>=97 && k<=122)) {
                                return true;
                            }
                            /* เช็คคีย์ไทย ทั้งแบบ non-unicode และ unicode */
                            if ((k>=161 && k<=255) || (k>=3585 && k<=3675)) {
                                return false;
                            }
                    }else{

                        var eKey = e.which || e.keyCode;
                        if((eKey<48 || eKey>57) && eKey!=46 && eKey!=44){
                            return false;
                        }
                    }
                });

                //วันที่จดทะเบียน/วันที่หมดอายุ
                $('#date_of_birth').change(function(event) {
                    check_date_max_now($(this));
                });

                // เช็ค e-mail
                $("#email").change(function(event) {
                     var  email = $(this).val();
                     if(checkNone(email)){
                         $.ajax({
                                url: "{!! url('auth/register/check_email') !!}",
                                method:"POST",
                                data:{
                                    _token: "{{ csrf_token() }}",
                                    email:email
                                    },
                                success:function (result){
                                    console.log(result);
                                    if(result.check == true){  // เช็ค E-Mail  ในระบบ
                                        Swal.fire({
                                                    title: result.status,
                                                    width: 1000,
                                                    showDenyButton: true,
                                                    showCancelButton: false,
                                                    confirmButtonText: 'OK'
                                                 });
                                        $('#email').val('');
                                    }else   if(result.check_email == false){   // เช็ครูปแบบ E-Mail
                                        Swal.fire({
                                                    title: result.status_email,
                                                    width: 1000,
                                                    showDenyButton: true,
                                                    showCancelButton: false,
                                                    confirmButtonText: 'OK'
                                                 });
                                        $('#email').val('');
                                    }
                                }
                         });
                    }
                });

                    // เช็ค password
                $("#password").change(function(event) {
                     var  password = $(this).val();
                     if(checkNone(password)){
                        var html = check_password_and_number(password);
                        if(html != ''){
                            Swal.fire({
                                title:'กรุณากรอกรูปแบบรหัสผ่านใหม่และความยาวไม่น้อยกว่า 8 อักษร',
                                html:html,
                                width: 700,
                                showDenyButton: true,
                                showCancelButton: false,
                                confirmButtonText: 'OK'
                            });
                            $('#password').val('');
                        }
                    }
                });
                    // เช็ค password confirmation
                $("#password2").change(function(event) {
                    var row = $(this).val();
                    var password = $('#password').val();
                    if(checkNone(row) && row!=password){
                        Swal.fire('confirm Password ไม่สำเร็จ! กรุณากรอกรูปแบบรหัสผ่านใหม่');
                        $('#password2').val('');
                    }
                });

                 // เช็ค เบอร์โทรศัพท์มือถือ
                $("#phone_number").change(function(event) {
                     var  phone_number = $(this).val();
                     if(checkNone(phone_number)){
                        phone_number = phone_number.toString().replace(/\D/g,'');
                        if(phone_number.length < 10){
                            Swal.fire({
                                        title: 'กรุณากรอกเบอร์โทรศัพท์มือถือให้ครบ 10 หลัก',
                                        width: 400,
                                        showDenyButton: true,
                                        showCancelButton: false,
                                        confirmButtonText: 'OK'
                                      });
                            $('#phone_number').val('');
                        }
                     }
                });


                $('#personfile').change( function () {
                        var fileExtension = ['pdf'];
                        if( $(this).val() != ''){
                                var max_size = "{{ ini_get('upload_max_filesize') }}";
                                var res = max_size.replace("M", "");
                                var size =   (this.files[0].size)/1024/1024 ; // หน่วย MB
                                if(size > res ){
                                    Swal.fire(
                                            'ขนาดไฟล์เกินกว่า ' + res +' MB',
                                            '',
                                            'info'
                                            );
                                    //  this.value = '';
                                    $(this).parent().parent().find('.fileinput-exists').click();
                                    return false;
                                }else{

                                        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                                        Swal.fire(
                                        'ไม่ใช่หลักฐานประเภทไฟล์ที่อนุญาต .pdf',
                                        '',
                                        'info'
                                        );
                                        this.value = '';
                                        return false;
                                     }
                                  }
                           }

                });






            //ปฎิทิน
           // Date Picker Thai
           $('.datepicker').datepicker({
                autoclose: true,
                toggleActive: true,
                todayHighlight: true,
                language:'th-th',
                format: 'dd/mm/yyyy'
            });

            $('#checkbox-pdpa').attr('checked', false);
            //$('#sign_applicant').attr('disabled', true);
            $('#checkbox-pdpa').change(function(){
                if($('#checkbox-pdpa').is(':checked')){
                    //$('#sign_applicant').attr('disabled', false);
                } else {
                    //$('#sign_applicant').attr('disabled', true);
                }
            });

            $('#show_map').click(function(){
                $('#modal-default').modal('show');
            });

            $('#button-modal-default').click(function(){
                if( $('#lat1').val() != ""){
                    $('#latitude').val( $('#lat1').val());
                }else{
                    $('#latitude').val('');
                }
                if( $('#lng1').val() != ""){
                    $('#longitude').val( $('#lng1').val());
                }else{
                    $('#longitude').val('');
                }
                $('#modal-default').modal('hide');
            });



            $('#eye_change').click(function(){
                if($($("#eye_change").find('i')).hasClass("glyphicon-eye-open")){
                    $("#eye_change").find('i').removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
                    $('#password').attr('type', 'password');
                } else {
                    $("#eye_change").find('i').removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
                    $('#password').attr('type', 'text');
                }
            });

            $('#sign_applicant').click(function(){
                var tax_number       = $('#tax_number').val() ;
                //   tax_number      = tax_number.toString().replace(/\D/g,'');
                var applicanttype_id =  $('.applicanttype_id:checked').val();
                var branch_type      =  $('.branch_type:checked').val();
                var branch_code      =  $('#branch_code').val();
                var email            =  $('#email').val();
                var check_api        =  $('#check_api').val();
                var date_of_birth    =  $('#date_of_birth').val();

                if(!$('#checkbox-pdpa').prop('checked')){
                    Swal.fire({
                        type: 'warning',
                        title: 'ยอมรับข้อกำหนดและเงื่อนไข',
                        text: 'กรุณายืนยันรับทราบข้อกำหนดและเงื่อนไขการลงทะเบียน'
                    })
                    return false;
                }

                if(checkNone(tax_number)){
                    $.ajax({
                        url: "{!! url('auth/register/check_tax_number') !!}",
                        method:"POST",
                        data:{
                            _token: "{{ csrf_token() }}",
                            tax_id:tax_number,
                            applicanttype_id:applicanttype_id,
                            branch_type:branch_type,
                            branch_code:branch_code,
                            email:email,
                            check_api:check_api,
                            date_of_birth:date_of_birth
                        },
                        success:function (result){
                            if(result.check == true && result.branch_code == true){
                                Swal.fire('ขออภัยรหัสสาขา '+ branch_code + ' มีการลงทะเบียนแล้วกับ' + result.name);
                            }else if(result.check == false && result.email == true){
                                Swal.fire('ขออภัย e-Mail '+ email + ' มีการลงทะเบียนแล้ว');
                            }else if(result.check == false){

                                if($('.applicanttype_id:checked').val()=='2' && $('#check_api').val()=='1'){ //บุคคลธรรมดาที่เช็คเลขแล้ว
                                    if(result.date_of_birth_check===false){ //วันเกิดไม่ถูกต้อง
                                        Swal.fire('วันเกิดไม่ถูกต้องกรุณาตรวจสอบ');
                                        return false;
                                    }else if(result.date_of_birth_check==='no-connect'){ //เชื่อม API ไม่ได้
                                        $('#check_api').val(''); //เซตเป็นไม่ได้เชื่อมจาก API
                                    }else{ //วันเกิดถูกต้อง
                                        $('#date_of_birth_encrypt').val(result.date_of_birth_encrypt);
                                    }
                                }

                                if(checkNone($('#password').val()) && check_password_and_number($('#password').val())!=''){
                                    Swal.fire('กรุณาตรวจสอบเงื่อนไขการตั้งรหัสผ่าน');
                                }else if(checkNone($('#password').val()) && checkLetterPassword($('#password').val()) < 8){
                                    Swal.fire('กรุณากรอกอย่างน้อย 8 ตัว');
                                }else{
                                    $('#register_form').submit();
                                }

                            }else{
                                Swal.fire('ขออภัยเลข '+ tax_number + ' มีการลงทะเบียนแล้ว');
                            }
                        }
                    });
                }else{
                    Swal.fire('กรุณากรอกรายละเอียด!');
                }

            });

            //กดยกเลิก
            $('.btn_cancel').click(function(event) {
                Swal.fire({
                    title: 'คุณต้องการออกจากหน้านี้ใช่ไหม?',
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: 'ใช่',
                    cancelButtonText: 'ไม่ใช่',
                }).then((result) => {

                    /* Read more about isConfirmed, isDenied below */
                    if (result.value) {
                        window.location = '{{ url('login') }}';
                    }

                })
            });

            $('#register_form').parsley().on('field:validated', function() {
                  var ok = $('.parsley-error').length === 0;
                  $('.bs-callout-info').toggleClass('hidden', !ok);
                  $('.bs-callout-warning').toggleClass('hidden', ok);
              })  .on('form:submit', function() {
                      // Text
                      $.LoadingOverlay("show", {
                      image       : "",
                      text  : "กำลังบันทึก กรุณารอสักครู่..."
                      });
                  return true; // Don't submit form for this demo
              });

            $('#checkbox_contact_address_no').click(function(){
                checkbox_contact_address_no();
            });




            // ข้อมูลผู้ติดต่อ
            $("#tax_number,#branch_code").keyup(function(event) {

                        var tax_number        = $('#tax_number').val() ;
                        $('#username').val('');
                    if(tax_number != ""){
                        tax_number              = tax_number.toString().replace(/\D/g,'');
                        var applicanttype_id    =  $('.applicanttype_id:checked').val();
                        var branch_type         =  $('.branch_type:checked').val();
                        var branch_code         =  $('#branch_code').val();
                        if(applicanttype_id == "1" && branch_type == "2" && branch_code != '' ){
                            $('#username').val(tax_number+branch_code);
                        }else{
                            $('#username').val(tax_number);
                        }
                    }
             });


            $("#search").click(function () {

                var faculty_title_allow  = '{{ $config->faculty_title_allow }}';
                var faculty_title_allows = faculty_title_allow.split(',');

                  data_value_null();
                  var row               = $(this).val() ;
                  var applicanttype_id  =  $('.applicanttype_id:checked').val();
                  const cars            = ["","นิติบุคคล", "บุคคลธรรมดา", "คณะบุคคล", "ส่วนราชการ", "อื่นๆ"];
                  var tax_number        = $('#tax_number').val() ;
     if(tax_number != ""){
                //console.log("Tax number is:",tax_number)
                //console.log("Juristic type is:",applicanttype_id)
                if(applicanttype_id == 1 || applicanttype_id == 2  || applicanttype_id == 3  || applicanttype_id == 4 ){ //  นิติบุคคล     บุคคลธรรมดา     คณะบุคคล     ส่วนราชการ
                    tax_number = tax_number.toString().replace(/\D/g,'');
                    if(tax_number.length >= 13){
                                // Text
                                //console.log("Tax number have at least 13 digits")
                                $.LoadingOverlay("show", { //plugin ไม่โหลด
                                        image       : "",
                                        text        : "กำลังโหลด..."
                                });
                                if(applicanttype_id == 4){ // ส่วนราชการ
                                    $.ajax({
                                        url: "{!! url('auth/register/get_taxid') !!}",
                                        method:"POST",
                                        data:{
                                            _token: "{{ csrf_token() }}",
                                            tax_id:tax_number,
                                            applicanttype_id:applicanttype_id
                                            },
                                        success:function (result){

                                            $.LoadingOverlay("hide");
                                            if(result.check == true  ){
                                                   Swal.fire({
                                                        title: 'ขออภัยเลข '+ tax_number +' ขึ้นทะเบียนในระบบประเภท'+ result.applicant_type  ,
                                                        width: 800,
                                                        showDenyButton: true,
                                                        showCancelButton: true,
                                                        confirmButtonText: 'กลับ',
                                                        cancelButtonText: 'ยกเลิก',
                                                    }).then((result) => {
                                                            if (result.value) {
                                                                    window.location.assign("{{ url('login') }}");
                                                            }
                                                    });

                                            }else if(result.check_api == true  ){

                                                    if(result.type == 1){ //นิติบุคคล
                                                                Swal.fire({
                                                                    title: result.status,
                                                                    showDenyButton: true,
                                                                    showCancelButton: true,
                                                                    width: 1500,
                                                                    confirmButtonText: 'ยืนยัน',
                                                                    cancelButtonText: 'ยกเลิก',
                                                                }).then((result) => {
                                                                        /* Read more about isConfirmed, isDenied below */
                                                                        if (result.value) {
                                                                            $('.applicanttype_id[value="1"]').prop('checked', true);
                                                                            $('.applicanttype_id').iCheck('update');
                                                                            $('.branch_type[value="1"]').prop('checked', true);
                                                                            $('.branch_type').iCheck('update');
                                                                            $('#branch_type1').prop('disabled', false);
                                                                            $('#person_type').children('option[value!=""]').remove();
                                                                            $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                                                                            $('#person_type').val('1');
                                                                            $('#person_type').select2();
                                                                            data_pid(tax_number);
                                                                        } else if ( result.dismiss === Swal.DismissReason.cancel  ) {
                                                                            window.location.assign("{{ url('login') }}");
                                                                        }
                                                                });
                                                    }else   if(result.type == 2){  // บุคคลธรรมดา
                                                                    if(result.person == 1 ){
                                                                        Swal.fire({
                                                                            title: result.status,
                                                                            width: 1500,
                                                                            showDenyButton: true,
                                                                            showCancelButton: true,
                                                                            confirmButtonText: 'กลับ',
                                                                            cancelButtonText: 'ยกเลิก',
                                                                        }).then((result) => {

                                                                                if (result.value) {
                                                                                        window.location.assign("{{ url('login') }}");
                                                                                }
                                                                        });

                                                                    }else{
                                                                      Swal.fire({
                                                                                title: result.status,
                                                                                showDenyButton: true,
                                                                                showCancelButton: true,
                                                                                width: 1500,
                                                                                confirmButtonText: 'ยืนยัน',
                                                                                cancelButtonText: 'ยกเลิก',
                                                                      }).then((result) => {
                                                                            /* Read more about isConfirmed, isDenied below */
                                                                            if (result.value) {
                                                                                $('.applicanttype_id[value="2"]').prop('checked', true);
                                                                                $('.applicanttype_id').iCheck('update');
                                                                                $('#person_type').children('option[value!=""]').remove();
                                                                                $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                                                                                $('#person_type').val('1');
                                                                                $('#person_type').select2();
                                                                                data_pid(tax_number);
                                                                            } else if ( result.dismiss === Swal.DismissReason.cancel  ) {
                                                                                window.location.assign("{{ url('login') }}");
                                                                            }
                                                                      });
                                                                    }
                                                       }else   if(result.type == 3){  // คณะบุคคล
                                                            Swal.fire({
                                                                title:  result.status,
                                                                showDenyButton: true,
                                                                showCancelButton: true,
                                                                width: 1500,
                                                                confirmButtonText: 'ยืนยัน',
                                                                cancelButtonText: 'ยกเลิก',
                                                            }).then((result) => {
                                                                    if (result.value) {
                                                                        $('.applicanttype_id[value="3"]').prop('checked', true);
                                                                          $('.applicanttype_id').iCheck('update');
                                                                        $('.branch_type[value="1"]').prop('checked', true);
                                                                        $('.branch_type').iCheck('update');
                                                                        $('#branch_type1').prop('disabled', false);
                                                                        $('#person_type').children('option[value!=""]').remove();
                                                                        $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                                                                        $('#person_type').val('1');
                                                                        $('#person_type').select2();
                                                                        data_pid(tax_number);
                                                                    } else if ( result.dismiss === Swal.DismissReason.cancel  ) {
                                                                        window.location.assign("{{ url('login') }}");
                                                                    }
                                                            });

                                                       }
                                            }else{

                                                        var title = 'เลขส่วนราชการ '+ tax_number + ' ไม่มีข้อมูลการลงทะเบียน ท่านต้องการลงทะเบียนหรือไม่?';
                                                        Swal.fire({
                                                            title: title,
                                                            showDenyButton: true,
                                                            showCancelButton: true,
                                                            width: 1500,
                                                            confirmButtonText: 'ยืนยัน',
                                                            cancelButtonText: 'ยกเลิก',
                                                        }).then((result) => {
                                                                if (result.value) {
                                                                    $('#check_api').val('');
                                                                    $('#personfile').prop('required', true);
                                                                    $('.label_personfile').find('span.personfile').html('*');
                                                                    $('.branch_type[value="1"]').prop('checked', true);
                                                                    $('.branch_type').iCheck('update');
                                                                    $('#branch_type1').prop('disabled', false);
                                                                    data_pid(tax_number);
                                                                } else if ( result.dismiss === Swal.DismissReason.cancel  ) {
                                                                    window.location.assign("{{ url('login') }}");
                                                                }
                                                        });
                                            }
                                        }
                                    });

                        }else if(applicanttype_id == 3){ // คณะบุคคล
                            $.ajax({
                                    url: "{!! url('auth/register/get_legal_faculty') !!}",
                                    method:"POST",
                                    data:{
                                            _token: "{{ csrf_token() }}",
                                            tax_id: tax_number
                                        },
                                    success:function (result){
                                        $.LoadingOverlay("hide");
                                        if(result.check == true){ //เลขคณะบุคคลมีข้อมูลการลงทะเบียน
                                            Swal.fire({
                                                title: 'ขออภัยเลข '+ tax_number +' ขึ้นทะเบียนในระบบประเภท'+ result.applicant_type  ,
                                                width: 800,
                                                showDenyButton: true,
                                                showCancelButton: true,
                                                confirmButtonText: 'กลับ',
                                                cancelButtonText: 'ยกเลิก',
                                            }).then((result) => {

                                                    if (result.value) {
                                                            window.location.assign("{{ url('login') }}");
                                                    }
                                            });

                                        }else{ //  เลขคณะบุคคลมีข้อมูลไม่มีการลงทะเบียน

                                            // if(faculty_title_allows.indexOf() result.branch_title ==  'คณะบุคคล' || result.branch_title ==  'สหกรณ์'){  //ลงทะเบียนคณะบุคคลมีสำนักงานใหญ่
                                            if(result.branch_title==='no-connect'){//เขื่อมต่อ API ไม่ได้

                                                Swal.fire({
                                                    title: 'ขออภัยในความไม่สะดวก ขณะนี้ระบบไม่สามารถเชื่อมโยงข้อมูลได้  ท่านต้องการลงทะเบียนโดยการกรอกข้อมูลเองหรือไม่?',
                                                    html : '<span class="font-15 text-danger"><b>โปรดอ่าน!</b> กรณีสมัครโดยกรอกข้อมูลเอง หลังยืนยันอีเมลต้องรอเจ้าหน้าที่อนุมัติการใช้งานอีกครั้ง</span>',
                                                    showDenyButton: true,
                                                    showCancelButton: true,
                                                    width: '80%',
                                                    confirmButtonText: 'ใช่ (กรอกข้อมูลเอง)',
                                                    cancelButtonText: 'ไม่ (ไว้สมัครภายหลัง)'
                                                }).then((result) => {
                                                    if (result.value) {//yes
                                                        $('.branch_type[value="1"]').prop('checked', true);
                                                        $('.branch_type').iCheck('update');
                                                        $('#branch_type1').prop('disabled', false);
                                                        $('#div_cancel').hide();//กล่องยกเลิกอย่างเดียว
                                                        $('#div_profile').show();//กล่องฟอร์มกรอกข้อมูล
                                                        $('#div_sign_up').show();//ปุ่มบันทึก
                                                        $('#username').val(tax_number);//เซตชื่อผู้ใช้งาน
                                                        $('#tax_number').prop('readonly', true);//ไม่ให้แก้ไขเลขผู้เสียภาษีแล้ว
                                                    }else if(result.dismiss === Swal.DismissReason.cancel) {//no
                                                        window.location.assign("{{ url('login') }}");
                                                    }
                                                });

                                            }else if(faculty_title_allows.indexOf(result.branch_title) !== -1){  //เช็คประเภทว่าเป็นคณะบุคคลไหม
                                                Swal.fire({
                                                    title: 'เลขคณะบุคคล '+ tax_number + ' ไม่มีข้อมูลการลงทะเบียน ท่านต้องการลงทะเบียนหรือไม่?',
                                                    showDenyButton: true,
                                                    showCancelButton: true,
                                                    width: 1500,
                                                    confirmButtonText: 'ยืนยัน',
                                                    cancelButtonText: 'ยกเลิก',
                                                }).then((result) => {
                                                        if (result.value) {
                                                            $('.branch_type[value="1"]').prop('checked', true);
                                                            $('.branch_type').iCheck('update');
                                                            $('#branch_type1').prop('disabled', false);
                                                            data_pid(tax_number);
                                                        } else if ( result.dismiss === Swal.DismissReason.cancel  ) {
                                                            window.location.assign("{{ url('login') }}");
                                                        }
                                                });
                                            }else{
                                                setTimeout(() => {
                                                    check_tax_ajax(tax_number, applicanttype_id);
                                                }, 500);
                                                // $('#div_profile').hide();
                                                // $('#div_cancel').show();
                                                // $('#div_sign_up').hide();
                                                // Swal.fire('ขออภัยเลข'+ tax_number +' ไม่ใช่เลขคณะบุคคล');
                                            }
                                        }
                                    }
                            });


                        }else if(applicanttype_id == 2){ // บุคคลธรรมดา
                            console.log("We got บุคคลธรรมดา")
                             $.LoadingOverlay("hide"); 
                            $.ajax({
                                    url: "{!! url('auth/register/get_tax_number') !!}",
                                    method:"POST",
                                    data:{
                                        _token: "{{ csrf_token() }}",
                                        tax_id:tax_number,
                                        },
                                    success:function (result){

                                        if((result.check == true && result.person != true) || result.person != true){

                                            if(result.person==='no-connect'){//เชื่อมต่อ API ไม่ได้

                                                Swal.fire({
                                                            title: 'ขออภัยในความไม่สะดวก ขณะนี้ระบบไม่สามารถเชื่อมโยงข้อมูลได้ ท่านต้องการลงทะเบียนโดยการกรอกข้อมูลเองหรือไม่?',
                                                            html : '<span class="font-15 text-danger"><b>โปรดอ่าน!</b> กรณีสมัครโดยกรอกข้อมูลเอง หลังยืนยันอีเมลต้องรอเจ้าหน้าที่อนุมัติการใช้งานอีกครั้ง</span>',
                                                            showDenyButton: true,
                                                            showCancelButton: true,
                                                            width: '80%',
                                                            confirmButtonText: 'ใช่ (กรอกข้อมูลเอง)',
                                                            cancelButtonText: 'ไม่ (ไว้สมัครภายหลัง)',
                                                        }).then((result) => {
                                                            if (result.value) {//yes
                                                                $('#date_of_birth').prop('placeholder', 'วันเกิด');
                                                                $('#div_cancel').hide();//กล่องยกเลิกอย่างเดียว
                                                                $('#div_profile').show();//กล่องฟอร์มกรอกข้อมูล
                                                                $('#div_sign_up').show();//ปุ่มบันทึก
                                                                $('#username').val(tax_number);//เซตชื่อผู้ใช้งาน
                                                                $('#tax_number').prop('readonly', true);//ไม่ให้แก้ไขเลขผู้เสียภาษีแล้ว
                                                            }else if(result.dismiss === Swal.DismissReason.cancel) {//no
                                                                window.location.assign("{{ url('login') }}");
                                                            }
                                                        });
                                            }else if(result.person==='not-found'){//ไม่พบข้อมูล
                                                setTimeout(() => {
                                                    check_tax_ajax(tax_number, applicanttype_id);
                                                }, 500);
                                            }else{
                                                
                                                Swal.fire({
                                                    title: result.person,
                                                    width: 800,
                                                    showDenyButton: true,
                                                    showCancelButton: true,
                                                    confirmButtonText: 'กลับ',
                                                    cancelButtonText: 'ยกเลิก',
                                                }).then((result) => {

                                                        if (result.value) {
                                                                window.location.assign("{{ url('login') }}");
                                                        }
                                                });
                                            }

                                        }else if(result.check == true){//มีการลงทะเบียนแล้ว
                                            Swal.fire({
                                                title: 'ขออภัยเลข '+ tax_number + ' มีการลงทะเบียนแล้ว ติดต่อเจ้าหน้าที่หรือรีเซ็ตรหัสผ่าน ท่านต้องการกลับหน้า Login หรือไม่?',
                                                width: 700,
                                                showDenyButton: true,
                                                showCancelButton: true,
                                                confirmButtonText: 'กลับ',
                                                cancelButtonText: 'ยกเลิก',
                                            }).then((result) => {

                                                    if (result.value) {
                                                            window.location.assign("{{ url('login') }}");
                                                    }
                                            });

                                        }else{
                                            // data_pid(tax_number);
                                            Swal.fire({
                                                title: 'ท่านต้องการลงทะเบียนผู้ประกอบการประเภทบุคคลธรรมดาใช่หรือไม่?',
                                                /*html : '<span class="font-15 text-danger"><b>โปรดอ่าน!</b> เมื่อกรอกข้อมูลลงทะเบียนและบันทึกข้อมูลเรียบร้อยแล้ว หลังยืนยันอีเมลต้องรอเจ้าหน้าที่อนุมัติการใช้งานอีกครั้ง</span>',*/
                                                showDenyButton: true,
                                                showCancelButton: true,
                                                width: '80%',
                                                confirmButtonText: 'ใช่',
                                                cancelButtonText: 'ไม่',
                                            }).then((result) => {
                                                if (result.value) {//yes
                                                    $('#date_of_birth').prop('placeholder', 'วันเกิด');
                                                    $('#div_cancel').hide();//กล่องยกเลิกอย่างเดียว
                                                    $('#div_profile').show();//กล่องฟอร์มกรอกข้อมูล
                                                    $('#div_sign_up').show();//ปุ่มบันทึก
                                                    $('#username').val(tax_number);//เซตชื่อผู้ใช้งาน
                                                    $('#tax_number').prop('readonly', true);//ไม่ให้แก้ไขเลขผู้เสียภาษีแล้ว
                                                    $('#check_api').val('1');
                                                }else if(result.dismiss === Swal.DismissReason.cancel) {//no
                                                    window.location.assign("{{ url('login') }}");
                                                }
                                            });
                                        }

                                    }
                            });
                            $('.branch_type[value="1"]').prop('checked', true);
                            $('.branch_type').iCheck('update');
                            $('#branch_type1').prop('disabled', false);
                        }else if(applicanttype_id == 1){  // นิติบุคคล
                            $.ajax({
                                    url: "{!! url('auth/register/get_legal_entity') !!}",
                                    method:"POST",
                                    data:{
                                            _token: "{{ csrf_token() }}",
                                            tax_id: tax_number
                                        },
                                    success:function (result){
                                        $.LoadingOverlay("hide");

                                        if(result.check == true){ //เลขนิติบุคคลมีข้อมูลการลงทะเบียน
                                                Swal.fire({
                                                    title: result.status,
                                                    width: 800,
                                                    showDenyButton: true,
                                                    showCancelButton: true,
                                                    confirmButtonText: 'กลับ',
                                                    cancelButtonText: 'ยกเลิก',
                                                }).then((result) => {
                                                    if (result.value) {
                                                        window.location.assign("{{ url('login') }}");
                                                    }
                                                });

                                        }else{ //เลขนิติบุคคลไม่มีการลงทะเบียน
                                            var juristic_status_pass = ['1', '2', '3'];
                                            if(juristic_status_pass.indexOf(result.juristic_status)!==-1){//สถานะดำเนินงานปกติ

                                                Swal.fire({
                                                    title: 'เลขนิติบุคคล '+ tax_number + ' ไม่มีข้อมูลการลงทะเบียน ท่านต้องการลงทะเบียนหรือไม่?',
                                                    showDenyButton: true,
                                                    showCancelButton: true,
                                                    width: 1500,
                                                    confirmButtonText: 'ยืนยัน',
                                                    cancelButtonText: 'ยกเลิก',
                                                }).then((result) => {
                                                    /* Read more about isConfirmed, isDenied below */
                                                    if (result.value) {
                                                        $('.branch_type[value="1"]').prop('checked', true);
                                                        $('.branch_type').iCheck('update');
                                                        $('#branch_type1').prop('disabled', false);
                                                        data_pid(tax_number);
                                                    } else if ( result.dismiss === Swal.DismissReason.cancel  ) {
                                                        window.location.assign("{{ url('login') }}");
                                                    }
                                                });

                                            }else if(result.juristic_status=='no-connect'){//ไม่สามารถเชื่อมโยงข้อมูล

                                                Swal.fire({
                                                            title: 'ขออภัยในความไม่สะดวก ขณะนี้ระบบไม่สามารถเชื่อมโยงข้อมูลได้  ท่านต้องการลงทะเบียนโดยการกรอกข้อมูลเองหรือไม่?',
                                                            html : '<span class="font-15 text-danger"><b>โปรดอ่าน!</b> กรณีสมัครโดยกรอกข้อมูลเอง หลังยืนยันอีเมลต้องรอเจ้าหน้าที่อนุมัติการใช้งานอีกครั้ง</span>',
                                                            showDenyButton: true,
                                                            showCancelButton: true,
                                                            width: '80%',
                                                            confirmButtonText: 'ใช่ (กรอกข้อมูลเอง)',
                                                            cancelButtonText: 'ไม่ (ไว้สมัครภายหลัง)',
                                                            }).then((result) => {
                                                                if (result.value) {//yes
                                                                    $('.branch_type[value="1"]').prop('checked', true);
                                                                    $('.branch_type').iCheck('update');
                                                                    $('#branch_type1').prop('disabled', false);
                                                                    $('#div_cancel').hide();//กล่องยกเลิกอย่างเดียว
                                                                    $('#div_profile').show();//กล่องฟอร์มกรอกข้อมูล
                                                                    $('#div_sign_up').show();//ปุ่มบันทึก
                                                                    $('#username').val(tax_number);//เซตชื่อผู้ใช้งาน
                                                                    $('#tax_number').prop('readonly', true);//ไม่ให้แก้ไขเลขผู้เสียภาษีแล้ว
                                                                }else if(result.dismiss === Swal.DismissReason.cancel) {//no
                                                                    window.location.assign("{{ url('login') }}");
                                                                }
                                                            });

                                            }else if(result.juristic_status=='not-found'){//ไม่พบข้อมูลใน DBD

                                                setTimeout(() => {
                                                    check_tax_ajax(tax_number, applicanttype_id);
                                                }, 500);

                                                // Swal.fire({
                                                //             title: 'ขออภัยไม่พบเลขนิติบุคคล ' + tax_number + ' คุณเป็นกิจการประเภทธุรกิจของคนต่างด้าวหรือไม่',
                                                //             showDenyButton: true,
                                                //             showCancelButton: true,
                                                //             width: 1500,
                                                //             confirmButtonText: 'ยืนยัน',
                                                //             cancelButtonText: 'ยกเลิก',
                                                //             }).then((result) => {
                                                //                 /* Read more about isConfirmed, isDenied below */
                                                //                 if (result.value) {
                                                //                     $('.applicanttype_id[value="5"]').prop('checked', true);
                                                //                     $('.applicanttype_id').iCheck('update');
                                                //                     $('#person_type').children('option[value!=""]').remove();
                                                //                     $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                                                //                     $('#person_type').append('<option value="2">เลขที่หนังสือเดินทาง</option>');
                                                //                     $('#person_type').append('<option value="3">เลขทะเบียนธุรกิจคนต่างด้าว</option>');
                                                //                     $('#person_type').val('3');
                                                //                     $('#person_type').select2();
                                                //                     $('#tax_number').attr('placeholder', 'เลขอื่นๆ');
                                                //                     $('#tax_number').attr('maxlength', '30');
                                                //                     $('#check_api').val('');
                                                //                     $('#personfile').prop('required', true);
                                                //                     $('.label_personfile').find('span.personfile').html('*');
                                                //                     $('.branch_type[value="1"]').prop('checked', true);
                                                //                     $('.branch_type').iCheck('update');
                                                //                     $('#branch_type1').prop('disabled', false);
                                                //                     data_pid(tax_number);
                                                //                 } else if ( result.dismiss === Swal.DismissReason.cancel  ) {

                                                //                 }
                                                //             });

                                            }else{

                                                $('#div_profile').hide();
                                                $('#div_cancel').show();
                                                $('#div_sign_up').hide();
                                                Swal.fire('ขออภัยเลขนิติบุคคล '+ tax_number +' สถานะ'+ result.juristic_status);

                                            }

                                        }

                                    }
                            });


                        }

                    }else{
                        data_value_null();
                            setinputreadonly(false);

                            Swal.fire({
                                position: 'center',
                                width: 600,
                                title: 'กรุณากรอกเลข'+cars[applicanttype_id]+'ให้ครบ!',
                                showConfirmButton: false,
                                timer: 1500
                            });
                    }

                }else{  //   อื่นๆ
                    check_tax_ajax(tax_number, applicanttype_id);
                }
            }else{
                Swal.fire({
                        position: 'center',
                        width: 600,
                        title: 'กรุณากรอกเลข'+cars[applicanttype_id]+'ให้ครบ!',
                        showConfirmButton: false,
                        timer: 1500
                });
            }
        });

            $('#person_type').val('1');
            $('#person_type').children('option[value="2"]').remove();
            $('#person_type').children('option[value="3"]').remove();
            $('#person_type').select2();
           $(".applicanttype_id").on("ifChanged",function(){
                    applicanttype();
                    setinputreadonly(false);
                    data_value_null();
                    $('#tax_number').val('');
                    $('#tax_number').prop('readonly', false);
                    $('#person_type').prop('readonly',false);
                  var applicanttype_id =  $('.applicanttype_id:checked').val();
                  if(applicanttype_id == 1){
                      $('#person_type').val('1');
                      $('#person_type').children('option[value="2"]').remove();
                      $('#person_type').children('option[value="3"]').remove();
                      $('#person_type').select2();
                      $('#tax_number').attr('placeholder', 'เลขนิติบุคคล');
                      $('#tax_number').attr('maxlength', '13');
                  }else    if(applicanttype_id == 2){
                      $('#person_type').children('option[value!=""]').remove();
                      $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                      $('#person_type').children('option[value="2"]').remove();
                      $('#person_type').children('option[value="3"]').remove();
                      $('#person_type').val('1');
                      $('#person_type').select2();
                      $('#tax_number').attr('placeholder', 'เลขประจำตัวประชาชน');
                      $('#tax_number').attr('maxlength', '13');
                   }else    if(applicanttype_id == 3){
                      $('#person_type').children('option[value!=""]').remove();
                      $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                      $('#person_type').children('option[value="2"]').remove();
                      $('#person_type').children('option[value="3"]').remove();
                      $('#person_type').val('1');
                      $('#person_type').select2();
                      $('#tax_number').attr('placeholder', 'เลขคณะบุคคล');
                      $('#tax_number').attr('maxlength', '13');
                   }else    if(applicanttype_id == 4){
                    $('#person_type').children('option[value!=""]').remove();
                      $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                      $('#person_type').children('option[value="2"]').remove();
                      $('#person_type').children('option[value="3"]').remove();
                      $('#person_type').val('1');
                      $('#person_type').select2();
                      $('#tax_number').attr('placeholder', 'เลขส่วนราชการ');
                      $('#tax_number').attr('maxlength', '13');
                    }else    if(applicanttype_id == 5){
                       $('#person_type').children('option[value!=""]').remove();
                      $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                      $('#person_type').append('<option value="2">เลขที่หนังสือเดินทาง</option>');
                      $('#person_type').append('<option value="3">เลขทะเบียนธุรกิจคนต่างด้าว</option>');
                    //   $('#person_type').val('');
                      $('#person_type').select2();
                      $('#tax_number').attr('placeholder', 'เลขอื่นๆ');
                      $('#tax_number').attr('maxlength', '30');
                  }else{
                      $('#person_type').children('option[value!=""]').remove();
                      $('#person_type').children('option[value="2"]').remove();
                      $('#person_type').children('option[value="3"]').remove();
                      $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                      $('#person_type').val('1');
                      $('#person_type').select2();
                      $('#tax_number').attr('placeholder', 'เลขประจำตัวประชาชน');
                      $('#tax_number').attr('maxlength', '13');
                  }
            });

            // ประเภทสาขา
            $(".branch_type").on("ifChanged",function(){
                 branch_type();
             });

            // ข้อมูลผู้ติดต่อ
             $("#contact_tax_id").keyup(function(event) {
                var tax_id        = $('#contact_tax_id').val() ;
                var applicanttype_id =  $('.applicanttype_id:checked').val();
                if(applicanttype_id != 2){
                    if(tax_id != ""){
                        tax_id = tax_id.toString().replace(/\D/g,'');
                        if(tax_id.length >= 13){
                            $.ajax({
                                url: "{!! url('auth/register/datatype') !!}",
                                method:"POST",
                                data:{
                                    _token: "{{ csrf_token() }}",
                                    applicanttype_id:'2',
                                    tax_id:tax_id
                                    },
                                success:function (result){
                                    if(checkNone(result.name) && result.length != 0){
                                        $('#first_name').val(result.name);
                                        $('#last_name').val(result.name_last);
                                        $('#contact_prefix_name').val(result.prefix_id).select2();
                                    }else{
                                        $('#first_name').val('');
                                        $('#last_name').val('');
                                        $('#contact_prefix_name').val('').select2();
                                    }
                                }
                            });
                    }else{
                                        $('#first_name').val('');
                                        $('#last_name').val('');
                                        $('#contact_prefix_name').val('').select2();
                    }
                    }else{
                                        $('#first_name').val('');
                                        $('#last_name').val('');
                                        $('#contact_prefix_name').val('').select2();
                    }
                 }
             });

                  applicanttype();
                  branch_type();


        $("#address_search").select2({
            dropdownAutoWidth: true,
            width: '100%',
            ajax: {
                url: "{{ url('/funtions/search-addreess') }}",
                type: "get",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchTerm: params // search term
                    };
                },
                results: function (response) {
                    return {
                        results: response
                    };
                },
                cache: true,
            },
            placeholder: 'คำค้นหา',
            minimumInputLength: 1,
        });

        $("#address_search").on('change', function () {
            $.ajax({
                url: "{!! url('/funtions/get-addreess/') !!}" + "/" + $(this).val() + '?khet=1'
            }).done(function( jsondata ) {
                if(jsondata != ''){

                    $('#subdistrict').val(jsondata.sub_title);
                    $('#district').val(jsondata.dis_title);
                    $('#province').val(jsondata.pro_title);
                    $('#zipcode').val(jsondata.zip_code);

                    $("#address_search").select2('val','');

                }
            });
        });

        $("#contact_address_search").select2({
            dropdownAutoWidth: true,
            width: '100%',
            ajax: {
                url: "{{ url('/funtions/search-addreess') }}",
                type: "get",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchTerm: params // search term
                    };
                },
                results: function (response) {
                    return {
                        results: response
                    };
                },
                cache: true,
            },
            placeholder: 'คำค้นหา',
            minimumInputLength: 1,
        });

        $("#contact_address_search").on('change', function () {
            $.ajax({
                url: "{!! url('/funtions/get-addreess/') !!}" + "/" + $(this).val() + '?khet=1'
            }).done(function( jsondata ) {
                if(jsondata != ''){

                    $('#contact_subdistrict').val(jsondata.sub_title);
                    $('#contact_district').val(jsondata.dis_title);
                    $('#contact_province').val(jsondata.pro_title);
                    $('#contact_zipcode').val(jsondata.zip_code);

                    $("#contact_address_search").select2('val','');

                }
            });
        });


    });



    // 1. การดึงข้อมูลนิติบุคคลจาก DBD ด้วยเลขนิติบุคคล 13 หลัก
    function data_pid(tax_number) {
        // Text
        $.LoadingOverlay("show", {
            image : "",
            text  : "กำลังโหลด..."
        });

        var applicanttype_id = $('.applicanttype_id:checked').val();
        if(applicanttype_id == 1 || applicanttype_id == 2 || applicanttype_id == 3){

            $.ajax({
                    url: "{!! url('auth/register/datatype') !!}",
                    method:"POST",
                    data:{
                          _token: "{{ csrf_token() }}",
                          applicanttype_id:applicanttype_id,
                          tax_id:tax_number
                        },
                    success: function (result){
                        $.LoadingOverlay("hide");
                        $('#tax_number').prop('readonly', true);
                        $('#check_api').val('1');
                        $('#personfile').prop('required', false);
                        $('.label_personfile').find('span.personfile').html('');
                        console.log(result);
                        if(checkNone(result.name) && result.length != 0){

                            if(applicanttype_id == 1 && (result.juristic_status != '1' && result.juristic_status != '2' && result.juristic_status != '3')){
                                Swal.fire({
                                    position: 'center',
                                    icon: 'success',
                                    title:  'เลขนิติบุคคล '+ tax_number + ' ไม่สามารถลงทะเบียนได้ ' + result.juristic_status,
                                    showConfirmButton: false,
                                    width: 1500,
                                    timer: 3000
                                });

                            }else{
                                $('#div_profile').show();
                                $('#div_cancel').hide();
                                $('#div_sign_up').show();
                                $('#username').val(tax_number);
                                $('#date_of_birth').val(result.RegisterDate);
                                if(applicanttype_id == 1){ //นิติบุคคล
                                    $('#prefix_name').val(result.prefix_id).select2();
                                    $('#name').val(result.name);
                                    $('#contact_tax_id').val('');
                                    $('#first_name').val('');
                                    $('#last_name').val('');
                                    $('#contact_prefix_name').val('').select2();
                                    $('#juristic_status').val(result.juristic_status);
                                }else if(applicanttype_id == 2){ //บุคคลธรรมดา
                                    $('#person_first_name').val(result.name);
                                    $('#person_last_name').val(result.name_last);
                                    $('#person_prefix_name').val(result.prefix_id).select2();

                                    $('#nationality').val(result.nationality); // สัญชาติ
                                    $('#contact_tax_id').val(tax_number);
                                    $('#first_name').val(result.name);
                                    $('#last_name').val(result.name_last);
                                    $('#contact_prefix_name').val(result.prefix_id).select2();
                                    $('#juristic_status').val('');
                                }else if(applicanttype_id == 3){ //คณะบุคคล
                                    $('#name').val('');
                                    $('#prefix_text').val('');
                                    $('#contact_tax_id').val('');
                                    $('#first_name').val('');
                                    $('#last_name').val('');
                                    $('#faculty_name').val(result.name);

                                    $('#contact_prefix_name').val('').select2();
                                    $('#juristic_status').val('');

                                }else{
                                    $('#contact_tax_id').val('');
                                    $('#first_name').val('');
                                    $('#last_name').val('');
                                    $('#contact_prefix_name').val('').select2();
                                    $('#juristic_status').val('');
                                }

                                $('#address_no').val(result.address);
                                // $('#building').val(result.building);
                                $('#soi').val(result.soi);
                                if(result.moo != 0){
                                    $('#moo').val(result.moo);
                                }else{
                                    $('#moo').val('');
                                }

                                $('#street').val(result.road);
                                $('#subdistrict').val(result.tumbol);
                                $('#district').val(result.ampur);
                                $('#province').val(result.province);
                                $('#zipcode').val(result.zipcode);

                                $('#email').val(result.email);
                                contact_readonly(true);//ไม่ให้แก้ไขที่อยู่ที่ดึงมา
                                setinputreadonly(true);
                            }
                        }else{

                            $('#div_profile').hide();
                            if(result.hasOwnProperty('connection') && result.connection===false){
                                Swal.fire({
                                    position: 'center',
                                    title: 'ไม่สามารถเชื่อมต่อบริการได้ในขณะนี้ กรุณาลองใหม่ในภายหลัง',
                                    showConfirmButton: true
                                });
                            }else{
                                const cars = ["","นิติบุคคล", "บุคคลธรรมดา", "คณะบุคคล", "ส่วนราชการ", "อื่นๆ"];
                               	Swal.fire({
                                    position: 'center',
                                    title: 'ไม่พบข้อมูล'+cars[applicanttype_id],
                                    showConfirmButton: false,
                                    timer: 1500
    					        });
                            }

                            data_value_null();
                            setinputreadonly(false);

                        }
                      }
                   });
                }else{

                    $.LoadingOverlay("hide");
                    $('#tax_number').prop('readonly', true);
                    $('#check_api').val('');
                    $('#personfile').prop('required', true);
                    $('.label_personfile').find('span.personfile').html('*');
                    $('#div_profile').show();
                    $('#div_cancel').hide();
                    $('#div_sign_up').show();
                    $('#username').val(tax_number);
                    setinputreadonly(false);
                    contact_readonly(false);//ให้แก้ไขที่อยู่ที่ไม่ได้ดึงมา
                }

                applicanttype();
                branch_type();

            }
            function applicanttype() {
                  if($('.applicanttype_id:checked').val() == 1){
                        $('.div_legal_entity').show(); //  นิติบุคคล
                        $('.div_natural_person').hide(); //  บุคคลธรรมดา
                        $('.div_natural_faculty').hide();  //   คณะบุคคล
                        $('.div_natural_service').hide(); //  ส่วนราชการ
                        $('.div_natural_another').hide();  //  อื่นๆ
                        $('.div_legal_nationality').hide(); //  สัญชาติ

                        $('#prefix_name').prop('required', true); //  นิติบุคคล
                        $('#name').prop('required', true);  //  นิติบุคคล
                        $('#person_prefix_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#person_first_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#person_last_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#faculty_name').prop('required', false);  //   คณะบุคคล
                        $('#service_name').prop('required', false);  //  ส่วนราชการ
                        $('#another_name').prop('required', false);  //  อื่นๆ

                        $('#span_date_birthday').html('วันที่จดทะเบียน');
                        $('#date_of_birth').attr('วันที่จดทะเบียน');
                        $('#label_address_no').html('ที่ตั้งสำนักงานใหญ่');
                        $('.checkbox_contact_address_no').html('ที่เดียวกับที่ตั้งสำนักงานใหญ่');

                        $('#div_branch_type').hide(); // ประเภทสาขา

                        $('.label_latitude').html('พิกัดที่ตั้ง (ลองจิจูด) <span class="text-danger">*</span>');
                        $('.label_longitude').html('พิกัดที่ตั้ง (ละติจูด) <span class="text-danger">*</span>');
                        $('#latitude').prop('required', true);
                        $('#longitude').prop('required', true);

                        $('#p_entity').show();
                        $('#p_another').hide();
                        // $('#personfile').attr('accept','');
                  }else    if($('.applicanttype_id:checked').val() == 2){
                        $('.div_legal_entity').hide(); //  นิติบุคคล
                        $('.div_natural_person').show(); //  บุคคลธรรมดา
                        $('.div_natural_faculty').hide();  //   คณะบุคคล
                        $('.div_natural_service').hide(); //  ส่วนราชการ
                        $('.div_natural_another').hide();  //  อื่นๆ
                        $('.div_legal_nationality').show(); //  สัญชาติ

                        $('#prefix_name').prop('required',false); //  นิติบุคคล
                        $('#name').prop('required', false);  //  นิติบุคคล
                        $('#person_prefix_name').prop('required', true);  //  บุคคลธรรมดา
                        $('#person_first_name').prop('required', true);  //  บุคคลธรรมดา
                        $('#person_last_name').prop('required', true);  //  บุคคลธรรมดา
                        $('#faculty_name').prop('required', false);  //   คณะบุคคล
                        $('#service_name').prop('required', false);  //  ส่วนราชการ
                        $('#another_name').prop('required', false);  //  อื่นๆ

                        $('#span_date_birthday').html('วันเกิด');
                        $('#date_of_birth').attr('วันเกิด');
                        $('#label_address_no').html('ที่อยู่ตามทะเบียนบ้าน');
                        $('.checkbox_contact_address_no').html('ที่เดียวกับที่อยู่ตามทะเบียนบ้าน');

                        $('#div_branch_type').hide(); // ประเภทสาขา

                        $('.label_latitude').html('พิกัดที่ตั้ง (ละติจูด)');
                        $('.label_longitude').html('พิกัดที่ตั้ง (ลองจิจูด)');
                        $('#latitude').prop('required', false);
                        $('#longitude').prop('required', false);


                        $('#p_entity').show();
                        $('#p_another').hide();
                        // $('#personfile').attr('accept','');
                    }else    if($('.applicanttype_id:checked').val() == 3){

                        $('.div_legal_entity').hide(); //  นิติบุคคล
                        $('.div_natural_person').hide(); //  บุคคลธรรมดา
                        $('.div_natural_faculty').show();  //   คณะบุคคล
                        $('.div_natural_service').hide(); //  ส่วนราชการ
                        $('.div_natural_another').hide();  //  อื่นๆ
                        $('.div_legal_nationality').hide(); //  สัญชาติ

                        $('#prefix_name').prop('required',false); //  นิติบุคคล
                        $('#name').prop('required', false);  //  นิติบุคคล
                        $('#person_prefix_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#person_first_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#person_last_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#faculty_name').prop('required', true);  //   คณะบุคคล
                        $('#service_name').prop('required', false);  //  ส่วนราชการ
                        $('#another_name').prop('required', false);  //  อื่นๆ

                        $('#span_date_birthday').html('วันที่จดทะเบียน');
                        $('#date_of_birth').attr('วันที่จดทะเบียน');
                        $('#label_address_no').html('ที่ตั้งคณะบุคคล');
                        $('.checkbox_contact_address_no').html('ที่เดียวกับที่ตั้งคณะบุคคล');

                        $('#div_branch_type').hide(); // ประเภทสาขา

                        $('.label_latitude').html('พิกัดที่ตั้ง (ละติจูด)');
                        $('.label_longitude').html('พิกัดที่ตั้ง (ลองจิจูด)');
                        $('#latitude').prop('required', false);
                        $('#longitude').prop('required', false);


                        $('#p_entity').show();
                        $('#p_another').hide();
                        // $('#personfile').attr('accept','');
                     }else    if($('.applicanttype_id:checked').val() == 4){

                        $('.div_legal_entity').hide(); //  นิติบุคคล
                        $('.div_natural_person').hide(); //  บุคคลธรรมดา
                        $('.div_natural_faculty').hide();  //   คณะบุคคล
                        $('.div_natural_service').show(); //  ส่วนราชการ
                        $('.div_natural_another').hide();  //  อื่นๆ
                        $('.div_legal_nationality').hide(); //  สัญชาติ

                        $('#prefix_name').prop('required',false); //  นิติบุคคล
                        $('#name').prop('required', false);  //  นิติบุคคล
                        $('#person_prefix_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#person_first_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#person_last_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#faculty_name').prop('required', false);  //   คณะบุคคล
                        $('#service_name').prop('required', true);  //  ส่วนราชการ
                        $('#another_name').prop('required', false);  //  อื่นๆ

                        $('#span_date_birthday').html('วันที่จดทะเบียน');
                        $('#date_of_birth').attr('วันที่จดทะเบียน');
                        $('#label_address_no').html('ที่อยู่/ที่ตั้งส่วนราชการ');
                        $('.checkbox_contact_address_no').html('ที่เดียวกับที่อยู่/ที่ตั้งส่วนราชการ');

                        $('#div_branch_type').hide(); // ประเภทสาขา

                        $('.label_latitude').html('พิกัดที่ตั้ง (ละติจูด)');
                        $('.label_longitude').html('พิกัดที่ตั้ง (ลองจิจูด)');
                        $('#latitude').prop('required', false);
                        $('#longitude').prop('required', false);


                        $('#p_entity').show();
                        $('#p_another').hide();
                        // $('#personfile').attr('accept','');

                    }else    if($('.applicanttype_id:checked').val() == 5){

                        $('.div_legal_entity').hide(); //  นิติบุคคล
                        $('.div_natural_person').hide(); //  บุคคลธรรมดา
                        $('.div_natural_faculty').hide();  //   คณะบุคคล
                        $('.div_natural_service').hide(); //  ส่วนราชการ
                        $('.div_natural_another').show();  //  อื่นๆ
                        $('.div_legal_nationality').show(); //  สัญชาติ

                        $('#prefix_name').prop('required',false); //  นิติบุคคล
                        $('#name').prop('required', false);  //  นิติบุคคล
                        $('#person_prefix_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#person_first_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#person_last_name').prop('required', false);  //  บุคคลธรรมดา
                        $('#faculty_name').prop('required', false);  //   คณะบุคคล
                        $('#service_name').prop('required', false);  //  ส่วนราชการ
                        $('#another_name').prop('required', true);  //  อื่นๆ

                        $('#span_date_birthday').html('วันที่จดทะเบียน/วันเกิด');
                        $('#date_of_birth').attr('วันที่จดทะเบียน/วันเกิด');
                        $('#label_address_no').html('ที่อยู่/ที่ตั้งอื่นๆ');
                        $('.checkbox_contact_address_no').html('ที่เดียวกับที่อยู่/ที่ตั้งอื่นๆ');

                        $('#div_branch_type').hide(); // ประเภทสาขา

                        $('.label_latitude').html('พิกัดที่ตั้ง (ละติจูด)');
                        $('.label_longitude').html('พิกัดที่ตั้ง (ลองจิจูด)');
                        $('#latitude').prop('required', false);
                        $('#longitude').prop('required', false);


                        $('#p_entity').hide();
                        $('#p_another').show();
                        // $('#personfile').attr('accept','application/pdf');
                  }
                //    data_value_null();

            }

            // เช็คความถูกต้องเลข 13 หลัก
            function checkID(id){
                if(id.substring(0,1)== 0) return false;
                if(id.length != 13) return false;
                for(i=0, sum=0; i < 12; i++)
                sum += parseFloat(id.charAt(i))*(13-i);
                if((11-sum%11)%10!=parseFloat(id.charAt(12))) return false;
                return true;
            }

            // รหัสสาขา
            function branch_type() {
               var row =  $('.branch_type:checked').val();
               if(row == 2){
                  $('.div_branch_code').show();
                  $('#branch_code').prop('required', true);
               }else{
                  $('.div_branch_code').hide();
                  $('#branch_code').prop('required', false);
               }
            }

            function checkbox_contact_address_no() {
                  if($('#checkbox_contact_address_no').is(':checked')){
                        $('#contact_address_no').val($('#address_no').val());
                        $('#contact_building').val($('#building').val());
                        $('#contact_soi').val($('#soi').val());
                        $('#contact_moo').val($('#moo').val());
                        $('#contact_street').val($('#street').val());
                        $('#contact_subdistrict').val($('#subdistrict').val());
                        $('#contact_district').val($('#district').val());
                        $('#contact_province').val($('#province').val());
                        $('#contact_zipcode').val($('#zipcode').val());
                        // $('#contact_country_code').val($('#country_code').val());
                  } else {
                        $('#contact_address_no').val('');
                        $('#contact_building').val('');
                        $('#contact_soi').val('');
                        $('#contact_moo').val('');
                        $('#contact_street').val('');
                        $('#contact_subdistrict').val('');
                        $('#contact_district').val('');
                        $('#contact_province').val('');
                        $('#contact_zipcode').val('');
                        // $('#contact_country_code').val('');
                 }
            }
            function checkNone(value) {
                  return value !== '' && value !== null && value !== undefined;
            }

            function check_password_and_number(value) {

                var html = '';
                var password = value.toString();
                var passwords= password.split("");
                var format   = {upper: false, lower: false, number: false, symbol: false}
                var allows   = Array(' ', '!', '"', '#', '$', '%',
                                     '&', "'", '(', ')', '*', '+',
                                     ',', '-', '.', '/', ':', ';',
                                     '<', '=', '>', '?', '@', '[',
                                     '\\', ']', '^', '_', '`', '{',
                                     '|', '}', '~'
                                    );
                var not_prefix = ' ';
                var not_allows = Array();

                $.each(passwords, function(index, value) {

                    if(value.match(/[A-Z]/g) !== null){
                        format.upper = true;
                    }

                    if(value.match(/[a-z]/g) !== null){
                        format.lower = true;
                    }

                    if(value.match(/[0-9]/g) !== null){
                        format.number = true;
                    }

                    if(value.match(/[A-Z]/g) == null && value.match(/[a-z]/g) == null && value.match(/[0-9]/g) == null){//นอกเหนือจากที่กำหนดไว้
                        if(!allows.includes(value)){//ไม่อยู่ในอักขระพิเศษที่อนุญาต
                            if(!not_allows.includes('<p>-ไม่อนุญาตให้ใช้ตัวอักษร '+value+'</p>')){//ยังไม่มี
                                not_allows.push('<p>-ไม่อนุญาตให้ใช้ตัวอักษร '+value+'</p>');
                            }
                        }else{
                            format.symbol = true;
                            if(value===not_prefix && index==0){
                                html += '<p>-ไม่อนุญาตให้ใช้ <code>ช่องว่าง</code> นำหน้า</p>';
                            }
                            if(value===not_prefix && index==(passwords.length-1)){
                                html += '<p>-ไม่อนุญาตให้ใช้ <code>ช่องว่าง</code> ตัวสุดท้าย</p>';
                            }
                        }
                    }

                });

                // if(format.upper===false){
                //     html += '<p>-อักษรภาษาอังกฤษตัวพิมพ์ใหญ่ อย่างน้อย 1 ตัว</p>';
                // }

                if(format.lower===false && format.upper===false){
                    html += '<p>-อักษรภาษาอังกฤษตัวพิมพ์ใหญ่ หรือ ตัวพิมพ์เล็ก อย่างน้อย 1 ตัว</p>';
                }

                if(format.number===false){
                    html += '<p>-ตัวเลข อย่างน้อย 1 ตัว</p>';
                }

                if(format.symbol===false){
                    html += '<p>-สัญลักษณ์พิเศษอย่างน้อย 1 ตัว</p>';
                }

                if(not_allows.length > 0){
                    html += not_allows.join('');
                }

                if(password.length < 8){
                    html += '<p>-คุณกรอกรหัสผ่านได้ '+password.length +' อักษร</p>';
                }

                return html ;

            }

            function checkLetterPassword(password) {
                var   password = password.toString();
                return  password.length ;
            }

            function contact_readonly(readonly){
                if($('#address_no').val()!=''){
                    $('#address_no').prop('readonly', readonly);
                }else{
                    $('#address_no').prop('readonly', false);
                }
                $('#building, #soi, #moo, #street').prop('readonly', readonly);
                $('#address_search').select2('enable', !readonly);
                
            }

            function setinputreadonly(value) {
                var applicanttype_id =     $('.applicanttype_id:checked').val();
                if(applicanttype_id == 1 || applicanttype_id == 2){
                    $('#person_type').prop('readonly',value);
                    $('#date_of_birth').prop('readonly',value);
                    if(value === true){
                            $('#date_of_birth').datepicker('remove');
                    }else{
                            $('#date_of_birth').datepicker('update');
                            $('.datepicker').datepicker({
                                autoclose: true,
                                toggleActive: true,
                                todayHighlight: true,
                                language:'th-th',
                                format: 'dd/mm/yyyy'
                            });
                    }

                    $('#name').prop('readonly',value);
                    if($('#prefix_name').val() == ""){
                            $('#prefix_name').prop('readonly',false);
                    }else{
                            $('#prefix_name').prop('readonly',value);
                    }
                    $('#prefix_name').select2();


                    if($('#person_prefix_name').val() == ""){
                            $('#person_prefix_name').prop('readonly',false);
                    }else{
                            $('#person_prefix_name').prop('readonly',value);
                    }
                    $('#person_prefix_name').select2();
                    $('#person_first_name').prop('readonly',value);
                    $('#person_last_name').prop('readonly',value);
                }else if(applicanttype_id == 3){
                    if($('#date_of_birth').val()!=""){
                        $('#date_of_birth').datepicker('remove');
                        $('#date_of_birth').prop('readonly',true);
                    }else{
                        $('#date_of_birth').prop('readonly', false);
                        $('#date_of_birth').datepicker({
                                autoclose: true,
                                toggleActive: true,
                                todayHighlight: true,
                                language:'th-th',
                                format: 'dd/mm/yyyy'
                            });
                    }
                    $('#faculty_name').prop('readonly',true);
                }else if(applicanttype_id == 4){
                    $('#date_of_birth').prop('readonly',false);
                     $('#date_of_birth').datepicker('update');
                     $('.datepicker').datepicker({
                        autoclose: true,
                        toggleActive: true,
                        todayHighlight: true,
                        language:'th-th',
                        format: 'dd/mm/yyyy'
                     });
                     $('#service_name').prop('readonly',false);
                }else if(applicanttype_id == 5){
                      $('#date_of_birth').prop('readonly',false);
                      $('#date_of_birth').datepicker('update');
                      $('.datepicker').datepicker({
                        autoclose: true,
                        toggleActive: true,
                        todayHighlight: true,
                        language:'th-th',
                        format: 'dd/mm/yyyy'
                     });
                     $('#another_name').prop('readonly',false);
                }
            }

            function data_value_null() {
                        $('#date_of_birth').val('');
                        $('#branch_code').val('');

                        $('#prefix_name').val('').select2();
                        $('#name').val('');

                        $('#person_prefix_name').val('').select2();
                        $('#person_first_name').val('');
                        $('#person_last_name').val('');


                        $('#faculty_name').val('');

                        $('#service_name').val('');
                        $('#another_name').val('');
                        $('#nationality').val('');
                        $('#address_no').val('');
                        $('#building').val('');
                        $('#soi').val('');
                        $('#moo').val('');
                        $('#street').val('');
                        $('#subdistrict').val('');
                        $('#district').val('');
                        $('#province').val('');
                        $('#zipcode').val('');
                        $('#latitude').val('');
                         $('#longitude').val('');

                        // $('#country_code').val('');

                        $('#checkbox_contact_address_no').prop('checked', false);
                        $('#contact_address_no').val('');
                        $('#contact_building').val('');
                        $('#contact_soi').val('');
                        $('#contact_moo').val('');
                        $('#contact_street').val('');
                        $('#contact_subdistrict').val('');
                        $('#contact_district').val('');
                        $('#contact_province').val('');
                        $('#contact_zipcode').val('');
                        // $('#contact_country_code').val('');



                        $('#contact_tax_id').val('');
                        $('#first_name').val('');
                        $('#last_name').val('');
                        $('#contact_prefix_name').val('').select2();
                        $('#juristic_status').val('');
                        $('#check_api').val('');
                        $('#personfile').prop('required', true);
                        $('.label_personfile').find('span.personfile').html('*');

                        $('#div_profile').hide();
                        // $('#tax_number').val('');
                        $('#password2').val('');
                        $('#password').val('');
                        $('#username').val('');
                        $('.delete_personfile').click();
                        $('#div_cancel').show();
                        $('#div_sign_up').hide();
                        $('#contact_prefix_name').val('').select2();
                        $('#first_name').val('');
                        $('#last_name').val('');
                        $('#contact_position').val('');
                        $('#tel').val('');
                        $('#phone_number').val('');
                        $('#fax').val('');
                        $('#email').val('');
                        $('#checkbox-pdpa').prop('checked', false);
                        $('#checkbox-pdpa').change();
              }

            function input_number() {
                  // อนุญาติให้กรอกได้เฉพาะตัวเลข 0-9 จุด และคอมม่า
                $(".input_number").on("keypress",function(e){
                    var eKey = e.which || e.keyCode;
                    if((eKey<48 || eKey>57) && eKey!=46 && eKey!=44){
                        return false;
                    }
                });
             }
             function check_format_en() {

                $(".check_format_en").on("keypress",function(event){
                var allowedEng = true; //อนุญาตให้คีย์อังกฤษ
                var allowedThai = false; //อนุญาตให้คีย์ไทย
                var allowedNum = true; //อนุญาตให้คีย์ตัวเลข
                var k = event.keyCode;/* เช็คตัวเลข 0-9 */
                if (k>=48 && k<=57) {
                    return allowedNum;
                }
                /* เช็คคีย์อังกฤษ a-z, A-Z */
                if ((k>=65 && k<=90) || (k>=97 && k<=122)) {
                    return allowedEng;
                }
                /* เช็คคีย์ไทย ทั้งแบบ non-unicode และ unicode */
                if ((k>=161 && k<=255) || (k>=3585 && k<=3675)) {
                    return allowedThai;
                }
                });
             }

            //ตรวจสอบวันที่ต้องไม่เกินวันที่ปัจจุบัน
            function check_date_max_now(ele){

                let dates = $(ele).val().split('/');

                if(dates.length == 3 && dates[2].length==4){
                    let input = new Date((dates[2]-543)+'-'+dates[1]+'-'+dates[0]);
                    let now = new Date("{{ date('Y-m-d') }}");
                    if(input.getTime() > now.getTime()){
                        $(ele).val('');
                        alert('กรุณาเลือก วันที่จดทะเบียน/วันเกิด ไม่เกินวันที่ปัจจุบัน');
                    }
                }else{
                    $(ele).val('');
                    alert('กรุณากรอก วันที่จดทะเบียน/วันเกิด ให้ถูกต้อง');
                }
            }

            //เช็คเลข 13 หลักจากทุก API
            function check_tax_ajax(tax_number, applicanttype_id){

                // Text
                $.LoadingOverlay("show", {
                    image : "",
                    text  : "กำลังโหลด..."
                });

                $.ajax({
                    url: "{!! url('auth/register/get_taxid') !!}",
                    method:"POST",
                    data:{
                        _token: "{{ csrf_token() }}",
                        tax_id: tax_number,
                        applicanttype_id: applicanttype_id
                    },
                    success:function (result){
                        $.LoadingOverlay("hide");
                        if(result.check == true){
                            Swal.fire({
                                title: 'ขออภัยเลข '+ tax_number +' ขึ้นทะเบียนในระบบประเภท'+ result.applicant_type  ,
                                width: 800,
                                showDenyButton: true,
                                showCancelButton: true,
                                confirmButtonText: 'กลับ',
                                cancelButtonText: 'ยกเลิก',
                            }).then((result) => {
                                if (result.value) {
                                    window.location.assign("{{ url('login') }}");
                                }
                            });

                        }else if(result.check_api == true){

                            if(result.type == 1){ //นิติบุคคล
                                Swal.fire({
                                    title: result.status,
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    width: 1500,
                                    confirmButtonText: 'ยืนยัน',
                                    cancelButtonText: 'ยกเลิก',
                                }).then((result) => {
                                    /* Read more about isConfirmed, isDenied below */
                                    if (result.value) {
                                        $('.applicanttype_id[value="1"]').prop('checked', true);
                                        $('.applicanttype_id').iCheck('update');
                                        $('.branch_type[value="1"]').prop('checked', true);
                                        $('.branch_type').iCheck('update');
                                        $('#branch_type1').prop('disabled', false);
                                        $('#person_type').children('option[value!=""]').remove();
                                        $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                                        $('#person_type').val('1');
                                        $('#person_type').select2();
                                        data_pid(tax_number);
                                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                                        window.location.assign("{{ url('login') }}");
                                    }
                                });
                            }else if(result.type == 2){  // บุคคลธรรมดา
                                if(result.person == 1){
                                    Swal.fire({
                                        title: result.status,
                                        width: 1500,
                                        showDenyButton: true,
                                        showCancelButton: true,
                                        confirmButtonText: 'กลับ',
                                        cancelButtonText: 'ยกเลิก',
                                    }).then((result) => {

                                        if (result.value) {
                                            window.location.assign("{{ url('login') }}");
                                        }
                                    });

                                }else{
                                    Swal.fire({
                                        title: result.status,
                                        showDenyButton: true,
                                        showCancelButton: true,
                                        width: 1500,
                                        confirmButtonText: 'ยืนยัน',
                                        cancelButtonText: 'ยกเลิก',
                                    }).then((result) => {
                                        /* Read more about isConfirmed, isDenied below */
                                        if (result.value) {
                                            $('.applicanttype_id[value="2"]').prop('checked', true);
                                            $('.applicanttype_id').iCheck('update');
                                            $('#person_type').children('option[value!=""]').remove();
                                            $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                                            $('#person_type').val('1');
                                            $('#person_type').select2();
                                            data_pid(tax_number);
                                        } else if ( result.dismiss === Swal.DismissReason.cancel  ) {
                                            window.location.assign("{{ url('login') }}");
                                        }
                                    });
                                }
                            }else if(result.type == 3){  // คณะบุคคล
                                Swal.fire({
                                    title: result.status,
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    width: 1500,
                                    confirmButtonText: 'ยืนยัน',
                                    cancelButtonText: 'ยกเลิก',
                                }).then((result) => {
                                    if (result.value) {
                                        $('.applicanttype_id[value="3"]').prop('checked', true);
                                        $('.applicanttype_id').iCheck('update');
                                        $('.branch_type[value="1"]').prop('checked', true);
                                        $('.branch_type').iCheck('update');
                                        $('#branch_type1').prop('disabled', false);
                                        $('#person_type').children('option[value!=""]').remove();
                                        $('#person_type').append('<option value="1">เลขประจำตัวผู้เสียภาษี</option>');
                                        $('#person_type').val('1');
                                        $('#person_type').select2();
                                        data_pid(tax_number);
                                    } else if ( result.dismiss === Swal.DismissReason.cancel  ) {
                                        window.location.assign("{{ url('login') }}");
                                    }
                                });
                            }
                        }else{

                            var title = 'เลขอื่นๆ '+ tax_number + ' ไม่มีข้อมูลการลงทะเบียน ท่านต้องการลงทะเบียนหรือไม่?';
                            Swal.fire({
                                title: title,
                                showDenyButton: true,
                                showCancelButton: true,
                                width: 1500,
                                confirmButtonText: 'ยืนยัน',
                                cancelButtonText: 'ยกเลิก',
                            }).then((result) => {
                                if (result.value) {

                                    $('.applicanttype_id').prop('checked', false);
                                    $('.applicanttype_id[value="5"]').prop('checked', true);
                                    $('.applicanttype_id').iCheck('update');

                                    $('#check_api').val('');
                                    $('#personfile').prop('required', true);
                                    $('.label_personfile').find('span.personfile').html('*');
                                    $('.branch_type[value="1"]').prop('checked', true);
                                    $('.branch_type').iCheck('update');
                                    $('#branch_type1').prop('disabled', false);
                                    data_pid(tax_number);
                                    var check_ID = checkID(tax_number);
                                    if(check_ID === false){
                                        $(".tax_id_format").inputmask();
                                    }                             
                                } else if ( result.dismiss === Swal.DismissReason.cancel  ) {
                                    window.location.assign("{{ url('login') }}");
                                }
                            });
                        }
                    }
                });

            }
    </script>
@endpush

@push('scripts')
<script>
window.__PREREG_BOOTING = true;

(function(){
  const hasjQ = () => !!(window.jQuery && window.$);

  // --- DOM ready ---
  const domReady = new Promise(res=>{
    if (document.readyState === 'complete' || document.readyState === 'interactive') return res();
    document.addEventListener('DOMContentLoaded', res, { once:true });
  });

  // ---------- Targeted SweetAlert auto-confirm (บุคคลธรรมดา) ----------
  let __PREREG_P2_CALLED = false;
  domReady.then(()=>{
    if (window.Swal && typeof Swal.fire === 'function' && !Swal.__autoConfirmType2){
      const orig = Swal.fire;
      const P2_CONFIRM = /(ท่านต้องการลงทะเบียนผู้ประกอบการประเภทบุคคลธรรมดา|หมายเลข.*บุคคลธรรมดา.*ลงทะเบียน|ลงทะเบียน.*บุคคลธรรมดา)/;
      Swal.fire = function(opts){
        const p = orig.apply(this, arguments);
        try {
          const title = (opts && typeof opts.title === 'string') ? opts.title : '';
          if (P2_CONFIRM.test(title)) {
            const autoPress = () => {
              const btn = document.querySelector('.swal2-confirm');
              if (btn && !btn.disabled && btn.offsetParent !== null) {
                const tax = (document.querySelector('#tax_number')?.value ||
                             document.querySelector('#contact_tax_id')?.value || '').replace(/\D/g,'');
                btn.click();
                console.log('[prereg] auto-confirmed P2 Swal:', title);
                setTimeout(()=>{
                  try {
                    const $ = window.jQuery;
                    const typeSel = ($ && $('.applicanttype_id:checked').val()) || '';
                    if (!__PREREG_P2_CALLED && typeSel == '2' &&
                        typeof window.data_pid === 'function' && tax.length >= 13) {
                      __PREREG_P2_CALLED = true;
                      console.log('[prereg] calling data_pid for P2…');
                      window.data_pid(tax);
                    }
                  } catch(_) {}
                }, 900);
                return true;
              }
              return false;
            };
            setTimeout(autoPress, 150);
            setTimeout(autoPress, 300);
            setTimeout(autoPress, 600);
          }
        } catch(_) {}
        return p;
      };
      Swal.__autoConfirmType2 = true;
    }
  });

  // ---------- wait until #search has a handler ----------
  const searchHandlerReady = new Promise(resolve=>{
    function alreadyBound(){
      const btn=document.getElementById('search');
      if(!btn) return false;
      if(typeof btn.onclick==='function') return true;
      try{ if(hasjQ()&&jQuery._data(btn,'events')?.click?.length) return true; }catch(_){}
      return false;
    }
    if (alreadyBound()) return resolve();
    const origAdd=EventTarget.prototype.addEventListener;
    EventTarget.prototype.addEventListener=function(type,listener,opts){
      if (type==='click'&&this&&this.id==='search') queueMicrotask(resolve);
      return origAdd.call(this,type,listener,opts);
    };
    domReady.then(()=>{
      if (hasjQ()&&!jQuery.fn.__preregOnHooked){
        const origOn=jQuery.fn.on;
        jQuery.fn.on=function(type){
          if (this.filter('#search').length && /(^|\s)click(\s|\.|$)/.test(type)) queueMicrotask(resolve);
          return origOn.apply(this,arguments);
        };
        jQuery.fn.__preregOnHooked=true;
      }
    });
  });

  // ---------- plugins must be ready ----------
  const pluginsReady = new Promise(resolve=>{
    function tryNow(){
      if (!hasjQ()) return false;
      const $=jQuery;
      if ($.fn.select2 && $.fn.iCheck && $.LoadingOverlay) { resolve(); return true; }
      return false;
    }
    domReady.then(tryNow); tryNow();
  });

  // ---------- app ready ----------
  const appReady = new Promise(resolve=>{
    domReady.then(()=>{
      const btn=document.getElementById('search');
      const readyNow=()=> (window.connection===true) || (btn && !btn.disabled && btn.getAttribute('aria-disabled')!=='true');
      if (readyNow()) return resolve();
      const mo=new MutationObserver(()=>{ if(readyNow()){ mo.disconnect(); resolve(); }});
      if (btn) mo.observe(btn,{attributes:true,attributeFilter:['disabled','aria-disabled','class']});
      let _conn=window.connection;
      Object.defineProperty(window,'connection',{
        configurable:true,
        get(){ return _conn; },
        set(v){ _conn=v; if (v===true) resolve(); }
      });
    });
  });

  // ---------- helpers ----------
  function nativeSet($el,val){
    if(!$el||!$el.length)return;
    const el=$el.get(0);
    const setter=Object.getOwnPropertyDescriptor(el.__proto__,'value')?.set
              || Object.getOwnPropertyDescriptor(HTMLInputElement.prototype,'value')?.set;
    if (setter) setter.call(el,String(val)); else el.value=String(val);
    el.dispatchEvent(new Event('input',{bubbles:true}));
    el.dispatchEvent(new Event('change',{bubbles:true}));
    $el.trigger('input').trigger('change');
  }

  function setApplicantAndWait(val){
    return new Promise(resolve=>{
      const $ = jQuery;
      const $r = $('.applicanttype_id[value="'+val+'"]');
      if (!$r.length) return resolve(false);
      const input = document.getElementById('tax_number');
      let mo;
      const finish = () => { if (mo) mo.disconnect(); resolve(true); };
      if (input && val === '2'){
        const isReady = () => {
          const okPh  = input.getAttribute('placeholder') === 'เลขประจำตัวประชาชน';
          const okLen = String(input.getAttribute('maxlength')) === '13';
          const okPT  = ($('#person_type').val() || '') === '1';
          return okPh && okLen && okPT;
        };
        if (isReady()) finish();
        else {
          mo = new MutationObserver(()=>{ if (isReady()) finish(); });
          mo.observe(input, { attributes:true, attributeFilter:['placeholder','maxlength'] });
          const pt = document.getElementById('person_type');
          if (pt) mo.observe(pt, { childList:true, subtree:true, attributes:true });
          setTimeout(finish, 1500);
        }
      } else {
        setTimeout(finish, 150);
      }
      if ($r.iCheck) { $r.iCheck('check'); $r.iCheck('update'); }
      else { $r.prop('checked', true).trigger('change'); }
    });
  }

  function mapJT(jt){
    if (jt==='1') return '2';                 // บุคคลธรรมดา (Iindustry quirk)
    if (jt==='2' || jt==='3') return '1';     // นิติ / คณะ
    if (jt==='4') return '3';                 // ส่วนราชการ
    return '5';
  }

  function clickSearch(){
    const btn=document.getElementById('search');
    if (btn){
      btn.dispatchEvent(new MouseEvent('click',{bubbles:true,cancelable:true,view:window}));
      if (window.jQuery) jQuery(btn).trigger('click');
    }
  }

  // ---------- main flow ----------
  Promise.all([domReady,searchHandlerReady,pluginsReady,appReady]).then(async function(){
    if (!hasjQ()) { window.__PREREG_BOOTING=false; return; }

    const $=jQuery;
    const p=window.__PREREG||{};
    const jt=p.jt?String(p.jt):'';
    const raw=p.bid||p.uid||p.tax_number||'';
    const tax=(raw?String(raw):'').replace(/\D/g,'');
    const $tax = $('#tax_number').length?$('#tax_number'):$('#contact_tax_id');

    if (!jt || !tax || !$tax.length) { window.__PREREG_BOOTING=false; return; }

    // 👉 only apply mapping for the radio select
    const radioVal = (p.source === 'i-industry') ? mapJT(jt) : jt;

    // 1) select applicant type
    await setApplicantAndWait(radioVal);

    // 2) type tax
    $tax.prop('readOnly',false);
    nativeSet($tax,tax);

    // 3) click search
    setTimeout(()=>{
      console.log('[prereg] CLICK fired',{jt,radioVal,tax:$tax.val()});
      clickSearch();
      window.__PREREG_BOOTING=false;
      setTimeout(()=>{
        document.body.style.overflowY='auto';
        document.documentElement.style.overflowY='auto';
      },1200);
    },150);
  });
})();
</script>
@endpush


@yield('scripts')
@stack('scripts')

