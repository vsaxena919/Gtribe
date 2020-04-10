<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 */


use Rtcl\Models\Listing;

?>
<div class="rtcl mb-3 rtcl-related-listing rtcl-listings">
    <div class="rtcl-related-title"><h2><?php esc_html_e("Related Listing", "classified-listing"); ?></h2></div>
    <div class="rtcl-related-listings">
        <?php if ($rtcl_related_query->have_posts()) : ?>
            <div class="rtcl-related-slider-wrap">
                <div class="rtcl-related-slider rtcl-carousel-slider" id="rtcl-related-slider"
                     data-options="<?php echo htmlspecialchars(wp_json_encode($slider_options)); // WPCS: XSS ok. ?>">
                    <?php
                    global $post;
                    while ($rtcl_related_query->have_posts()):
                        $rtcl_related_query->the_post();
                        $listing = new Listing(get_the_ID());
                        ?>
                        <div class="rtcl-related-slider-item listing-item rtcl-listing-item">
                            <div class="related-item-inner grid-item">
                                <div class="listing-thumb">
                                    <a href="<?php the_permalink(); ?>"><?php $listing->the_thumbnail('rtcl-thumbnail'); ?></a>
                                </div>
                                <div class="item-content">
                                    <?php $listing->the_labels(); ?>
                                    <h3 class="listing-title">
                                        <a href="<?php the_permalink(); ?>"><?php echo esc_html($post->post_title); ?></a>
                                    </h3>
                                    <ul class="listing-meta rtcl-listing-meta-data">
                                        <li class="date"><i class="rtcl-icon rtcl-icon-clock" aria-hidden="true"></i>
                                            <?php $listing->the_time(); ?>
                                        </li>
                                        <?php if ($listing->has_location() && $listing->can_show_location()): ?>
                                            <li class="place"><i class="rtcl-icon rtcl-icon-location"
                                                                 aria-hidden="true"></i>
                                                <?php $listing->the_locations(); ?>
                                            </li>
                                        <?php endif; ?>
                                        <li class="tag-ctg"><i class="rtcl-icon rtcl-icon-tags" aria-hidden="true"></i>
                                            <?php $listing->the_categories(); ?>
                                        </li>
                                    </ul>
                                    <?php if ($listing->can_show_price()): ?>
                                        <div class="listing-price"><?php $listing->the_price(); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>