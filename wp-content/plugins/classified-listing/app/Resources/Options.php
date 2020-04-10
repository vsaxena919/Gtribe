<?php

namespace Rtcl\Resources;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Text;

class Options
{

    static function get_listings_orderby_options() {
        $options = array(
            'title-asc'  => __("A to Z ( title )", 'classified-listing'),
            'title-desc' => __("Z to A ( title )", 'classified-listing'),
            'date-desc'  => __("Recently added ( latest )", 'classified-listing'),
            'date-asc'   => __("Date added ( oldest )", 'classified-listing'),
            'views-desc' => __("Most viewed", 'classified-listing'),
            'views-asc'  => __("Less viewed", 'classified-listing')
        );
        if (!Functions::is_price_disabled()) {
            $options['price-asc'] = __("Price ( low to high )", 'classified-listing');
            $options['price-desc'] = __("Price ( high to low )", 'classified-listing');
        }

        return $options;

    }


    /**
     * @return mixed|void
     */
    static function get_redirect_page_list() {

        $list = array(
            'account'    => esc_html__("Account", "classified-listing"),
            'submission' => esc_html__("Regular submission", "classified-listing"),
            'custom'     => esc_html__("Custom", "classified-listing")
        );

        return apply_filters('rtcl_get_redirect_page_list', $list);

    }

    static function get_listings_view_options() {
        $options = array(
            'list' => __("List", 'classified-listing'),
            'grid' => __("Grid", 'classified-listing')
        );

        return $options;

    }

    static function get_status_list($all = null) {
        $status = array(
            'publish'       => __('Published', 'classified-listing'),
            'pending'       => __('Pending', 'classified-listing'),
            'draft'         => __('Draft', 'classified-listing'),
            'rtcl-reviewed' => __('Reviewed', 'classified-listing'),
            'rtcl-expired'  => __('Expired', 'classified-listing'),
        );
        if ($all) {
            $status['rtcl-temp'] = __('Temporary', 'classified-listing');
        }

        return $status;

    }

    /**
     * @return array
     */
    static function detail_page_sidebar_position() {
        $status = array(
            'right'  => __('Right', 'classified-listing'),
            'left'   => __('Left', 'classified-listing'),
            'bottom' => __('Bottom', 'classified-listing'),
        );

        return apply_filters('rtcl_detail_page_sidebar_position', $status);
    }

    static function get_payment_status_list($short = false) {
        $statuses = array(
            'rtcl-pending'    => _x('Pending', 'Payment status', 'classified-listing'),
            'rtcl-processing' => _x('Processing', 'Payment status', 'classified-listing'),
            'rtcl-on-hold'    => _x('On hold', 'Payment status', 'classified-listing'),
            'rtcl-completed'  => _x('Completed', 'Payment status', 'classified-listing'),
            'rtcl-cancelled'  => _x('Cancelled', 'Payment status', 'classified-listing'),
            'rtcl-refunded'   => _x('Refunded', 'Payment status', 'classified-listing'),
            'rtcl-failed'     => _x('Failed', 'Payment status', 'classified-listing'),
            'rtcl-created'    => _x('Created', 'Payment status', 'classified-listing')
        );
        if ($short) {
            unset($statuses['rtcl-created']);
        }

        return $statuses;
    }

    static function get_price_types() {
        $price_types = array(
            'fixed'      => Text::price_type_fixed(),
            'negotiable' => Text::price_type_negotiable(),
            'on_call'    => Text::price_type_on_call()
        );

        return apply_filters('rtcl_price_types', $price_types);
    }


    public static function get_default_listing_types() {
        $default_types = array(
            "sell"     => __("Sell", "classified-listing"),
            "buy"      => __("Buy", "classified-listing"),
            "exchange" => __("Exchange", "classified-listing"),
            "job"      => __("Job", "classified-listing"),
            "to_let"   => __("To-Let", "classified-listing"),
        );

        return apply_filters('rtcl_get_default_listing_types', $default_types);
    }

    public static function get_ad_types() {
        _deprecated_function(__FUNCTION__, '1.2.17', 'Functions::get_listing_types()');

        return Functions::get_listing_types();
    }

