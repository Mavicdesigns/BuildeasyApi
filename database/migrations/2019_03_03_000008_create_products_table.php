<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'products';

    /**
     * Run the migrations.
     * @table products
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->string('product_id');
            $table->string('category_id');
            $table->string('title');
            $table->decimal('price', 10, 0);
            $table->integer('min-quantity')->nullable()->default(null);
            $table->string('status', 200);
            $table->json('images');
            $table->string('valuation');
            $table->text('description');
            $table->json('attributes')->nullable()->default(null);
            $table->json('options')->nullable()->default(null);
            $table->string('supplier_id');
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
