<?php

namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Model;

class UserNameHistory extends Model
{
    public $table = 'user_name_history';
    public $fillable = ['user_id', 'name'];
}
