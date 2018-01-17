<?php

namespace App\Models\Vault;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Motm extends Model
{
    protected $table = 'motms';
    protected $fillable = ['item_id', 'year', 'month'];

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
