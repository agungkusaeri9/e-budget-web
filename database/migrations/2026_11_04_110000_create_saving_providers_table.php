<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('savings', function (Blueprint $table) {
            $table->dropColumn('provider');
            $table->foreignId('saving_provider_id')->nullable()->after('name')->constrained('saving_providers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('savings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('saving_provider_id');
            $table->string('provider')->nullable()->after('name');
        });
        Schema::dropIfExists('saving_providers');
    }
};
