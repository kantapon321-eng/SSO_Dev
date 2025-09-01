<?php
// app/Models/SSO/PreRegisterSnapshot.php
namespace App\Models\SSO;

use Illuminate\Database\Eloquent\Model;

class PreRegisterSnapshot extends Model
{
    protected $table = 'pre_register_snapshots';
    protected $fillable = [
        'token','tax_number','source','progid','uid','bid',
        'i_customer','i_owner','raw_xml',
    ];
    protected $casts = [
        'i_customer' => 'array',
        'i_owner'    => 'array',
    ];
}
