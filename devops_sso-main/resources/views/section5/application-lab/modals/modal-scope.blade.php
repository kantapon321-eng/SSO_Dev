<div class="modal fade bs-example-modal-lg" role="dialog" aria-labelledby="ScopeModalLabel" aria-hidden="true" id="ScopeModal" >
    <div class="modal-dialog modal-dialog-centere modal-lg" style="width: 1140px;max-width: 1140px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h4 class="modal-title" id="ScopeModalLabel">เลือกรายการทดสอบที่ขอรับการแต่งตั้ง</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">

                        @php
                            $list_standard = App\Models\Basic\Tis::select('tb3_Tisno', 'tb3_TisThainame', 'tb3_TisAutono')->whereIn('status', ['-1', '0', '1', '2', '3'])->orderBy('tb3_Tisno')->get();

                            $option_standard = [];
                            foreach ($list_standard as $key => $item ) {
                                $option_standard[$item->tb3_TisAutono] = $item->tb3_Tisno.' : '.(strip_tags($item->tb3_TisThainame));
                            }
                        @endphp

                        <div class="form-group required">
                            {!! Form::label('modal_tis_id', 'มอก.', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                {!! Form::select('modal_tis_id', $option_standard, null, ['class' => 'form-control', 'placeholder'=>'- เลือกมอก. -', 'id' => 'modal_tis_id']) !!}
                            </div>
                        </div>
                        <div class="form-group required">
                            {!! Form::label('modal_tis_name', 'ชื่อ มอก.', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                {!! Form::text('modal_tis_name', null, ['class' => 'form-control', 'id' => 'modal_tis_name', 'disabled' => true]) !!}
                            </div>
                        </div>

                        <div class="form-group required">
                            {!! Form::label('modal_test_item', 'รายการทดสอบ', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                {!! Form::select('modal_test_item', [], null, ['class' => 'form-control', 'placeholder'=>'- เลือกรายการทดสอบ -', 'id' => 'modal_test_item']) !!}
                            </div>
                        </div>

                        <div class="form-group required box_input_tools_select">
                            {!! Form::label('modal_test_tools', 'เครื่องมือที่ใช้', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                <div class="input-group">
                                    {!! Form::select('modal_test_tools', [], null, ['class' => 'form-control', 'placeholder'=>'- เลือกเครื่องมือที่ใช้ -', 'id' => 'modal_test_tools']) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-success" type="button" id="modal_btn_test_tools_specify">ระบุเอง</button>
                                    </span>
                                </div>
                                <span class="text-danger"><i>(ระบุเครื่องมือที่ใช้ โดยไม่ต้องระบุ ยี่ห้อ/รุ่น)</i></span>
                            </div>
                        </div>

                        <div class="form-group required box_input_tools_txt">
                            {!! Form::label('modal_test_tools_txt', 'เครื่องมือที่ใช้', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                <div class="input-group">
                                    {!! Form::text('modal_test_tools_txt', null, ['class' => 'form-control', 'id' => 'modal_test_tools_txt']) !!}
                                    <span class="modal_test_tools_select">{!! Form::select('modal_test_tools_select', [], null, ['class' => 'form-control', 'id' => 'modal_test_tools_select']) !!}</span>
                                    <span class="input-group-btn">
                                        <button class="btn btn-info" type="button" id="modal_btn_test_tools_input" value="1">เลือก</button>
                                        <button class="btn btn-success" type="button" id="modal_btn_test_tools_add">เพิ่ม</button>
                                        <button class="btn btn-danger" type="button" id="modal_btn_test_tools_cancel">ยกเลิก</button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('modal_test_tools_no', 'รหัส/หมายเลข', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                {!! Form::text('modal_test_tools_no', null, ['class' => 'form-control', 'id' => 'modal_test_tools_no']) !!}
                            </div>
                        </div>

                        <div class="form-group required">
                            {!! Form::label('modal_capacity', 'ขีดความสามารถ', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                {!! Form::text('modal_capacity', null, ['class' => 'form-control', 'id' => 'modal_capacity']) !!}
                            </div>
                        </div>

                        <div class="form-group required">
                            {!! Form::label('modal_range', 'ช่วงการใช้งาน', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                {!! Form::text('modal_range', null, ['class' => 'form-control', 'id' => 'modal_range']) !!}
                            </div>
                        </div>

                        <div class="form-group required">
                            {!! Form::label('modal_true_value', 'ความละเอียดที่อ่านได้', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                {!! Form::text('modal_true_value', null, ['class' => 'form-control', 'id' => 'modal_true_value']) !!}
                            </div>
                        </div>

                        <div class="form-group required">
                            {!! Form::label('modal_fault_value', 'ความคลาดเคลื่อนที่ยอมรับ', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                {!! Form::text('modal_fault_value', null, ['class' => 'form-control', 'id' => 'modal_fault_value']) !!}
                            </div>
                        </div>

                        <div class="form-group required">
                            {!! Form::label('modal_test_duration', 'ระยะการทดสอบ(วัน)', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                {!! Form::text('modal_test_duration', null, ['class' => 'form-control input_number', 'id' => 'modal_test_duration']) !!}
                            </div>
                        </div>

                        <div class="form-group required">
                            {!! Form::label('modal_test_price', 'ค่าใช้จ่ายในการทดสอบ/ชุดละ', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-8">
                                {!! Form::text('modal_test_price', null, ['class' => 'form-control input_number', 'id' => 'modal_test_price']) !!}
                            </div>
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-11">
                            <button type="button" class="btn btn-success waves-effect text-left pull-right" id="btn_get_tr">เพิ่ม</button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-11">
                            <p class="text-danger">หมายเหตุ : เพิ่มข้อมูลในตารางรายการทดสอบภายใต้ มอก. เดียวกันเท่านั้น</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="myTableScopeCopy" data-toggle="table" >
                                <thead>
                                    <tr>
                                        <th align="top" width="2%" class="text-center">#</th>
                                        <th align="top" width="10%" class="text-center text-top">รายการทดสอบ</th>
                                        <th align="top" width="15%" class="text-center text-top">เครื่องมือที่ใช้</th>
                                        <th align="top" width="10%" class="text-center">รหัส/หมายเลข</th>
                                        <th align="top" width="15%" class="text-center">ขีดความสามารถ</th>
                                        <th align="top" width="10%" class="text-center">ช่วงการ<br>ใช้งาน</th>
                                        <th align="top" width="10%" class="text-center">ความละเอียดที่อ่านได้</th>
                                        <th align="top" width="10%" class="text-center">ความคลาดเคลื่อนที่ยอมรับ</th>
                                        <th align="top" width="10%" class="text-center">ระยะการทดสอบ(วัน)</th>
                                        <th align="top" width="10%" class="text-center">ค่าใช้จ่ายในการทดสอบ/ชุดละ</th>
                                        <th align="top" width="5%" class="text-center">ลบ</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                            <table class="table table-bordered table-sm" id="myTableScope" data-toggle="table" style="display: none" >
                                <thead>
                                    <tr>
                                        <th align="top" width="2%" class="text-center">#</th>
                                        <th align="top" width="10%" class="text-center text-top">รายการทดสอบ</th>
                                        <th align="top" width="15%" class="text-center text-top">เครื่องมือที่ใช้</th>
                                        <th align="top" width="10%" class="text-center">รหัส/หมายเลข</th>
                                        <th align="top" width="15%" class="text-center">ขีดความสามารถ</th>
                                        <th align="top" width="10%" class="text-center">ช่วงการ<br>ใช้งาน</th>
                                        <th align="top" width="10%" class="text-center">ความละเอียดที่อ่านได้</th>
                                        <th align="top" width="10%" class="text-center">ความคลาดเคลื่อนที่ยอมรับ</th>
                                        <th align="top" width="10%" class="text-center">ระยะการทดสอบ(วัน)</th>
                                        <th align="top" width="10%" class="text-center">ค่าใช้จ่ายในการทดสอบ/ชุดละ</th>
                                        <th align="top" width="5%" class="text-center">ลบ</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success waves-effect text-left" id="btn_gen_box">สร้าง</button>
                <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    <!-- /.modal-content -->
    </div>
<!-- /.modal-dialog -->
</div>

@push('js')
    <script>

        var arr_tools = {};
        var arr_tst_item = {};
        $(document).ready(function () {

            $(".input_number").on("keypress",function(e){
                var eKey = e.which || e.keyCode;
                if((eKey<48 || eKey>57) && eKey!=46 && eKey!=44){
                    return false;
                }
            });

            $(".Mscope_number_only").on("keypress keyup blur",function (event) {
                $(this).val($(this).val().replace(/[^0-9\.]/g,''));
                if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                    event.preventDefault();
                }
            });

            $('.box_input_tools_txt').hide();

            showInput();

            $('#modal_btn_test_tools_specify').click(function (e) { 
                var modal_test_item = $('#modal_test_item').val();
                if( modal_test_item != ''){
                    $('.box_input_tools_txt').show();
                    $('.box_input_tools_select').hide();
                }else{
                    alert('กรุณาเลือกรายการทดสอบ');
                }
            });

            $('#modal_btn_test_tools_cancel').click(function (e) { 
                $('.box_input_tools_txt').hide();
                $('.box_input_tools_select').show();
            });

            $('#modal_btn_test_tools_add').click(function (e) { 

                var btn = $('#modal_btn_test_tools_input').val();
            
                if( btn == 1 ){
                    var txtr = $('#modal_test_tools_txt').val();
                }else{
                    var txtr = $('#modal_test_tools_select').val();
                }

                if( !empty(txtr) ){
                    SaveTestTools();
                }else{  
                    alert('กรุณากรอกเครื่องมือที่ใช้ ?');
                }
            });

            $('#modal_btn_test_tools_input').click(function (e) { 
                
                var val = $(this).val();

                if( val == 1 ){
                    $('#modal_btn_test_tools_input').val(2);
                    $('#modal_btn_test_tools_input').text('ระบุ');
                }else{
                    $('#modal_btn_test_tools_input').val(1);
                    $('#modal_btn_test_tools_input').text('เลือก');
                }

                showInput();
            });

            $("#modal_tis_id").on('change', function () {
                var val = $(this).val();

                $('#modal_test_item').html('<option value=""> -เลือกรายการทดสอบ- </option>');

                $('#modal_test_tools').html('<option value=""> -เลือกเครื่องมือที่ใช้- </option>');

                $('#modal_tis_name').val('');

                $('#modal_test_item').val('').trigger('change').select2();
                $('#modal_test_tools').val('').trigger('change').select2();

                $('#modal_test_tools_no').val('');
                $('#modal_capacity').val('');
                $('#modal_range').val('');
                $('#modal_true_value').val('');
                $('#modal_fault_value').val('');

                $('#modal_test_duration').val('');
                $('#modal_test_price').val('');

                if(  val != '' && $.isNumeric(val) ){

                    $.ajax({
                        url: "{!! url('/request-section-5/application-lab/get-tis_name') !!}" + "/" + val
                    }).done(function( object ) {
                        $('#modal_tis_name').val( object.tb3_TisThainame );
                    });


                    $.ajax({
                        url: "{!! url('/request-section-5/application-lab/get-test-item') !!}" + "/" + val
                    }).done(function( object ) {

                        if( object.length > 0){
                            $.each(object, function( index, data ) {
                                $('#modal_test_item').append('<option value="'+data.id+'">'+data.title+'</option>');
                                arr_tst_item[ data.id ] = data.title;

                            });
                        }

                    });

                }
                data_list_disabled();
            });

            $("#modal_tis_id").change();

            $("#modal_test_item").on('change', function () {
                var val = $(this).val();

                $('#modal_test_tools').html('<option value=""> -เลือกเครื่องมือที่ใช้- </option>');

                if( val != ''){
                    
                    $.ajax({
                        url: "{!! url('/request-section-5/application-lab/get-test-tools') !!}" + "/" + val
                    }).done(function( object ) {

                        if( object.length > 0){
                            $.each(object, function( index, data ) {
                                $('#modal_test_tools').append('<option value="'+data.id+'">'+data.title+'</option>');
                                arr_tools[ data.id ] = data.title;

                            });
                        }

                    });

                }

            });

            $('#btn_get_tr').click(function (e) { 
    
                var tis_id = $('#modal_tis_id').val();
                var tis_name =  $('#modal_tis_name').val();
                var tis_num = $('#modal_tis_id').find('option:selected').text();

                var test_item = $('#modal_test_item').val();
                var test_item_txt = $('#modal_test_item').find('option:selected').text();

                var test_tools = $('#modal_test_tools').val();
                var test_tools_txt = $('#modal_test_tools').find('option:selected').text();

               var test_tools_no = $('#modal_test_tools_no').val();
                var capacity = $('#modal_capacity').val();
                var range = $('#modal_range').val();
                var true_value = $('#modal_true_value').val();
                var fault_value = $('#modal_fault_value').val();

                var test_duration = $('#modal_test_duration').val();
                var test_price = $('#modal_test_price').val();

                var explode_tis_num = tis_num.split(':');

                if( tis_id == '' ){
                    alert('กรุณากรอก มอก.');
                }else if( test_item == '' ){
                    alert('กรุณากรอก รายการทดสอบ');
                }else if( test_tools == '' ){
                    alert('กรุณากรอก เครื่องมือที่ใช้');
              //  }else if( test_tools_no == '' ){
              //      alert('กรุณากรอก รหัส/หมายเลข');
                }else if( capacity == '' ){
                    alert('กรุณากรอก ขีดความสามารถ');
                }else if( capacity == '' ){
                    alert('กรุณากรอก ช่วงการใช้งาน');
                }else if( true_value == '' ){
                    alert('กรุณากรอก ความละเอียดที่อ่านได้');
                }else if( fault_value == '' ){
                    alert('กรุณากรอก ความคลาดเคลื่อนที่ยอมรับ');
                }else if( test_duration == '' ){
                    alert('กรุณากรอก ระยะการทดสอบ');
                }else if( test_price == '' ){
                    alert('กรุณากรอก ค่าใช้จ่ายในการทดสอบ');
                }else{

                    arr_tools[ test_tools ] = test_tools_txt;
                    arr_tst_item[ test_item ] = test_item_txt;

                    var id_row_tr = Math.floor(Math.random() * 26) + Date.now();

                    var LastRow = $('#myTableScope tbody').length;

                    var inputSTD = '<input type="hidden" class="myTableScope_tis_id" name="tis_id" value="'+(tis_id)+'"><input type="hidden" class="Mscope_tis_tisno" name="tis_tisno" value="'+($.trim(explode_tis_num[0]))+'">';
                    var idRows = '<input type="hidden" class="Mscope_id" name="scope_id" value="">';

                    var inputHidden = inputSTD;
                        inputHidden += idRows;
                        inputHidden += '<input type="hidden" class="Mscope_test_item_id" name="test_item_id" value="'+(test_item)+'">';
                        inputHidden += '<input type="hidden" class="Mscope_test_tools_id" name="test_tools_id" value="'+(test_tools)+'">';
                        inputHidden += '<input type="hidden" class="Mscope_test_tools_no" name="test_tools_no" value="'+(test_tools_no)+'">';
                        inputHidden += '<input type="hidden" class="Mscope_capacity" name="capacity" value="'+(capacity)+'">';
                        inputHidden += '<input type="hidden" class="Mscope_range" name="range" value="'+(range)+'">';
                        inputHidden += '<input type="hidden" class="Mscope_true_value" name="true_value" value="'+(true_value)+'">';
                        inputHidden += '<input type="hidden" class="Mscope_fault_value" name="fault_value" value="'+(fault_value)+'">';
                        inputHidden += '<input type="hidden" class="Mscope_test_duration" name="test_duration" value="'+(test_duration)+'">';
                        inputHidden += '<input type="hidden" class="Mscope_test_price" name="test_price" value="'+(test_price)+'">';

                    var _tr = '';
                        _tr += '<tr class="row_tr_'+(id_row_tr)+'">';
                        _tr += '<td class="text-center text-top"><span class="Modalno_'+(test_item)+'"></span></td>';
                        _tr += '<td class="text-center text-top">'+(test_item_txt)+'</td>';
                        _tr += '<td class="text-center text-top">'+(test_tools_txt)+'</td>';
                        _tr += '<td class="text-center text-top">'+(test_tools_no)+'</td>';
                        _tr += '<td class="text-center text-top">'+(capacity)+'</td>';
                        _tr += '<td class="text-center text-top">'+(range)+'</td>';
                        _tr += '<td class="text-center text-top">'+(true_value)+'</td>';
                        _tr += '<td class="text-center text-top">'+(fault_value)+'</td>';
                        _tr += '<td class="text-center text-top">'+(test_duration)+'</td>';
                        _tr += '<td class="text-center text-top">'+(test_price)+'</td>';
                        _tr += '<td class="text-center text-top"><button type="button" class="btn btn-danger btn-sm btn_remove_modalscope" data-tr="'+(id_row_tr)+'">ลบ</button>'+inputHidden+'</td>';
                        _tr += '</tr>';

                    var table = $('#myTableScope tbody');
                    var addRowsCheck = true;
                    if( table.find('.myTableScope_tis_id').length > 0 ){
                        $('.myTableScope_tis_id').each(function(index, element){
                            if( $(element).val() != tis_id ){
                                addRowsCheck = false;
                            }
                        });
                    }

                    if( addRowsCheck == true ){

                        if( table.find('.Mscope_test_item_id').length > 0 ){

                            var values_test_item = $('#myTableScope').find(".Mscope_test_item_id").map(function(){return $(this).val(); }).get();
                                values_test_item = jQuery.unique( values_test_item );

                            if(values_test_item.indexOf( test_item.toString() ) != -1){

                                var last_input = $('#myTableScope').find('.Mscope_test_item_id[value="'+ test_item +'"]').last().parent().parent();
                                var _tr = '';
                                    _tr += '<tr class="row_tr_'+(id_row_tr)+'">';
                                    _tr += '<td class="text-center text-top"><span class="Modalno_'+(test_item)+'"></span></td>';
                                    _tr += '<td class="text-center text-top">'+(test_item_txt)+'</td>';
                                    _tr += '<td class="text-center text-top">'+(test_tools_txt)+'</td>';
                                    _tr += '<td class="text-center text-top">'+(test_tools_no)+'</td>';
                                    _tr += '<td class="text-center text-top">'+(capacity)+'</td>';
                                    _tr += '<td class="text-center text-top">'+(range)+'</td>';
                                    _tr += '<td class="text-center text-top">'+(true_value)+'</td>';
                                    _tr += '<td class="text-center text-top">'+(fault_value)+'</td>';
                                    _tr += '<td class="text-center text-top">'+(test_duration)+'</td>';
                                    _tr += '<td class="text-center text-top">'+(test_price)+'</td>';
                                    _tr += '<td class="text-center text-top"><button type="button" class="btn btn-danger btn-sm btn_remove_modalscope"  data-tr="'+(id_row_tr)+'">ลบ</button>'+inputHidden+'</td>';
                                    _tr += '</tr>';

                                last_input.closest('tr').after(_tr);

                            }else{
                                $('#myTableScope tbody').append(_tr);
                            }

                        }else{
                            $('#myTableScope tbody').append(_tr);
                        }

                        
                        resetOrderNo();

                        CloneTableScope();

                        $('#modal_test_item').val('').select2();
                        $('#modal_test_tools').val('').select2();

                        $('#modal_test_tools_no').val('');
                        $('#modal_capacity').val('');
                        $('#modal_range').val('');
                        $('#modal_true_value').val('');
                        $('#modal_fault_value').val('');

                        $('#modal_test_duration').val('');
                        $('#modal_test_price').val('');

                    }else{
                        alert('กรุณาเลือกมอก. ให้ตรงกัน');
                    }

                    // data_test_item_list_disabled();
                }

            });

            $("body").on('click', '.btn_remove_modalscope', function () {
                if(confirm('ยืนยันการลบข้อมูล แถวนี้')){

                    $('#myTableScope tbody').find('.row_tr_'+ $(this).data('tr') ).remove();

                    resetOrderNo();

                    CloneTableScope();
                }
            });
            

            $('#btn_gen_box').click(function (e) { 

                if( $('#myTableScope tbody tr').length > 0 ){

                    var length =  $('body').find('.table_multiples').length;

                    var tis_num = $('#modal_tis_id').find('option:selected').text();
                    var tis_id = $('#modal_tis_id').val();
                    // var tis_name =  $('#modal_tis_name').val();

                    var html = "";
                        html += '<div class="row white-box repeater-table-scope">';
                        html += '<div class="col-md-12">';
                        html += '<div class="row">'
                        html += '<h5 class="pull-left">รายการทดสอบ ตามมาตรฐานเลขที่ มอก. '+(tis_num)+' </h5>';
                        html += '<div class="pull-right">';
                        html += '<button class="btn btn-warning btn_section_edit" data-tis_id="'+(tis_id)+'" data-table="table-group-'+( tis_id )+'" type="button">แก้ไข</button>';
                        html += ' ';
                        html += '<button class="btn btn-danger btn_section_remove" type="button">ลบชุดรายการทดสอบ</button>';
                        html += '</div>'; 
                        html += '</div>'; 
                        html += '<input type="hidden" name="section_box_tis[]" value="'+ tis_id +'" class="form-control section_box_tis">';
                        html += '<hr>';
                        html += '<div class="clearfix"></div>';
                        html += '<div class="table-responsive">';
                        html += '<table class="table table-bordered table_multiples inner-repeater" id="table-group-'+( tis_id )+'">';
                        html += '<thead>';
                        html += '<tr>';
                        html += '<th width="2%" class="text-center">#</th>';
                        html += '<th width="10%" class="text-center">รายการทดสอบ</th>';
                        html += '<th width="15%" class="text-center">เครื่องมือที่ใช้</th>';
                        html += '<th width="10%" class="text-center">รหัส/หมายเลข</th>';
                        html += '<th width="15%" class="text-center">ขีดความสามารถ</th>';
                        html += '<th width="10%" class="text-center">ช่วงการใช้งาน</th>';
                        html += '<th width="15%" class="text-center">ความละเอียดที่อ่านได้</th>';
                        html += '<th width="10%" class="text-center">ความคลาดเคลื่อนที่ยอมรับ</th>';
                        html += '<th width="10%" class="text-center">ระยะการทดสอบ(วัน)</th>';
                        html += '<th width="10%" class="text-center">ค่าใช้จ่ายในการทดสอบ/ชุดละ</th>';
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody data-repeater-list="repeater-group-'+( tis_id )+'">';

                        var i = 0;
                        var _tr = '';
                        $('#myTableScope').find('.myTableScope_tis_id').each(function (index, rowId) {
                            i++;
                            var row = $(rowId).parent().parent();

                            var NumBerRow = row.children("td:nth-child(0)").text();

                            var tis_tisno = row.find('.Mscope_tis_tisno').val();
                            var test_item_id = row.find('.Mscope_test_item_id').val();
                            var test_tools_id = row.find('.Mscope_test_tools_id').val();
                            var test_tools_no = row.find('.Mscope_test_tools_no').val();
                            var capacity = row.find('.Mscope_capacity').val();
                            var range = row.find('.Mscope_range').val();
                            var true_value = row.find('.Mscope_true_value').val();
                            var fault_value = row.find('.Mscope_fault_value').val();

                            var test_duration = row.find('.Mscope_test_duration').val();
                            var test_price = row.find('.Mscope_test_price').val();

                            var test_item_txt = arr_tst_item[test_item_id];
                            var test_tools_txt = arr_tools[test_tools_id];
                            var scope_id = row.find('.Mscope_id').val();

                            var GenInput =  '<input type="hidden" class="scope_tis_id" name="tis_id" value="'+(rowId.value)+'"><input type="hidden" class="scope_tis_tisno" name="tis_tisno" value="'+(tis_tisno)+'">';
                                GenInput += '<input type="hidden" class="scope_id" name="scope_id" value="'+(scope_id)+'">';
                                GenInput += '<input type="hidden" class="scope_test_item_id" name="test_item_id" value="'+(test_item_id)+'">';
                                GenInput += '<input type="hidden" class="scope_test_tools_id" name="test_tools_id" value="'+(test_tools_id)+'">';
                                GenInput += '<input type="hidden" class="scope_test_tools_no" name="test_tools_no" value="'+(test_tools_no)+'">';
                                GenInput += '<input type="hidden" class="scope_capacity" name="capacity" value="'+(capacity)+'">';
                                GenInput += '<input type="hidden" class="scope_range" name="range" value="'+(range)+'">'; 
                                GenInput += '<input type="hidden" class="scope_true_value" name="true_value" value="'+(true_value)+'">';
                                GenInput += '<input type="hidden" class="scope_fault_value" name="fault_value" value="'+(fault_value)+'">';
                                GenInput += '<input type="hidden" class="scope_test_duration" name="test_duration" value="'+(test_duration)+'">';
                                GenInput += '<input type="hidden" class="scope_test_price" name="test_price" value="'+(test_price)+'">';

                            _tr += '<tr data-repeater-item>';
                            _tr += '<td class="text-center text-top"><span class="Tscope_number-'+(test_item_id)+'"></span>'+(NumBerRow)+'</td>';
                            _tr += '<td class="text-center text-top">'+(test_item_txt)+'</td>';
                            _tr += '<td class="text-center text-top">'+(test_tools_txt)+'</td>';
                            _tr += '<td class="text-center text-top">'+(test_tools_no)+'</td>';
                            _tr += '<td class="text-center text-top">'+(capacity)+'</td>';
                            _tr += '<td class="text-center text-top">'+(range)+'</td>';
                            _tr += '<td class="text-center text-top">'+(true_value)+'</td>';
                            _tr += '<td class="text-center text-top">'+(fault_value)+'</td>';
                            _tr += '<td class="text-center text-top">'+(test_duration)+'</td>';
                            _tr += '<td class="text-center text-top">'+(test_price)+''+(GenInput)+'</td>';
                            _tr += '</tr>';

                        });
                        html += _tr;
                        html += '</tbody>';
                        html += '</table>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>'; 
     
                    var values = $('.section_box_tis').map(function(){return $(this).val(); }).get();

                    if ( length > 0 ) {;

                        if( $('#table-group-'+( tis_id )+' tbody').length == 0){
                            //.ให้เพิ่มแค่ มอก เดียว
                            $('#box_scope_request').html('');
                            $('#box_scope_request').append(html);
                        }else{
                            //.ให้เพิ่มแค่ มอก เดียว
                            $('#box_scope_request').html('');
                            $('#box_scope_request').append(html);
                            // $('#table-group-'+( tis_id )+' tbody').html('');
                            // $('#table-group-'+( tis_id )+' tbody').append(_tr);
                            NumberTableMScope( tis_id );
                        }

                        $('#myTableScope tbody').html('');
                        $('#modal_tis_id').val('').trigger('change').select2();
              
                    }else{ 

                        //.ให้เพิ่มแค่ มอก เดียว
                        $('#box_scope_request').html('');
                        $('#box_scope_request').append(html);

                        $('#myTableScope tbody').html('');
                        $('#modal_tis_id').val('').trigger('change').select2();
                    }
                    $('#ScopeModal').modal('hide');
                    merge_table_box_scope();
                    $('.repeater-table-scope').repeater();     
                }else{
                    alert('กรุณาเพิ่มข้อมูลในตาราง รายการทดสอบ');
                }
   
            });
            
            data_list_disabled();
        });

        function data_list_disabled(){
            $('#modal_tis_id').children('option').prop('disabled',false);
            $('.section_box_tis').each(function(index , item){
                var data_list = $(item).val();
                $('#modal_tis_id').children('option[value="'+data_list+'"]:not(:selected):not([value=""])').prop('disabled',true);
            });
        }

        function data_test_item_list_disabled(){
            $('#modal_test_item').children('option').prop('disabled',false);
            $('.Mscope_test_item_id').each(function(index , item){
                var data_list = $(item).val();
                $('#modal_test_item').children('option[value="'+data_list+'"]:not(:selected):not([value=""])').prop('disabled',true);
            });
        }

        function resetOrderNo(){

            var values_test_item = $('#myTableScope').find(".Mscope_test_item_id").map(function(){return $(this).val(); }).get();
                values_test_item = jQuery.unique( values_test_item );

            var i = 0;
            $.each(values_test_item , function( index, item ) {
                i++;
                $('#myTableScope').find('span.Modalno_'+ item +'').each(function(index, el) {
                    $(el).text( i );
                });
            });

        }

        function NumberTableMScope(tis_id){
            var values_test_item = $( '#table-group-'+( tis_id ) ).find(".scope_test_item_id").map(function(){return $(this).val(); }).get();
                values_test_item = jQuery.unique( values_test_item );

            var i = 0;
            $.each(values_test_item , function( index, item ) {
                i++;
                $( '#table-group-'+( tis_id ) ).find('span.Tscope_number-'+ item +'').each(function(index, el) {
                    $(el).text( i );
                });
            });
        }

        function SaveTestTools(){

            var test_item = $('#modal_test_item').val();
            var test_tool = $('#modal_test_tools_txt').val();
            var test_tool_id = $('#modal_test_tools_select').val();

            var btn = $('#modal_btn_test_tools_input').val();

            $.ajax({
                method: "POST",
                url: "{{ url('/request-section-5/application-lab/save_test_tools') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "test_item": test_item,
                    "test_tool": test_tool,
                    "test_tool_id": test_tool_id,
                    "type": btn
                },
                success : function (data){
                    if (data.mgs == "success") {

                        $.toast({
                            heading: 'Compleate!',
                            text: 'บันทึกสำเร็จ',
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'success',
                            hideAfter: 1000,
                            stack: 6,
                        });

                        LoadItemTools( data );

                        $('#modal_test_tools_txt').val('');

                        $('.box_input_tools_txt').hide();
                        $('.box_input_tools_select').show();
                
                    }else{

                        $.toast({
                            heading: 'Compleate!',
                            text: 'บันทึกไม่สำเร็จ',
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'error',
                            hideAfter: 1000,
                            stack: 6,

                        });

                    }
                }
            });
        }

        function LoadItemTools( data ){
            
            $('#modal_test_tools').html('<option value=""> -เลือกเครื่องมือที่ใช้- </option>');
            var val  = $('#modal_test_item').val();
            if(  val != '' && $.isNumeric(val) ){

                $.LoadingOverlay("show", {
                    image       : "",
                    text        : "Loading..."
                });

                $.ajax({
                    url: "{!! url('/request-section-5/application-lab/get-test-tools') !!}" + "/" + val
                }).done(function( object ) {

                    if( object.length > 0){
                        $.each(object, function( index, data ) {
                            $('#modal_test_tools').append('<option value="'+data.id+'">'+data.title+'</option>');
                        });

                        $('#modal_test_tools').val( data.tools_id).trigger('change.select2');
                        
                        $.LoadingOverlay("hide", true);  
                    }else{
                        $.LoadingOverlay("hide", true);    
                    }

                });

            } 
        }

        function showInput(){

            var btn =   $('#modal_btn_test_tools_input').val();

            $('#modal_test_tools_select').html('<option value=""> -เลือกเครื่องมือที่ใช้- </option>');

            $('#modal_test_tools_txt').val('');
            $('#modal_test_tools_select').val('').trigger('change.select2');

            if( btn ==  2 ){
                $('#modal_test_tools_txt').hide();
                $('.modal_test_tools_select').show();
                LoadToolsBasic();
            }else{
                $('#modal_test_tools_txt').show();
                $('.modal_test_tools_select').hide();
            }

        }

        function LoadToolsBasic(){
            
            var val  = $('#modal_test_item').val();

            $.LoadingOverlay("show", {
                image       : "",
                text        : "Loading..."
            });

            $.ajax({
                url: "{!! url('/request-section-5/application-lab/get-basic-tools') !!}" + "/" + val
            }).done(function( object ) {

                if( object.length > 0){
                    $.each(object, function( index, data ) {
                        $('#modal_test_tools_select').append('<option value="'+data.id+'">'+data.title+'</option>');
                    });
                    $.LoadingOverlay("hide", true);  
                }else{
                    $.LoadingOverlay("hide", true);    
                }

            });

        }

        function merge_table_modal_scope(){
            const table = document.querySelector('#myTableScopeCopy'); //อยู่ใน form.php

            //Col 1
            let headerCell = null;
            for (let row of table.rows) {
                const Cell1 = row.cells[0];
                const Cell2 = row.cells[1];

                if (headerCell === null || Cell1.innerText !== headerCell.innerText) {
                    headerCell = Cell1;
                    header2Cell = Cell2;

                } else {
                    headerCell.rowSpan++;
                    header2Cell.rowSpan++;
                    Cell1.remove();//ลบคอลัมภ์แรก
                    Cell2.remove();//ลบคอลัมภ์สอง
                }
            }
        }

        function CloneTableScope(){

            resetOrderNo();

            $('#myTableScopeCopy tbody').html('');
            
            var Maintbody = $('#myTableScope tbody').clone();

            $('#myTableScopeCopy tbody').append( Maintbody.html() );

            merge_table_modal_scope();

        }
    </script>
@endpush
