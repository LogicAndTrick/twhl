<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesTable extends Migration {

	public function up()
	{
		Schema::create('games', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('engine_id');
            $table->string('name');
            $table->string('abbreviation');
            $table->integer('orderindex');

            $table->foreign('engine_id')->references('id')->on('engines');
		});

        $games = [
            [ 1, 'Half-Life', 'HL', 1                  ],
            [ 1, 'Counter-Strike', 'CS', 9             ],
            [ 1, 'Team Fortress Classic', 'TFC', 12    ],
            [ 2, 'Half-Life 2', 'HL2', 3               ],
            [ 2, 'Counter-Strike: Source', 'CSS', 10   ],
            [ 2, 'Half-Life 2: Deathmatch', 'HL2DM', 6 ],
            [ 1, 'Half-Life: Deathmatch', 'HLDM', 2    ],
            [ 1, 'Day of Defeat', 'DOD', 16            ],
            [ 1, 'Spirit of Half-Life', 'Spirit', 22   ],
            [ 3, 'Other', 'Other', 100                 ],
            [ 2, 'Half-Life 2: Episode 1', 'EP1', 4    ],
            [ 2, 'Half-Life 2: Episode 2', 'EP2', 5    ],
            [ 2, 'Team Fortress 2', 'TF2', 13          ],
            [ 2, 'Portal', 'Portal', 14                ],
            [ 2, 'Day of Defeat: Source', 'DODS', 17   ],
            [ 1, 'Not Listed (HL1 Engine)', 'NL', 98   ],
            [ 2, 'Not Listed (Source)', 'NLS', 99      ],
            [ 2, 'Alien Swarm', 'AS', 20               ],
            [ 1, 'Blue Shift', 'BS', 7                 ],
            [ 1, 'Deathmatch Classic', 'DMC', 11       ],
            [ 2, 'Garry\'s Mod', 'GMOD', 21            ],
            [ 2, 'Left 4 Dead', 'L4D', 18              ],
            [ 2, 'Left 4 Dead 2', 'L4D2', 19           ],
            [ 1, 'Opposing Force', 'OP4', 8            ],
            [ 2, 'Portal 2', 'Portal2', 15             ],
        ];

        foreach ($games as $game) {
            \App\Models\Game::Create([
                'engine_id' => $game[0],
                'name' => $game[1],
                'abbreviation' => $game[2],
                'orderindex' => $game[3]
            ]);
        }
	}

	public function down()
	{
		Schema::drop('games');
	}

}
