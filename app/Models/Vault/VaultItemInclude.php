<?php namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

class VaultItemInclude extends Model {

    public $table = 'vault_item_includes';
    public $timestamps = false;
    public $fillable = ['item_id', 'include_id'];

}
