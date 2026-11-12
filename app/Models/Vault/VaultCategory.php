<?php namespace App\Models\Vault;

use App\Models\ModelWithExtraTypeInfo;

class VaultCategory extends ModelWithExtraTypeInfo {

    public $table = 'vault_categories';
    public $timestamps = false;
    public $fillable = ['name', 'description', 'orderindex'];
    public $visible = ['id', 'name', 'description', 'orderindex', 'adjective'];
    protected $casts = [
        'orderindex' => 'integer',
    ];

}
