<?php namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

class WikiUpload extends Model {

	//
    protected $table = 'wiki_uploads';
    protected $fillable = ['object_id', 'revision_id', 'extension'];

    public function getServerFileName() {
        return public_path($this->getRelativePath());
    }

    public function getResourceFileName() {
        return asset($this->getRelativePath());
    }

    public function getRelativePath() {
        $sub = $this->getRelativeDirectoryName();
        $fil = $this->getFileName();
        return "{$sub}/{$fil}";
    }

    public function getFileName() {
        return "{$this->id}.{$this->extension}";
    }

    public function getRelativeDirectoryName() {
        $sub = strval($this->id)[0];
        return "uploads/wiki/{$sub}";
    }
}
