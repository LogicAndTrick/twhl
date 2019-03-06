<?php namespace App\Models\Accounts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ban extends Model {

    protected $table = 'bans';
    protected $fillable = [ 'user_id', 'ip', 'ends_at', 'reason' ];

    public function getDates()
    {
        return ['ends_at', 'created_at', 'updated_at', 'deleted_at'];
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function isActive()
    {
        $now = Carbon::now();
        return $this->created_at <= $now && (!$this->ends_at || $this->ends_at >= $now);
    }
}
