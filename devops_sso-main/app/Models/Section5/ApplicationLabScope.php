<?php

namespace App\Models\Section5;

use App\Models\Basic\Tis;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tis\Standard;

use App\Models\Bsection5\TestTool;
use App\Models\Bsection5\TestItemTools;
use App\Models\Bsection5\TestItem;

class ApplicationLabScope extends Model
{
    protected $table = 'section5_application_labs_scope';

    protected $primaryKey = 'id';

        /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [ 
        'application_lab_id',
        'application_no',
        'tis_tisno',
        'test_item_id',
        'test_tools_id',
        'test_tools_no',
        'capacity',
        'range',
        'true_value',
        'fault_value',
        'tis_id',
        'audit_result',
        'remark',
        'test_duration',
        'test_price',
        'lab_id', 
        'lab_code'
        
    ];

    public function standards(){
        return $this->belongsTo(Tis::class, 'tis_id');
    }  

    public function test_items(){
        return $this->belongsTo(TestItem::class, 'test_item_id');
    }

    public function getTestItemFullNameAttribute() {

        $test_items = $this->test_items;
        $mains    = !empty( $test_items->main_test_item )?$test_items->main_test_item:null;

        return ( !empty( $test_items->no )?$test_items->no.' ' :'' ).$test_items->title.' <em>(ภายใต้หัวข้อทดสอบ '.(  ( !empty( $mains->no )?$mains->no.' ' :null ).$mains->title ).')</em>';
    }

    
    public function getTestItemNameAttribute() {
        return @$this->test_items->title;
    }

    public function test_tools(){
        return $this->belongsTo(TestTool::class, 'test_tools_id');
    }

    public function getToolsNameAttribute() {
        return @$this->test_tools->title;
    }
}
