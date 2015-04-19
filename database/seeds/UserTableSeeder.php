<?php

use \App\Models\Accounts\Permission;
use \App\Models\Accounts\User;
use \App\Models\Accounts\UserPermission;

class UserTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        $p_admin = Permission::create([ 'name' => 'Admin', 'is_default' => false ]);
        $p_forum_admin = Permission::create([ 'name' => 'ForumAdmin', 'is_default' => false ]);
        $p_vault_admin = Permission::create([ 'name' => 'VaultAdmin', 'is_default' => false ]);

        $p_forum_create = Permission::create([ 'name' => 'ForumCreate', 'is_default' => true ]);
        $p_forum_edit = Permission::create([ 'name' => 'ForumEdit', 'is_default' => true ]);

        $p_wiki_create = Permission::create([ 'name' => 'WikiCreate', 'is_default' => true ]);
        $p_wiki_edit = Permission::create([ 'name' => 'WikiEdit', 'is_default' => true ]);
        $p_wiki_delete = Permission::create([ 'name' => 'WikiDelete', 'is_default' => false ]);

        $p_vault_create = Permission::create([ 'name' => 'VaultCreate', 'is_default' => true ]);

        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@twhl.info',
            'password' => bcrypt('admin'),
        ]);

        $user->permissions()->attach([
            $p_admin->id,
            $p_forum_admin->id,
            $p_vault_admin->id
        ]);

        $user = User::create([
            'name' => 'user',
            'email' => 'user@twhl.info',
            'password' => bcrypt('user'),
        ]);
    }
}
