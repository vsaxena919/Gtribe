<?php
/**
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */


if (count($images)) : ?>
    <div id="rtcl-slider-wrapper" class="rtcl-slider-wrapper mb-4">
        <?php
        if (1 == count($images)) :
            $attachment_id = reset($images)->ID;
            $image_attributes = wp_get_attachment_image_src($attachment_id, 'rtcl-gallery'); ?>
            <div class="rtcl-listing-single-image">
                <img class="rtcl-thumbnail" src="<?php echo esc_url($image_attributes[0]); ?>"
                     alt="<?php echo get_the_title($attachment_id); ?>"/>
            </div>
        <?php else : ?>
            <!-- Slider -->
            <div class="owl-carousel rtcl-slider">
                <?php foreach ($images as $index => $image) :
                    $image_attributes = wp_get_attachment_image_src($image->ID, 'rtcl-gallery');
                    $image_full = wp_get_attachment_image_src($image->ID, 'full');
                    ?>
                    <div class="rtcl-slider-item">
                        <img src="<?php echo esc_html($image_attributes[0]); ?>"
                             data-src="<?php echo esc_attr($image_full[0]) ?>"
                             data-large_image="<?php echo esc_attr($image_full[0]) ?>"
                             data-large_image_width="<?php echo esc_attr($image_full[1]) ?>"
                             data-large_image_height="<?php echo esc_attr($image_full[2]) ?>"
                             alt="<?php echo get_the_title($image->ID); ?>"
                             data-caption="<?php echo esc_attr(wp_get_attachment_caption($image->ID)); ?>"
                             class="rtcl-responsive-item"/>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Slider nav -->
            <div class="owl-carousel rtcl-slider-nav">
                <?php foreach ($images as $index => $image) : ?>
                    <div class="rtcl-slider-thumb-item">
                        <?php echo wp_get_attachment_image($image->ID, 'rtcl-gallery-thumbnail') ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif;