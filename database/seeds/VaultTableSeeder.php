<?php

use \App\Models\Accounts\Permission;
use \App\Models\Accounts\User;

class VaultTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        for ($i = 0; $i < 100; $i++) {
            $item = \App\Models\Vault\VaultItem::create([
                'user_id' => ($i % 10) + 1,
                'engine_id' => ($i % 2) + 1,
                'game_id' => (($i % 2) * 3) + ($i % 3) + 1,
                'category_id' => ($i % 4) + 1,
                'type_id' => 1,
                'license_id' => ($i % 7) + 1,
                'name' => 'Vault Item #'.$i,
                'content_text' => 'This is vault item #'.$i,
                'content_html' => 'This is vault item #'.$i,
                'is_hosted_externally' => true,
                'file_location' => 'http://example.com/',
                'file_size' => 0,
                'flag_notify' => true,
                'flag_ratings' => true,
                'stat_views' => 0,
                'stat_downloads' => 0,
                'stat_ratings' => 0,
                'stat_comments' => 0,
                'stat_average_rating' => 0
            ]);

            $incs = [];
            $incs[] = new \App\Models\Vault\VaultItemInclude(['include_id' => 1]);
            if ($i % 3 == 0) $incs[] = new \App\Models\Vault\VaultItemInclude(['include_id' => 2]);
            $item->vault_item_includes()->saveMany($incs);
        }
    }
}
