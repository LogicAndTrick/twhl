<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionRestrictionsTable extends Migration {

	public function up()
	{
		Schema::create('competition_restrictions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('group_id');
            $table->text('content_text');
            $table->text('content_html');
		});

        $restrictions = [
            [ 1, 'Your entry [b]must[/b] run without error: no excuses! We will not be chasing up authors if their entry does not work.'                                                         ],

            [ 2, 'No custom content of any kind is allowed.'                                                                                                                                     ],
            [ 2, 'Custom textures are permitted, but no other custom content can be used.'                                                                                                       ],
            [ 2, 'Custom textures, sounds, models, etc. are allowed, but no custom code can be used.'                                                                                            ],
            [ 2, 'There are no restrictions on custom content. Textures, models, code, and more can be used.'                                                                                    ],

            [ 3, 'Custom textures should be -wadincluded for Goldsource, or included in the BSP for Source.'                                                                                     ],
            [ 3, 'Custom content should be placed in the appropriate folder structure. A mini-mod format will not be accepted.'                                                                  ],
            [ 3, 'Custom content should be placed in the appropriate folder structure, or a mini-mod format can be used if needed.'                                                              ],
            [ 3, 'If any custom content is used, it must be placed in a mini-mod format.'                                                                                                        ],
            [ 3, 'Your entry must be one or more screenshot(s). A single screenshot can be uploaded (JPG or PNG format only), or multiple screenshots can be submitted in a ZIP or RAR format.'  ],

            [ 4, 'All entries must be in ZIP or RAR format. Do not upload just the BSP file.'                                                                                                    ],
            [ 4, 'Your entry must contain a readme file detailing your TWHL username, the map name, and the game that the map is for.'                                                           ],
            [ 4, 'Your entry (map files or mod name) must be named in the following format: twhl_competition_{competition_id}_{username}'                                                        ],

            [ 5, 'You must upload your entry using the entry form below. For entries over 16mb, choose the \'external link\' option and enter the URL to the download file.'                     ],

            [ 6, 'Goldsource and Source entries will be judged separately.'                                                                                                                      ],
            [ 6, 'Goldsource and Source entries will be judged together. The judges appreciate the work and limitations of both, so they are able to appreciate the nuances of each engine.'     ],
            [ 6, 'The winners will be decided by a voting process. In the case of a tie, the admin team will step in and decide the winners.'                                                    ],

            [ 7, 'Your entry can be for the Steam versions of any Valve game.'                                                                                                                   ],
            [ 7, 'Your entry can be for the Steam versions of any Valve game, or the Spirit of Half-Life mod.'                                                                                   ],
            [ 7, 'Your entry can be for the Steam versions of any Valve game based on the Goldsource engine.'                                                                                    ],
            [ 7, 'Your entry can be for the Steam versions of any Valve game based on the Goldsource engine, or the Spirit of Half-Life mod.'                                                    ],
            [ 7, 'Your entry can be for any Valve game based on the Source engine.'                                                                                                              ],
            [ 7, 'Your entry must be for the Steam version of one of the following games:'                                                                                                       ],
            [ 7, 'Your entry can be for the Steam versions of any Valve game, except for the following:'                                                                                         ],
            [ 7, 'Half-Life'                                                                                                                                                                     ],
            [ 7, 'Spirit of Half-Life (version 1.8)'                                                                                                                                  ],
            [ 7, 'Half-Life: Deathmatch'                                                                                                                                                         ],
            [ 7, 'Blue Shift'                                                                                                                                                                    ],
            [ 7, 'Opposing Force'                                                                                                                                                                ],
            [ 7, 'Counter-Strike'                                                                                                                                                                ],
            [ 7, 'Day of Defeat'                                                                                                                                                                 ],
            [ 7, 'Team Fortress Classic'                                                                                                                                                         ],
            [ 7, 'Half-Life 2'                                                                                                                                                                   ],
            [ 7, 'Half-Life 2: Episode One'                                                                                                                                                      ],
            [ 7, 'Half-Life 2: Episode Two'                                                                                                                                                      ],
            [ 7, 'Half-Life 2: Deathmatch'                                                                                                                                                       ],
            [ 7, 'Counter-Strike: Source'                                                                                                                                                        ],
            [ 7, 'Day of Defeat: Source'                                                                                                                                                         ],
            [ 7, 'Team Fortress 2'                                                                                                                                                               ],
            [ 7, 'Left 4 Dead'                                                                                                                                                                   ],
            [ 7, 'Left 4 Dead 2'                                                                                                                                                                 ],
            [ 7, 'Portal'                                                                                                                                                                        ],
            [ 7, 'Portal 2'                                                                                                                                                                      ],

            [ 8, 'Your entry must be singleplayer.'                                                                                                                                              ],
            [ 8, 'Your entry must be multiplayer.'                                                                                                                                               ],
            [ 8, 'Your entry can be either singleplayer or multiplayer.'                                                                                                                         ],
        ];

        foreach ($restrictions as $rs) {
            \App\Models\Competitions\CompetitionRestriction::Create([
                'group_id' => $rs[0],
                'content_text' => $rs[1],
                'content_html' => bbcode($rs[1])
            ]);
        }
	}

	public function down()
	{
		Schema::drop('competition_restrictions');
	}

}
