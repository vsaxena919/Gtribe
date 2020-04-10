<?php

namespace Rtcl\Controllers\Admin\Meta;

use Rtcl\Helpers\Functions;
use Rtcl\Resources\FontAwesome;
use Rtcl\Resources\Options;

class AddTermMetaField
{

    function __construct() {
        add_action(rtcl()->category . '_add_form_fields', array($this, 'taxonomy_add_new_meta_field'), 10, 2);
        add_action(rtcl()->category . '_edit_form_fields', array($this, 'taxonomy_edit_meta_field'), 10, 2);
        add_action(rtcl()->category . '_add_form_fields', array($this, 'category_add_meta_field'), 10, 2);
        add_action(rtcl()->category . '_edit_form_fields', array($this, 'category_edit_meta_field'), 10, 2);

        add_action(rtcl()->location . '_add_form_fields', array($this, 'taxonomy_add_new_meta_field'), 10, 2);
        add_action(rtcl()->location . '_edit_form_fields', array($this, 'taxonomy_edit_meta_field'), 10, 2);

        add_action('edited_' . rtcl()->category, array($this, 'save_taxonomy_custom_meta'), 10, 2);
        add_action('create_' . rtcl()->category, array($this, 'save_taxonomy_custom_meta'), 10, 2);
        add_action('edited_' . rtcl()->category, array($this, 'save_category_meta'), 10, 2);
        add_action('create_' . rtcl()->category, array($this, 'save_category_meta'), 10, 2);
        add_action('edited_' . rtcl()->location, array($this, 'save_taxonomy_custom_meta'), 10, 2);
        add_action('create_' . rtcl()->location, array($this, 'save_taxonomy_custom_meta'), 10, 2);

        add_filter('manage_' . rtcl()->category . '_custom_column', array(
            $this,
            'add_taxonomy_order_column_value'
        ), 10, 3);
        if (!Functions::is_ad_type_disabled()) {
            add_filter('manage_' . rtcl()->category . '_custom_column', array(
                $this,
                'add_cat_types_column_value'
            ), 10, 3);

            add_filter('manage_edit-' . rtcl()->category . '_columns', array(
                $this,
                'add_cat_type_columns'
            ), 10, 1);
        }
        add_filter('manage_edit-' . rtcl()->category . '_columns', array(
            $this,
            'add_taxonomy_order_columns'
        ), 10, 1);
        add_filter('manage_' . rtcl()->location . '_custom_column', array(
            $this,
            'add_taxonomy_order_column_value'
        ), 10, 3);
        add_filter('manage_edit-' . rtcl()->location . '_columns', array(
            $this,
            'add_taxonomy_order_columns'
        ), 10, 1);


        add_action('quick_edit_custom_box', array($this, 'taxonomy_quick_edit_order'), 10, 2);
        add_action('admin_print_footer_scripts-edit-tags.php', array(
            $this,
            'taxonomy_quick_edit_order_javascript'
        ));

        add_filter('manage_edit-' . rtcl()->post_type_cfg . '_columns', array($this, 'arrange_cfg_columns'));
        add_action('manage_' . rtcl()->post_type_cfg . '_posts_custom_column',
            array($this, 'manage_cfg_columns'), 10, 2);
        add_filter('taxonomy_parent_dropdown_args', array($this, 'taxonomy_parent_dropdown_args'), 10, 2);
        add_action('restrict_manage_posts', array($this, 'restrict_manage_custom_field'));
        add_action('parse_query', array($this, 'parse_query_custom_field'));
    }

    function restrict_manage_custom_field() {
        global $typenow, $wp_query;

        if (rtcl()->post_type_cfg == $typenow) {
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
        }
    }

    function parse_query_custom_field($query) {
        global $pagenow, $post_type;

        if ('edit.php' == $pagenow && rtcl()->post_type_cfg == $post_type) {

            // Convert category id to taxonomy term in query
            if (isset($query->query_vars['rtcl_category']) && ctype_digit($query->query_vars['rtcl_category']) && $query->query_vars['rtcl_category'] != 0) {

                $term = get_term_by('id', $query->query_vars['rtcl_category'], 'rtcl_category');
                $query->query_vars['rtcl_category'] = $term->slug;

            }

        }
    }

    function taxonomy_parent_dropdown_args($args, $taxonomy) {
        if (rtcl()->category != $taxonomy && rtcl()->location != $taxonomy) {
            return $args;
        } // no change
        if (rtcl()->category == $taxonomy) {
            $args['depth'] = '1';
        }
        if (rtcl()->location == $taxonomy) {
            $args['depth'] = '3';
        }

        return apply_filters('rtcl_taxonomy_parent_dropdown_args', $args, $taxonomy);
    }

