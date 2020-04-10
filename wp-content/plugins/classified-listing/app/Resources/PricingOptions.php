<?php

namespace Rtcl\Resources;


use Rtcl\Helpers\Functions;

class PricingOptions
{

    static function rtcl_pricing_option($post) {
        $description = get_post_meta($post->ID, "description", true);
        $price = esc_attr(get_post_meta($post->ID, "price", true));
        $visible = get_post_meta($post->ID, "visible", true);
        $featured = get_post_meta($post->ID, "featured", true) ? 1 : 0;
        wp_nonce_field(rtcl()->nonceText, rtcl()->nonceId);
        ?>
        <div class="row form-group">
            <label class="col-2 col-form-label"
                   for="pricing-type"><?php printf('%s [%s]', __("Price", 'classified-listing'),
                    Functions::get_currency_symbol('', true)); ?></label>
            <div class="col-10">
                <input
                        type="text"
                        id="rtcl-pricing-price"
                        name="price"
                        value="<?php echo $price; ?>"
                        class="form-control"
                        required>
            </div>
        </div>
        <div class="row form-group">
            <label class="col-2 col-form-label"
                   for="pricing-type"><?php _e("Visible", 'classified-listing'); ?></label>
            <div class="col-10">
                <input type="number" step="1" name="visible" value="<?php echo esc_attr($visible); ?>"
                       class="form-control" required>
                <span class="description"><?php _e("Number of days the pricing will be validate.",
                        "classified-listing") ?></span>
            </div>
        </div>

        <div class="row form-group">
            <label class="col-2 col-form-label"
                   for="pricing-featured"><?php _e("Allowed", 'classified-listing'); ?></label>
            <div class="col-10">
                <div class="form-check">
                    <input class="form-check-input" name="featured" type="checkbox"
                           value="1" <?php checked(1, $featured); ?> id="allowed_featured">
                    <label class="form-check-label" for="allowed_featured">
                        <?php _e("Featured", 'classified-listing'); ?>
                    </label>
                </div>
            </div>
        </div>

        <div class="row form-group">
            <label class="col-2 col-form-label"
                   for="pricing-type"><?php _e("Description", "classified-listing"); ?></label>
            <div class="col-10">
                <textarea rows="5" class="form-control"
                          name="description"><?php Functions::print_html($description); ?></textarea>
            </div>
        </div>
        <?php
    }

}