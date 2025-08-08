<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();

            $table->string('sec_spl_srn_image')->nullable();
            $table->string('sec_spl_srn_title')->nullable();
            $table->text('sec_spl_srn_desc')->nullable();

            $table->string('business_spl_srn_image')->nullable();
            $table->string('business_spl_srn_title')->nullable();

            $table->string('consumer_spl_srn_image')->nullable();
            $table->string('consumer_spl_srn_title')->nullable();

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
        Schema::dropIfExists('settings');
    }
};
