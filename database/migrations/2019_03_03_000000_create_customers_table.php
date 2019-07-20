<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'customers';

    /**
     * Run the migrations.
     * @table customers
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->string('user_id');
            $table->string('firstName');
            $table->string('LastName');
            $table->string('CompanyName')->nullable()->default(null);
            $table->string('country');
            $table->string('city');
            $table->string('community');
            $table->integer('zip');
            $table->text('address1');
            $table->text('address2');
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists($this->tableName);
     }
}
