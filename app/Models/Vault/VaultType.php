<?php namespace App\Models\Vault;

use App\Models\ModelWithExtraTypeInfo;

class VaultType extends ModelWithExtraTypeInfo {

    public $table = 'vault_types';
    public $timestamps = false;
    public $fillable = ['name', 'orderindex'];
    public $visible = ['id', 'name', 'orderindex'];
    protected $casts = [
        'orderindex' => 'integer',
    ];
}
