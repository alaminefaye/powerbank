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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade'); // Le kiosque
            $table->integer('slot_id'); // Numéro du slot (1-12)
            $table->string('powerbank_sn')->nullable(); // SN de la batterie délivrée
            
            // État de la location
            $table->enum('status', ['pending', 'paid', 'active', 'completed', 'cancelled', 'failed'])->default('pending');
            
            // Informations de paiement
            $table->string('payment_method')->default('wave'); // wave, orange_money, etc.
            $table->string('payment_reference')->nullable(); // ID transaction Wave
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency')->default('XOF');
            
            // Horodatage
            $table->timestamp('started_at')->nullable(); // Quand la batterie est sortie
            $table->timestamp('ended_at')->nullable();   // Quand la batterie est rendue
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
