<?php

use Rtcl\Helpers\Pagination;
use Rtcl\Models\Listing;

?>
<div class="rtcl rtcl-widget-listings">
	<?php
	if ( $rtcl_query->have_posts() ) : ?>
        <div class="rtcl-widget-listings-wrap view-<?php echo esc_attr( $instance['view'] );
		echo esc_attr( $instance['view'] == 'grid' ? " rtcl-equal-height" : '' ); ?>">
            <div class="row rtcl-grid-view <?php echo esc_attr( $instance['view'] == 'slider' ? " rtcl-carousel-slider" : '' ); ?>" data-options="<?php echo htmlspecialchars( wp_json_encode( $instance['slider_options'] ) ); // WPCS: XSS ok. ?>">
				<?php
				while ( $rtcl_query->have_posts() ):
					$rtcl_query->the_post();
					$listing      = new Listing( get_the_ID() );
					$listing_meta = $img = $labels = $uInfo = $time = $location = $category = $price = null;
					?>
                    <div class="rtcl-widget-listing-item grid-item col-md-<?php echo esc_attr( $instance['view'] == 'grid' ? floor( 12 / $instance['columns'] ) : 12 );
					echo esc_attr( $instance['view'] == 'grid' ? " equal-item" : '' ); ?>">
                        <div class="rtcl-wli-inner">
							<?php
							if ( $instance['show_image'] ) {
								$img = sprintf( "<a href='%s' title='%s'>%s</a>",
									get_the_permalink(),
									esc_html( get_the_title() ),
									$listing->get_the_thumbnail( 'rtcl-thumbnail' )
								);
							}
							if ( $instance['show_labels'] ) {
								$labels = $listing->the_labels( false );
							}
							if ( $instance['show_date'] ) {
								$time = sprintf( '<li class="date"><i class="rtcl-icon rtcl-icon-clock" aria-hidden="true"></i>%s</li>',
									$listing->get_the_time()
								);
							}
							if ( $instance['show_location'] ) {
								$location = sprintf( '<li class="location"><i class="rtcl-icon rtcl-icon-location" aria-hidden="true"></i>%s</li>',
									$listing->the_locations( false )
								);
							}
							if ( $instance['show_category'] ) {
								$category = sprintf( '<li class="category"><i class="rtcl-icon rtcl-icon-tags" aria-hidden="true"></i>%s</li>',
									$listing->the_categories( false )
								);
							}
							if ( $instance['show_price'] ) {
								$price = sprintf( '<div class="listing-price">%s</div>', $listing->get_the_price() );
							}
							$info = array();
							if ( $instance['show_user'] ) {
								$info[] = '<i class="rtcl-icon rtcl-icon-user" aria-hidden="true"></i>' . get_the_author();
							}
							if ( $instance['show_views'] ) {
								$views  = absint( get_post_meta( get_the_ID(), '_views', true ) );
								$info[] = '<i class="rtcl-icon rtcl-icon-eye" aria-hidden="true"></i>' . sprintf( _n( "%s view", "%s views", $views, 'classified-listing' ), number_format_i18n( $views ) );
							}
							if ( ! empty( $info ) ) {
								$uInfo = sprintf( '<li class="info">%s</li>',
									implode( ' / ', $info )
								);
							}

							if ( $uInfo || $time || $category || $location ) {
								$listing_meta = sprintf( '<ul class="listing-meta">%s%s%s%s</ul>', $uInfo, $time, $category,
									$location );
							}

							$title = sprintf( '<h3><a href="%1$s" title="%2$s">%2$s</a></h3>',
								get_the_permalink(),
								esc_html( get_the_title() )
							);

							$item_content = sprintf( '<div class="item-content">%s %s %s %s</div>',
								$labels,
								$title,
								$listing_meta,
								$price );
							if ( $instance['image_position'] == "top" ) {
								printf( "%s%s", $img, $item_content );
							} else {
								printf( "<div class='row'><div class='col-6'>%s</div><div class='col-6'>%s</div></div>", $img, $item_content );
							}
							?>

                        </div>
                    </div>
				<?php
				endwhile;
				wp_reset_postdata();
				?>
            </div>
			<?php if ( $instance['pagination'] && $instance['view'] === 'grid' ) {
				Pagination::pagination($rtcl_query);
			} ?>
        </div>
	<?php else: ?>
        <p><?php esc_html_e("No post found.", "classified-listing"); ?></p>
	<?php endif; ?>
</div>