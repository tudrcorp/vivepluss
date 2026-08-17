<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Configuration extends Model
{
    protected $connection = 'mysql_vivepluss';

    protected $table = 'configurations';

    protected $fillable = [
        'agency_id',
        'code_agency',
        'brandLogoDefault',
        'brandLogoHeightDefault',
        'faviconDefault',
        'brandLogo',
        'brandLogoHeight',
        'quote_cover_individual',
        'quote_back_cover_individual',
        'primaryColor',
        'infoColor',
        'currency_symbol',

        'web_headTitle',
        'web_headDescription',
        'web_headKeywords',
        'web_headOpTitle',
        'web_headOpDescription',
        'web_headXTitle',
        'web_headXDescription',

        'web_sectionOne_title',
        'web_sectionOne_title_ln_2',
        'web_icons_redSocial',
        'web_url_facebook',
        'web_url_instagram',
        'web_instagram_posts',
        'web_url_twitter',
        'web_url_whatsapp',
        'web_headerLogo',
        'web_nosotros',

        'web_mision',
        'web_imageMision',

        'web_vision',
        'web_imageVision',

        'web_namePlan_1',
        'web_pricePlan_1',
        'web_descriptionPlan_1',
        'web_descriptionBottonPlan_1',

        'web_namePlan_2',
        'web_pricePlan_2',
        'web_descriptionPlan_2',
        'web_descriptionBottonPlan_2',

        'web_namePlan_3',
        'web_pricePlan_3',
        'web_descriptionPlan_3',
        'web_descriptionBottonPlan_3',
        'web_formaPagoPlan_1',
        'web_descriptionPricePlan_1',
        'web_formaPagoPlan_2',
        'web_descriptionPricePlan_2',
        'web_formaPagoPlan_3',
        'web_descriptionPricePlan_3',

        'web_footerPlans',
        'web_footerBottonPlans',

        'web_footerLogo',
        'web_footerLogoText',
        'web_footerContactEmail',
        'web_footerContactPhone',
        'web_footerContactAddress',

        'web_nosotrosTitle_parteIzquierda',
        'web_nosotrosTitle_parteDerecha',
        'web_nosotros',

        'web_plansTitle',
        'web_plansSubTitle',

        'web_ubicacionTitle',
        'web_ubicacionSubTitle',
        'web_ubicacionUrl',
        'web_ubicacionDireccion',
        'web_ubicacionHorarios',

        'table_af_corp_table_title',
        'table_af_corp_table_description',

        'table_af_ind_table_title',
        'table_af_ind_table_description',

        'table_quote_corp_table_title',
        'table_quote_corp_table_description',

        'table_quote_ind_table_title',
        'table_quote_ind_table_description',

        'table_request_table_title',
        'table_request_table_description',

        'table_agency_title',
        'table_agency_description',

        'menu_top',
        'duplicatedSession',
        'agents_module_enabled',

        // agregados
        'web_imagePlan_1',
        'web_imagePlan_2',
        'web_imagePlan_3',

        'web_Plan_1_items_1',
        'web_Plan_1_items_2',
        'web_Plan_1_items_3',
        'web_Plan_1_items_4',

        'web_Plan_2_items_1',
        'web_Plan_2_items_2',
        'web_Plan_2_items_3',
        'web_Plan_2_items_4',

        'web_Plan_3_items_1',
        'web_Plan_3_items_2',
        'web_Plan_3_items_3',
        'web_Plan_3_items_4',

        'document_notifications_enabled',
        'document_notification_emails',
        'document_notification_phones',

        'payment_notifications_enabled',
        'payment_notification_emails',
        'payment_notification_phones',
    ];

    protected $casts = [
        'web_icons_redSocial' => 'array',
        'web_instagram_posts' => 'array',
        'document_notifications_enabled' => 'boolean',
        'document_notification_emails' => 'array',
        'document_notification_phones' => 'array',
        'payment_notifications_enabled' => 'boolean',
        'payment_notification_emails' => 'array',
        'payment_notification_phones' => 'array',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Resolves the white_company_id scope for the current panel: the tenant
     * matching the authenticated user's white_company_id, falling back to
     * the first configured tenant when there's no match.
     */
    public static function currentWhiteCompanyId(): int|string|null
    {
        return static::where('white_company_id', Auth::user()->white_company_id)->value('white_company_id')
            ?? static::query()->value('white_company_id');
    }

    public static function currencySymbol(): string
    {
        return once(function (): string {
            return static::query()->value('currency_symbol') ?: 'EUR€';
        });
    }

    /**
     * Las coberturas (montos de plan) siempre se expresan en dólares,
     * independientemente de la moneda configurada para tarifas y totales.
     */
    public static function coverageCurrencySymbol(): string
    {
        return 'USD$';
    }

    public static function currencyName(): string
    {
        return match (static::currencySymbol()) {
            'US$' => 'dólares',
            default => 'euros',
        };
    }
}
