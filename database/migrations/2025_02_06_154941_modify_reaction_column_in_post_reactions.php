<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('post_reactions', function (Blueprint $table) {
            $table->dropForeign(['reaction_id']);
            $table->renameColumn('reaction_id', 'reaction');
            $table->string('reaction')->change();
        });
    }

    public function down()
    {
        Schema::table('post_reactions', function (Blueprint $table) {
            $table->integer('reaction')->change();
            $table->renameColumn('reaction', 'reaction_id');
            $table->foreign('reaction_id')->references('id')->on('reactions')->onDelete('cascade');
        });
    }
};
