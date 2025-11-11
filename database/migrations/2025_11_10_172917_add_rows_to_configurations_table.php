<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->string('web_headDescription');
            $table->string('web_headKeywords');
            $table->string('web_headOpTitle');
            $table->string('web_headOpDescription');
            $table->string('web_headXTitle');
            $table->string('web_headXDescription');
            $table->string('web_sectionOne_title');
            $table->string('web_icons_redSocial');
            $table->string('web_headerLogo');
            $table->string('web_nosotros');
            $table->string('web_mision');
            $table->string('web_imageMision');
            $table->string('web_vision');
            $table->string('web_imageVision');
            $table->string('web_namePlan_1');
            $table->string('web_pricePlan_1');
            $table->string('web_descriptionPlan_1');
            $table->string('web_descriptionBottonPlan_1');
            $table->string('web_namePlan_2');
            $table->string('web_pricePlan_2');
            $table->string('web_descriptionPlan_2');
            $table->string('web_descriptionBottonPlan_2');
            $table->string('web_namePlan_3');
            $table->string('web_pricePlan_3');
            $table->string('web_descriptionPlan_3');
            $table->string('web_descriptionBottonPlan_3');
            $table->string('web_footerPlans');
            $table->string('web_footerBottonPlans');
            $table->string('web_footerLogo');
            $table->string('web_footerLogoText');
            $table->string('web_footerContactEmail');
            $table->string('web_footerContactPhone');
            $table->string('web_footerContactAddress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            //
        });
    }
};