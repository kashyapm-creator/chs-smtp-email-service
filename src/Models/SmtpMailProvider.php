<?php

namespace Chs\EmailService\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmtpMailProvider extends Model
{
    use HasFactory;
    public function getConnectionName()
    {
        return config('chs-email.database_connection');
    }
}
