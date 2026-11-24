<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_technician')->default(false)->after('permissions');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('technician_id')->nullable()->after('service_provider_id')
                ->constrained('users')->restrictOnDelete();
        });

        Schema::create('provider_technician_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['technician_id', 'service_id'], 'tech_service_unique');
        });

        Schema::create('provider_technician_sub_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sub_service_id')->constrained('sub_services')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['technician_id', 'sub_service_id'], 'tech_sub_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_technician_sub_services');
        Schema::dropIfExists('provider_technician_services');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('technician_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_technician');
        });
    }
};
