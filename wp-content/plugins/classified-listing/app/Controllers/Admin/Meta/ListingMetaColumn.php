<?php

namespace Rtcl\Controllers\Admin\Meta;


use Rtcl\Helpers\Functions;
use Rtcl\Models\Payment;
use Rtcl\Models\RtclEmail;
use Rtcl\Resources\Options;

class ListingMetaColumn
{

    public function __construct() {
        add_action('manage_edit-' . rtcl()->post_type . '_columns', array($this, 'listing_get_columns'));
        add_action('manage_' . rtcl()->post_type . '_posts_custom_column',
            array($this, 'listing_column_content'), 10, 2);
        add_action('restrict_manage_posts', array($this, 'restrict_manage_posts'));
        add_action('transition_post_status', array($this, 'transition_post_status'), 10, 3);
        add_action('before_delete_post', array($this, 'before_delete_post'));
        add_action('parse_query', array($this, 'parse_query'));

    }

    function listing_get_columns($columns) {
        $new_columns = array(
            'views'       => __('Views', 'classified-listing'),
            'featured'    => __('Featured', 'classified-listing'),
            '_top'        => __('Top', 'classified-listing'),
            'posted_date' => __('Posted Date', 'classified-listing'),
            'expiry_date' => __('Expires on', 'classified-listing'),
            'status'      => __('Status', 'classified-listing')
        );

        unset($columns['date']);

        $taxonomy_column = 'taxonomy-' . rtcl()->location;

        return Functions::array_insert_after($taxonomy_column, $columns, $new_columns);
    }

