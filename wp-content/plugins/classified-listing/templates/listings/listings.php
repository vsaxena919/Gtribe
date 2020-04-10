<?php
/**
 *
 * @author        RadiusTheme
 * @package    classified-listing/templates
 * @version     1.0.0
 */


use Rtcl\Helpers\Functions;
use Rtcl\Models\Listing;
use Rtcl\Helpers\Pagination;

?>
<div class="rtcl rtcl-listings">
	<?php Functions::sorting_action(); ?>
	<?php if ( $rtcl_query->have_posts() ) : ?>
        <div class="rtcl-list-view rtcl-listing-wrapper">
            <!-- the loop -->
			<?php
			while ( $rtcl_query->have_posts() ) : $rtcl_query->the_post();
				$listing = new Listing( get_the_ID() );
				?>
				<?php Functions::get_template('listings/listing/list', compact('listing')); ?>
			<?php endwhile; ?>
            <!-- end of the loop -->

            <!-- Use reset postdata to restore original query -->
			<?php wp_reset_postdata(); ?>
        </div>
        <!-- pagination here -->
		<?php Pagination::pagination( $rtcl_query ); ?>
	<?php else: ?>
        <p><?php esc_html_e( 'No listing found.', 'classified-listing' ); ?></p>
	<?php endif; ?>
</div>
