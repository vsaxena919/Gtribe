<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('FP_GF_Order_Related_Functions_frontend')) {

    final class FP_GF_Order_Related_Functions_frontend {

        public static function init() {
            //Out of stock check
            add_action('woocommerce_single_product_summary', array('FP_GF_Order_Related_Functions', 'out_of_stock_galaxy_funder_qty'));
            //Woocommerce thank you process
            add_filter('woocommerce_get_availability', array(__CLASS__, 'crowdfunding_change_out_of_stock_caption'));
            //wp head stock checker
            add_action('wp_head', array(__CLASS__, 'new_stock_checker'));
        }

        

        public static function crowdfunding_change_out_of_stock_caption($availability) {
            global $post;
            $checkvalue = get_post_meta($post->ID, '_crowdfundingcheckboxvalue', true);
            if ($checkvalue == 'yes') {
                $checkstatus = get_post_meta($post->ID, '_stock_status', true);
                if ($checkstatus == 'outofstock') {
                    $availability['availability'] = get_option('cf_outofstock_label');
                } else {
                    $availability['availability'] = '';
                }
            }
            return $availability;
        }

        public static function new_stock_checker() {

            global $post;
            if (is_product() || is_page()) {
                $newid = $post->ID;
                $getdate = FP_GF_Common_Functions::date_with_format();
                $gethour = date("h");
                $getminutes = date("i");
                $fromdate = get_post_meta($post->ID, '_crowdfundingfromdatepicker', true);
                $todate = get_post_meta($post->ID, '_crowdfundingtodatepicker', true);
                $tohours = get_post_meta($post->ID, '_crowdfundingtohourdatepicker', true);
                $tominutes = get_post_meta($post->ID, '_crowdfundingtominutesdatepicker', true);
                $fromhours = get_post_meta($post->ID, '_crowdfundingfromhourdatepicker', true);
                $fromminutes = get_post_meta($post->ID, '_crowdfundingfromminutesdatepicker', true);
                $local_current_time = strtotime(FP_GF_Common_Functions::date_time_with_format());

                if ($fromdate != '') {
                    if ($fromhours == '' || $fromminutes == '') {
                        $fromdate = $fromdate . "23:59:59";
                    } else {
                        $time = $fromhours . ':' . $fromminutes . ':' . '00';
                        $fromdate = $fromdate . $time;
                    }
                } else {
                    if ($tohours == '' || $tominutes == '') {
                        $fromdate = $getdate;
                    } else {
                        $fromdate = $getdate;
                        $fromhour = $gethour;
                        $fromminutes = $getminutes;
                    }
                    fp_gf_update_campaign_metas($post->ID, '_crowdfundingfromdatepicker', $getdate);
                    fp_gf_update_campaign_metas($post->ID, '_crowdfundingfromhourdatepicker', $gethour);
                    fp_gf_update_campaign_metas($post->ID, '_crowdfundingfromminutesdatepicker', $getminutes);
                }
                if ($tohours != '' || $tominutes != '') {
                    $time = $tohours . ':' . $tominutes . ':' . '00';
                    $datestr = $todate . $time; //Your date
                } else {
                    $datestr = $todate . "23:59:59";
                } //Your date
                $date = strtotime($datestr); //Converted to a PHP date (a second count)
                $checkvalue = get_post_meta($newid, '_crowdfundingcheckboxvalue', true);

                if ($checkvalue == 'yes') {
                    $gettargetendselection = get_post_meta($post->ID, '_target_end_selection', true);
                    if ($gettargetendselection == '1') {
                        if ((strtotime($fromdate) == strtotime($getdate) || strtotime($fromdate) < strtotime($getdate)) && ($date >= $local_current_time)) {
                            
                        } elseif ((strtotime($fromdate) > strtotime($getdate)) && ($date >= $local_current_time)) {
                            fp_gf_update_campaign_metas($post->ID, '_stock_status', 'instock');
                        } else {
                            fp_gf_update_campaign_metas($post->ID, '_stock_status', 'outofstock');
                        }
                    }
                    if ($gettargetendselection == '2') {
                        
                    }

                    if ($gettargetendselection == '3') {
                        $targetprice1 = get_post_meta($post->ID, '_crowdfundinggettargetprice', true);
                        $targetprice = fp_wpml_multi_currency($targetprice1);
                        $totalprice = fp_wpml_multi_currency(get_post_meta($post->ID, '_crowdfundingtotalprice', true));
                        if (($totalprice == $targetprice) || ($totalprice > $targetprice)) {
                            fp_gf_update_campaign_metas($post->ID, '_stock_status', 'outofstock');
                        }
                    }
                    if ($gettargetendselection == '4') {
                        $targetprice1 = get_post_meta($post->ID, '_crowdfundinggettargetprice', true);
                        $targetprice = fp_wpml_multi_currency($targetprice1);
                        $totalprice = fp_wpml_multi_currency(get_post_meta($post->ID, '_crowdfundingtotalprice', true));
                        $fromdatestrtotime = strtotime($fromdate);
                        $todatestrtotime = strtotime($datestr);
                        if (($totalprice == $targetprice) || ($totalprice > $targetprice) || ($todatestrtotime < $local_current_time)) {
                            fp_gf_update_campaign_metas($post->ID, '_stock_status', 'outofstock');
                        }
                    }

                    if ($gettargetendselection == '5') {
                        $targetquantity = get_post_meta($post->ID, '_crowdfundingquantity', true);
                    }
                }
            }
        }

    }

    FP_GF_Order_Related_Functions_frontend::init();
}