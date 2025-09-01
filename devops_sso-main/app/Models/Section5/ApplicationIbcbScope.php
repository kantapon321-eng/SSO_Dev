<?php

namespace App\Models\Section5;

use Illuminate\Database\Eloquent\Model;

use App\Models\Basic\BranchGroup;
use App\Models\Section5\ApplicationIbcbScopeDetail;
use App\Models\Section5\ApplicationIbcbScopeTis;
class ApplicationIbcbScope extends Model
{

    protected $table = 'section5_application_ibcb_scopes';

    protected $primaryKey = 'id';

    protected $fillable = [
        'application_id',
        'application_no',
        'branch_group_id',
        'isic_no',
        'created_by',
        'updated_by',
        'ibcb_id', 
        'ibcb_code'
    ];

    public function bs_branch_group(){
        return $this->belongsTo(BranchGroup::class, 'branch_group_id');
    }

    public function scopes_details(){
        return $this->hasMany(ApplicationIbcbScopeDetail::class, 'ibcb_scope_id');
    }

    public function scopes_tis(){
        return $this->hasMany(ApplicationIbcbScopeTis::class, 'ibcb_scope_id');
    }

    public function getBranchGroupTitleAttribute(){
        return @$this->bs_branch_group->title;
    }

    public function getScopeBranchsAttribute(){

        $app_scope = $this->scopes_details()->select('branch_id')->groupBy('branch_id')->get();
        $list = [];
        foreach( $app_scope AS $item ){
            $bs_branch = $item->bs_branch;

            if( !is_null($bs_branch) ){
                $list[] = $bs_branch->title;
            }

        }

        $txt = implode( ' ,',  $list );

        return $txt;
    }
}
