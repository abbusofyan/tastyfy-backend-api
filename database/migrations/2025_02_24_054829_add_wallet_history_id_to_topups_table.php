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
        Schema::table('topups', function (Blueprint $table) {
            // First add wallet_history_id
            $table->foreignId('wallet_history_id')->nullable()->constrained('wallet_histories')->cascadeOnDelete();
        });

        // Only modify customer_id if needed in a separate operation
        if (Schema::hasColumn('topups', 'customer_id')) {
            Schema::table('topups', function (Blueprint $table) {
                // $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            });

            Schema::table('topups', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topups', function (Blueprint $table) {
            //
            $table->dropForeign(['wallet_history_id']);
            $table->dropForeign(['customer_id']);
        });
    }
};
