<?php
/**
 * Dashboard
 *
 * @author 		RadiusTheme
 * @package 	classified-listing/templates
 * @version     1.0.0
 */


use Rtcl\Helpers\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
    <div class="rtcl-user-info media">
        <div class="media-thumb rtcl-user-avatar mr-3">
			<?php echo get_avatar( $current_user->ID ); ?>
        </div>
        <div class="media-body">
            <h5 class="mt-0 mb-2"><?php echo esc_html(Functions::get_author_name($current_user)); ?></h5>
            <p class="media-heading"><?php printf("<strong>%s</strong> : %s", __("Email", "classified-listing"), $current_user->user_email); ?></p>
			<?php $current_user->description ? printf("<p>%s</p>", $current_user->description) : '' ?>
        </div>
    </div>

<?php do_action( 'rtcl_account_dashboard' );