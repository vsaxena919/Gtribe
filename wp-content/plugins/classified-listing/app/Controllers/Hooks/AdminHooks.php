<?php

namespace Rtcl\Controllers\Hooks;


class AdminHooks {

	public static function init() {
		add_action( "rtcl_sent_email_to_user_by_moderator", array(
			__CLASS__,
			'update_user_notification_by_moderator',
			10,
			1
		) );

		add_action( "rtcl_sent_email_to_user_by_visitor", array(
			__CLASS__,
			'update_user_notification_by_visitor',
			10,
			1
		) );

		add_filter( 'rtcl_register_settings_tabs', array(
			__CLASS__,
			'add_addon_theme_tab_item_at_settings_tabs_list'
		), 99 );

		add_action( 'rtcl_admin_settings_groups', array( __CLASS__, 'add_addon_theme_feature' ), 10, 2 );


	}


	function update_user_notification_by_moderator( $post_id ) {
		$count = absint( get_post_meta( $post_id, "notification_by_moderation", true ) );

		update_post_meta( $post_id, 'notification_by_moderation', $count + 1 );
	}

	function update_user_notification_by_visitor( $post_id ) {

		$count = absint( get_post_meta( $post_id, "notification_by_visitor", true ) );

		update_post_meta( $post_id, 'notification_by_visitor', $count + 1 );

	}

	public static function add_addon_theme_tab_item_at_settings_tabs_list( $tabs ) {
		$tabs['addon_theme'] = __( 'Addon & Theme (Pro)', 'classified-listing' );

		return $tabs;
	}

	public static function add_addon_theme_feature( $active_tab, $current_section ) {
		if ( $active_tab === "addon_theme" ) {
			?>
            <div class="rtcl-product-list">
                <div class="rtcl-product">
                    <img src="<?php echo esc_url( rtcl()->get_assets_uri( 'images/classified-listing-pro.jpg' ) ) ?>">
                    <div class="rtcl-product-info">
                        <h3 class="rtcl-p-title">
                            <a target="_blank" href="https://www.radiustheme.com/downloads/classified-listing-pro-wordpress/">Classified Listing Pro for WordPress</a>
                        </h3>
                        <div class="rtcl-p-action">
                            <a class="rtcl-p-buy button button-primary" target="_blank"
                               href="https://www.radiustheme.com/downloads/classified-listing-pro-wordpress/"><?php _e( "Buy", "classified-listing" ); ?></a>
                            <a class="rtcl-p-demo button" target="_blank"
                               href="https://radiustheme.com/demo/wordpress/classifiedpro/"><?php _e( "Live Demo", "classified-listing" ); ?></a>
                        </div>
                    </div>
                </div>
                <div class="rtcl-product">
                    <img src="<?php echo esc_url( rtcl()->get_assets_uri( 'images/store.jpg' ) ) ?>">
                    <div class="rtcl-product-info">
                        <h3 class="rtcl-p-title"><a target="_blank"
                                                    href="https://www.radiustheme.com/downloads/classified-listing-store-membership-addon-for-wordpress/">Classified
                                Listing Store & Membership addon for WordPress</a></h3>
                        <div class="rtcl-p-action">
                            <a class="rtcl-p-buy button button-primary" target="_blank"
                               href="https://www.radiustheme.com/downloads/classified-listing-store-membership-addon-for-wordpress/"><?php _e( "Buy", "classified-listing" ); ?></a>
                            <a class="rtcl-p-demo button" target="_blank"
                               href="https://radiustheme.com/demo/wordpress/classifiedpro/store-list/"><?php _e( "Live Demo", "classified-listing" ); ?></a>
                        </div>
                    </div>
                </div>
                <div class="rtcl-product">
                    <img src="<?php echo esc_url( rtcl()->get_assets_uri( 'images/theme-classima.png' ) ) ?>">
                    <div class="rtcl-product-info">
                        <h3 class="rtcl-p-title">
                            <a target="_blank" href="https://1.envato.market/2944O">Classima – Classified Ads WordPress Theme</a>
                        </h3>
                        <div class="rtcl-p-action">
                            <a class="rtcl-p-buy button button-primary" target="_blank"
                               href="https://1.envato.market/2944O"><?php _e( "Buy", "classified-listing" ); ?></a>
                            <a class="rtcl-p-demo button" target="_blank"
                               href="https://www.radiustheme.com/demo/wordpress/themes/classima/"><?php _e( "Live Demo", "classified-listing" ); ?></a>
                        </div>
                    </div>
                </div>
                <div class="rtcl-product">
                    <img src="<?php echo esc_url( rtcl()->get_assets_uri( 'images/theme.jpg' ) ) ?>">
                    <div class="rtcl-product-info">
                        <h3 class="rtcl-p-title"><a target="_blank"
                                                    href="https://www.radiustheme.com/downloads/classilist-classified-ads-wordpress-theme/">ClassiList
                                – Classified ads WordPress Theme</a></h3>
                        <div class="rtcl-p-action">
                            <a class="rtcl-p-buy button button-primary" target="_blank"
                               href="https://www.radiustheme.com/downloads/classilist-classified-ads-wordpress-theme/"><?php _e( "Buy", "classified-listing" ); ?></a>
                            <a class="rtcl-p-demo button" target="_blank"
                               href="https://www.radiustheme.com/demo/wordpress/themes/classilist"><?php _e( "Live Demo", "classified-listing" ); ?></a>
                        </div>
                    </div>
                </div>
            </div>

			<?php
		}
	}

}