    static function get_field_list() {
        return array(
            'text'     => array(
                'name'    => __("Text Box", "classified-listing"),
                'symbol'  => "font",
                'options' => self::common_options() +
                    array(
                        '_default_value' => array(
                            'label' => __("Default value", "classified-listing"),
                            'type'  => 'text',
                        ),
                        '_placeholder'   => array(
                            'label' => __("Placeholder text", "classified-listing"),
                            'type'  => 'text',
                        )
                    )
            ),
            'textarea' => array(
                'name'    => __("Textarea", "classified-listing"),
                'symbol'  => "align-justify",
                'options' => self::common_options() +
                    array(
                        '_default_value' => array(
                            'label' => __("Default value", "classified-listing"),
                            'type'  => 'text',
                        ),
                        '_placeholder'   => array(
                            'label' => __("Placeholder text", "classified-listing"),
                            'type'  => 'text',
                        ),
                        '_rows'          => array(
                            'label' => __("Rows", "classified-listing"),
                            'type'  => 'number'
                        )
                    )
            ),
            'url'      => array(
                'name'    => __("URL", "classified-listing"),
                'symbol'  => "globe",
                'options' => self::common_options() +
                    array(
                        '_default_value' => array(
                            'label' => __("Default value", "classified-listing"),
                            'type'  => 'text',
                        ),
                        '_placeholder'   => array(
                            'label' => __("Placeholder text", "classified-listing"),
                            'type'  => 'text',
                        ),
                        '_target'        => array(
                            'label'   => __("Open link in a new window?", "classified-listing"),
                            'type'    => 'radio',
                            'class'   => '',
                            'options' => array(
                                1 => __("Yes", "classified-listing"),
                                0 => __("No", "classified-listing"),
                            )
                        ),
                        '_nofollow'      => array(
                            'label'   => __('Use rel="nofollow" when displaying the link?',
                                "classified-listing"),
                            'type'    => 'radio',
                            'class'   => '',
                            'options' => array(
                                1 => __("Yes", "classified-listing"),
                                0 => __("No", "classified-listing"),
                            )
                        ),
                    )
            ),
            'number'   => array(
                'name'    => __("Number", "classified-listing"),
                'symbol'  => "calc",
                'options' => self::common_options() +
                    array(
                        '_default_value' => array(
                            'label' => __("Default value", "classified-listing"),
                            'type'  => 'text',
                        ),
                        '_placeholder'   => array(
                            'label' => __("Placeholder text", "classified-listing"),
                            'type'  => 'text',
                        ),
                        '_min'           => array(
                            'label' => __("Minimum value", "classified-listing"),
                            'type'  => 'number'
                        ),
                        '_max'           => array(
                            'label' => __("Maximum value", "classified-listing"),
                            'type'  => 'number'
                        ),
                        '_step_size'     => array(
                            'label' => __("Step Size", "classified-listing"),
                            'type'  => 'number'
                        )
                    )
            ),
            'select'   => array(
                'name'    => __("Select", "classified-listing"),
                'symbol'  => "tablet rtcl-rotate-180",
                'options' => self::common_options() +
                    array(
                        '_options' => array(
                            'label' => __("Options", "classified-listing"),
                            'type'  => 'select'
                        )
                    )
            ),
            'radio'    => array(
                'name'    => __("Radio", "classified-listing"),
                'symbol'  => "dot-circled",
                'options' => self::common_options() +
                    array(
                        '_options' => array(
                            'label' => __("Options", "classified-listing"),
                            'type'  => 'select'
                        )
                    )
            ),
            'checkbox' => array(
                'name'    => __("Checkbox", "classified-listing"),
                'symbol'  => "check rtcl-checkboxes",
                'options' => self::common_options() +
                    array(
                        '_options' => array(
                            'label' => __("Options", "classified-listing"),
                            'type'  => 'checkbox'
                        )
                    )
            )
        );
    }

    static function common_options() {
        return array(
            '_label'       => array(
                'label'       => __("Field label", "classified-listing"),
                'type'        => 'text',
                'placeholder' => __("Enter field label", "classified-listing"),
                'class'       => 'rtcl-forms-set-legend js-rtcl-slugize-source'
            ),
            '_slug'        => array(
                'label'       => __("Field slug/name", "classified-listing"),
                'type'        => 'text',
                'placeholder' => __("Enter field slug/name", "classified-listing"),
                'class'       => 'rtcl-forms-field-slug js-rtcl-slugize'
            ),
            '_description' => array(
                'label'       => __("Field description", "classified-listing"),
                'type'        => 'textarea',
                'placeholder' => __("Enter field description", "classified-listing")
            ),
            '_required'    => array(
                'label'   => __("Required?", "classified-listing"),
                'type'    => 'radio',
                'class'   => '',
                'options' => array(
                    1 => __("Yes", "classified-listing"),
                    0 => __("No", "classified-listing"),
                )
            ),
            '_searchable'  => array(
                'label'   => __("Include this field in the search form? <span class='rtcl-pro'>[PRO]</span>", "classified-listing"),
                'type'    => 'radio',
                'class'   => '',
                'options' => array(
                    1 => __("Yes", "classified-listing"),
                    0 => __("No", "classified-listing"),
                )
            ),
            '_listable'    => array(
                'label'   => __("Include this field in the listing? <span class='rtcl-pro'>[PRO]</span>", "classified-listing"),
                'type'    => 'radio',
                'class'   => '',
                'options' => array(
                    1 => __("Yes", "classified-listing"),
                    0 => __("No", "classified-listing"),
                )
            )
        );
    }

    static function getContactDetailsFields() {
        return array(
            'zipcode' => array(
                'type'  => 'text',
                'label' => __("Zip Code", 'classified-listing'),
                'id'    => 'rtcl-zipcode',
                'class' => 'rtcl-map-field'
            ),
            'address' => array(
                'type'  => 'textarea',
                'label' => __("Address", 'classified-listing'),
                'id'    => 'rtcl-address',
                'class' => 'rtcl-map-field'
            ),
            'phone'   => array(
                'type'  => 'text',
                'label' => __("Phone", 'classified-listing'),
                'id'    => 'rtcl-phone',
                'class' => ''
            ),
            'email'   => array(
                'type'  => 'email',
                'label' => __("Email", 'classified-listing'),
                'id'    => 'rtcl-email',
                'class' => ''
            ),
            'website' => array(
                'type'  => 'url',
                'label' => __("Website", 'classified-listing'),
                'id'    => 'rtcl-website',
                'class' => ''
            )
        );
    }

