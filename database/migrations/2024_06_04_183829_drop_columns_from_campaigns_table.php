<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnsFromCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('campaign_url');
            $table->dropColumn('campaign_connection');
            $table->dropColumn('end_date');
            $table->dropColumn('is_archive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->text('campaign_url');
            $table->string('campaign_connection')->nullable();
            $table->date('end_date')->nullable();   
            $table->boolean('is_archive')->default(0);
        });
    }
}
