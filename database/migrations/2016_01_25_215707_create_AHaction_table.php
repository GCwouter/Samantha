<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAHactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('AHaction_table', function($table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('categorie');
            $table->integer('week');
            $table->date('actionDateStart');
            $table->date('actionDateEnd');
            $table->float('originalPrice')->nullable();
            $table->float('actionPrice');
            $table->string('unitSize');
            $table->string('period')->nullable();
            $table->string('label')->nullable();
            $table->string('AHid')->index();
            $table->softDeletes();
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
        Schema::drop('AHaction_table');
    }
}
