<?php
/**
 * Login Form
 * @package classified-listing/Templates
 * @version 1.0.0
 */

use Rtcl\Helpers\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

Functions::print_notices();
?>
<?php do_action( 'rtcl_before_user_login_form' ); ?>
<div class="row" id="rtcl-user-login-wrapper">
    <div class="col-<?php echo ( Functions::is_registration_enabled() ) ? "6" : '12'; ?> rtcl-login-form-wrap">
        <h2><?php esc_html_e( 'Login', 'classified-listing' ); ?></h2>
        <form id="rtcl-login-form" class="form-horizontal" method="post">
			<?php do_action( 'rtcl_login_form_start' ); ?>
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
                <input type="submit" name="rtcl-login" class="btn btn-primary"
                       value="<?php _e( 'Login', 'classified-listing' ); ?>"/>
                <label>
                    <input type="checkbox" name="rememberme" id="rtcl-rememberme"
                           value="forever"><?php _e( 'Remember Me', 'classified-listing' ); ?>
                </label>
            </div>

            <div class="form-group">
                <p class="rtcl-forgot-password">
                    <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php _e( 'Forgot your password?',
							'classified-listing' ); ?></a>
                </p>
            </div>
			<?php do_action( 'rtcl_login_form_end' ); ?>
        </form>
    </div>
	<?php if ( Functions::is_registration_enabled() ): ?>
        <div class="col-6 rtcl-registration-form-wrap">

            <h2><?php esc_html_e( 'Register', 'classified-listing' ); ?></h2>

            <form id="rtcl-register-form" class="form-horizontal" method="post">

				<?php do_action( 'rtcl_register_form_start' ); ?>

                <div class="form-group">
                    <label for="rtcl-reg-username"
                           class="control-label"><?php _e( 'Username', 'classified-listing' ); ?>
                        <strong>*</strong></label>
                    <input type="text" name="username" id="rtcl-reg-username" class="form-control" required/>
                    <span class="help-block"><?php _e( 'Username cannot be changed.', 'classified-listing' ); ?></span>
                </div>

                <div class="form-group">
                    <label for="rtcl-reg-email"
                           class="control-label"><?php esc_html_e( 'Email address', 'classified-listing' ); ?>
                        <strong>*</strong></label>
                    <input type="email" name="email" id="rtcl-reg-email" class="form-control" required/>
                </div>

                <div class="form-group">
                    <label for="rtcl-reg-password"
                           class="control-label"><?php _e( 'Password', 'classified-listing' ); ?>
                        <strong>*</strong></label>
                    <input type="password" name="password" id="rtcl-reg-password" class="form-control"
                           autocomplete="off" required/>
                </div>

				<?php do_action( 'rtcl_register_form' ); ?>

                <div class="form-group">
                    <div id="rtcl-registration-g-recaptcha"></div>
                    <div id="rtcl-registration-g-recaptcha-message"></div>
					<?php wp_nonce_field( 'rtcl-register', 'rtcl-register-nonce' ); ?>
                    <input type="submit" name="rtcl-register" class="btn btn-primary"
                           value="<?php _e( 'Register', 'classified-listing' ); ?>"/>
                </div>
				<?php do_action( 'rtcl_register_form_end' ); ?>
            </form>
        </div>
	<?php endif; ?>
</div>
<?php do_action( 'rtcl_after_user_login_form' ); ?>