    function arrange_cfg_columns($columns) {
        $field_count = array('_rtcl_field_count' => __('Fields', 'classified-listing'));

        return array_slice($columns, 0, 2, true) + $field_count + array_slice($columns, 1, null, true);
    }

    function manage_cfg_columns($column) {
        switch ($column) {
            case '_rtcl_category':
                $_catId = get_post_meta(get_the_ID(), '_category', true);
                if ($_catId) {
                    $term = get_term($_catId, rtcl()->category);
                    if ($term) {
                        echo $term->name;
                    }
                }
                break;
            case '_rtcl_field_count':
                $fields = Functions::get_all_cf_fields_by_cfg_id(get_the_ID());
                echo count($fields);
                break;
            default:
                break;
        }
    }

    function taxonomy_quick_edit_order_javascript() {
        $current_screen = get_current_screen();
        if (
            ($current_screen->id != 'edit-' . rtcl()->category || $current_screen->taxonomy != rtcl()->category)
            && ($current_screen->id != 'edit-' . rtcl()->location || $current_screen->taxonomy != rtcl()->location)
        ) {
            return;
        }
        wp_enqueue_script('jquery');
        ?>
        <script type="text/javascript">
            /*global jQuery*/
            jQuery(function ($) {
                $('#the-list').on('click', 'button.editinline', function (e) {
                    // e.preventDefault();
                    var $tr = $(this).closest('tr'),
                        order = $tr.find('td._rtcl_order').text(),
                        types = $tr.find('td._rtcl_types').text().split(', '),
                        type_checkbox_list = $('tr.inline-edit-row .rtcl_category-types-list');
                    // Update field
                    $('tr.inline-edit-row :input[name="_rtcl_order"]').val(order ? parseInt(order, 10) : 0);
                    type_checkbox_list.find('input:checkbox').attr('checked', false);

                    // Types
                    if (types.length) {
                        types.map(function (type) {
                            type_checkbox_list.find('input:checkbox[value="' + type + '"]').attr('checked', true);
                        });
                    }
                });
            });
        </script>
        <style>
            .rtcl-quick-field-wrap:after {
                clear: both;
                display: block;
                content: "";
                float: none;
            }
        </style>
        <?php
    }

    function taxonomy_quick_edit_order($column_name, $screen) {
        if ($screen != 'edit-tags' || $column_name != '_rtcl_order' ||
            !isset($_GET['taxonomy']) ||
            !in_array($_GET['taxonomy'], array(rtcl()->category, rtcl()->location))) {
            return false;
        }
        ?>
        <div class="rtcl-quick-field-wrap">
            <fieldset style="width: 50%; float: left">
                <div id="rtcl-taxonomy-content" class="inline-edit-col">
                    <label>
                        <span class="title"><?php _e('Order', 'classified-listing'); ?></span>
                        <span class="input-text-wrap"><input type="number" name="<?php echo $column_name; ?>"
                                                             value=""></span>
                    </label>
                </div>
            </fieldset>
            <?php if ($_GET['taxonomy'] == rtcl()->category && !Functions::is_ad_type_disabled()) :
                $types = Functions::get_listing_types();
                ?>
                <fieldset style="width: 50%; float: left">
                    <div class="form-field term-group" id="rtcl-category-types">
                        <label for="rtcl-category-types"><span
                                    class="title"><?php _e('Types', 'classified-listing'); ?></span></label>
                        <?php if (!empty($types)): ?>
                            <ul class="cat-checklist rtcl_category-types-list">
                                <?php foreach ($types as $type_id => $type) : ?>
                                    <li id="rtcl_category-<?php echo $type_id; ?>">
                                        <label class="selectit">
                                            <input type="checkbox" name="_rtcl_types[]"
                                                   value="<?php echo esc_html($type_id); ?>"
                                            /> <?php echo esc_html($type); ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </fieldset>
            <?php endif; ?>
        </div>
        <?php
    }

    function save_taxonomy_custom_meta($term_id) {
        $newOrder = !empty($_POST['_rtcl_order']) ? esc_attr(absint($_POST['_rtcl_order'])) : 0;
        update_term_meta($term_id, '_rtcl_order', $newOrder);
    }

