<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->decimal('target_amount', 15, 2)->nullable();
            $table->date('deadline')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('saving_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saving_id')->constrained('savings')->cascadeOnDelete();
            $table->foreignId('budget_plan_id')->nullable()->constrained('budget_plans')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_saving')->default(false)->after('is_default');
            $table->foreignId('saving_id')->nullable()->after('is_saving')->constrained('savings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('saving_id');
            $table->dropColumn('is_saving');
        });
        Schema::dropIfExists('saving_transactions');
        Schema::dropIfExists('savings');
    }
};
