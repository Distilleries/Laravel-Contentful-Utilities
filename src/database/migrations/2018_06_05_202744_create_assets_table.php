<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->string('contentful_id')->index();
            $table->string('locale', 16)->index();
            $table->string('title');
            $table->text('description');
            $table->string('url');
            $table->string('file_name');
            $table->string('content_type');
            $table->integer('size')->unsigned()->default(0);
            $table->integer('width')->unsigned()->default(0);
            $table->integer('height')->unsigned()->default(0);
            $table->timestamps();

            $table->primary(['contentful_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assets');
    }
}
