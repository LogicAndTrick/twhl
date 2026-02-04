<?php namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

class VaultCategory extends Model {

    public $table = 'vault_categories';
    public $timestamps = false;
    public $fillable = ['name', 'description', 'orderindex'];
    public $visible = ['id', 'name', 'description', 'orderindex', 'adjective'];

}
