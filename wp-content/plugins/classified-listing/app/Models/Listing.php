<?php

namespace Rtcl\Models;


use Rtcl\Controllers\RtclPublic;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

class Listing
{

    protected $id;
    protected $listing;
    protected $status;
    protected $type;
    protected $post_date;
    protected $post_content;
    protected $user_id;
    protected $moderation_settings = array();
    protected $general_settings = array();
    protected $misc_settings = array();
    protected $page_settings = array();
    protected $categories;
    protected $locations;
    protected $price_units = null;
    protected $price_unit;
    protected $price_type;

    function __construct($Listing_id) {
        $listing = get_post($Listing_id);
        if (is_object($listing) && $listing->post_type == rtcl()->post_type) {
            $this->listing = $listing;
            $this->id = $listing->ID;
            $this->status = $listing->post_status;
            $this->post_date = $listing->post_date;
            $this->post_content = $listing->post_content;
            $this->user_id = $listing->post_author;
            $this->type = get_post_meta($this->id, 'ad_type', true);
            $this->categories = wp_get_object_terms($this->id, rtcl()->category);
            $this->locations = wp_get_object_terms($this->id, rtcl()->location);
            $this->setTermOrder('location');
        }

    }


    /**
     * By default wp_get_object_terms get all the terms as order by name , need to order them by ancestor order
     *
     * @param string $target_term
     */
    private function setTermOrder($target_term = 'category') {
        $target = $target_term === 'category' ? 'categories' : 'locations';
        $raw_terms = $this->$target;

        if (!empty($raw_terms)) {
            if (1 < count($raw_terms)) {

                $locations = [];
                $parent_id = 0;
                $loop = 0;
                while (count($raw_terms)) {
                    $parent_index = 0;
                    foreach ($raw_terms as $index => $raw_term) {
                        if ($raw_term->parent === $parent_id) {
                            $parent_id = $raw_term->term_id;
                            $parent_index = $index + 1;
                            $locations[] = $raw_term;
                            break;
                        }
                    }
                    if ($parent_id && $parent_index) {
                        unset($raw_terms[$parent_index - 1]);
                    }
                    $loop++;
                    if ($loop > 2 && $parent_id === 0) {
                        break;
                    }
                }
                $this->$target = !empty($locations) ? $locations : $this->$target;
            }
        }

    }

    /**
     * Returns the unique ID for this object.
     *
     * @return int
     * @since  1.0.0
     */
    public function get_id() {
        return $this->id;
    }


    /**
     * @return string
     */
    public function get_owner_id() {
        return $this->user_id;
    }


    /**
     * @return string
     */
    public function get_owner_name() {
        $user = get_userdata($this->user_id);
        if ($user) {
            return $user->display_name;
        }

        return '';
    }

    /**
     * @return string
     */
    public function get_owner_user_name() {
        $user = get_userdata($this->user_id);
        if ($user) {
            return $user->user_login;
        }

        return '';
    }

    /**
     * @return string
     */
    public function get_owner_email() {
        $user = get_userdata($this->user_id);
        if ($user) {
            return $user->user_email;
        }

        return '';
    }

    /**
     *
     */
    public function get_the_title() {
        return apply_filters('rtcl_listing_get_the_title', get_the_title($this->listing));
    }


    /**
     *
     */
    public function get_the_permalink() {
        return apply_filters('rtcl_listing_get_the_permalink', get_the_permalink($this->listing));
    }

    /**
     *
     */
    public function the_title() {
        echo apply_filters('rtcl_the_title', $this->get_the_title());
    }


    /**
     * @return int
     */
    function is_featured() {
        return absint(get_post_meta($this->id, 'featured', true)) ? true : false;
    }

    /**
     * @return int
     */
    function is_top() {
        return absint(get_post_meta($this->id, '_top', true)) ? true : false;
    }


    /**
     * @return boolean
     */
    function is_buy() {
        return $this->type == 'buy' ? true : false;
    }

    /**
     * @return boolean
     */
    function is_sell() {
        return $this->type == 'sell' ? true : false;
    }

    /**
     * @return boolean
     */
    function is_exchange() {
        return $this->type == 'exchange' ? true : false;
    }

    /**
     * @return boolean
     */
    function is_job() {
        return $this->type == 'job' ? true : false;
    }

