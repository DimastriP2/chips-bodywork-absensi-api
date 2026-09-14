<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicate = DB::table('attendances')
            ->select('user_id', 'date')
            ->groupBy('user_id', 'date')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate) {
            throw new RuntimeException(
                'Duplicate attendance found. Review user_id '.$duplicate->user_id
                .' on '.$duplicate->date.' before migrating; no records were deleted.'
            );
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['user_id', 'date'], 'attendances_user_date_unique');
            $table->index('date', 'attendances_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_user_date_unique');
            $table->dropIndex('attendances_date_index');
        });
    }
};
