<?php

use \App\Models\Wiki\WikiObject;
use \App\Models\Wiki\WikiRevision;
use \App\Models\Wiki\WikiRevisionMeta;
use \App\Models\Wiki\WikiType;
use \App\Models\Wiki\WikiUpload;

class WikiTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        $home = WikiObject::create([
            'type_id' => 1,
            'current_revision_id' => 0,
            'permission_id' => null
        ]);

        $rev = WikiRevision::create([
            'object_id' => $home->id,
            'user_id' => 1,
            'is_active' => 1,
            'slug' => 'Home',
            'title' => 'Home',
            'content_text' => 'This is the home page.',
            'content_html' => 'This is the home page.',
            'message' => ''
        ]);

        $home->update([
            'current_revision_id' => $rev->id
        ]);
    }
}
