<?php namespace App\Models\Vault;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class VaultItem extends Model {

    use SoftDeletes;

    public $table = 'vault_items';
    public $fillable = [
        'user_id', 'engine_id', 'game_id', 'category_id', 'type_id', 'license_id',
        'name', 'content_text', 'content_html',
        'is_hosted_externally', 'file_location', 'file_size',
        'flag_notify', 'flag_ratings',
        'stat_views', 'stat_downloads', 'stat_ratings', 'stat_comments', 'stat_average_rating'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }

    public function license()
    {
        return $this->belongsTo('App\Models\License');
    }

    public function vault_category()
    {
        return $this->belongsTo('App\Models\Vault\VaultCategory', 'category_id');
    }

    public function vault_type()
    {
        return $this->belongsTo('App\Models\Vault\VaultType', 'type_id');
    }

    public function vault_item_includes()
    {
        return $this->hasMany('App\Models\Vault\VaultItemInclude', 'item_id');
    }

    public function vault_includes()
    {
        return $this->belongsToMany('App\Models\Vault\VaultInclude', 'vault_item_includes', 'item_id', 'include_id');
    }

    public function vault_screenshots()
    {
        return $this->hasMany('App\Models\Vault\VaultScreenshot', 'item_id');
    }

    public function hasPrimaryScreenshot()
    {
        return $this->getPrimaryScreenshot() != null;
    }

    public function getPrimaryScreenshot()
    {
        return array_first($this->vault_screenshots, function($i, $x) {
            return $x->is_primary > 0;
        });
    }

    public function getDownloadUrl()
    {
        if ($this->is_hosted_externally) return $this->file_location;
        else return asset('uploads/vault/items/'.$this->file_location);
    }

    public function getServerFilePath()
    {
        if ($this->is_hosted_externally) return '';
        else return public_path('uploads/vault/items/'.$this->file_location);
    }

    /**
     * Returns true if this item is editable by the current user.
     * @return bool
     */
    public function isEditable()
    {
        $user = Auth::user();
        return $user && ($user->id == $this->user_id || permission('VaultAdmin'));
    }

}
