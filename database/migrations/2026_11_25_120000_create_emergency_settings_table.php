<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تشغيل الميجريشن لإنشاء جدول إعدادات وتسعير طلبات الطوارئ حسب المناطق.
     */
    public function up(): void
    {
        Schema::create('emergency_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->unique()->constrained('regions')->cascadeOnDelete();
            $table->decimal('price', 10, 2)->default(150.00)->comment('سعر أو رسوم طلب الطوارئ بالريال السعودي');
            $table->decimal('cancellation_fee', 10, 2)->default(0.00)->comment('رسوم الإلغاء إن وجدت');
            $table->unsignedInteger('estimated_time_minutes')->default(30)->comment('متوسط زمن الاستجابة المتوقع بالدقائق');
            $table->boolean('is_active')->default(true)->comment('حالة تفعيل خدمة الطوارئ في المنطقة');
            $table->text('notes')->nullable()->comment('ملاحظات تشغيلية خاصة بالمنطقة');
            $table->timestamps();
        });

        // تهيئة السجلات للمناطق الحالية تلقائياً بالأسعار الافتراضية
        $regions = DB::table('regions')->get();
        foreach ($regions as $region) {
            DB::table('emergency_settings')->insertOrIgnore([
                'region_id' => $region->id,
                'price' => 150.00,
                'cancellation_fee' => 0.00,
                'estimated_time_minutes' => 30,
                'is_active' => true,
                'notes' => 'التسعيرة الافتراضية لخدمة الطوارئ',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * التراجع عن الميجريشن.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_settings');
    }
};
