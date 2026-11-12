<?php namespace App\Models\Vault;

use App\Models\ModelWithExtraTypeInfo;

class VaultInclude extends ModelWithExtraTypeInfo {

    public $table = 'vault_includes';
    public $timestamps = false;
    public $fillable = ['type_id', 'name', 'description', 'orderindex'];
    public $visible = ['id', 'type_id', 'name', 'description', 'orderindex', 'vault_type'];
    protected $casts = [
        'orderindex' => 'integer'
    ];

    public function vault_type()
    {
        return $this->belongsTo('App\Models\Vault\VaultType', 'type_id');
    }

}
