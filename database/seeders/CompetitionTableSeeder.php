<?php

namespace Database\Seeders;

use \App\Models\Accounts\Permission;
use \App\Models\Accounts\User;

class CompetitionTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        // Create some closed competitions
        for ($i = 0; $i < 15; $i++) {
            $comp = \App\Models\Competitions\Competition::create([
                'status_id' => 5,
                'type_id' => ($i % 2) + 1,
                'judge_type_id' => 2,
                'name' => 'Competition #'.$i,
                'brief_text' => 'This is the brief text for competition #'.$i,
                'brief_html' => 'This is the brief text for competition #'.$i,
                'brief_attachment' => '',
                'open_date' => \Carbon\Carbon::create(2015, 01, 01),
                'close_date' => \Carbon\Carbon::create(2015, 02, 01),
                'voting_close_date' => null,
                'results_intro_text' => 'Results intro for competition #'.$i,
                'results_intro_html' => 'Results intro for competition #'.$i,
                'results_outro_text' => 'Results outro for competition #'.$i,
                'results_outro_html' => 'Results outro for competition #'.$i
            ]);
            $comp->engines()->attach([ 1 ]);
            $comp->judges()->attach([ 1 ]);
            $comp->restrictions()->attach([ 1, 2, 6, 11, 16, 20, 44 ]);

            // Create some entries
            $ents = [];
            $num = mt_rand(4, 10);
            for ($j = 0; $j < $num; $j++) {
                $ent = \App\Models\Competitions\CompetitionEntry::create([
                    'competition_id' => $comp->id,
                    'user_id' => $j + 1,
                    'title' => 'Entry #'.$j,
                    'content_text' => 'This is entry #'.$j,
                    'content_html' => 'This is entry #'.$j,
                    'is_hosted_externally' => true,
                    'file_location' => 'http://example.com'
                ]);
                $ents[] = $ent->id;
            }

            // Create some results
            shuffle($ents);
            for ($j = 0; $j < 3; $j++) {
                \App\Models\Competitions\CompetitionResult::create([
                    'competition_id' => $comp->id,
                    'entry_id' => $ents[$j],
                    'rank' => $j + 1,
                    'content_text' => 'This is result #'.$j,
                    'content_html' => 'This is result #'.$j
                ]);
            }
        }
    }
}
