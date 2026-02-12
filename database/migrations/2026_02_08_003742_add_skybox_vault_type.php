<?php

use App\Models\Vault\VaultType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSkyboxVaultType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Keep "Other" as the last type
        $other = VaultType::where('name', '=', 'Other')->firstOrFail();
        $other->orderindex = 99;
        $other->save();

        VaultType::Create([
            'name' => 'Skybox',
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
        $skybox = VaultType::where('name', '=', 'Skybox')->firstOrFail();
        $skybox->delete();

        $other = VaultType::where('name', '=', 'Other')->firstOrFail();
        $other->orderindex = 7;
        $other->save();
    }
}
