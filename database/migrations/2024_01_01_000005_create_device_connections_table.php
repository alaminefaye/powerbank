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
        Schema::create('device_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->string('client_id')->comment('MQTT Client ID');
            $table->string('product_key')->default('powerbank')->comment('产品名称');
            $table->string('host')->comment('MQTT服务器地址');
            $table->integer('port')->default(1883)->comment('MQTT端口');
            $table->string('username')->comment('MQTT用户名');
            $table->string('password')->comment('MQTT密码');
            $table->bigInteger('timestamp')->comment('时间戳');
            $table->enum('status', ['connected', 'disconnected', 'error'])->default('disconnected')->comment('连接状态');
            $table->timestamp('connected_at')->nullable()->comment('连接时间');
            $table->timestamp('disconnected_at')->nullable()->comment('断开时间');
            $table->timestamps();
            
            $table->index('device_id');
            $table->index('client_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_connections');
    }
};

