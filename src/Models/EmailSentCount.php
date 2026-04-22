<?php

namespace Chs\EmailService\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSentCount extends Model
{
    use HasFactory;

    public function getConnectionName()
    {
        return config('chs-email.database_connection');
    }
    protected $table = 'email_sent_count';

    protected $fillable = [
        'email_count',
        'limit',
    ];
}
