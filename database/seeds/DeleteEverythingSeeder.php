<?php

class DeleteEverythingSeeder extends \Illuminate\Database\Seeder
{

    private function clearAndReset($tables) {
        foreach ($tables as $table) {
            DB::table($table)->delete();
            DB::statement("alter table $table AUTO_INCREMENT = 1");
        }
    }

    public function run()
    {
        $this->clearAndReset([
            'vault_item_reviews',

            'comment_metas',
            'comments',

            'competition_results',
            'competition_rules',
            'competition_judges',
            'competition_entry_votes',
            'competition_entry_screenshots',
            'competition_entries',
            'competition_engines',
            'competitions',

            'journals',

            'forum_posts',
            'forum_threads',
            'forums',

            'message_users',
            'message_thread_users',
            'messages',
            'message_threads',

            'news',

            'poll_item_votes',
            'poll_items',
            'polls',

            'shouts',

            'motms',
            'vault_screenshots',
            'vault_item_includes',
            'vault_items',

            'wiki_revision_metas',
            'wiki_uploads',
            'wiki_revisions',
            'wiki_objects',

            'bans',
            'password_resets',
            'user_permissions',
            'user_name_history',
            'users',
        ]);
    }
}
