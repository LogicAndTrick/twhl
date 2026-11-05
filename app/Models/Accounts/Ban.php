<?php namespace App\Models\Accounts;

use App\Models\ModelWithExtraTypeInfo;
use Carbon\Carbon;

class Ban extends ModelWithExtraTypeInfo {

    protected $table = 'bans';
    protected $fillable = [ 'user_id', 'ip', 'ends_at', 'reason' ];
    protected $casts = [
        'ends_at' => 'datetime',
    ];
    protected $nullable = [
        'ends_at',
        'user_id',
    ];

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
