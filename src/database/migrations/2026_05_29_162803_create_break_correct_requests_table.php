<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakCorrectRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_correct_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correct_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('break_record_id')->nullable()->constrained()->cascadeOnDelete();
            $table->datetime('requested_break_in')->nullable();
            $table->datetime('requested_break_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('break_correct_requests');
    }
}