    function save_category_meta($term_id) {
        if (!Functions::is_ad_type_disabled()) {
            $types = isset($_POST['_rtcl_types']) ? $_POST['_rtcl_types'] : array();
            delete_term_meta($term_id, '_rtcl_types');
            if (is_array($types)) {
                foreach ($types as $type) {
                    add_term_meta($term_id, '_rtcl_types', $type);
                }
            }
        }

        if (isset($_POST['_rtcl_price_units'])) {
            delete_term_meta($term_id, '_rtcl_price_units');
            $price_units = !empty($_POST['_rtcl_price_units']) && is_array($_POST['_rtcl_price_units']) ? $_POST['_rtcl_price_units'] : array();
            $price_units = array_map('trim', $price_units);
            $price_units = array_filter($price_units);
            if (!empty($price_units)) {
                foreach ($price_units as $unit) {
                    add_term_meta($term_id, '_rtcl_price_units', $unit);
                }
            }
        }

        if (isset($_POST['_rtcl_image'])) {
            update_term_meta($term_id, '_rtcl_image', absint($_POST['_rtcl_image']));
        }
        if (isset($_POST['_rtcl_icon'])) {
            update_term_meta($term_id, '_rtcl_icon', esc_attr($_POST['_rtcl_icon']));
        }

    }

    function taxonomy_add_new_meta_field() {
        ?>
        <div class="form-field term-group">
            <label for="tag-rtcl-order"><?php _e('Order', 'classified-listing'); ?></label>
            <input type="number" name="_rtcl_order" id="tag-rtcl-order" value="">
            <p class="description"><?php _e('Enter an integer value for this order', 'classified-listing'); ?></p>
        </div>
        <?php
    }

    function category_add_meta_field() {
        $icons = Options::get_icon_list();
        $price_unit_list = Options::get_price_unit_list();
        if (!Functions::is_ad_type_disabled()):
            $types = Functions::get_listing_types();
            ?>
            <div class="form-field term-group" id="rtcl-category-types">
                <label for="rtcl-category-types"><?php _e('Types', 'classified-listing'); ?></label>
                <fieldset class="rtcl-checkbox-wrap">
                    <?php if (!empty($types)): ?>
                        <?php foreach ($types as $type_id => $type) : ?>
                            <label>
                                <input type="checkbox" name="_rtcl_types[]"
                                       value="<?php echo esc_html($type_id); ?>"
                                    <?php echo $type_id == 'sell' ? " checked" : null; ?>
                                /> <?php echo esc_html($type); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </fieldset>
            </div>
        <?php endif; ?>
        <div class="form-field term-group">
            <label for="rtcl-price-units"><?php _e('Price Units', 'classified-listing'); ?></label>
            <fieldset class="rtcl-checkbox-wrap">
                <?php if (!empty($price_unit_list)) {
                    foreach ($price_unit_list as $unit_key => $unit) {
                        echo sprintf('<label><input type="checkbox" name="_rtcl_price_units[]" value="%s" /> %s (%s)</label>',
                            esc_attr($unit_key),
                            $unit['title'],
                            $unit['short']
                        );
                    }
                } ?>
            </fieldset>
        </div>
        <div class="form-field term-group">
            <label for="rtcl-category-image-id"><?php _e('Image',
                    'classified-listing'); ?></label>
            <input type="hidden" id="rtcl-category-image-id" name="_rtcl_image"/>
            <div id="rtcl-categories-image-wrapper"></div>
            <p>
                <input type="button" class="button button-secondary" id="rtcl-categories-upload-image"
                       value="<?php _e('Add Image', 'classified-listing'); ?>"/>
                <input type="button" class="button button-secondary" id="rtcl-categories-remove-image"
                       value="<?php _e('Remove Image', 'classified-listing'); ?>"/>
            </p>
        </div>
        <div class="form-field term-group">
            <label for="tag-rtcl-icon"><?php _e('Icon', 'classified-listing'); ?></label>
            <p><select name="_rtcl_icon" class="rtcl-select2-icon" id="tag-rtcl-icon">
                    <option value=""><?php _e("Select one", "classified-listing") ?></option>
                    <?php
                    foreach ($icons as $icon) {
                        echo "<option value='{$icon}' data-icon='{$icon}'>{$icon}</option>";
                    }
                    ?>
                </select></p>
        </div>
        <?php
    }

