<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $s2 = \App\Models\Engine::Create([
            'name' => 'Source 2', // The future is now ...
            'orderindex' => 3
        ]);

        $s2id = $s2->id;

        $games = [
            [     1, 'Sven Co-op',            'SC',   11 ],
            [     1, 'CS: Condition Zero',    'CZ',   10 ],
            [     2, 'CS: Global Offensive',  'CSGO', 10 ],
            [     2, 'Black Mesa',            'BM',   23 ],
            [ $s2id, 'Half-Life: Alyx',       'ALYX', 6  ], // ... as long as you have some expensive hardware
            [ $s2id, 'Not Listed (Source 2)', 'NLS2', 99 ],
        ];

        foreach ($games as $game) {
            \App\Models\Game::Create([
                'engine_id' => $game[0],
                'name' => $game[1],
                'abbreviation' => $game[2],
                'orderindex' => $game[3]
            ]);
        }


        \App\Models\Vault\VaultType::Create([
            'name' => 'Other',
            'orderindex' => 7
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
