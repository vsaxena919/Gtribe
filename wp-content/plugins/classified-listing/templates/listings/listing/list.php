<?php
/**
 * List listing
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
$can_show_price = $listing->can_show_price();
?>
<div class="row listing-item rtcl-listing-item<?php $listing->the_label_class(); ?>">
    <div class="col-md-3">
        <div class="listing-thumb">
            <a href="<?php the_permalink(); ?>"><?php $listing->the_thumbnail(); ?></a>
        </div>
    </div>

    <div class="col-md-<?php echo esc_attr($can_show_price ? '7' : '9'); ?>">
        <div class="rtcl-listings-title-block">
            <h3 class="listing-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<?php $listing->the_labels(); ?>
        </div>
        <!-- // display listable field-->
		<?php $listing->the_meta(); ?>
		<?php if ( $listing->can_show_excerpt() ): ?>
			<?php $listing->the_excerpt(); ?>
		<?php endif; ?>
    </div>
	<?php if ( $can_show_price ): ?>
        <div class="col-md-2 text-right rtcl-price-block">
			<?php $listing->the_price(); ?>
        </div>
	<?php endif; ?>
</div>