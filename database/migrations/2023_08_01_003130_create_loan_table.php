<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('amount');
            $table->integer('term');
            $table->dateTime('startDate');
            $table->tinyInteger('status')->default(\App\Enums\LoanStatus::PENDING);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('created_by')->references('id')->on('customers');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('repays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('loans');
            $table->decimal('amount');
            $table->dateTime('payDate');
            $table->tinyInteger('status')->default(\App\Enums\RepayStatus::PENDING);
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
        Schema::dropIfExists('repays');
        Schema::dropIfExists('loans');
    }
}
