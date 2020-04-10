<?php
/**
 * Login Form Contact
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

use Rtcl\Helpers\Functions;

?>
<div class="rtcl-post-contact-details rtcl-post-section">
    <div class="rtcl-post-section-title">
        <h3><i class="rtcl-icon rtcl-icon-users"></i><?php _e( "Contact Details", "classified-listing" ); ?>
        </h3>
    </div>
    <div class="row">
        <div class="col-md-6">
			<?php if ( ! in_array( 'location', $hidden_fields ) ): ?>
                <div class="form-group" id="rtcl-location-row">
                    <label for='rtcl-location'><?php echo esc_html( $state_text ); ?><span
                                class="require-star">*</span></label>
                    <select id="rtcl-location" name="location"
                            class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                        <option value="">--<?php _e( 'Select location', 'classified-listing' ) ?>--</option>
						<?php
						$locations = Functions::get_one_level_locations();
						if ( ! empty( $locations ) ) {
							foreach ( $locations as $location ) {
								$slt = '';
								if ( in_array( $location->term_id, $selected_locations ) ) {
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
                    <label for='rtcl-sub-location'><?php echo esc_html( $city_text ) ?><span
                                class="require-star">*</span></label>
                    <select id="rtcl-sub-location" name="sub_location"
                            class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                        <option value="">--<?php _e( 'Select location', 'classified-listing' ) ?>--</option>
						<?php
						if ( ! empty( $sub_locations ) ) {
							foreach ( $sub_locations as $location ) {
								$slt = '';
								if ( in_array( $location->term_id, $selected_locations ) ) {
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
                    <label for='rtcl-sub-sub-location'><?php echo esc_html( $town_text ) ?>
                        <span class="require-star">*</span></label>
                    <select id="rtcl-sub-sub-location" name="sub_sub_location"
                            class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                        <option value="">--<?php _e( 'Select location', 'classified-listing' ) ?>--</option>
						<?php
						if ( ! empty( $sub_sub_locations ) ) {
							foreach ( $sub_sub_locations as $location ) {
								$slt = '';
								if ( in_array( $location->term_id, $selected_locations ) ) {
									$slt = " selected";
								}
								echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
							}
						}
						?>
                    </select>
                </div>
			<?php endif; ?>
			<?php if ( ! in_array( 'zipcode', $hidden_fields ) ): ?>
                <div class="form-group">
                    <label for="rtcl-zipcode"><?php _e( "Zip Code", "classified-listing" ) ?></label>
                    <input type="text" name="zipcode" value="<?php echo esc_attr( $zipcode ); ?>"
                           class="rtcl-map-field form-control" id="rtcl-zipcode"/>
                </div>
			<?php endif; ?>
			<?php if ( ! in_array( 'address', $hidden_fields ) ): ?>
                <div class="form-group">
                    <label for="rtcl-address"><?php _e( "Address", "classified-listing" ) ?></label>
                    <textarea name="address" rows="2" class="rtcl-map-field form-control"
                              id="rtcl-address"><?php echo esc_textarea( $address ); ?></textarea>
                </div>
			<?php endif; ?>
        </div>
        <div class="col-md-6">
			<?php if ( ! in_array( 'phone', $hidden_fields ) ): ?>
                <div class="form-group">
                    <label for="rtcl-phone"><?php _e( "Phone", "classified-listing" ) ?></label>
                    <input type="text" class="form-control" id="rtcl-phone" name="phone"
                           value="<?php echo esc_attr( $phone ); ?>"/>
                </div>
			<?php endif; ?>
			<?php if ( ! in_array( 'email', $hidden_fields ) ): ?>
                <div class="form-group">
                    <label for="rtcl-email"><?php _e( "Email", "classified-listing" ) ?></label>
                    <input type="email" class="form-control" id="rtcl-email" name="email"
                           value="<?php echo esc_attr( $email ); ?>"/>
                </div>
			<?php endif; ?>
			<?php if ( ! in_array( 'website', $hidden_fields ) ): ?>
                <div class="form-group">
                    <label for="rtcl-website"><?php _e( "Website", "classified-listing" ) ?></label>
                    <input type="url" class="form-control" id="rtcl-website" value="<?php echo esc_url( $website ); ?>"
                           name="website"/>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>