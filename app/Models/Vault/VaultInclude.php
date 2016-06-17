<?php namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

class VaultInclude extends Model {

    public $table = 'vault_includes';
    public $timestamps = false;
    public $fillable = ['type_id', 'name', 'description', 'orderindex'];
    public $visible = ['id', 'type_id', 'name', 'description', 'orderindex', 'vault_type'];

    public function vault_type()
    {
        return $this->belongsTo('App\Models\Vault\VaultType', 'type_id');
    }

}
