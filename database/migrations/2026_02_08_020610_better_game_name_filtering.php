<?php

use App\Models\Game;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BetterGameNameFiltering extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            // A list of variants of the same name, used for filtering
            $table->string('name_variants', 255)->default('');
            $table->fullText(['name', 'name_variants', 'abbreviation'], 'games_name_name_variants_abbreviation_fulltext');
        });

        $altNames = [
            'Counter-Strike: Source' => 'CS Source',
            'CS: Global Offensive' => 'CS GO, Counter-Strike: Global Offensive',
            'CS: Condition Zero' => 'CS CZ, CSCZ, CZDS, Counter-Strike: Condition Zero Deleted Scenes',
            'Day of Defeat: Source' => 'DoD Source',
            'Garry\'s Mod' => 'Garrys Mod, Gary\'s Mod, Garys Mod',
            'Not Listed (HL1 Engine)' => 'GoldSrc, Gold Src, GoldSource, Gold Source',
            'Spirit of Half-Life' => 'SoHL',
        ];

        foreach ($altNames as $name => $name_variants) {
            $game = Game::where( 'name', '=', $name)->firstOrFail();
            $game->name_variants = $name_variants;
            $game->save();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropFullText('games_name_name_variants_abbreviation_fulltext');
            $table->dropColumn('name_variants');
        });
    }
}
