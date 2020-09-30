<?php

namespace Database\Seeders;

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
            'avatar_file' => 'classic_027.jpg'
        ]);

        $avs = [
            'hl_blueshift.jpg',
            'hl_opforce.jpg',
            'hl_tentacle.jpg',
            'hl_vortigaunt.jpg',
            'hl2_alyx.jpg',
            'hl2_combine1.jpg',
            'hl2_combine2.jpg',
            'hl2_freeman.jpg',
            'hl2_gman.jpg',
            'hl2_headcrab.jpg',
            'hl2_hunters.jpg',
            'hl2_metrocop.jpg',
        ];

        // Create a bunch more users
        for ($i = 1; $i < 10; $i++) {
            User::create([
                'name' => 'user'.$i,
                'email' => 'user'.$i.'@twhl.info',
                'password' => bcrypt('user'.$i),
                'avatar_file' => $avs[$i % count($avs)]
            ]);
        }
    }
}
