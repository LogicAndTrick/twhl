<?php namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

class VaultType extends Model {

    public $table = 'vault_types';
    public $timestamps = false;
    public $fillable = ['name', 'orderindex'];
    public $visible = ['id', 'name', 'orderindex'];

}
