<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 */

use Rtcl\Helpers\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'rtcl_before_edit_account_form' ); ?>

<form class="rtcl-EditAccountForm form-horizontal" id="rtcl-user-account" method="post">

	<?php do_action( 'rtcl_edit_account_form_start' ); ?>

    <div class="form-group row">
        <label for="rtcl-username"
               class="col-sm-3 control-label"><?php esc_html_e( 'Username', 'classified-listing' ); ?></label>
        <div class="col-sm-9">
            <p class="form-control-static"><strong><?php echo esc_html( $username ); ?></strong></p>
        </div>
    </div>

    <div class="form-group row">
        <label for="rtcl-first-name"
               class="col-sm-3 control-label"><?php esc_html_e( 'First Name', 'classified-listing' ); ?></label>
        <div class="col-sm-9">
            <input type="text" name="first_name" id="rtcl-first-name" value="<?php echo esc_attr( $first_name ); ?>"
                   class="form-control"/>
        </div>
    </div>

    <div class="form-group row">
        <label for="rtcl-last-name"
               class="col-sm-3 control-label"><?php esc_html_e( 'Last Name', 'classified-listing' ); ?></label>
        <div class="col-sm-9">
            <input type="text" name="last_name" id="rtcl-last-name" value="<?php echo esc_attr( $last_name ); ?>"
                   class="form-control"/>
        </div>
    </div>

    <div class="form-group row">
        <label for="rtcl-email"
               class="col-sm-3 control-label"><?php esc_html_e( 'E-mail Address', 'classified-listing' ); ?>
            <strong>*</strong></label>
        <div class="col-sm-9">
            <input type="email" name="email" id="rtcl-email" class="form-control"
                   value="<?php echo esc_attr( $email ); ?>" required="required"/>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-offset-3 col-sm-9">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="change_password" id="rtcl-change-password"
                           value="1"><?php esc_html_e( 'Change Password', 'classified-listing' ); ?>
                </label>
            </div>
        </div>
    </div>

    <div class="form-group row rtcl-password-fields" style="display: none;">
        <label for="password"
               class="col-sm-3 control-label"><?php esc_html_e( 'New Password', 'classified-listing' ); ?>
            <strong>*</strong></label>
        <div class="col-sm-9">
            <input type="password" name="pass1" id="password" class="form-control" autocomplete="off"
                   required="required"/>
        </div>
    </div>

    <div class="form-group row rtcl-password-fields" style="display: none">
        <label for="password_confirm"
               class="col-sm-3 control-label"><?php esc_html_e( 'Confirm Password', 'classified-listing' ); ?>
            <strong>*</strong></label>
        <div class="col-sm-9">
            <input type="password" name="pass2" id="password_confirm" class="form-control" autocomplete="off"
                   data-rule-equalTo="#password" required/>
        </div>
    </div>

    <div class="form-group row">
        <label for="rtcl-last-name"
               class="col-sm-3 control-label"><?php esc_html_e( 'Phone', 'classified-listing' ); ?></label>
        <div class="col-sm-9">
            <input type="text" name="phone" id="rtcl-phone" value="<?php echo esc_attr( $phone ); ?>"
                   class="form-control"/>
        </div>
    </div>

    <div class="form-group row">
        <label for="rtcl-last-name"
               class="col-sm-3 control-label"><?php esc_html_e( 'Phone', 'classified-listing' ); ?></label>
        <div class="col-sm-9">
            <input type="text" name="phone" id="rtcl-phone" value="<?php echo esc_attr( $phone ); ?>"
                   class="form-control"/>
        </div>
    </div>
    <div class="form-group row">
        <label for="rtcl-last-name"
               class="col-sm-3 control-label"><?php esc_html_e( 'Website', 'classified-listing' ); ?></label>
        <div class="col-sm-9">
            <input type="url" name="website" id="rtcl-website" value="<?php echo esc_attr( $website ); ?>"
                   class="form-control"/>
        </div>
    </div>
    <div class="form-group row">
        <label for="rtcl-last-name"
               class="col-sm-3 control-label"><?php esc_html_e( 'Location', 'classified-listing' ); ?></label>
        <div class="col-sm-9">
            <div class="form-group" id="rtcl-location-row">
                <label for='rtcl-location'><?php echo esc_html( $state_text ); ?><span
                            class="require-star">*</span></label>
                <select id="rtcl-location" name="location"
                        class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                    <option value="">--<?php esc_html_e( 'Select state', 'classified-listing' ) ?>--</option>
					<?php
					$locations = Functions::get_one_level_locations();
					if ( ! empty( $locations ) ) {
						foreach ( $locations as $location ) {
							$slt = '';
							if ( in_array( $location->term_id, $user_locations ) ) {
								$location_id = $location->term_id;
								$slt         = " selected";
							}
							echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
						}
					}
					?>
                </select>
            </div>
			<?php
			$sub_locations = array();
			if ( $location_id ) {
				$sub_locations = Functions::get_one_level_locations( $location_id );
			}
			?>
            <div class="form-group<?php echo empty( $sub_locations ) ? ' rtcl-hide' : ''; ?>"
                 id="sub-location-row">
                <label for='rtcl-sub-location'><?php echo esc_html( $city_text ); ?><span
                            class="require-star">*</span></label>
                <select id="rtcl-sub-location" name="sub_location"
                        class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                    <option value="">--<?php esc_html_e( 'Select location', 'classified-listing' ) ?>--</option>
					<?php
					if ( ! empty( $sub_locations ) ) {
						foreach ( $sub_locations as $location ) {
							$slt = '';
							if ( in_array( $location->term_id, $user_locations ) ) {
								$sub_location_id = $location->term_id;
								$slt             = " selected";
							}
							echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
						}
					}
					?>
                </select>
            </div>
			<?php
			$sub_sub_locations = array();
			if ( $sub_location_id ) {
				$sub_sub_locations = Functions::get_one_level_locations( $sub_location_id );
			}
			?>
            <div class="form-group<?php echo empty( $sub_sub_locations ) ? ' rtcl-hide' : ''; ?>"
                 id="sub-sub-location-row">
                <label for='rtcl-sub-sub-location'><?php echo esc_html( $town_text ); ?>
                    <span class="require-star">*</span></label>
                <select id="rtcl-sub-sub-location" name="sub_sub_location"
                        class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                    <option value="">--<?php esc_html_e( 'Select location', 'classified-listing' ) ?>--</option>
					<?php
					if ( ! empty( $sub_sub_locations ) ) {
						foreach ( $sub_sub_locations as $location ) {
							$slt = '';
							if ( in_array( $location->term_id, $user_locations ) ) {
								$slt = " selected";
							}
							echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
						}
					}
					?>
                </select>
            </div>
            <div class="form-group">
                <label for="rtcl-zipcode"><?php esc_html_e( "Zip Code", "classified-listing" ) ?></label>
                <input type="text" name="zipcode" value="<?php echo esc_attr( $zipcode ); ?>"
                       class="rtcl-map-field form-control" id="rtcl-zipcode"/>
            </div>
            <div class="form-group">
                <label for="rtcl-address"><?php esc_html_e( "Address", "classified-listing" ) ?></label>
                <textarea name="address" rows="2" class="rtcl-map-field form-control"
                          id="rtcl-address"><?php echo esc_textarea( $address ); ?></textarea>
            </div>
        </div>
    </div>
	<?php do_action( 'rtcl_edit_account_form' ); ?>

	<?php wp_nonce_field( 'rtcl_update_user_account', 'rtcl_user_account_nonce' ); ?>

    <div class="form-group row">
        <div class="col-sm-offset-3 col-sm-9">
            <input type="submit" name="submit" class="btn btn-primary"
                   value="<?php esc_html_e( 'Update Account', 'classified-listing' ); ?>"/>
        </div>
    </div>

    <div class="rtcl-response"></div>

	<?php do_action( 'rtcl_edit_account_form_end' ); ?>
</form>

<?php do_action( 'rtcl_after_edit_account_form' ); ?>