    static function get_month_list() {
        return array(
            __("Jan", 'classified-listing'),
            __("Feb", 'classified-listing'),
            __("Mar", 'classified-listing'),
            __("Apr", 'classified-listing'),
            __("May", 'classified-listing'),
            __("Jun", 'classified-listing'),
            __("Jul", 'classified-listing'),
            __("Aug", 'classified-listing'),
            __("Sep", 'classified-listing'),
            __("Oct", 'classified-listing'),
            __("Nov", 'classified-listing'),
            __("Dec", 'classified-listing')
        );
    }

    static function get_currency_symbols() {
        $symbols = array(
            'AED' => '&#x62f;.&#x625;',
            'AFN' => '&#x60b;',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => '&fnof;',
            'AOA' => 'Kz',
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => 'Afl.',
            'AZN' => 'AZN',
            'BAM' => 'KM',
            'BBD' => '&#36;',
            'BDT' => '&#2547;&nbsp;',
            'BGN' => '&#1083;&#1074;.',
            'BHD' => '.&#x62f;.&#x628;',
            'BIF' => 'Fr',
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => 'Bs.',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYR' => 'Br',
            'BYN' => 'Br',
            'BZD' => '&#36;',
            'CAD' => '&#36;',
            'CDF' => 'Fr',
            'CHF' => '&#67;&#72;&#70;',
            'CLP' => '&#36;',
            'CNY' => '&yen;',
            'COP' => '&#36;',
            'CRC' => '&#x20a1;',
            'CUC' => '&#36;',
            'CUP' => '&#36;',
            'CVE' => '&#36;',
            'CZK' => '&#75;&#269;',
            'DJF' => 'Fr',
            'DKK' => 'DKK',
            'DOP' => 'RD&#36;',
            'DZD' => '&#x62f;.&#x62c;',
            'EGP' => 'EGP',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '&euro;',
            'FJD' => '&#36;',
            'FKP' => '&pound;',
            'GBP' => '&pound;',
            'GEL' => '&#x10da;',
            'GGP' => '&pound;',
            'GHS' => '&#x20b5;',
            'GIP' => '&pound;',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => 'L',
            'HRK' => 'Kn',
            'HTG' => 'G',
            'HUF' => '&#70;&#116;',
            'IDR' => 'Rp',
            'ILS' => '&#8362;',
            'IMP' => '&pound;',
            'INR' => '&#8377;',
            'IQD' => '&#x639;.&#x62f;',
            'IRR' => '&#xfdfc;',
            'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
            'ISK' => 'kr.',
            'JEP' => '&pound;',
            'JMD' => '&#36;',
            'JOD' => '&#x62f;.&#x627;',
            'JPY' => '&yen;',
            'KES' => 'KSh',
            'KGS' => '&#x441;&#x43e;&#x43c;',
            'KHR' => '&#x17db;',
            'KMF' => 'Fr',
            'KPW' => '&#x20a9;',
            'KRW' => '&#8361;',
            'KWD' => '&#x62f;.&#x643;',
            'KYD' => '&#36;',
            'KZT' => 'KZT',
            'LAK' => '&#8365;',
            'LBP' => '&#x644;.&#x644;',
            'LKR' => '&#xdbb;&#xdd4;',
            'LRD' => '&#36;',
            'LSL' => 'L',
            'LYD' => '&#x644;.&#x62f;',
            'MAD' => '&#x62f;.&#x645;.',
            'MDL' => 'MDL',
            'MGA' => 'Ar',
            'MKD' => '&#x434;&#x435;&#x43d;',
            'MMK' => 'Ks',
            'MNT' => '&#x20ae;',
            'MOP' => 'P',
            'MRO' => 'UM',
            'MUR' => '&#x20a8;',
            'MVR' => '.&#x783;',
            'MWK' => 'MK',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => 'MT',
            'NAD' => '&#36;',
            'NGN' => '&#8358;',
            'NIO' => 'C&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#x631;.&#x639;.',
            'PAB' => 'B/.',
            'PEN' => 'S/.',
            'PGK' => 'K',
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PRB' => '&#x440;.',
            'PYG' => '&#8370;',
            'QAR' => '&#x631;.&#x642;',
            'RMB' => '&yen;',
            'RON' => 'lei',
            'RSD' => '&#x434;&#x438;&#x43d;.',
            'RUB' => '&#8381;',
            'RWF' => 'Fr',
            'SAR' => '&#x631;.&#x633;',
            'SBD' => '&#36;',
            'SCR' => '&#x20a8;',
            'SDG' => '&#x62c;.&#x633;.',
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&pound;',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '&#36;',
            'SSP' => '&pound;',
            'STD' => 'Db',
            'SYP' => '&#x644;.&#x633;',
            'SZL' => 'L',
            'THB' => '&#3647;',
            'TJS' => '&#x405;&#x41c;',
            'TMT' => 'm',
            'TND' => '&#x62f;.&#x62a;',
            'TOP' => 'T&#36;',
            'TRY' => '&#8378;',
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => 'Sh',
            'UAH' => '&#8372;',
            'UGX' => 'UGX',
            'USD' => '&#36;',
            'UYU' => '&#36;',
            'UZS' => 'UZS',
            'VEF' => 'Bs F',
            'VND' => '&#8363;',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'CFA',
            'XCD' => '&#36;',
            'XOF' => 'CFA',
            'XPF' => 'Fr',
            'YER' => '&#xfdfc;',
            'ZAR' => '&#82;',
            'ZMW' => 'ZK',
        );
        return apply_filters('rtcl_get_currency_symbols', $symbols);
    }

