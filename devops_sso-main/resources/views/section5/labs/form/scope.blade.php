<fieldset class="white-box">
    <legend>ข้อมูลขอบข่าย</legend>

    <div class="row">
        <div class="col-md-12 h4">
            <div class="col-md-6">
                {!! Form::label('filter_search', 'คำค้นหา'.': ', ['class' => 'col-md-2 control-label text-right']) !!}
                <div class="form-group col-md-10">
                    {!! Form::text('filter_search', null, ['class' => 'form-control', 'placeholder'=>'ค้นหาจากมอก.', 'id' => 'filter_search']); !!}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 h4">
            <div class="table-responsive">
                <table class="table table-bordered scope-repeater" id="table-scope">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">ลำดับ</th>
                            <th class="text-center" width="45%">มอก.</th>
                            <th class="text-center" width="50%">รายการทดสอบ</th>
                        </tr>
                    </thead>
                    <tbody>

                        @php
                            $controller = new App\Http\Controllers\Funtion\Section5Controller;

                            $lab_id = $labs->id;

                            $scope_standard_name = $labs->scope_standard()->get()->pluck('StandardTisNoName', 'tis_id')->toArray();

                            $scope_standard  = $labs->scope_standard()->get()->groupBy('tis_id');
                            $i = 0;
                        @endphp

                        @foreach ( $scope_standard  as $tis_key => $scope  )

                            @php

                                $test_item_id = $scope->pluck('test_item_id', 'test_item_id')->toArray();

                                $testitem = App\Models\Bsection5\TestItem::Where('tis_id', $tis_key)
                                                                        ->where('type',1)
                                                                        ->where( function($query) use($lab_id, $tis_key){
                                                                            $ids = DB::table((new App\Models\Section5\LabsScope)->getTable().' AS scope')
                                                                                        ->leftJoin((new App\Models\Bsection5\TestItem)->getTable().' AS test', 'test.id', '=', 'scope.test_item_id')
                                                                                        ->where('scope.lab_id', $lab_id )
                                                                                        ->where('test.tis_id', $tis_key )
                                                                                        ->select('test.main_topic_id');
                                                                            $query->whereIn('id', $ids  );
                                                                        })
                                                                        ->orderby('no')
                                                                        ->get();
                            @endphp
                            <tr>
                                <td class="text-top text-center ">{!! ++$i !!}</td>
                                <td class="text-top ">{!! array_key_exists( $tis_key, $scope_standard_name )?$scope_standard_name[ $tis_key ]:'-'  !!}</td>
                                <td>
                                    @if( count( $testitem ) >= 1 )
                                        {!! $controller->GetDataTestItem( $testitem, $test_item_id , $scope ) !!}
                                    @else

                                        <ul><li class="text-muted"><em>ไม่พบข้อมูลรายการทดสอบ </em></li></ul>
                                    @endif
                      
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</fieldset>