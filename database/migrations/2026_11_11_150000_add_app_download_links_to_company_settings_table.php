<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('app_store_url', 255)->nullable()->default('https://apps.apple.com/sa/app/6829814780')->after('support_chat_enabled');
            $table->string('play_store_url', 255)->nullable()->default('https://play.google.com/store/apps/details?id=com.futuretec.rounderapp.sa')->after('app_store_url');
            $table->boolean('app_download_enabled')->default(true)->after('play_store_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'app_store_url',
                'play_store_url',
                'app_download_enabled',
            ]);
        });
    }
};

