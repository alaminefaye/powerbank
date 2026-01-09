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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique()->comment('IMEI号 - 设备全球唯一码');
            $table->string('device_id')->default('0')->comment('设备ID，默认0');
            $table->string('sim_uuid')->nullable()->comment('SIM卡ICCID');
            $table->string('sim_mobile')->nullable()->comment('SIM卡手机号');
            $table->string('name')->nullable()->comment('设备名称');
            $table->string('location')->nullable()->comment('设备位置');
            $table->string('hardware_version')->nullable()->comment('硬件版本');
            $table->string('software_version')->nullable()->comment('软件版本');
            $table->string('mqtt_client_id')->nullable()->comment('MQTT Client ID');
            $table->string('mqtt_host')->nullable()->comment('MQTT服务器地址');
            $table->integer('mqtt_port')->nullable()->comment('MQTT端口');
            $table->string('mqtt_username')->nullable()->comment('MQTT用户名');
            $table->string('mqtt_password')->nullable()->comment('MQTT密码');
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('offline')->comment('设备状态');
            $table->timestamp('last_heartbeat')->nullable()->comment('最后心跳时间');
            $table->integer('total_slots')->default(0)->comment('总仓位数');
            $table->json('metadata')->nullable()->comment('额外数据');
            $table->timestamps();
            
            $table->index('uuid');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};

