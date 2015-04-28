<?php

use \App\Models\Accounts\Permission;
use \App\Models\Accounts\User;

class UserTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {

        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@twhl.info',
            'password' => bcrypt('admin'),
        ]);

        $per = Permission::where('is_default', '=', false)->get();
        $ids = [];
        foreach ($per as $p) $ids[] = $p->id;
        $user->permissions()->attach($ids);

        $user = User::create([
            'name' => 'user',
            'email' => 'user@twhl.info',
            'password' => bcrypt('user'),
        ]);
    }
}
