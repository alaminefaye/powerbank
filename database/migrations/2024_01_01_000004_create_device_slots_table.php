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
        Schema::create('device_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->integer('slot_number')->comment('仓位编号');
            $table->enum('status', ['empty', 'occupied', 'fault', 'maintenance'])->default('empty')->comment('仓位状态');
            $table->string('powerbank_sn')->nullable()->comment('充电宝SN号');
            $table->integer('battery_level')->nullable()->comment('电量百分比 0-100');
            $table->timestamp('last_update')->nullable()->comment('最后更新时间');
            $table->json('metadata')->nullable()->comment('额外数据');
            $table->timestamps();
            
            $table->unique(['device_id', 'slot_number']);
            $table->index('powerbank_sn');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_slots');
    }
};

