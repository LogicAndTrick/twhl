<?php namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

class VaultScreenshot extends Model {

	//
    public $table = 'vault_screenshots';
    public $fillable = ['item_id', 'is_primary', 'image_thumb', 'image_small', 'image_medium', 'image_large', 'image_full', 'image_size', 'order_index'];
    public $visible = ['id', 'item_id', 'is_primary', 'image_thumb', 'image_small', 'image_medium', 'image_large', 'image_full', 'image_size', 'order_index'];

    public function delete()
    {
        $result = parent::delete();
        if ($result) {
            $shots = ['image_thumb', 'image_small', 'image_medium', 'image_large', 'image_full'];
            foreach ($shots as $s) {
                $location = public_path('uploads/vault/'.$this->$s);
                if (file_exists($location)) unlink($location);
            }
        }
        return $result;
    }

}