    static function get_currencies() {
        $currency_list = self::get_currencies_list();
        $currencies = array();
        foreach ($currency_list as $code => $name) {
            $currencies[$code] = sprintf(esc_html__('%1$s (%2$s)', 'classified-listing'), $name,
                Functions::get_currency_symbol($code));
        }

        return $currencies;
    }

    static function get_currencies_list() {
        return array(
            'AED' => __('United Arab Emirates dirham', 'classified-listing'),
            'AFN' => __('Afghan afghani', 'classified-listing'),
            'ALL' => __('Albanian lek', 'classified-listing'),
            'AMD' => __('Armenian dram', 'classified-listing'),
            'ANG' => __('Netherlands Antillean guilder', 'classified-listing'),
            'AOA' => __('Angolan kwanza', 'classified-listing'),
            'ARS' => __('Argentine peso', 'classified-listing'),
            'AUD' => __('Australian dollar', 'classified-listing'),
            'AWG' => __('Aruban florin', 'classified-listing'),
            'AZN' => __('Azerbaijani manat', 'classified-listing'),
            'BAM' => __('Bosnia and Herzegovina convertible mark', 'classified-listing'),
            'BBD' => __('Barbadian dollar', 'classified-listing'),
            'BDT' => __('Bangladeshi taka', 'classified-listing'),
            'BGN' => __('Bulgarian lev', 'classified-listing'),
            'BHD' => __('Bahraini dinar', 'classified-listing'),
            'BIF' => __('Burundian franc', 'classified-listing'),
            'BMD' => __('Bermudian dollar', 'classified-listing'),
            'BND' => __('Brunei dollar', 'classified-listing'),
            'BOB' => __('Bolivian boliviano', 'classified-listing'),
            'BRL' => __('Brazilian real', 'classified-listing'),
            'BSD' => __('Bahamian dollar', 'classified-listing'),
            'BTC' => __('Bitcoin', 'classified-listing'),
            'BTN' => __('Bhutanese ngultrum', 'classified-listing'),
            'BWP' => __('Botswana pula', 'classified-listing'),
            'BYR' => __('Belarusian ruble (old)', 'classified-listing'),
            'BYN' => __('Belarusian ruble', 'classified-listing'),
            'BZD' => __('Belize dollar', 'classified-listing'),
            'CAD' => __('Canadian dollar', 'classified-listing'),
            'CDF' => __('Congolese franc', 'classified-listing'),
            'CHF' => __('Swiss franc', 'classified-listing'),
            'CLP' => __('Chilean peso', 'classified-listing'),
            'CNY' => __('Chinese yuan', 'classified-listing'),
            'COP' => __('Colombian peso', 'classified-listing'),
            'CRC' => __('Costa Rican col&oacute;n', 'classified-listing'),
            'CUC' => __('Cuban convertible peso', 'classified-listing'),
            'CUP' => __('Cuban peso', 'classified-listing'),
            'CVE' => __('Cape Verdean escudo', 'classified-listing'),
            'CZK' => __('Czech koruna', 'classified-listing'),
            'DJF' => __('Djiboutian franc', 'classified-listing'),
            'DKK' => __('Danish krone', 'classified-listing'),
            'DOP' => __('Dominican peso', 'classified-listing'),
            'DZD' => __('Algerian dinar', 'classified-listing'),
            'EGP' => __('Egyptian pound', 'classified-listing'),
            'ERN' => __('Eritrean nakfa', 'classified-listing'),
            'ETB' => __('Ethiopian birr', 'classified-listing'),
            'EUR' => __('Euro', 'classified-listing'),
            'FJD' => __('Fijian dollar', 'classified-listing'),
            'FKP' => __('Falkland Islands pound', 'classified-listing'),
            'GBP' => __('Pound sterling', 'classified-listing'),
            'GEL' => __('Georgian lari', 'classified-listing'),
            'GGP' => __('Guernsey pound', 'classified-listing'),
            'GHS' => __('Ghana cedi', 'classified-listing'),
            'GIP' => __('Gibraltar pound', 'classified-listing'),
            'GMD' => __('Gambian dalasi', 'classified-listing'),
            'GNF' => __('Guinean franc', 'classified-listing'),
            'GTQ' => __('Guatemalan quetzal', 'classified-listing'),
            'GYD' => __('Guyanese dollar', 'classified-listing'),
            'HKD' => __('Hong Kong dollar', 'classified-listing'),
            'HNL' => __('Honduran lempira', 'classified-listing'),
            'HRK' => __('Croatian kuna', 'classified-listing'),
            'HTG' => __('Haitian gourde', 'classified-listing'),
            'HUF' => __('Hungarian forint', 'classified-listing'),
            'IDR' => __('Indonesian rupiah', 'classified-listing'),
            'ILS' => __('Israeli new shekel', 'classified-listing'),
            'IMP' => __('Manx pound', 'classified-listing'),
            'INR' => __('Indian rupee', 'classified-listing'),
            'IQD' => __('Iraqi dinar', 'classified-listing'),
            'IRR' => __('Iranian rial', 'classified-listing'),
            'IRT' => __('Iranian toman', 'classified-listing'),
            'ISK' => __('Icelandic kr&oacute;na', 'classified-listing'),
            'JEP' => __('Jersey pound', 'classified-listing'),
            'JMD' => __('Jamaican dollar', 'classified-listing'),
            'JOD' => __('Jordanian dinar', 'classified-listing'),
            'JPY' => __('Japanese yen', 'classified-listing'),
            'KES' => __('Kenyan shilling', 'classified-listing'),
            'KGS' => __('Kyrgyzstani som', 'classified-listing'),
            'KHR' => __('Cambodian riel', 'classified-listing'),
            'KMF' => __('Comorian franc', 'classified-listing'),
            'KPW' => __('North Korean won', 'classified-listing'),
            'KRW' => __('South Korean won', 'classified-listing'),
            'KWD' => __('Kuwaiti dinar', 'classified-listing'),
            'KYD' => __('Cayman Islands dollar', 'classified-listing'),
            'KZT' => __('Kazakhstani tenge', 'classified-listing'),
            'LAK' => __('Lao kip', 'classified-listing'),
            'LBP' => __('Lebanese pound', 'classified-listing'),
            'LKR' => __('Sri Lankan rupee', 'classified-listing'),
            'LRD' => __('Liberian dollar', 'classified-listing'),
            'LSL' => __('Lesotho loti', 'classified-listing'),
            'LYD' => __('Libyan dinar', 'classified-listing'),
            'MAD' => __('Moroccan dirham', 'classified-listing'),
            'MDL' => __('Moldovan leu', 'classified-listing'),
            'MGA' => __('Malagasy ariary', 'classified-listing'),
            'MKD' => __('Macedonian denar', 'classified-listing'),
            'MMK' => __('Burmese kyat', 'classified-listing'),
            'MNT' => __('Mongolian t&ouml;gr&ouml;g', 'classified-listing'),
            'MOP' => __('Macanese pataca', 'classified-listing'),
            'MRO' => __('Mauritanian ouguiya', 'classified-listing'),
            'MUR' => __('Mauritian rupee', 'classified-listing'),
            'MVR' => __('Maldivian rufiyaa', 'classified-listing'),
            'MWK' => __('Malawian kwacha', 'classified-listing'),
            'MXN' => __('Mexican peso', 'classified-listing'),
            'MYR' => __('Malaysian ringgit', 'classified-listing'),
            'MZN' => __('Mozambican metical', 'classified-listing'),
            'NAD' => __('Namibian dollar', 'classified-listing'),
            'NGN' => __('Nigerian naira', 'classified-listing'),
            'NIO' => __('Nicaraguan c&oacute;rdoba', 'classified-listing'),
            'NOK' => __('Norwegian krone', 'classified-listing'),
            'NPR' => __('Nepalese rupee', 'classified-listing'),
            'NZD' => __('New Zealand dollar', 'classified-listing'),
            'OMR' => __('Omani rial', 'classified-listing'),
            'PAB' => __('Panamanian balboa', 'classified-listing'),
            'PEN' => __('Peruvian nuevo sol', 'classified-listing'),
            'PGK' => __('Papua New Guinean kina', 'classified-listing'),
            'PHP' => __('Philippine peso', 'classified-listing'),
            'PKR' => __('Pakistani rupee', 'classified-listing'),
            'PLN' => __('Polish z&#x142;oty', 'classified-listing'),
            'PRB' => __('Transnistrian ruble', 'classified-listing'),
            'PYG' => __('Paraguayan guaran&iacute;', 'classified-listing'),
            'QAR' => __('Qatari riyal', 'classified-listing'),
            'RON' => __('Romanian leu', 'classified-listing'),
            'RSD' => __('Serbian dinar', 'classified-listing'),
            'RUB' => __('Russian ruble', 'classified-listing'),
            'RWF' => __('Rwandan franc', 'classified-listing'),
            'SAR' => __('Saudi riyal', 'classified-listing'),
            'SBD' => __('Solomon Islands dollar', 'classified-listing'),
            'SCR' => __('Seychellois rupee', 'classified-listing'),
            'SDG' => __('Sudanese pound', 'classified-listing'),
            'SEK' => __('Swedish krona', 'classified-listing'),
            'SGD' => __('Singapore dollar', 'classified-listing'),
            'SHP' => __('Saint Helena pound', 'classified-listing'),
            'SLL' => __('Sierra Leonean leone', 'classified-listing'),
            'SOS' => __('Somali shilling', 'classified-listing'),
            'SRD' => __('Surinamese dollar', 'classified-listing'),
            'SSP' => __('South Sudanese pound', 'classified-listing'),
            'STD' => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'classified-listing'),
            'SYP' => __('Syrian pound', 'classified-listing'),
            'SZL' => __('Swazi lilangeni', 'classified-listing'),
            'THB' => __('Thai baht', 'classified-listing'),
            'TJS' => __('Tajikistani somoni', 'classified-listing'),
            'TMT' => __('Turkmenistan manat', 'classified-listing'),
            'TND' => __('Tunisian dinar', 'classified-listing'),
            'TOP' => __('Tongan pa&#x2bb;anga', 'classified-listing'),
            'TRY' => __('Turkish lira', 'classified-listing'),
            'TTD' => __('Trinidad and Tobago dollar', 'classified-listing'),
            'TWD' => __('New Taiwan dollar', 'classified-listing'),
            'TZS' => __('Tanzanian shilling', 'classified-listing'),
            'UAH' => __('Ukrainian hryvnia', 'classified-listing'),
            'UGX' => __('Ugandan shilling', 'classified-listing'),
            'USD' => __('United States dollar', 'classified-listing'),
            'UYU' => __('Uruguayan peso', 'classified-listing'),
            'UZS' => __('Uzbekistani som', 'classified-listing'),
            'VEF' => __('Venezuelan bol&iacute;var', 'classified-listing'),
            'VND' => __('Vietnamese &#x111;&#x1ed3;ng', 'classified-listing'),
            'VUV' => __('Vanuatu vatu', 'classified-listing'),
            'WST' => __('Samoan t&#x101;l&#x101;', 'classified-listing'),
            'XAF' => __('Central African CFA franc', 'classified-listing'),
            'XCD' => __('East Caribbean dollar', 'classified-listing'),
            'XOF' => __('West African CFA franc', 'classified-listing'),
            'XPF' => __('CFP franc', 'classified-listing'),
            'YER' => __('Yemeni rial', 'classified-listing'),
            'ZAR' => __('South African rand', 'classified-listing'),
            'ZMW' => __('Zambian kwacha', 'classified-listing'),
        );
    }

    public static function get_pricing_types() {
        $types = array(
            "regular" => __("Regular", "classified-listing"),
        );

        return apply_filters('rtcl_pricing_type', $types);
    }

    public static function get_currency_positions() {
        return array(
            'left'        => __('Left ($99)', 'classified-listing'),
            'right'       => __('Right (99$)', 'classified-listing'),
            'left_space'  => __('Left with space ($ 99)', 'classified-listing'),
            'right_space' => __('Right with space (99 $)', 'classified-listing')
        );
    }

    public static function get_icon_list() {
        $icons = array(
            "user",
            "users",
            "male",
            "glass",
            "music",
            "search",
            "mail",
            "mail-alt",
            "heart",
            "heart-empty",
            "star",
            "female",
            "video",
            "videocam",
            "picture",
            "camera",
            "camera-alt",
            "th-large",
            "th",
            "th-list",
            "ok",
            "ok-circled",
            "ok-circled2",
            "ok-squared",
            "cancel",
            "cancel-circled",
            "cancel-circled2",
            "plus",
            "plus-circled",
            "plus-squared",
            "plus-squared-alt",
            "minus",
            "minus-circled",
            "minus-squared",
            "minus-squared-alt",
            "help",
            "help-circled",
            "info-circled",
            "info",
            "home",
            "link",
            "unlink",
            "link-ext",
            "link-ext-alt",
            "attach",
            "lock",
            "lock-open",
            "lock-open-alt",
            "pin",
            "eye",
            "eye-off",
            "tag",
            "tags",
            "bookmark",
            "bookmark-empty",
            "flag",
            "flag-empty",
            "flag-checkered",
            "thumbs-up",
            "thumbs-down",
            "thumbs-up-alt",
            "thumbs-down-alt",
            "download",
            "upload",
            "download-cloud",
            "upload-cloud",
            "reply",
            "reply-all",
            "forward",
            "quote-left",
            "quote-right",
            "code",
            "export",
            "export-alt",
            "pencil",
            "pencil-squared",
            "edit",
            "print",
            "retweet",
            "keyboard",
            "gamepad",
            "comment",
            "chat",
            "comment-empty",
            "chat-empty",
            "bell",
            "bell-alt",
            "attention-alt",
            "attention",
            "attention-circled",
            "location",
            "direction",
            "compass",
            "trash",
            "doc",
            "docs",
            "doc-text",
            "doc-inv",
            "doc-text-inv",
            "folder",
            "folder-open",
            "folder-empty",
            "folder-open-empty",
            "box",
            "rss",
            "rss-squared",
            "phone",
            "phone-squared",
            "menu",
            "cog",
            "cog-alt",
            "wrench",
            "basket",
            "calendar",
            "calendar-empty",
            "login",
            "logout",
            "mic",
            "mute",
            "volume-off",
            "volume-down",
            "volume-up",
            "headphones",
            "clock",
            "lightbulb",
            "block",
            "resize-full",
            "resize-full-alt",
            "resize-small",
            "resize-vertical",
            "resize-horizontal",
            "move",
            "zoom-in",
            "zoom-out",
            "down-circled2",
            "up-circled2",
            "left-circled2",
            "right-circled2",
            "down-dir",
            "up-dir",
            "left-dir",
            "right-dir",
            "down-open",
            "left-open",
            "rocket",
            "up-open",
            "angle-left",
            "angle-right",
            "angle-up",
            "angle-down",
            "angle-circled-left",
            "angle-circled-right",
            "angle-circled-up",
            "angle-circled-down",
            "angle-double-left",
            "angle-double-right",
            "angle-double-up",
            "angle-double-down",
            "down",
            "left",
            "right",
            "up",
            "down-big",
            "left-big",
            "right-big",
            "up-big",
            "right-hand",
            "left-hand",
            "up-hand",
            "down-hand",
            "left-circled",
            "right-circled",
            "up-circled",
            "down-circled",
            "cw",
            "ccw",
            "arrows-cw",
            "level-up",
            "level-down",
            "shuffle",
            "exchange",
            "expand",
            "collapse",
            "expand-right",
            "collapse-left",
            "play",
            "play-circled",
            "play-circled2",
            "stop",
            "pause",
            "to-end",
            "to-end-alt",
            "to-start",
            "to-start-alt",
            "fast-fw",
            "fast-bw",
            "eject",
            "target",
            "signal",
            "award",
            "desktop",
            "laptop",
            "tablet",
            "mobile",
            "inbox",
            "globe",
            "sun",
            "cloud",
            "flash",
            "moon",
            "umbrella",
            "flight",
            "fighter-jet",
            "leaf",
            "font",
            "bold",
            "italic",
            "text-height",
            "text-width",
            "align-left",
            "align-center",
            "align-right",
            "align-justify",
            "list",
            "indent-left",
            "indent-right",
            "list-bullet",
            "list-numbered",
            "strike",
            "underline",
            "superscript",
            "subscript",
            "table",
            "columns",
            "crop",
            "scissors",
            "paste",
            "briefcase",
            "suitcase",
            "ellipsis",
            "ellipsis-vert",
            "off",
            "road",
            "list-alt",
            "qrcode",
            "barcode",
            "book",
            "ajust",
            "tint",
            "check",
            "check-empty",
            "circle",
            "circle-empty",
            "dot-circled",
            "asterisk",
            "gift",
            "fire",
            "magnet",
            "chart-bar",
            "ticket",
            "credit-card",
            "floppy",
            "megaphone",
            "hdd",
            "key",
            "fork",
            "child",
            "bug",
            "certificate",
            "tasks",
            "filter",
            "beaker",
            "magic",
            "truck",
            "money",
            "euro",
            "pound",
            "dollar",
            "rupee",
            "yen",
            "rouble",
            "try",
            "won",
            "bitcoin",
            "sort",
            "sort-down",
            "sort-up",
            "sort-alt-up",
            "sort-alt-down",
            "sort-name-up",
            "sort-name-down",
            "sort-number-up",
            "sort-number-down",
            "hammer",
            "gauge",
            "sitemap",
            "spinner",
            "coffee",
            "food",
            "beer",
            "user-md",
            "stethoscope",
            "ambulance",
            "medkit",
            "h-sigh",
            "hospital",
            "building",
            "smile",
            "frown",
            "meh",
            "anchor",
            "terminal",
            "eraser",
            "puzzle",
            "shield",
            "extinguisher",
            "bullseye",
            "star-empty",
            "adn",
            "android",
            "apple",
            "bitbucket",
            "bitbucket-squared",
            "css3",
            "dribbble",
            "dropbox",
            "facebook",
            "facebook-squared",
            "flickr",
            "foursquare",
            "github",
            "github-squared",
            "github-circled",
            "gittip",
            "gplus-squared",
            "gplus",
            "html5",
            "instagramm",
            "linkedin-squared",
            "star-half",
            "linkedin",
            "maxcdn",
            "pagelines",
            "pinterest-circled",
            "pinterest-squared",
            "renren",
            "skype",
            "stackexchange",
            "stackoverflow",
            "trello",
            "tumblr",
            "tumblr-squared",
            "twitter-squared",
            "twitter",
            "vimeo-squared",
            "vkontakte",
            "weibo",
            "star-half-alt",
            "xing",
            "xing-squared",
            "youtube",
            "youtube-squared",
            "youtube-play",
            "blank",
            "lemon",
            "wheelchair",
            "windows",
            "linux",
            "mail-squared",
            "share-squared",
            "trash-1",
            "file-pdf",
            "file-word",
            "file-excel",
            "file-powerpoint",
            "file-image",
            "file-archive",
            "file-audio",
            "file-video",
            "file-code",
            "share",
            "bell-off",
            "bell-off-empty",
            "sliders",
            "right-open",
            "wifi",
            "history",
            "paper-plane",
            "space-shuttle",
            "paper-plane-empty",
            "toggle-off",
            "toggle-on",
            "chart-line",
            "chart-pie",
            "chart-area",
            "bus",
            "bicycle",
            "taxi",
            "cab",
            "circle-notch",
            "circle-thin",
            "paragraph",
            "header",
            "shekel",
            "building-filled",
            "bank",
            "language",
            "graduation-cap",
            "paw",
            "spoon",
            "cube",
            "cubes",
            "recycle",
            "tree",
            "database",
            "lifebuoy",
            "rebel",
            "empire",
            "bomb",
            "soccer-ball",
            "tty",
            "binoculars",
            "plug",
            "calc",
            "newspaper",
            "copyright",
            "codeopen",
            "cc",
            "behance-squared",
            "behance",
            "angellist",
            "cc-stripe",
            "cc-paypal",
            "cc-amex",
            "cc-mastercard",
            "cc-visa",
            "birthday",
            "brush",
            "eyedropper",
            "at",
            "delicious",
            "deviantart",
            "digg",
            "cc-discover",
            "drupal",
            "git-squared",
            "git",
            "google",
            "gwallet",
            "hacker-news",
            "ioxhost",
            "joomla",
            "jsfiddle",
            "lastfm",
            "lastfm-squared",
            "meanpath",
            "openid",
            "paypal",
            "pied-piper-squared",
            "pied-piper-alt",
            "tencent-weibo",
            "stumbleupon-circled",
            "stumbleupon",
            "steam-squared",
            "steam",
            "spotify",
            "soundcloud",
            "slideshare",
            "slack",
            "reddit-squared",
            "reddit",
            "qq",
            "twitch",
            "vine",
            "wechat",
            "wordpress",
            "yelp",
            "yahoo"
        );

        return apply_filters('rtcl_get_icon_list', $icons);
    }


    public static function get_price_unit_list() {

        $unit_list = array(
            'year'  => array(
                'title' => esc_html__("Year", 'classified-listing'),
                'short' => esc_html__("per year", 'classified-listing')
            ),
            'month' => array(
                'title' => esc_html__("Month", 'classified-listing'),
                'short' => esc_html__("per month", 'classified-listing')
            ),
            'week'  => array(
                'title' => esc_html__("Week", 'classified-listing'),
                'short' => esc_html__("per week", 'classified-listing')
            ),
            'sqft'  => array(
                'title' => esc_html__("Square Feet", 'classified-listing'),
                'short' => esc_html__("per sqft", 'classified-listing')
            ),
            'total' => array(
                'title' => esc_html__("Total Price", 'classified-listing'),
                'short' => esc_html__("total price", 'classified-listing')
            )
        );

        return apply_filters('rtcl_get_price_unit_list', $unit_list);

    }

    public static function get_admin_email_notification_options() {
        $options = array(
            'register_new_user' => __('A new user is registered (Only work when user registered using Classified listing plugin registration form)', 'classified-listing'),
            'listing_submitted' => __('A new listing is submitted', 'classified-listing'),
            'listing_edited'    => __('A listing is edited', 'classified-listing'),
            'listing_expired'   => __('A listing expired', 'classified-listing'),
            'order_created'     => __('Order created', 'classified-listing'),
            'order_completed'   => __('Payment received / Order Completed', 'classified-listing'),
            'listing_contact'   => __('A contact message is sent to a listing owner', 'classified-listing')
        );

        return apply_filters('rtcl_get_admin_email_notification_options', $options);
    }


    public static function get_user_email_notification_options() {
        $options = array(
            'listing_submitted'     => __('Listing is submitted', 'classified-listing'),
            'listing_published'     => __('Listing is approved/published', 'classified-listing'),
            'listing_renewal'       => __('Listing is about to expire (reached renewal email threshold)', 'classified-listing'),
            'listing_expired'       => __('Listing expired', 'classified-listing'),
            'remind_renewal'        => __('Listing expired and reached renewal reminder email threshold', 'classified-listing'),
            'order_created'         => __('Order created', 'classified-listing'),
            'order_completed'       => __('Order completed', 'classified-listing'),
            'disable_contact_email' => __('Disable contact email to listing owner', 'classified-listing')
        );

        return apply_filters('rtcl_get_user_email_notification_options', $options);
    }

    public static function get_exclude_slugs() {
        $excludeSlugs = null;
        $exclude = array();
        $potTypes = get_post_types(array('public' => true, '_builtin' => false));
        foreach ($potTypes as $pot_type) {
            $obj = get_post_type_object($pot_type);
            if ($obj->rewrite['slug']) {
                $exclude[] = $obj->rewrite['slug'];
            } else {
                $exclude[] = $pot_type;
            }

        }
        $exclude = apply_filters('rtcl_get_exclude_slugs', $exclude);
        if (!empty($exclude)) {
            $excludeSlugs = implode('|', $exclude);
        }

        return apply_filters('rtcl_get_exclude_slugs_string', $excludeSlugs);
    }


    public static function get_email_type_options() {
        $types = array('plain' => __('Plain text', 'classified-listing'));

        if (class_exists('DOMDocument')) {
            $types['html'] = __('HTML', 'classified-listing');
            $types['multipart'] = __('Multipart', 'classified-listing');
        }

        return $types;
    }

    public static function get_recaptcha_form_list() {
        return apply_filters('rtcl_recaptcha_form_list', array(
            'registration' => __('User Registration form', 'classified-listing'),
            'listing'      => __('New Listing form', 'classified-listing'),
            'contact'      => __('Contact form', 'classified-listing'),
            'report_abuse' => __('Report abuse form', 'classified-listing')
        ));
    }

    public static function get_listing_detail_page_display_options() {
        $options = array(
            'category'   => __('Category name', 'classified-listing'),
            'location'   => __('Location name', 'classified-listing'),
            'date'       => __('Date added', 'classified-listing'),
            'user'       => __('Listing owner name', 'classified-listing'),
            'views'      => __('Views count', 'classified-listing'),
            'price_type' => __('Price type', 'classified-listing'),
            'price'      => __('Price', 'classified-listing'),
            'featured'   => __('Feature Label', 'classified-listing'),
            'new'        => __('New Label', 'classified-listing'),
            'address'    => __('Address', 'classified-listing'),
            'zipcode'    => __('Zip Code', 'classified-listing'),
        );

        return apply_filters('rtcl_get_listing_detail_page_display_options', $options);
    }

    public static function get_listing_display_options() {
        $options = array(
            'category'   => __('Category name', 'classified-listing'),
            'location'   => __('Location name', 'classified-listing'),
            'date'       => __('Date added', 'classified-listing'),
            'user'       => __('Listing owner name', 'classified-listing'),
            'views'      => __('Views count', 'classified-listing'),
            'price'      => __('Price', 'classified-listing'),
            'price_type' => __('Price type', 'classified-listing'),
            'excerpt'    => __('Short description', 'classified-listing'),
            'featured'   => __('Feature Label', 'classified-listing'),
            'new'        => __('New Label', 'classified-listing'),
        );

        return apply_filters('rtcl_get_listing_display_options', $options);
    }
}