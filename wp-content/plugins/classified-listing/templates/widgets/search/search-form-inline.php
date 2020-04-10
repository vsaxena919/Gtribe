<?php

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Helpers\Text;

?>

<div class="rtcl rtcl-search rtcl-search-inline">
    <form action="<?php echo Link::get_listings_page_link(true); ?>"
          data-action="<?php echo Link::get_listings_page_link(); ?>" class="form-vertical rtcl-search-inline-form">
        <div class="row rtcl-no-margin active-<?php echo esc_attr($active_count); ?>">
            <?php if ($can_search_by_location) : ?>
                <div class="form-group ws-item ws-location col-sm-6 col-12">
                    <?php
                    wp_dropdown_categories(array(
                        'show_option_none'  => Text::get_select_location_text(),
                        'taxonomy'          => rtcl()->location,
                        'name'              => 'rtcl_location',
                        'id'                => 'rtcl-location-search-' . $id,
                        'class'             => 'form-control rtcl-location-search',
                        'selected'          => get_query_var('rtcl_location'),
                        'hierarchical'      => true,
                        'option_none_value' => '',
                        'value_field'       => 'slug',
                        'depth'             => 3,
                        'show_count'        => false,
                        'hide_empty'        => false,
                    ));
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($can_search_by_category) : ?>
                <div class="form-group ws-item ws-category col-sm-6 col-12">
                    <?php
                    wp_dropdown_categories(array(
                        'show_option_none'  => Text::get_select_category_text(),
                        'taxonomy'          => rtcl()->category,
                        'name'              => 'rtcl_category',
                        'id'                => 'rtcl-category-search-' . $id,
                        'class'             => 'form-control rtcl-category-search',
                        'selected'          => get_query_var('rtcl_category'),
                        'hierarchical'      => true,
                        'value_field'       => 'slug',
                        'option_none_value' => '',
                        'depth'             => 2,
                        'show_count'        => false,
                        'hide_empty'        => false,
                    ));
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($can_search_by_listing_types) : ?>
                <div class="form-group ws-item ws-type col-sm-6 col-12">
                    <select class="form-control">
                        <option value=""><?php esc_html_e('Select type', 'classified-listing'); ?></option>
                        <?php
                        $listing_types = Functions::get_listing_types();
                        if (!empty($listing_types)) {
                            foreach ($listing_types as $key => $listing_type) {
                                ?>
                                <option value="<?php echo esc_attr($key) ?>"><?php echo esc_html($listing_type) ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($can_search_by_price) : ?>
                <div class="form-group ws-item ws-price col-sm-6  col-12">
                    <div class="row">
                        <div class="col-md-6 col-xs-6">
                            <input type="text" name="filters[price][min]" class="form-control"
                                   placeholder="<?php _e('min', 'classified-listing'); ?>"
                                   value="<?php if (isset($_GET['filters']['price'])) {
                                       echo esc_attr($_GET['filters']['price']['min']);
                                   } ?>">
                        </div>
                        <div class="col-md-6 col-xs-6">
                            <input type="text" name="filters[price][max]" class="form-control"
                                   placeholder="<?php _e('max', 'classified-listing'); ?>"
                                   value="<?php if (isset($_GET['filters']['price'])) {
                                       echo esc_attr($_GET['filters']['price']['max']);
                                   } ?>">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group ws-item ws-text col-sm-6">
                <div class="rt-autocomplete-wrapper">
                    <input type="text" name="q" class="rtcl-autocomplete form-control"
                           placeholder="<?php _e('Enter your keyword here ...', 'classified-listing'); ?>"
                           value="<?php if (isset($_GET['q'])) {
                               echo esc_attr($_GET['q']);
                           } ?>">
                </div>
            </div>

            <div class="form-group ws-item ws-button  col-sm-6">
                <div class="rtcl-action-buttons text-right">
                    <button type="submit"
                            class="btn btn-primary"><?php _e('Search', 'classified-listing'); ?></button>
                </div>
            </div>
        </div>
        <?php do_action('rtcl_widget_search_inline_form', $can_search_by_location, $can_search_by_category) ?>
    </form>
</div>