<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('users')
            ->select('auth_provider', 'provider_id')
            ->selectRaw('COUNT(*) as duplicate_count')
            ->whereNotNull('provider_id')
            ->groupBy('auth_provider', 'provider_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $keepId = DB::table('users')
                ->where('auth_provider', $duplicate->auth_provider)
                ->where('provider_id', $duplicate->provider_id)
                ->orderBy('id')
                ->value('id');

            if ($keepId) {
                DB::table('users')
                    ->where('auth_provider', $duplicate->auth_provider)
                    ->where('provider_id', $duplicate->provider_id)
                    ->where('id', '!=', $keepId)
                    ->update(['provider_id' => null]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['auth_provider', 'provider_id'], 'users_social_provider_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_social_provider_unique');
        });
    }
};
