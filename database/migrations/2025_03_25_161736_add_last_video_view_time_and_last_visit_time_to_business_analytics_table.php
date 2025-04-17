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
        Schema::table('business_analytics', function (Blueprint $table) {
            $table->string('website_clicks')->nullable()->after('unique_video_visits');
            $table->timestamp('last_video_view_time')->nullable()->after('website_clicks');
            $table->timestamp('last_visit_time')->nullable()->after('last_video_view_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_analytics', function (Blueprint $table) {
            $table->dropColumn(['website_clicks', 'last_video_view_time', 'last_visit_time']);
        });
    }
};
