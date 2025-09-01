<?php
// app/Models/SSO/LoginAudit.php
namespace App\Models\SSO;

use Illuminate\Database\Eloquent\Model;

class LoginAudit extends Model
{
    protected $table = 'login_audits';
    protected $fillable = [
        'source','tax_number','uid','bid','progid',
        'session_id','client_ip','payload_xml',
    ];
}