    /**
     * @return boolean
     */
    function is_to_let() {
        return $this->type == 'to_let' ? true : false;
    }

    /**
     * @return bool
     */
    function is_popular() {
        $this->setModerationSettings();
        $views = (int)get_post_meta($this->id, '_views', true);
        if ($views >= (int)$this->moderation_settings['popular_listing_threshold']) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    function is_new() {

        $this->setModerationSettings();
        $each_hours = 60 * 60 * 24; // seconds in a day
        $s_date1 = strtotime(current_time('mysql')); // seconds for date 1
        $s_date2 = strtotime($this->post_date); // seconds for date 2
        $s_date_diff = abs($s_date1 - $s_date2); // different of the two dates in seconds
        $days = round($s_date_diff / $each_hours); // divided the different with second in a day
        if ($days <= (int)$this->moderation_settings['new_listing_threshold']) {
            return true;
        }

        return false;
    }


    /**
     * @return bool
     */
    function has_price_units() {
        return !empty($this->get_price_units());
    }

    /**
     * @return array|mixed
     */
    function get_price_units() {
        if (is_array($this->price_units)) {
            return $this->price_units;
        }
        $category = null;
        $categories = $this->get_categories();
        if (!empty($this->get_categories()) && !is_wp_error($categories)) {
            if (count($categories) > 1) {
                foreach ($categories as $term) {
                    if ($term->parent) {
                        $category = $term;
                    }
                }
            } else {
                $category = $categories[0];
            }
        }
        if ($category) {
            $this->price_units = get_term_meta($category->term_id, '_rtcl_price_units');
            if (empty($this->price_units)) {
                if ($category->parent) {
                    $this->price_units = get_term_meta($category->parent, '_rtcl_price_units');
                }
            }
        }

        return $this->price_units;
    }

    function get_price_type() {
        if ($this->price_type) {
            return $this->price_type;
        }

        $price_type = get_post_meta($this->get_id(), 'price_type', true);
        $this->price_type = $price_type ? $price_type : 'regular';

        return $this->price_type;
    }

    function get_price_unit() {
        if ($this->price_unit) {
            return $this->price_unit;
        }

        return get_post_meta($this->get_id(), '_rtcl_price_unit', true);
    }


    function has_phone() {
        if (get_post_meta($this->id, 'phone', true)) {
            return true;
        }

        return false;

    }

    function has_location() {
        return !empty($this->locations) ? true : false;
    }

    function has_category() {
        return !empty($this->categories) ? true : false;
    }

    function can_edit() {
        if (get_current_user_id() == $this->user_id && in_array($this->status, array(
                'publish',
                'draft',
                'rtcl-reviewed'
            ))) {
            return true;
        }

        return false;
    }

    function can_delete() {
        if (get_current_user_id() == $this->user_id) {
            return true;
        }

        return false;
    }

    function can_show_new() {
        $this->setModerationSettings();
        $display_option = is_singular(rtcl()->post_type) ? 'display_options_detail' : 'display_options';

        return !empty($this->moderation_settings[$display_option]) && in_array('new',
            $this->moderation_settings[$display_option]) ? true : false;
    }

    function can_show_top() {
        $this->setModerationSettings();
        $display_option = is_singular(rtcl()->post_type) ? 'display_options_detail' : 'display_options';

        return !empty($this->moderation_settings[$display_option]) && in_array('top',
            $this->moderation_settings[$display_option]) ? true : false;
    }

    function can_show_popular() {
        $this->setModerationSettings();
        $display_option = is_singular(rtcl()->post_type) ? 'display_options_detail' : 'display_options';

        return !empty($this->moderation_settings[$display_option]) && in_array('popular',
            $this->moderation_settings[$display_option]) ? true : false;
    }

    function can_show_featured() {
        $this->setModerationSettings();
        $display_option = is_singular(rtcl()->post_type) ? 'display_options_detail' : 'display_options';

        return !empty($this->moderation_settings[$display_option]) && in_array('featured',
            $this->moderation_settings[$display_option]) ? true : false;
    }

    function can_show_date() {
        $this->setModerationSettings();
        $display_option = is_singular(rtcl()->post_type) ? 'display_options_detail' : 'display_options';

        return (!empty($this->moderation_settings[$display_option]) && in_array('date',
                $this->moderation_settings[$display_option])) ? true : false;
    }

    function can_show_category() {
        $this->setModerationSettings();
        $display_option = is_singular(rtcl()->post_type) ? 'display_options_detail' : 'display_options';

        return (!empty($this->moderation_settings[$display_option]) && in_array('category',
                $this->moderation_settings[$display_option])) ? true : false;
    }

    function can_show_location() {
        $this->setModerationSettings();
        $display_option = is_singular(rtcl()->post_type) ? 'display_options_detail' : 'display_options';

        return (!empty($this->moderation_settings[$display_option]) && in_array('location',
                $this->moderation_settings[$display_option])) ? true : false;
    }

    function can_show_views() {
        $this->setModerationSettings();
        $display_option = is_singular(rtcl()->post_type) ? 'display_options_detail' : 'display_options';

        return (!empty($this->moderation_settings[$display_option]) && in_array('views',
                $this->moderation_settings[$display_option])) ? true : false;
    }

    function can_show_user() {
        $this->setModerationSettings();
        $display_option = is_singular(rtcl()->post_type) ? 'display_options_detail' : 'display_options';

        return (!empty($this->moderation_settings[$display_option]) && in_array('user',
                $this->moderation_settings[$display_option])) ? true : false;
    }

    function can_show_excerpt() {

        $this->setModerationSettings();

        return !empty($this->moderation_settings['display_options']) && in_array('excerpt',
            $this->moderation_settings['display_options']) ? true : false;
    }

    function can_show_price() {

        $this->setModerationSettings();
        $display_option = is_singular(rtcl()->post_type) ? 'display_options_detail' : 'display_options';

        return ((!empty($this->moderation_settings[$display_option]) && !in_array('price',
                    $this->moderation_settings[$display_option])) || $this->is_job() || Functions::is_price_disabled()) ? false : true;
    }

    function has_map() {

        if (empty($this->moderation_settings)) {
            $this->setModerationSettings();
        }

        $hide_map = get_post_meta($this->id, 'hide_map', true);

        if (!empty($this->moderation_settings['has_map']) && $this->moderation_settings['has_map'] == 'yes' && !$hide_map) {
            return true;
        }

        return false;

    }

    function has_thumbnail() {
        return (has_post_thumbnail($this->id) || !empty(Functions::get_listing_image_ids($this->id)));
    }

    function get_new_label_text() {
        $this->setModerationSettings();

        return !empty($this->moderation_settings['new_listing_label']) ? $this->moderation_settings['new_listing_label'] : __("New", "classified-listing");
    }

    function get_popular_label_text() {
        $this->setModerationSettings();

        return !empty($this->moderation_settings['popular_listing_label']) ? $this->moderation_settings['popular_listing_label'] : __("Popular", "classified-listing");
    }

    function get_top_label_text() {
        $this->setModerationSettings();

        return !empty($this->moderation_settings['listing_top_label']) ? $this->moderation_settings['listing_top_label'] : __("Top", "classified-listing");
    }

    function get_featured_label_text() {
        $this->setModerationSettings();

        return !empty($this->moderation_settings['listing_featured_label']) ? $this->moderation_settings['listing_featured_label'] : __("Featured", "classified-listing");
    }

    function get_view_counts() {
        return absint(get_post_meta($this->id, '_views', true));
    }

    function get_label_class() {
        $class = '';
        if ($this->is_top()) {
            $class .= " is-top";
        }
        if ($this->is_featured()) {
            $class .= " is-featured";
        }
        if ($this->is_popular()) {
            $class .= " is-popular";
        }
        if ($this->is_new()) {
            $class .= " is-new";
        }
        if ($this->type) {
            $class .= " is-" . $this->type;
        }

        return $class;
    }

    /**
     * @return mixed
     */
    function get_type() {
        return $this->type;
    }

    function the_label_class() {
        echo esc_attr($this->get_label_class());
    }


    function the_labels($echo = true) {

        if (!$echo) {
            return Functions::get_template_html("listings/single/labels");
        }

        Functions::get_template("listings/single/labels");
    }

    /**
     * @param string $size
     *
     * @return null|string
     */
    function get_the_thumbnail($size = 'rtcl-thumbnail') {
        $thumb_id = null;
        if (has_post_thumbnail($this->id)) {
            $thumb_id = get_post_thumbnail_id($this->id);
        } else {
            $images = Functions::get_listing_image_ids($this->id);
            if (!empty($images)) {
                $images = array_slice($images, 0, 1);
                $thumb_id = $images[0]->ID;
            } else {
                $thumb_id = Functions::get_option_item('rtcl_misc_settings', 'placeholder_image', null, 'number');
            }
        }
        if ($thumb_id) {
            $image = wp_get_attachment_image($thumb_id, $size, false, array("class" => "rtcl-thumbnail"));
        } else {
            $image = sprintf('<img src="%s" alt="%s">', esc_url(Functions::get_default_placeholder_url()), esc_attr($this->get_the_title()));
        }

        return apply_filters('get_the_thumbnail', $image, $this->id);
    }

    /**
     * @param string $size
     *
     * @return null|string
     */
    function the_thumbnail($size = 'rtcl-thumbnail') {

        echo apply_filters('the_thumbnail', $this->get_the_thumbnail($size), $this->id);
    }

    /**
     * @param bool $gmt
     *
     * @return string
     */
    function get_the_time($gmt = false) {
        return sprintf(__('%s ago', 'classified-listing'), human_time_diff(get_post_time('U', $gmt, $this->listing, false), current_time('timestamp', $gmt)));
    }

    /**
     * @param bool $gmt
     *
     * @return string
     */
    function the_time($gmt = false) {
        echo $this->get_the_time($gmt);
    }

    /**
     * @return string Author name
     */
    function get_author_name() {
        $authorData = get_user_by('id', $this->listing->post_author);
        $author_name = '';
        if (is_object($authorData)) {
            $author[] = $authorData->first_name;
            $author[] = $authorData->last_name;
            $author = array_filter($author);
            if (!empty($author)) {
                $author_name = implode(' ', $author);
            } else {
                $author_name = $authorData->display_name;
            }
        }

        return apply_filters('rtcl_listing_get_author_name', $author_name, $authorData);
    }

    /**
     * @return string Author name
     */
    function the_author() {
        echo apply_filters('rtcl_listing_the_author', $this->get_author_name(), $this);
    }

    function the_meta() {
        Functions::get_template("listings/single/meta");
    }

    function the_excerpt() {
        echo apply_filters('rtcl_listing_the_excerpt', get_the_excerpt($this->listing));
    }

    function the_content() {
        remove_action('the_content', array(RtclPublic::class, 'the_content'), 20);
        echo apply_filters('rtcl_listing_the_content', apply_filters('the_content', $this->post_content));
        add_action('the_content', array(RtclPublic::class, 'the_content'), 20);
    }

    /**
     * @param bool $echo
     * @param bool $link
     * @param      $address
     *
     * @return string
     */
    function the_locations($echo = true, $link = false, $address = false) {
        $html = '';

        if (!empty($this->locations)) {
            $loc = array();
            foreach ($this->locations as $location) {
                if ($link) {
                    $loc[] = sprintf('<a href="%s">%s</a>',
                        Link::get_location_page_link($location),
                        $location->name
                    );
                } else {
                    $loc[] = $location->name;
                }

            }
            $loc = array_reverse($loc);
            $html = implode(', ', $loc);
        }
        $getAddress = get_post_meta($this->id, 'address', true);
        if ($address && $getAddress) {
            $html .= sprintf("<div class='loc-address'>%s</div>", esc_textarea($getAddress));
        }

        if (!$echo) {
            return $html;
        }
        echo $html;
    }

    /**
     * @return array|\WP_Error
     */
    function get_locations() {
        return $this->locations;
    }

    /**
     * @param bool $echo
     * @param bool $link
     *
     * @return string
     */
    function the_categories($echo = true, $link = false) {
        $html = '';

        if (!empty($this->categories)) {
            $loc = array();
            foreach ($this->categories as $category) {
                if ($link) {
                    $loc[] = sprintf('<a href="%s">%s</a>',
                        Link::get_category_page_link($category),
                        $category->name
                    );
                } else {
                    $loc[] = $category->name;
                }
            }
            $html = implode(', ', $loc);
        }

        if (!$echo) {
            return $html;
        }
        echo $html;
    }

    /**
     * @return array|\WP_Error
     */
    function get_categories() {
        return $this->categories;
    }

    /**
     * @return array|bool|null|object|\WP_Error
     */
    function get_parent_category() {
        $categories = $this->get_categories();
        if (!empty($categories)) {
            $parent = get_ancestors($categories[0]->term_id, rtcl()->category);
            if (empty($parent)) {
                $parent[] = $categories[0]->term_id;
            }
            $parent = array_pop($parent);
            if ($parent) {
                return get_term($parent, rtcl()->category);
            }
        }

        return false;
    }

    /**
     * @return mixed|void
     */
    function get_the_price() {
        $price = Functions::get_formatted_price(get_post_meta($this->id, 'price', true));

        $price = apply_filters('rtcl_listing_get_the_price', $price, $this->id);

        /** @var TYPE_NAME $price */
        return $price;
    }

    /**
     * @return mixed|void
     */
    function the_price() {
        $price = apply_filters('rtcl_listing_the_price', $this->get_the_price(), $this->id);
        echo $price;
    }

    function the_gallery() {
        if (!Functions::is_gallery_disabled()) {
            $images = Functions::get_listing_image_ids($this->id);
            Functions::get_template("listings/single/gallery", compact('images'));
        }
    }

    function the_custom_fields() {

        $data = null;
        $category_id = Functions::get_term_child_id_for_a_post($this->categories);

        // Get custom fields
        $custom_field_ids = Functions::get_custom_field_ids($category_id);


        $fields = array();
        if (!empty($custom_field_ids)) {
            $args = array(
                'post_type'        => rtcl()->post_type_cf,
                'post_status'      => 'publish',
                'posts_per_page'   => -1,
                'post__in'         => $custom_field_ids,
                'orderby'          => 'menu_order',
                'order'            => 'ASC',
                'suppress_filters' => false
            );

            $fields = get_posts($args);
        }

        Functions::get_template("listings/single/custom-fields", array(
            'fields'     => $fields,
            'listing_id' => $this->id
        ));
    }


    function the_actions() {
        Functions::get_template("listings/single/actions", array(
            'can_add_favourites' => Functions::get_option_item('rtcl_moderation_settings', 'has_favourites', '', 'checkbox') ? true : false,
            'can_report_abuse'   => Functions::get_option_item('rtcl_moderation_settings', 'has_report_abuse', '', 'checkbox') ? true : false,
            'social'             => $this->the_social_share(false),
            'listing_id'         => $this->id
        ));
    }

    /**
     * @param bool $echo
     *
     * @return null|string
     */
    function the_social_share($echo = true) {
        global $post;
        $html = null;
        $this->setMiscSettings();
        $this->setPageSettings();
        $page = 'none';

        if (rtcl()->post_type == $post->post_type) {
            $page = 'listing';
        }

        if ($post->ID == $this->page_settings['listings']) {
            $page = 'listings';
        }

        if (!empty($this->misc_settings['social_pages']) && in_array($page, $this->misc_settings['social_pages'])) {

            // Get current page URL
            $url = Link::get_current_url();

            // Get current page title
            $title = get_the_title();

            if (get_query_var('rtcl_location') || get_query_var('rtcl_category')) {

                $title = Functions::get_single_term_title();

            }

            $title = str_replace(' ', '%20', $title);

            // Get Post Thumbnail
            $thumbnail = '';

            if ('listing' == $page) {
                $images = get_post_meta($post->ID, 'images', true);

                if (!empty($images)) {
                    $image_attributes = wp_get_attachment_image_src($images[0], 'full');
                    $thumbnail = is_array($image_attributes) ? $image_attributes[0] : '';
                }
            } else {
                $image_attributes = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
                $thumbnail = is_array($image_attributes) ? $image_attributes[0] : '';
            }
            if (!empty($this->misc_settings['social_services'])) {
                $html = Functions::get_template_html("listings/single/social-share", array(
                    'misc_settings' => $this->misc_settings,
                    'title'         => $title,
                    'url'           => rawurldecode($url),
                    'thumbnail'     => $thumbnail
                ));
            }
        }
        if ($echo) {
            echo $html;
        } else {
            return $html;
        }
    }


    function the_related_listings() {

        $this->setGeneralSettings();

        $category = !empty($this->categories) ? end($this->categories)->term_id : 0;
        $posts_per_page = !empty($this->general_settings['related_posts_per_page']) ? absint($this->general_settings['related_posts_per_page']) : 4;
        $query_args = array(
            'post_type'      => rtcl()->post_type,
            'post_status'    => 'publish',
            'posts_per_page' => apply_filters('rtcl_listing_related_posts_per_page', $posts_per_page),
            'post__not_in'   => array($this->id)
        );
        if ($category) {
            $this->setGeneralSettings();

            $query_args['tax_query'] = [
                [
                    'taxonomy'         => rtcl()->category,
                    'field'            => 'term_id',
                    'terms'            => $category,
                    'include_children' => isset($this->general_settings['include_results_from']) && in_array('child_categories',
                        $this->general_settings['include_results_from']) ? true : false
                ]
            ];
        }
        $rtcl_related_query = new \WP_Query(apply_filters('rtcl_related_listing_query_arg', $query_args));

        $slider_options = apply_filters('rtcl_related_slider_options', array(
            "margin"       => 15,
            "items"        => 4,
            "tab_items"    => 3,
            "mobile_items" => 1,
            "nav"          => true,
            "dots"         => false,
        ));

        Functions::get_template("listings/single/related-listings", compact('rtcl_related_query', 'slider_options'));
    }

    function the_user_info() {

        $locations = array();

        if (count($this->locations)) {
            foreach ($this->locations as $location) {
                $locations[] = $location->name;
            }
        }
        $locations = array_reverse($locations);
        $phone = get_post_meta($this->id, 'phone', true);
        $email = get_post_meta($this->id, 'email', true);
        $address = get_post_meta($this->id, 'address', true);
        $zipcode = get_post_meta($this->id, 'zipcode', true);
        if ($address && Functions::get_option_item('rtcl_moderation_settings', 'display_options_detail', 'address', 'multi_checkbox')) {
            array_unshift($locations, $address);
        }
        if ($zipcode && Functions::get_option_item('rtcl_moderation_settings', 'display_options_detail', 'zipcode', 'multi_checkbox')) {
            $locations[] = $zipcode;
        }
        $website = get_post_meta($this->id, 'website', true);
        Functions::get_template("listings/single/user-information", array(
            'locations'            => $locations,
            'phone'                => $phone,
            'email'                => $email,
            'has_contact_form'     => Functions::get_option_item('rtcl_moderation_settings', 'has_contact_form', false, 'checkbox'),
            'website'              => $website,
            'listing_id'           => $this->id,
            'email_to_seller_form' => $this->email_to_seller_form(false)
        ));
    }

    /**
     * @param bool $echo
     *
     * @return string
     */
    function email_to_seller_form($echo = true) {
        if ($echo) {
            Functions::get_template("listings/single/email-to-seller-form");
        } else {
            return Functions::get_template_html("listings/single/email-to-seller-form");
        }
    }

    public function the_map() {
        $this->setModerationSettings();
        $latitude = get_post_meta($this->id, 'latitude', true);
        $longitude = get_post_meta($this->id, 'longitude', true);
        Functions::get_template("listings/single/map", array(
            'has_map'   => $this->has_map(),
            'latitude'  => $latitude,
            'longitude' => $longitude
        ));
    }

    public function setGeneralSettings() {
        if (!empty($this->general_settings)) {
            $this->general_settings = Functions::get_option('rtcl_general_settings');
        }
    }

    public function setModerationSettings() {
        if (empty($this->moderation_settings)) {
            $this->moderation_settings = Functions::get_option('rtcl_moderation_settings');
        }
    }

    public function setMiscSettings() {
        if (empty($this->misc_settings)) {
            $this->misc_settings = Functions::get_option('rtcl_misc_settings');
        }
    }

    public function setPageSettings() {
        if (empty($this->page_settings)) {
            $this->page_settings = Functions::get_page_ids();
        }
    }

    public function setSettings() {
        $this->setGeneralSettings();
        $this->setModerationSettings();
        $this->setMiscSettings();
        $this->setPageSettings();
    }

}