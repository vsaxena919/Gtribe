<?php

namespace Rtcl\Shortcodes;


use Rtcl\Helpers\Functions;

class ListingForm {

    public static function output($atts)
    {

        if (!is_user_logged_in()) {
            Functions::login_form();

            return;
        }

        do_action('rtcl_before_add_new_listing_condition');

        if (Functions::notice_count('error')) {
            Functions::print_notices();

            return;
        }

        $post_id = 'edit' == get_query_var('rtcl_action') ? get_query_var('rtcl_listing_id', null) : null;
        $has_permission = true;

        if ($post_id > 0 && !Functions::current_user_can('edit_' . rtcl()->post_type, $post_id)) {
            $has_permission = false;
        } else if (!Functions::current_user_can('add_' . rtcl()->post_type)) {
            $has_permission = false;
        }

        if (!$has_permission) {
            Functions::add_notice(__('You do not have sufficient permissions to access this page.', 'classified-listing'), 'error');
        }

        Functions::get_template("listing-form/form", array('post_id' => $post_id));

    }

}