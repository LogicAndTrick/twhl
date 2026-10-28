<?php

namespace App\Models\Accounts;

use App\Models\ModelWithExtraTypeInfo;

class UserNameHistory extends ModelWithExtraTypeInfo
{
    public $table = 'user_name_history';
    public $fillable = ['user_id', 'name'];
}
