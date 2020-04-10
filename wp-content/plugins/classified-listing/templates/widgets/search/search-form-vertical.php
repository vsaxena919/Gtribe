<?php

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

?>
<div class="rtcl rtcl-search rtcl-search-vertical">
    <form action="<?php echo Link::get_listings_page_link(true); ?>"
          data-action="<?php echo Link::get_listings_page_link(); ?>" class="form-vertical">
        <div class="form-group">
            <input type="text" name="q" class="form-control"
                   placeholder="<?php _e('Enter your keyword here ...', 'classified-listing'); ?>"
                   value="<?php if (isset($_GET['q'])) {
                       echo esc_attr($_GET['q']);
                   } ?>">
        </div>

        <?php if ($can_search_by_location) : ?>
            <!-- Location field -->
            <div class="form-group">
                <label><?php _e('Select a location', 'classified-listing'); ?></label>
                <?php
                $location = 0;
                if ($loc_slug = get_query_var('rtcl_location')) {
                    $lTerm = get_term_by('slug', $loc_slug, rtcl()->location);
                    if ($lTerm) {
                        $location = $lTerm->term_id;
                    }
                }
                Functions::dropdown_terms(array(
                    'show_option_none' => '-- ' . __('Select a location', 'classified-listing') . ' --',
                    'taxonomy'         => rtcl()->location,
                    'name'             => 'l',
                    'class'            => 'form-control',
                    'selected'         => $location
                ));
                ?>
            </div>
        <?php endif; ?>

        <?php if ($can_search_by_category) : ?>
            <!-- Category field -->
            <div class="form-group">
                <label><?php _e('Select a category', 'classified-listing'); ?></label>
                <?php
                $category = 0;
                if ($cat_slug = get_query_var('rtcl_category')) {
                    $cTerm = get_term_by('slug', $cat_slug, rtcl()->category);
                    if ($cTerm) {
                        $category = $cTerm->term_id;
                    }
                }
                Functions::dropdown_terms(array(
                    'show_option_none'  => '-- ' . __('Select a category', 'classified-listing') . ' --',
                    'option_none_value' => -1,
                    'taxonomy'          => rtcl()->category,
                    'name'              => 'c',
                    'class'             => 'form-control rtcl-category-search',
                    'selected'          => $category
                ));
                ?>
            </div>
        <?php endif; ?>

        <?php if ($can_search_by_listing_types) : ?>
            <div class="form-group">
                <label><?php _e('Select Type', 'classified-listing'); ?></label>
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
            <!-- Price fields -->
            <div class="form-group">
                <label><?php _e('Price Range', 'classified-listing'); ?></label>
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
        <?php do_action('rtcl_widget_search_vertical_form', $can_search_by_location, $can_search_by_category) ?>
        <!-- Action buttons -->
        <button type="submit"
                class="btn btn-primary"><?php _e('Search Listings', 'classified-listing'); ?></button>
        <a href="<?php echo get_permalink(); ?>"
           class="btn btn-danger"><?php _e('Reset', 'classified-listing'); ?></a>
    </form>
</div>