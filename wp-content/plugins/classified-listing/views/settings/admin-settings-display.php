<div class="wrap rtcl-settings">
    <?php
    settings_errors();
    self::show_messages();
    ?>

    <h2 class="nav-tab-wrapper">
        <?php
        foreach ( $this->tabs as $slug => $title ) {
            $class = "nav-tab nav-".$slug;
            if ( $this->active_tab == $slug ) {
                $class .= ' nav-tab-active';
            }
            echo '<a href="?post_type=' . rtcl()->post_type . '&page=rtcl-settings&tab=' . $slug . '" class="' . $class . '">' . $title . '</a>';
        }
        ?>
    </h2>
    <?php
    if ( ! empty( $this->subtabs ) ) {
        echo '<ul class="subsubsub">';
        $array_keys = array_keys( $this->subtabs );
        foreach ( $this->subtabs as $id => $label ) {
            echo '<li><a href="' . admin_url( 'edit.php?post_type=' . rtcl()->post_type . '&page=rtcl-settings&tab=payment&section=' . sanitize_title( $id ) ) . '" class="' . ( $this->current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
        }
        echo '</ul><br class="clear" />';
    }
    ?>

    <form method="post" action="">
        <?php
        do_action( 'rtcl_admin_settings_groups', $this->active_tab, $this->current_section );
        wp_nonce_field( 'rtcl-settings' );
        submit_button();
        ?>
    </form>

    <div class="rtcl-get-pro">
        <a href="https://www.radiustheme.com/downloads/classilist-classified-ads-wordpress-theme/" target="_blank">
            <img src="<?php echo esc_url(RTCL_URL . '/assets/images/banner.png') ?>">
        </a>
    </div>
</div>