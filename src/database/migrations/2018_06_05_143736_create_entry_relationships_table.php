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
            $table->string('country', 16);
            $table->string('source_contentful_id')->index();
            $table->string('source_contentful_type')->index();
            $table->string('related_contentful_id')->index();
            $table->string('related_contentful_type')->index();
            $table->integer('order')->unsigned()->default(0);

            $table->index(['locale', 'source_contentful_id'], 'l_sid_idx');
            $table->index(['country', 'locale', 'source_contentful_id'], 'c_l_sid_idx');
            // @TODO... Specified key was too long
            // $table->index(['locale', 'source_contentful_id', 'related_contentful_id', 'related_contentful_type'], 'l_sid_rid_rty_idx');
            // @TODO... Specified key was too long
            // $table->index(['country', 'locale', 'source_contentful_id', 'related_contentful_id', 'related_contentful_type'], 'c_l_sid_rid_rty_idx');
            $table->index(['locale', 'related_contentful_id'], 'l_rid_idx');
            $table->index(['country', 'locale', 'related_contentful_id'], 'c_l_rid_idx');
            // @TODO... Specified key was too long
            // $table->index(['country', 'locale', 'related_contentful_id', 'source_contentful_id', 'source_contentful_type'], 'c_l_rid_sid_sty_idx');

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
