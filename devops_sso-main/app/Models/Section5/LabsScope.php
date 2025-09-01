<?php

namespace App\Models\Section5;

use App\Models\Basic\Tis;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tis\Standard;
use App\Models\Bsection5\TestItem;
use App\Models\Section5\ApplicationLabBoardApprove;

class LabsScope extends Model
{
    protected $table = 'section5_labs_scopes';

    protected $primaryKey = 'id';

        /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [ 
        'lab_id',
        'lab_code',
        'tis_id',
        'tis_tisno',
        'test_item_id',
        'state',
        'start_date',
        'end_date',
        'remarks',
        'type',
        'close_state_date',
        'close_remarks',
        'close_date',
        'close_by',
        'ref_lab_application_no'
    ];
    
    public function tis_standards(){
        return $this->belongsTo(Tis::class, 'tis_id');
    } 
     
    public function test_item(){
        return $this->belongsTo(TestItem::class, 'test_item_id');
    } 
    
    public function getStandardTitleAttribute() {
        return @$this->tis_standards->tb3_TisThainame;
    }
    
    public function getTestItemToolsTitleAttribute() {
        return @$this->test_item->title;
    }

    public function application_board_approve(){
        return $this->belongsTo(ApplicationLabBoardApprove::class, 'ref_lab_application_no', 'application_no'  );
    }

    public function getStandardTisNoNameAttribute() {
        return (@$this->tis_standards->tb3_Tisno).' : '.(@$this->tis_standards->tb3_TisThainame);
    }
    
}