    /**
     * @param $term \WP_Term
     */
    function taxonomy_edit_meta_field($term) {
        $term_meta = esc_attr(absint(get_term_meta($term->term_id, "_rtcl_order", true)));
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label
                        for="tag-rtcl-order"><?php _e('Order', 'classified-listing'); ?></label></th>
            <td>
                <input type="number" name="_rtcl_order" id="tag-rtcl-order"
                       value="<?php echo $term_meta ? $term_meta : 0; ?>">
                <p class="description"><?php _e('Enter an integer value for this order', 'classified-listing'); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * @param $term \WP_Term
     */
    function category_edit_meta_field($term) {
        $price_unit_list = Options::get_price_unit_list();
        $icons = Options::get_icon_list();
        $f_icon = esc_attr(get_term_meta($term->term_id, "_rtcl_icon", true));
        $image_id = absint(get_term_meta($term->term_id, "_rtcl_image", true));
        $price_units = get_term_meta($term->term_id, "_rtcl_price_units");
        $image_src = $image_id ? wp_get_attachment_thumb_url($image_id) : '';
        if (!Functions::is_ad_type_disabled()):
            $types = Functions::get_listing_types();
            $selected_types = get_term_meta($term->term_id, '_rtcl_types');
            ?>
            <tr class="form-field term-group-wrap" id="rtcl-category-types">
                <th scope="row">
                    <label for="rtcl-category-types"><?php _e('Types', 'classified-listing'); ?></label>
                </th>
                <td>
                    <fieldset class="rtcl-checkbox-wrap">
                        <?php if (!empty($types)): ?>
                            <?php foreach ($types as $type_id => $type) :
                                $slt = in_array($type_id, $selected_types) ? " checked" : null;
                                ?>
                                <label>
                                    <input type="checkbox" name="_rtcl_types[]"
                                           value="<?php echo esc_html($type_id); ?>"
                                        <?php echo $slt ?>
                                    /> <?php echo esc_html($type); ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </fieldset>
                </td>
            </tr>
        <?php endif; ?>
        <tr class="form-field term-group-wrap" id="rtcl-price-unit-wrap">
            <th scope="row">
                <label for="rtcl-price-unit"><?php esc_html_e('Price Units', 'classified-listing'); ?></label>
            </th>
            <td>
                <fieldset class="rtcl-checkbox-wrap">
                    <input type="hidden" name="_rtcl_price_units[]" value=""/>
                    <?php if (!empty($price_unit_list)) {
                        foreach ($price_unit_list as $unit_key => $unit) {
                            echo sprintf('<label><input type="checkbox" name="_rtcl_price_units[]" value="%s"%s/> %s (%s)</label>',
                                $unit_key,
                                in_array($unit_key, $price_units) ? " checked" : null,
                                $unit['title'],
                                $unit['short']
                            );
                        }
                    } ?>
                </fieldset>
            </td>
        </tr>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="rtcl-category-image-id"><?php _e('Image',
                        'classified-listing'); ?></label>
            </th>
            <td>
                <input type="hidden" id="rtcl-category-image-id" name="_rtcl_image" value="<?php echo $image_id; ?>"/>
                <div id="rtcl-categories-image-wrapper">
                    <?php if ($image_src) : ?>
                        <img src="<?php echo $image_src; ?>"/>
                    <?php endif; ?>
                </div>
                <p>
                    <input type="button" class="button button-secondary" id="rtcl-categories-upload-image"
                           value="<?php _e('Add Image', 'classified-listing'); ?>"/>
                    <input type="button" class="button button-secondary" id="rtcl-categories-remove-image"
                           value="<?php _e('Remove Image', 'classified-listing'); ?>"/>
                </p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="tag-rtcl-icon"><?php _e('Icon', 'classified-listing'); ?></label>
            </th>
            <td>
                <select name="_rtcl_icon" class="rtcl-select2-icon" id="tag-rtcl-icon">
                    <option value=""><?php _e("Select one", "classified-listing") ?></option>
                    <?php
                    foreach ($icons as $icon) {
                        $slt = $icon == $f_icon ? " selected" : null;
                        echo "<option value='{$icon}'{$slt} data-icon='{$icon}'>{$icon}</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }

    function add_taxonomy_order_column_value($content, $column_name, $term_id) {
        if ($column_name == '_rtcl_order') {
            $content = absint(get_term_meta($term_id, '_rtcl_order', true));
        }

        return $content;
    }

    function add_cat_types_column_value($content, $column_name, $term_id) {
        if ($column_name == '_rtcl_types') {
            $content = implode(', ', get_term_meta($term_id, '_rtcl_types'));
        }

        return $content;
    }

    function add_taxonomy_order_columns($columns) {
        $order = array('_rtcl_order' => __('Order', 'Classified-listing'));

        return array_slice($columns, 0, 2, true) + $order + array_slice($columns, 1, null, true);
    }

    function add_cat_type_columns($columns) {
        $order = array('_rtcl_types' => __('Types', 'Classified-listing'));

        return array_slice($columns, 0, 2, true) + $order + array_slice($columns, 1, null, true);
    }
}