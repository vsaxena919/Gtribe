<?php

namespace Rtcl\Widgets;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Models\RtclCFGField;
use Rtcl\Resources\Options;

/**
 * Class Filter
 *
 * @package Rtcl\Widgets
 */
class Filter extends \WP_Widget
{

    protected $widget_slug;
    protected $instance;

    public function __construct() {

        $this->widget_slug = 'rtcl-widget-filter';

        parent::__construct(
            $this->widget_slug,
            __('Classified Listing Filter', 'classified-listing'),
            array(
                'classname'   => 'rtcl ' . $this->widget_slug . '-class',
                'description' => __('Classified listing Filter.', 'classified-listing')
            )
        );

        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles_scripts'), 11);

    }

    /**
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance) {
        $this->instance = $instance;
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $data = array(
            'category_filter' => $this->get_category_filter(),
            'location_filter' => $this->get_location_filter(),
            'ad_type_filter'  => $this->get_type_filter(),
            'price_filter'    => $this->get_price_filter()
        );
        Functions::get_template("widgets/filter", $data);

        echo $args['after_widget'];
    }

    /**
     * @param $settings
     *
     * @return string
     */
    public function get_list($settings) {
        $slug = get_query_var('rtcl_category');
        $slug = $slug ? $slug : get_query_var('rtcl_location');

        $defaults = array(
            'taxonomy'   => rtcl()->category,
            'hide_empty' => $this->instance['hide_empty'],
            'pad_counts' => true,
            'parent'     => 0,
            'show_count' => $this->instance['show_count']
        );
        $args = wp_parse_args($settings, $defaults);


        $terms = get_terms($args['taxonomy'], array(
            'meta_key' => '_rtcl_order',
            'orderby'  => 'meta_value_num',
            'order'    => 'DESC',
            'parent'   => $args['parent']
        ));

        $html = '';

        if (count($terms) > 0) {
            $ulCls = $args['parent'] ? 'sub-list' : 'filter-list';
            $html .= "<ul class='$ulCls'>";

            foreach ($terms as $term) {

                $count = Functions::get_listings_count_by_taxonomy($term->term_id, $args['taxonomy'], $args['pad_counts']);
                if (!empty($args['hide_empty']) && 0 == $count) {
                    continue;
                }
                $children = get_term_children($term->term_id, $args['taxonomy']);
                $cls = $has_arrow = null;
                if (!empty($children)) {
                    $cls = "is-parent has-sub";
                    $has_arrow = "<span class='arrow'><i class='rtcl-icon rtcl-icon-down-open'></i></span>";
                    $cls_open = null;
                    if ($slug) {
                        if ($term->slug === $slug) {
                            $cls_open = " is-open";
                        } else {
                            $temp_term = get_term_by('slug', $slug, $args['taxonomy']);
                            if (!empty($temp_term)) {
                                $ids = get_ancestors($temp_term->term_id, $args['taxonomy']);
                                if (!empty($ids) && in_array($term->term_id, $ids)) {
                                    $cls_open = " is-open";
                                }
                            }
                        }
                    }
                    $cls = $cls . $cls_open;
                }

                $link = '/';
                switch ($args['taxonomy']) {
                    case rtcl()->category:
                        $link = Link::get_category_page_link($term, true);
                        break;
                    case  rtcl()->location:
                        $link = Link::get_location_page_link($term, true);
                        break;
                }

                $html .= "<li class='{$cls}'>";
                $html .= "<a href='{$link}'>";
                $html .= $term->name;
                if (!empty($args['show_count'])) {
                    $html .= ' (' . $count . ')';
                }
                $html .= '</a>' . $has_arrow;
                $args['parent'] = $term->term_id;
                $html .= $this->get_list($args);
                $html .= '</li>';
            }

            $html .= '</ul>';

        }

        return $html;

    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    public function update($new_instance, $old_instance) {

        $instance = $old_instance;

        $instance['title'] = !empty($new_instance['title']) ? strip_tags($new_instance['title']) : '';
        $instance['search_by_category'] = isset($new_instance['search_by_category']) ? 1 : 0;
        $instance['search_by_location'] = isset($new_instance['search_by_location']) ? 1 : 0;
        $instance['search_by_ad_type'] = isset($new_instance['search_by_ad_type']) ? 1 : 0;
        $instance['search_by_price'] = isset($new_instance['search_by_price']) ? 1 : 0;
        $instance['hide_empty'] = isset($new_instance['hide_empty']) ? 1 : 0;
        $instance['show_count'] = isset($new_instance['show_count']) ? 1 : 0;

        return $instance;

    }

    /**
     * @param array $instance
     *
     * @return string|void
     */
    public function form($instance) {

        // Define the array of defaults
        $defaults = array(
            'title'              => __('Filter', 'classified-listing'),
            'search_by_category' => 1,
            'search_by_location' => 1,
            'search_by_ad_type'  => 1,
            'search_by_price'    => 1,
            'hide_empty'         => 0,
            'show_count'         => 1,
        );

        // Parse incoming $instance into an array and merge it with $defaults
        $instance = wp_parse_args(
            (array)$instance,
            $defaults
        );

        // Display the admin form
        include(RTCL_PATH . "views/widgets/filter.php");

    }

    public function get_category_filter() {
        if (!empty($this->instance['search_by_category'])) {

            return sprintf('<div class="rtcl-category-filter ui-accordion-item is-open">
					                <a class="ui-accordion-title">
					                    <span>%s</span>
					                    <span class="ui-accordion-icon rtcl-icon rtcl-icon-anchor"></span>
					                </a>
					                <div class="ui-accordion-content">%s</div>
					            </div>',
                __("Category", "classified-listing"),
                $this->get_list(array('taxonomy' => rtcl()->category))
            );
        }
    }

    public function get_location_filter() {
        if (!empty($this->instance['search_by_location'])) {

            return sprintf('<div class="rtcl-location-filter ui-accordion-item is-open">
					                <a class="ui-accordion-title">
					                    <span>%s</span>
					                    <span class="ui-accordion-icon rtcl-icon rtcl-icon-anchor"></span>
					                </a>
					                <div class="ui-accordion-content">%s</div>
					            </div>',
                __("Location", "classified-listing"),
                $this->get_list(array('taxonomy' => rtcl()->location))
            );
        }
    }

    public function get_type_filter() {
        if (!empty($this->instance['search_by_ad_type']) && !Functions::is_ad_type_disabled()) {
            $filters = !empty($_GET['filters']) ? $_GET['filters'] : array();
            $ad_type = !empty($filters['ad_type']) ? esc_attr($filters['ad_type']) : null;
            $field_html = "<ul class='ui-link-tree is-collapsed'>";
            $ad_types = Functions::get_listing_types();
            if (!empty($ad_types)) {
                foreach ($ad_types as $key => $option) {
                    $checked = ($ad_type == $key) ? " checked " : '';
                    $field_html .= "<li class='ui-link-tree-item ad-type-{$key}'>";
                    $field_html .= "<input id='filters-ad-type-values-{$key}' name='filters[ad_type]' {$checked} value='{$key}' type='radio' class='ui-checkbox filter-submit-trigger'>";
                    $field_html .= "<a href='#' class='filter-submit-trigger'>" . $option . "</a>";
                    $field_html .= "</li>";
                }
            }
            $field_html .= '<li class="is-opener"><span><i class="rtcl-icon rtcl-icon-plus-circled"></i><span class="text">' . __("Show More",
                    "classified-listing") . '</span></span></li>';
            $field_html .= "</ul>";

            return sprintf('<div class="rtcl-listing-type-filter ui-accordion-item is-open">
									                <a class="ui-accordion-title">
									                    <span>%s</span>
									                    <span class="ui-accordion-icon rtcl-icon rtcl-icon-anchor"></span>
									                </a>
									                <div class="ui-accordion-content">%s</div>
									            </div>',
                __("Type", "classified-listing"),
                $field_html
            );
        }
    }

    public function get_price_filter() {
        if (!empty($this->instance['search_by_price'])) {
            $filters = !empty($_GET['filters']) ? $_GET['filters'] : array();
            $fMinValue = !empty($filters['price']['min']) ? esc_attr($filters['price']['min']) : null;
            $fMaxValue = !empty($filters['price']['max']) ? esc_attr($filters['price']['max']) : null;
            $field_html = sprintf('<div class="form-group">
							            <div class="row">
							                <div class="col-md-6 col-xs-6">
							                    <input type="number" name="filters[price][min]" class="form-control" placeholder="%s" value="%s">
							                </div>
							                <div class="col-md-6 col-xs-6">
							                    <input type="number" name="filters[price][max]" class="form-control" placeholder="%s" value="%s">
							                </div>
							                <div class="col-md-12">
							                	<div class="ui-buttons has-expanded"><button class="btn btn-primary">%s</button></div>
											</div>
							            </div>
							        </div>',
                __('min', 'classified-listing'),
                $fMinValue,
                __('max', 'classified-listing'),
                $fMaxValue,
                __("Apply filters", 'classified-listing')
            );

            return sprintf('<div class="rtcl-price-filter ui-accordion-item is-open">
									                <a class="ui-accordion-title">
									                    <span>%s</span>
									                    <span class="ui-accordion-icon rtcl-icon rtcl-icon-anchor"></span>
									                </a>
									                <div class="ui-accordion-content">%s</div>
									            </div>',
                __("Price Range", "classified-listing"),
                $field_html
            );
        }
    }

    public function enqueue_styles_scripts() {

        if (is_active_widget(false, $this->id, $this->id_base, true)) {

            wp_enqueue_style('rtcl-public');

        }

    }
}