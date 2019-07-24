<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuppliersTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'suppliers';

    /**
     * Run the migrations.
     * @table suppliers
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->string('supplier_id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('phone_number')->nullable(true);
            $table->string('place_id', 200)->nullable(true);
            $table->string('LG')->nullable(true);
            $table->string('avi')->nullable(true);
            $table->string('state')->nullable(true);
            $table->string('country', 200)->nullable(true);
            $table->text('address')->nullable(true);
            $table->string('status')->default('inActive');
            $table->string('title');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable(true);
            $table->timestamp('phone_verified_at')->nullable(true);
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
