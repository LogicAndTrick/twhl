<?php

class DeleteEverythingSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        DB::table('forum_posts')->delete();
        DB::table('forum_threads')->delete();
        DB::table('forums')->delete();

        DB::table('wiki_revision_metas')->delete();
        DB::table('wiki_uploads')->delete();
        DB::table('wiki_revisions')->delete();
        DB::table('wiki_objects')->delete();

        DB::table('user_permissions')->delete();
        DB::table('users')->delete();

        DB::statement('alter table forum_posts AUTO_INCREMENT = 1');
        DB::statement('alter table forum_threads AUTO_INCREMENT = 1');
        DB::statement('alter table forums AUTO_INCREMENT = 1');

        DB::statement('alter table user_permissions AUTO_INCREMENT = 1');
        DB::statement('alter table users AUTO_INCREMENT = 1');
    }
}
