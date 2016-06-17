<?php namespace App\Models\Accounts;

use App\Models\Comments\Comment;
use App\Models\Messages\MessageUser;
use App\Models\Wiki\WikiRevision;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Illuminate\Support\Str;

class ApiKey extends Model {

	protected $table = 'api_keys';
	protected $fillable = [ 'user_id', 'key', 'app', 'ip' ];
    public $visible = [ 'user_id', 'key', 'app', 'created_at' ];
    protected $dates = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public static function GenerateKey($user_id)
    {
        $now = Carbon::now();
        $key = Str::random(60);
        return $now->getTimestamp() . '-' . $user_id . '-' . $key;
    }
}
