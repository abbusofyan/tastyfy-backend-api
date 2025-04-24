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
        Schema::table('wallet_histories', function (Blueprint $table) {
            //i realize that customer_id is only name without active foreign key , add foreign key to it . also add new columns here => type (after credits, string ) , admin_id ( after type, connect to users table, nullable) , note (string nullable , after admin_id)
            //drop customer_id column if exists

            $table->string('type')->nullable()->after('credit');
            $table->foreignId('admin_id')->nullable()->after('type')->constrained('users')->cascadeOnDelete();
            $table->string('note')->nullable()->after('admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_histories', function (Blueprint $table) {
            //

            $table->dropForeign(['admin_id']);
            $table->dropColumn('type');
            $table->dropColumn('note');
        });
    }
};
