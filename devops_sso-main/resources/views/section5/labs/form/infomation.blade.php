<fieldset class="white-box">
    <legend>ข้อมูลหน่วยตรวจสอบ</legend>
    <div class="row">

        <div class="col-md-12 col-sm-12 h4">

            @php
                $type_arr = [1 => 'IB', 2 => 'CB'];
            @endphp

            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">ชื่อหน่วยตรวจสอบ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->lab_name)?$labs->lab_name: '-') !!}</span></p>
                </div>
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">รหัสหน่วยตรวจสอบ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->lab_code)?$labs->lab_code: '-') !!}</span></p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">ชื่อหน่วยงานที่ยื่นขอ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->name)?$labs->name:' - ') !!}</span></p>
                </div>
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">เลขนิติบุคคล :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->taxid)?$labs->taxid:' - ') !!}</span></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">วันที่เริ่มเป็นหน่วยตรวจสอบ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->lab_start_date)?HP::DateThaiFull($labs->lab_start_date): '-') !!}</span></p>
                </div>
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">วันที่สิ้นสุดเป็นหน่วยตรวจสอบ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    @php
                        $max_data = $labs->scope_standard()->whereNotNull('end_date')->orderBy('end_date','desc')->first();
                    @endphp
                    <p class="div_dotted"><span class="text-bold-300">{!! !empty( $labs->lab_end_date )?HP::DateThaiFull($labs->lab_end_date):(!empty($max_data->end_date)?HP::DateThaiFull($max_data->end_date): '-') !!}</span></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">สถานะหน่วยตรวจสอบ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted">
                        
                        @php
                            $StateHtml = [1 => '<span class="text-success">Active</span>', 2 => '<span class="text-danger">Not Active</span>' ];

                            if(!empty($max_data)){
                                echo (!empty($max_data->end_date) && $max_data->end_date >= date('Y-m-d')) && array_key_exists($max_data->state, $StateHtml) ? $StateHtml[$max_data->state] : '<span class="text-danger">Not Active</span>' ;
                            }else{
                                echo '<span class="text-danger">Not Active</span>';
                            }
                        @endphp
          
                    </p>
                </div>
            </div>

            <hr>
            
            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">ที่อยู่ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->lab_address)?$labs->lab_address:' - ') !!}</span></p>
                </div>
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">หมู่ที่ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->lab_moo)?$labs->lab_moo:' - ') !!}</span></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">หมู่บ้าน/อาคาร :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->lab_building)?$labs->lab_building:' - ') !!}</span></p>
                </div>
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">ตรอก/ซอย :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->lab_soi)?$labs->lab_soi:' - ') !!}</span></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">ถนน :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->lab_road)?$labs->lab_road:' - ') !!}</span></p>
                </div>
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">ตำบล/แขวง :</span></p>
                </div>
                 <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->LabSubdistrictName)?$labs->LabSubdistrictName:' - ') !!}</span></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">อำเภอ/เขต :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->LabDistrictName)?$labs->LabDistrictName:' - ') !!}</span></p>
                </div>
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">จังหวัด :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->LabProvinceName)?$labs->LabProvinceName:' - ') !!}</span></p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">รหัสไปรษณีย์ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->lab_zipcode)?$labs->lab_zipcode:' - ') !!}</span></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">โทรศัพท์ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->lab_phone)?$labs->lab_phone: '-') !!}</span></p>
                </div>
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">โทรสาร :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->lab_fax)?$labs->lab_fax: '-') !!}</span></p>
                </div>
            </div>

            <hr>
            
            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">ชื่อผู้ประสานงาน :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->co_name)?$labs->co_name: '-') !!}</span></p>
                </div>
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">ตำแหน่งผู้ประสานงาน :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->co_position)?$labs->co_position: '-') !!}</span></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">โทรศัพท์มือถือ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->co_mobile)?$labs->co_mobile: '-') !!}</span></p>
                </div>
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">โทรศัพท์ :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->co_phone)?$labs->co_phone: '-') !!}</span></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">โทรสาร :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->co_fax)?$labs->co_fax: '-') !!}</span></p>
                </div>
                <div class="col-md-2 col-sm-12">
                    <p class="text-right"><span class="text-bold-300">อีเมล :</span></p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <p class="div_dotted"><span class="text-bold-300">{!! (!empty($labs->co_email)?$labs->co_email: '-') !!}</span></p>
                </div>
            </div>

        </div>

    </div>
    
</fieldset>