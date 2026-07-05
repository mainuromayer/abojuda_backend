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
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id')->nullable()->change();
            
            $table->unsignedBigInteger('reported_user_id')->nullable()->after('user_id');
            $table->foreign('reported_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('is_read')->default(false)->after('other_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['reported_user_id']);
            $table->dropColumn('reported_user_id');
            
            $table->unsignedBigInteger('post_id')->change();
        });
    }
};
