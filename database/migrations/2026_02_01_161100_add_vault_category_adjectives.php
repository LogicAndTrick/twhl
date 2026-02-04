<?php

use App\Models\Vault\VaultCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVaultCategoryAdjectives extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vault_categories', function (Blueprint $table) {
            // Adjective form used to describe items.
            // If a vault item has the VaultCategory (name=Examples adjective=Example)
            // and the VaultType (name=Mod), the item will be described as an "Example Mod"
            // in the Vault item list.
            $table->string('adjective');
        });

        $unfinished = VaultCategory::where('name', '=', 'Unfinished')->firstOrFail();
        $unfinished->adjective = 'Unfinished';
        $unfinished->save();

        $completed = VaultCategory::where('name', '=', 'Completed')->firstOrFail();
        $completed->adjective = 'Completed';
        $completed->save();

        $problems = VaultCategory::where('name', '=', 'Problems')->firstOrFail();
        $problems->adjective = 'Problem';
        $problems->save();

        $examples = VaultCategory::where('name', '=', 'Examples')->firstOrFail();
        $examples->adjective = 'Example';
        $examples->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vault_categories', function (Blueprint $table) {
            $table->dropColumn('adjective');
        });
    }
}
