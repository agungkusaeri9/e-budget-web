<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $tables = [
        'categories',
        'budget_plan_items',
        'recurring_expenses',
        'saving_providers',
        'savings',
        'saving_transactions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            });
        }

        $firstUserId = DB::table('users')->min('id');
        if ($firstUserId) {
            foreach ([...$this->tables, 'budget_plans'] as $tableName) {
                DB::table($tableName)->whereNull('user_id')->update(['user_id' => $firstUserId]);
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};
