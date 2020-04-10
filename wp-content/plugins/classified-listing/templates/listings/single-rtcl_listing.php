<?php
/**
 *
 * @author        RadiusTheme
 * @package    classified-listing/templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header(); ?>

    <div class="rtcl rtcl-single-listing-wrap">
		<?php while ( have_posts() ) : the_post(); ?>

			<?php
			the_content();

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>

		<?php endwhile; // end of the loop. ?>
    </div>

<?php get_footer();