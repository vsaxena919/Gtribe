<?php
/**
 * Listing Information
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

?>
<div class="rtcl-post-details rtcl-post-section">
    <?php if (!Functions::is_ad_type_disabled()) : ?>
        <div id="rtcl-ad-type-selection">
            <div class="rtcl-post-section-title">
                <h3>
                    <i class="rtcl-icon rtcl-icon-tags"></i><?php esc_html_e("Select a type", "classified-listing"); ?>
                </h3>
            </div>
            <div class="form-group row">
                <label for="rtcl-category"
                       class="col-md-2 col-form-label"><?php _e('Ad Type', 'classified-listing'); ?>
                    <span class="require-star">*</span>
                </label>
                <div class="col-md-10">
                    <select class="rtcl-select2 form-control" id="rtcl-ad-type" name="ad_type" required>
                        <option value="">--<?php esc_html_e("Select a type", "classified-listing") ?>--</option>
                        <?php
                        $types = Functions::get_listing_types();
                        if (!empty($types)):
                            foreach ($types as $type_id => $type):
                                $slt = $type_id == $ad_type ? " selected" : null;
                                echo "<option value='" . esc_attr($type_id) . "' " . esc_html($slt) . ">" . esc_html($type) . "</option>";
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="rtcl-post-section-title">
        <h3>
            <i class="rtcl-icon rtcl-icon-picture"></i><?php esc_html_e("Listing Information",
                "classified-listing"); ?>
        </h3>
    </div>
    <div class="rtcl-post-category">
        <div class="form-group row">
            <label for="rtcl-category"
                   class="col-md-2 col-form-label"><?php _e('Category', 'classified-listing'); ?>
                <span class="require-star">*</span></label>
            <div class="col-md-10">
                <select class="rtcl-select2 form-control" id="rtcl-category" name="category"
                        required>
                    <option value="">--<?php _e("Select category", "classified-listing") ?>--</option>
                    <?php
                    if (Functions::is_ad_type_disabled()) {
                        $cats = Functions::get_one_level_categories(0);
                    } else {
                        $cats = $ad_type ? Functions::get_one_level_categories(0, $ad_type) : array();
                    }
                    if (!empty($cats)) {
                        foreach ($cats as $cat) {
                            $slt = '';
                            if (in_array($cat->term_id, $selected_categories)) {
                                $slt = ' selected';
                                $parent_cat_id = $cat->term_id;
                            }
                            echo "<option value='{$cat->term_id}'{$slt}>{$cat->name}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php $child_cats = $parent_cat_id ? Functions::get_one_level_categories($parent_cat_id) : array() ?>
        <div class="form-group row<?php echo empty($child_cats) ? ' rtcl-hide' : ''; ?>"
             id="sub-cat-row">
            <label for="rtcl_sub_category"
                   class="col-md-2 col-form-label"><?php _e('Sub Category',
                    'classified-listing'); ?>
                <span class="require-star">*</span></label>
            <div class="col-md-10">
                <select class="form-control rtcl-select2" id="rtcl-sub-category"
                        name="sub_category"
                        required>
                    <?php
                    if (!empty($child_cats)) {
                        echo "<option value=''>" . __('Select sub category',
                                'classified-listing') . "</option>";
                        foreach ($child_cats as $cat) {
                            $slt = '';
                            if (in_array($cat->term_id, $selected_categories)) {
                                $slt = ' selected';
                                $child_cat_id = $cat->term_id;
                            }
                            echo "<option value='{$cat->term_id}'{$slt}>{$cat->name}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="rtcl-category"><?php _e('Title', 'classified-listing'); ?><span
                    class="require-star">*</span></label>
        <input type="text" class="rtcl-select2 form-control" value="<?php echo esc_attr($title); ?>" id="rtcl-title"
               name="title"
               required/>
    </div>
    <?php if (!in_array('price', $hidden_fields)): ?>
        <div class="row" id="rtcl-form-price-wrap">
            <?php if (!in_array('price_type', $hidden_fields)): ?>
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="rtcl-category"><?php _e('Price Type', 'classified-listing'); ?><span
                                    class="require-star">*</span></label>
                        <select class="form-control rtcl-select2" id="rtcl-price-type" name="price_type">
                            <?php
                            $price_types = Options::get_price_types();
                            foreach ($price_types as $key => $type) {
                                $slt = $price_type == $key ? " selected" : null;
                                echo "<option value='{$key}'{$slt}>{$type}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
            <div class="col-12 col-md-<?php echo esc_attr(in_array('price_type', $hidden_fields) ? '12' : '6'); ?>">
                <div id="rtcl-price-row" class="row">
                    <div id="rtcl-price-wrap"
                         class="form-group col-12 col-md-<?php echo esc_attr(($listing && $listing->has_price_units()) || ($category_id && Functions::category_has_price_units($category_id)) ? '6' : '12'); ?>">
                        <label for="rtcl-category"><?php echo sprintf('<span class="price-label">%s [%s]</span>',
                                esc_html__("Price", 'classified-listing'),
                                Functions::get_currency_symbol()
                            ); ?><span class="require-star">*</span></label>
                        <input type="text"
                               class="form-control"
                               value="<?php echo esc_attr($price); ?>" name="price"
                               id="rtcl-price"<?php echo esc_attr(!$price_type || $price_type == 'fixed' ? " required" : '') ?>>
                    </div>
                    <?php do_action('rtcl_listing_form_price_unit', $listing, $category_id); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div id="rtcl-custom-fields-list" data-post_id="<?php echo absint($post_id); ?>">
        <?php
        $selected_cat = $child_cat_id ? $child_cat_id : $parent_cat_id;
        do_action('wp_ajax_rtcl_custom_fields_listings', $post_id, $selected_cat); ?>
    </div>
    <?php if (!in_array('description', $hidden_fields)): ?>
        <div class="form-group">
            <label for="description"><?php _e('Description', 'classified-listing'); ?></label>
            <?php

            if ('textarea' == $editor) { ?>
                <textarea id="description" name="description" class="form-control"
                          rows="8"><?php Functions::print_html($post_content); ?></textarea>
                <?php
            } else {
                wp_editor(
                    $post_content,
                    'description',
                    array(
                        'media_buttons' => false,
                        'editor_height' => 200
                    )
                );
            }
            ?>
        </div>
    <?php endif; ?>
</div>