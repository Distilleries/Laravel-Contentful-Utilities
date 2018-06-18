<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntryRelationshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entry_relationships', function (Blueprint $table) {
            $table->string('locale', 16);
            $table->string('source_contentful_id')->index();
            $table->string('source_contentful_type')->index();
            $table->string('related_contentful_id')->index();
            $table->string('related_contentful_type')->index();
            $table->integer('order')->unsigned()->default(0);

            $table->index(['locale', 'source_contentful_id'], 'locale_source_contentful_id_index');
            $table->index(['locale', 'source_contentful_id', 'related_contentful_id', 'related_contentful_type'], 'locale_source_related_index');
            $table->index(['locale', 'related_contentful_id'], 'locale_related_contentful_id_index');
            $table->index(['locale', 'related_contentful_id', 'source_contentful_id', 'source_contentful_type'], 'locale_related_source_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entry_relationships');
    }
}
