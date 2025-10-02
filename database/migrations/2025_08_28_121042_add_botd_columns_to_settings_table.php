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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('botd_heading')->nullable()->after('consumer_spl_srn_title');
            $table->string('botd_image')->nullable()->after('botd_heading');
            $table->string('botd_business')->nullable()->after('botd_image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['botd_heading', 'botd_image', 'botd_business']);
        });
    }
};