    function listing_column_content($column, $post_id) {

        switch ($column) {
            case 'views' :
                echo absint(get_post_meta($post_id, '_views', true));
                break;
            case 'featured' :
                $value = get_post_meta($post_id, 'featured', true);
                echo '<span class="rtcl-tick-cross">' . ($value == 1 ? '&#x2713;' : '&#x2717;') . '</span>';
                break;
            case '_top' :
                $value = get_post_meta($post_id, '_top', true);
                echo '<span class="rtcl-tick-cross">' . ($value == 1 ? '&#x2713;' : '&#x2717;') . '</span>';
                break;
            case 'posted_date' :
                printf(_x('%s ago', '%s = human-readable time difference', 'classified-listing'),
                    human_time_diff(get_the_time('U', $post_id), current_time('timestamp')));
                break;
            case 'expiry_date' :
                $never_expires = get_post_meta($post_id, 'never_expires', true);

                if (!empty($never_expires)) {
                    _e('Never Expires', 'classified-listing');
                } else {
                    $expiry_date = get_post_meta($post_id, 'expiry_date', true);

                    if (!empty($expiry_date)) {
                        echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'),
                            strtotime($expiry_date));
                    } else {
                        echo '-';
                    }
                }
                break;
            case 'status' :
                $listing_status = get_post_meta($post_id, 'listing_status', true);
                $listing_status = (empty($listing_status) || 'post_status' == $listing_status) ? get_post_status($post_id) : $listing_status;
                $status_list = Options::get_status_list();
                echo !empty($status_list[$listing_status]) ? $status_list[$listing_status] : "-";
                break;
        }
    }

    public function restrict_manage_posts() {

        global $typenow, $wp_query;

        if (rtcl()->post_type == $typenow) {

            // Restrict by location
            wp_dropdown_categories(array(
                'show_option_none'  => __("All Locations", 'classified-listing'),
                'option_none_value' => "",
                'taxonomy'          => rtcl()->location,
                'name'              => 'rtcl_location',
                'orderby'           => 'name',
                'selected'          => isset($wp_query->query['rtcl_location']) ? $wp_query->query['rtcl_location'] : '',
                'hierarchical'      => true,
                'depth'             => 3,
                'show_count'        => false,
                'hide_empty'        => false,
            ));

            // Restrict by category
            wp_dropdown_categories(array(
                'show_option_none'  => __("All Categories", 'classified-listing'),
                'option_none_value' => 0,
                'taxonomy'          => rtcl()->category,
                'name'              => 'rtcl_category',
                'orderby'           => 'name',
                'id'                => 'rtcl_category_listing',
                'selected'          => isset($wp_query->query['rtcl_category']) ? $wp_query->query['rtcl_category'] : '',
                'hierarchical'      => true,
                'depth'             => 3,
                'show_count'        => false,
                'hide_empty'        => false,
            ));

            // Restrict by featured
            $payment_settings = Functions::get_option('rtcl_payment_settings');
            if (!empty($payment_settings['payment']) && $payment_settings['payment'] == 1) {
                $featured = isset($_GET['featured']) ? $_GET['featured'] : 0;
                echo '<select name="featured">';
                printf('<option value="%d"%s>%s</option>', 0, selected(0, $featured, false),
                    __("All listings", 'classified-listing'));
                printf('<option value="%d"%s>%s</option>', 1, selected(1, $featured, false),
                    __("Featured only", 'classified-listing'));
                echo '</select>';

            }
            $stat = isset($_GET['post_status']) ? $_GET['post_status'] : "all";
            if ("trash" !== $stat) {
                echo '<select name="post_status">';
                $status_list = Options::get_status_list(true);
                printf('<option value="%s">%s</option>', 'all',
                    __("All Status", 'classified-listing'));
                foreach ($status_list as $key => $status) {
                    $slt = $key == $stat ? " selected" : null;
                    printf('<option value="%s"%s>%s</option>', $key, $slt, $status);
                }
                echo '</select>';
            }

        }

    }

    public function transition_post_status($new_status, $old_status, $post) {

        if (rtcl()->post_type !== $post->post_type) {
            return;
        }

        // Check if we are transitioning from pending to publish
        if ('pending' == $old_status && 'publish' == $new_status) {

            try {
                Functions::apply_payment_pricing($post->ID);
            } catch (\Exception $e) {
            }
            if (Functions::get_option_item('rtcl_email_settings', 'notify_users', 'listing_published', 'multi_checkbox')) {
                rtcl()->mailer()->emails['Listing_Published_Email_To_Owner']->trigger($post->ID);
            }

        }

        // Check if we are transitioning from private to publish
        if ('private' == $old_status && 'publish' == $new_status) {

            // TODO : If need some data

        }

        // Check if we are transitioning from private to publish
        if ('draft' == $old_status && 'publish' == $new_status) {

            // TODO : If need some data

        }

    }

    function before_delete_post($post_id) {
        global $post_type;

        if (rtcl()->post_type != $post_type) {
            return;
        }

        $children = get_children(array(
            'post_parent'    => $post_id,
            'post_type'      => 'attachment',
            'posts_per_page' => -1,
            'post_status'    => 'inherit'
        ));

        if (!empty($children)) {
            foreach ($children as $child) {
                wp_delete_attachment($child->ID, true);
            }
        }
    }

    public function parse_query($query) {

        global $pagenow, $post_type;

        if ('edit.php' == $pagenow && rtcl()->post_type == $post_type) {

            // Convert location id to taxonomy term in query
            if (isset($query->query_vars['rtcl_location']) && ctype_digit($query->query_vars['rtcl_location']) && $query->query_vars['rtcl_location'] != 0) {

                $term = get_term_by('id', $query->query_vars['rtcl_location'],
                    'rtcl_location');
                $query->query_vars['rtcl_location'] = $term->slug;
            }

            // Convert category id to taxonomy term in query
            if (isset($query->query_vars['rtcl_category']) && ctype_digit($query->query_vars['rtcl_category']) && $query->query_vars['rtcl_category'] != 0) {

                $term = get_term_by('id', $query->query_vars['rtcl_category'],
                    'rtcl_category');
                $query->query_vars['rtcl_category'] = $term->slug;

            }

            // Set featured meta in query
            if (isset($_GET['featured']) && 1 == $_GET['featured']) {
                $query->query_vars['meta_key'] = 'featured';
                $query->query_vars['meta_value'] = 1;
            }

        }

    }

}