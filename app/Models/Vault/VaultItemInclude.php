<?php namespace App\Models\Vault;

use App\Models\ModelWithExtraTypeInfo;

class VaultItemInclude extends ModelWithExtraTypeInfo {

    public $table = 'vault_item_includes';
    public $timestamps = false;
    public $fillable = ['item_id', 'include_id'];
    protected $casts = [
        'orderindex' => 'integer'
    ];
}
