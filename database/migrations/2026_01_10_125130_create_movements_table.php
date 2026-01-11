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
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('origin_office_id');
            $table->unsignedBigInteger('origin_user_id');
            $table->unsignedBigInteger('destination_office_id');
            $table->unsignedBigInteger('destination_user_id');
            $table->string('action');
            $table->text('indication')->nullable();
            $table->text('observation')->nullable();
            $table->date('receipt_date');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
