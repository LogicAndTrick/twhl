<?php namespace App\Models\Vault;

use App\Models\ModelWithExtraTypeInfo;

class VaultScreenshot extends ModelWithExtraTypeInfo {

	//
    public $table = 'vault_screenshots';
    public $fillable = ['item_id', 'is_primary', 'image_thumb', 'image_small', 'image_medium', 'image_large', 'image_full', 'image_size', 'order_index'];
    public $visible = ['id', 'item_id', 'is_primary', 'image_thumb', 'image_small', 'image_medium', 'image_large', 'image_full', 'image_size', 'order_index', 'created_at', 'vault_item'];
    protected $casts = [
        'image_size' => 'integer',
        'is_primary' => 'boolean',
        'order_index' => 'integer',
    ];

    public function vault_item()
    {
        return $this->belongsTo('App\Models\Vault\VaultItem', 'item_id');
    }

    public function delete()
    {
        $result = parent::delete();
        if ($result) {
            $shots = ['image_thumb', 'image_small', 'image_medium', 'image_large', 'image_full'];
            foreach ($shots as $s) {
                $location = public_path('uploads/vault/'.$this->$s);
                if (file_exists($location) && is_file($location)) unlink($location);
            }
        }
        return $result;
    }

}
