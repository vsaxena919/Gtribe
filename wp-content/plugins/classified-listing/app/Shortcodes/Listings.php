<?php

namespace Rtcl\Shortcodes;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Pagination;
use Rtcl\Controllers\Shortcodes;
use Rtcl\Models\RtclCFGField;
use Rtcl\Resources\Options;

class Listings
{

    /**
     * Get the shortcode content.
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string
     */
    public static function get($atts) {
        return Shortcodes::shortcode_wrapper(array(__CLASS__, 'output'), $atts);
    }


    /**
     * Output the shortcode.
     *
     * @param array $atts Shortcode attributes.
     */
    public static function output($atts) {
        Functions::clear_notices();
        $general_settings = Functions::get_option('rtcl_general_settings');
        $moderation_settings = Functions::get_option('rtcl_moderation_settings');
        $atts = shortcode_atts(array(
            'category'             => '',
            'location'             => '',
            'filterby'             => '',
            'authors'              => '',
            'orderby'              => !empty($general_settings['orderby']) ? $general_settings['orderby'] : 'date',
            'order'                => !empty($general_settings['order']) ? $general_settings['order'] : 'DESC',
            'listings_per_page'    => !empty($general_settings['listings_per_page']) ? absint($general_settings['listings_per_page']) : -1,
            'listing_top_per_page' => !empty($moderation_settings['listing_top_per_page']) ? absint($moderation_settings['listing_top_per_page']) : 2,
        ), $atts);


        wp_enqueue_script('rtcl-public');

        // Define the query
        $args = array(
            'post_type'      => rtcl()->post_type,
            'post_status'    => 'publish',
            'posts_per_page' => $atts['listings_per_page'],
            'paged'          => Pagination::get_page_number(),
        );
        if (isset($_GET['q'])) {
            $args['s'] = $_GET['q'];
        }
        if ($atts['authors'] && $authors = explode(',', $atts['authors'])) {
            if (!empty($authors)) {
                $args['author__in'] = $authors;
            }
        }
        $tax_queries = array();
        if ($atts['location']) {
            $locs = explode(',', $atts['location']);
            $tax_queries[] = array(
                'taxonomy'         => rtcl()->location,
                'field'            => 'term_id',
                'terms'            => $locs,
                'include_children' => isset($general_settings['include_results_from']) && in_array('child_locations',
                    $general_settings['include_results_from']) ? true : false,
            );
        } else {
            if ($loc_slug = get_query_var('rtcl_location')) {
                if ($location = get_term_by('slug', $loc_slug, rtcl()->location)) {
                    $tax_queries[] = array(
                        'taxonomy'         => rtcl()->location,
                        'field'            => 'term_id',
                        'terms'            => $location->term_id,
                        'include_children' => isset($general_settings['include_results_from']) && in_array('child_categories',
                            $general_settings['include_results_from']) ? true : false,
                    );
                } else {
                    Functions::add_notice(sprintf(__("Location \"%s\" not found.", "classified-listing"), $loc_slug), 'error');
                }
            }
        }
        if ($atts['category']) {
            $cats = explode(',', $atts['category']);
            $tax_queries[] = array(
                'taxonomy'         => rtcl()->category,
                'field'            => 'term_id',
                'terms'            => $cats,
                'include_children' => isset($general_settings['include_results_from']) && in_array('child_locations',
                    $general_settings['include_results_from']) ? true : false,
            );
        } else {
            if ($cat_slug = get_query_var('rtcl_category')) {
                if ($category = get_term_by('slug', $cat_slug, rtcl()->category)) {
                    $tax_queries[] = array(
                        'taxonomy'         => rtcl()->category,
                        'field'            => 'term_id',
                        'terms'            => $category->term_id,
                        'include_children' => isset($general_settings['include_results_from']) && in_array('child_locations',
                            $general_settings['include_results_from']) ? true : false,
                    );
                } else {
                    Functions::add_notice(sprintf(__("Category \"%s\" not found.", "classified-listing"), $cat_slug), 'error');
                }
            }
        }


        if (!empty($tax_queries)) {
            $args['tax_query'] = (count($tax_queries) > 1) ? array_merge(array('relation' => 'AND'),
                $tax_queries) : $tax_queries;
        }

        $meta_queries = array();

        $filters = isset($_GET['filters']) ? (array)$_GET['filters'] : array();

        if (!empty($filters)) {

            // Price filter
            if (!empty($filters['price'])) {

                $price = array_filter($filters['price']);

                if ($n = count($price)) {

                    if (2 == $n) {
                        $meta_queries[] = array(
                            'key'     => 'price',
                            'value'   => array_map('intval', $price),
                            'type'    => 'NUMERIC',
                            'compare' => 'BETWEEN'
                        );
                    } else {
                        if (empty($price['min'])) {
                            $meta_queries[] = array(
                                'key'     => 'price',
                                'value'   => (int)$price['max'],
                                'type'    => 'NUMERIC',
                                'compare' => '<='
                            );
                        } else {
                            $meta_queries[] = array(
                                'key'     => 'price',
                                'value'   => (int)$price['min'],
                                'type'    => 'NUMERIC',
                                'compare' => '>='
                            );
                        }
                    }

                }

            }
            unset($filters['price']);

            // Ad type filtering
            if (!empty($filters['ad_type']) && in_array($filters['ad_type'], array_keys(Functions::get_listing_types())) && !Functions::is_ad_type_disabled()) {
                $ad_type = $filters['ad_type'];

                $meta_queries[] = array(
                    'key'     => 'ad_type',
                    'value'   => $ad_type,
                    'compare' => '='
                );

            }

            $cf = array_filter($filters);
            if (!empty($cf)) {
                foreach ($cf as $key => $values) {
                    $field_id = absint(str_replace("_field_", '', $key));
                    $field = new RtclCFGField($field_id);
                    if ($field_id && is_object($field)) {
                        if (is_array($values)) {
                            if ($field->getType() === 'number') {
                                $values = array_filter($values);
                                if ($n = count($values)) {
                                    if (2 == $n) {
                                        $meta_queries[] = array(
                                            'key'     => $key,
                                            'value'   => array_map('intval', $values),
                                            'type'    => 'NUMERIC',
                                            'compare' => 'BETWEEN'
                                        );
                                    } else {
                                        if (empty($values['min'])) {
                                            $meta_queries[] = array(
                                                'key'     => $key,
                                                'value'   => (int)$values['max'],
                                                'type'    => 'NUMERIC',
                                                'compare' => '<='
                                            );
                                        } else {
                                            $meta_queries[] = array(
                                                'key'     => $key,
                                                'value'   => (int)$values['min'],
                                                'type'    => 'NUMERIC',
                                                'compare' => '>='
                                            );
                                        }
                                    }

                                }
                            } else if (in_array($field->getType(), array('select', 'radio'))) {
                                if (is_array($values) && count($values)) {
                                    $values = $values[0];
                                }
                                $meta_queries[] = array(
                                    'key'     => $key,
                                    'value'   => sanitize_text_field($values),
                                    'compare' => 'LIKE'
                                );
                            } else if ($field->getType() === 'checkbox') {

                                if (count($values) > 1) {

                                    $sub_meta_queries = array();

                                    foreach ($values as $value) {
                                        $sub_meta_queries[] = array(
                                            'key'     => $key,
                                            'value'   => sanitize_text_field($value),
                                            'compare' => 'LIKE'
                                        );
                                    }

                                    $meta_queries[] = array_merge(array('relation' => 'OR'), $sub_meta_queries);

                                } else {
                                    $meta_queries[] = array(
                                        'key'     => $key,
                                        'value'   => sanitize_text_field($values[0]),
                                        'compare' => 'LIKE'
                                    );
                                }
                            }
                        } else {
                            $operator = (in_array($field->getType(),
                                array('text', 'textarea', 'url'))) ? 'LIKE' : '=';
                            $meta_queries[] = array(
                                'key'     => $key,
                                'value'   => sanitize_text_field($values),
                                'compare' => $operator
                            );
                        }
                    }

                }
            }
        }

        $current_order = Pagination::get_listings_current_order($atts['orderby'] . '-' . $atts['order']);

        switch ($current_order) {
            case 'title-asc' :
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'title-desc' :
                $args['orderby'] = 'title';
                $args['order'] = 'DESC';
                break;
            case 'date-asc' :
                $args['orderby'] = 'date';
                $args['order'] = 'ASC';
                break;
            case 'date-desc' :
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
            case 'price-asc' :
                $args['meta_key'] = 'price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'price-desc' :
                $args['meta_key'] = 'price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'views-asc' :
                $args['meta_key'] = '_views';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'views-desc' :
                $args['meta_key'] = '_views';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'rand' :
                $args['orderby'] = 'rand';
                break;
        }

        $count_meta_queries = count($meta_queries);
        if ($count_meta_queries) {
            $args['meta_query'] = ($count_meta_queries > 1) ? array_merge(array('relation' => 'AND'),
                $meta_queries) : $meta_queries;
        }

        $rtcl_query = new \WP_Query(apply_filters('rtcl_listings_shortcode_args', $args));

        $data = array(
            'rtcl_query' => $rtcl_query,
        );

        Functions::print_notices();
        // Process output
        Functions::get_template("listings/listings", $data);
    }

}