<?php

use App\Models\Accounts\Permission;
use App\Models\Accounts\UserPermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $perm = Permission::create([
            'name' => 'PulseDashboardAdmin',
            'description' => 'Access to the Pulse Dashboard',
            'is_default' => false
        ]);
        UserPermission::create([
            'user_id' => 1983,
            'permission_id' => $perm->id,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $perm = Permission::where('name', 'PulseDashboardAdmin')->first();
        if ($perm) {
            UserPermission::where('permission_id', $perm->id)->delete();
            $perm->delete();
        }
    }
};
