<?php
/**
 * Login form
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 */

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( is_user_logged_in() ) {
	return;
}

?>
<div class="rtcl rtcl-login-form-wrap"<?php if ( $hidden ) { ?> style="display:none;"<?php } ?>>

	<?php Functions::print_notices(); ?>
    <form class="rtcl-form rtcl-login-form" method="post">

		<?php do_action( 'rtcl_login_form_start' ); ?>

		<?php if ( $message ) {
			echo wpautop( wptexturize( $message ) );
		} // @codingStandardsIgnoreLine ?>

        <div class="form-group">
            <label for="rtcl-user-login" class="control-label"><?php _e( 'Username or E-mail',
					'classified-listing' ); ?></label>
            <input type="text" name="username" autocomplete="username"
                   value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
                   id="rtcl-user-login" class="form-control" required/>
        </div>

        <div class="form-group">
            <label for="rtcl-user-pass" class="control-label"><?php _e( 'Password',
					'classified-listing' ); ?></label>
            <input type="password" name="password" id="rtcl-user-pass" autocomplete="current-password"
                   class="form-control" required/>
        </div>

		<?php do_action( 'rtcl_login_form' ); ?>

        <div class="form-group">
			<?php wp_nonce_field( 'rtcl-login', 'rtcl-login-nonce' ); ?>
            <input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>"/>
            <input type="submit" name="rtcl-login" class="btn btn-primary"
                   value="<?php _e( 'Login', 'classified-listing' ); ?>"/>
            <label>
                <input type="checkbox" name="rememberme" id="rtcl-rememberme"
                       value="forever"><?php _e( 'Remember Me', 'classified-listing' ); ?>
            </label>
        </div>

        <div class="form-group">
            <p class="rtcl-forgot-password">
				<?php if ( Functions::is_registration_enabled() ) : ?>
                    <a href="<?php echo Link::get_my_account_page_link(); ?>"><?php _e( 'Register',
							'classified-listing' ); ?></a>&nbsp;
				<?php endif; ?>
                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php _e( 'Forgot your password?',
						'classified-listing' ); ?></a>

            </p>
        </div>
		<?php do_action( 'rtcl_login_form_end' ); ?>

    </form>
</div>
