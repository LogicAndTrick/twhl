<?php namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

class WikiUpload extends Model {

	//
    protected $table = 'wiki_uploads';
    protected $fillable = ['object_id', 'revision_id', 'extension'];

    public function revision()
    {
        return $this->belongsTo('App\Models\Wiki\WikiRevision', 'revision_id');
    }

    public function isEmbeddable() {
        return $this->extension == 'avif'
            || $this->extension == 'gif'
            || $this->extension == 'jpg'
            || $this->extension == 'jpeg'
            || $this->extension == 'png'
            || $this->extension == 'webp'
            || $this->extension == 'mp3'
            || $this->extension == 'mp4';
    }

    public function isImage() {
        return $this->extension == 'avif'
            || $this->extension == 'gif'
            || $this->extension == 'jpg'
            || $this->extension == 'jpeg'
            || $this->extension == 'png'
            || $this->extension == 'webp';
    }

    public function isVideo() {
        return $this->extension == 'mp4';
    }

    public function isAudio() {
        return $this->extension == 'mp3';
    }

    public function getServerFileName() {
        return public_path($this->getRelativePath());
    }

    public function getEmbeddableFileName() {
        return act('wiki', 'embed', $this->revision->getEmbedSlug());
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
