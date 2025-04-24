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
        Schema::create('model_has_customer_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_role_id'); // Use customer_role_id
            $table->morphs('model');
            $table->index(['model_id', 'model_type']);

            $table->foreign('customer_role_id')->references('id')->on('customer_roles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_customer_roles');
    }
};
