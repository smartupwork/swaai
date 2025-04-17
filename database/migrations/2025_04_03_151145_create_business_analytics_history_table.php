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
        Schema::create('business_analytics_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('page_visits')->default(0);
            $table->integer('unique_visits')->default(0);
            $table->integer('video_views')->default(0);
            $table->integer('unique_video_visits')->default(0);
            $table->integer('coupon_selection')->default(0);
            $table->integer('website_clicks')->default(0);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_analytics_history');
    }
};
