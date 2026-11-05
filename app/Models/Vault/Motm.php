<?php

namespace App\Models\Vault;

use App\Models\ModelWithExtraTypeInfo;
use Carbon\Carbon;

class Motm extends ModelWithExtraTypeInfo
{
    protected $table = 'motms';
    protected $fillable = ['item_id', 'year', 'month'];
    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
    ];
    protected $formats = [
        'month' => 'uint32',
        'year' => 'uint32',
    ];
    protected $nullable = [
        'item_id',
    ];

    public function vault_item()
    {
        return $this->belongsTo('App\Models\Vault\VaultItem', 'item_id');
    }

    public function getDateString()
    {
        return Carbon::create($this->year, $this->month, 10)->format('F Y');
    }

    public function getShortDateString()
    {
        return Carbon::create($this->year, $this->month, 10)->format('M Y');
    }
}
