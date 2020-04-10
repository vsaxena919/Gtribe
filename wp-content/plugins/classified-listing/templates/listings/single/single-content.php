<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 */


use Rtcl\Helpers\Functions;
use Rtcl\Models\Listing;

$listing = new Listing( $listing_id );

?>
<div class="rtcl rtcl-listing">

    <div class="row">
        <!-- Main content -->
        <div class="<?php echo esc_attr( $content_class ); ?>">
            <div class="mb-4 rtcl-single-listing-details">
                <div class="rtcl-listing-title"><h2 class="entry-title"><?php $listing->the_title(); ?></h2></div>
                <div class="rtcl-single-listing-detail-content">
                    <!-- Meta data -->
                    <div class="rtcl-listing-meta mb-3">
						<?php $listing->the_labels(); ?>
						<?php $listing->the_meta(); ?>
                    </div>

                    <!-- Image(s) -->
					<?php $listing->the_gallery(); ?>
                    <div class="row">
                        <!--  Content -->
                        <div class="col-md-8">
							<?php if ( ! Functions::is_price_disabled() ): ?>
                                <!-- Price -->
                                <div class="rtcl-price-block mb-2">
									<?php $listing->the_price(); ?>
                                </div>
							<?php endif; ?>

							<?php if ( $content ) : ?>
                                <!-- Description -->
                                <div class="rtcl-listing-description"><?php $listing->the_content(); ?></div>
							<?php endif; ?>

							<?php if ( $sidebar_position === "bottom" ) : ?>
                                <!-- Sidebar -->
								<?php Functions::get_template( "listings/single/listing-sidebar", compact( 'listing', 'sidebar_class' ) ); ?>
							<?php endif; ?>
                        </div>
                        <!--  Inner Sidebar -->
                        <div class="col-md-4">
                            <div class="single-listing-inner-sidebar">
                                <!-- Custom fields -->
								<?php $listing->the_custom_fields(); ?>

                                <!-- Actions  -->
								<?php $listing->the_actions( $listing_id ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Listing -->
			<?php $listing->the_related_listings(); ?>
        </div>

		<?php if ( in_array( $sidebar_position, array( 'left', 'right' ) ) ) : ?>
            <!-- Sidebar -->
			<?php Functions::get_template( "listings/single/listing-sidebar", compact( 'listing', 'sidebar_class' ) ); ?>
		<?php endif; ?>
    </div>
</div>