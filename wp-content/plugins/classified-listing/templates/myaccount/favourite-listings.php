<?php
/**
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Helpers\Pagination;
use Rtcl\Models\Listing;

global $post;
?>

<div class="rtcl-favourite-listings rtcl rtcl-listings">

    <?php if ( $rtcl_query->have_posts() ) : ?>
        <div class="rtcl-list-view">
            <!-- the loop -->
            <?php while ( $rtcl_query->have_posts() ) : $rtcl_query->the_post();
                $post_meta = get_post_meta( $post->ID );
                $listing   = new Listing( $post->ID );
                ?>
                <div class="row listing-item rtcl-listing-item">
                    <div class="col-md-3">
                        <div class="listing-thumb">
                            <a href="<?php the_permalink(); ?>"><?php $listing->the_thumbnail(); ?></a>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="rtcl-listings-title-block">
                            <h3 class="listing-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <?php $listing->the_labels(); ?>
                        </div>
                        <?php $listing->the_meta(); ?>
                    </div>

                    <div class="col-md-2 text-right">
                        <div class="btn-group btn-group-justified">
                            <a href="#" class="btn btn-danger btn-sm rtcl-delete-favourite-listing"
                               data-id="<?php echo absint($post->ID) ?>"><?php _e( 'Delete', 'classified-listing' ) ?></a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
            <!-- end of the loop -->
        </div>
        <!-- pagination here -->
        <?php Pagination::pagination($rtcl_query); ?>
    <?php else : ?>
        <p> <?php _e( 'No listing found.', 'classified-listing' ) ?></p>
    <?php endif; ?>

</div>