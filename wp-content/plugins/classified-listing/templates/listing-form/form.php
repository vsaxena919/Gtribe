<?php
/**
 * Login Form
 *
 * @author 		RadiusTheme
 * @package 	classified-listing/templates
 * @version     1.0.0
 */

?>

<div class="rtcl rtcl-user rtcl-post-form-wrap">
    <form action="" method="post" id="rtcl-post-form" class="form-vertical">
        <div class="rtcl-post">
            <?php do_action("rtcl_listing_form", $post_id); ?>
        </div>
		<?php wp_nonce_field( rtcl()->nonceText, rtcl()->nonceId ); ?>
        <input type="hidden" name="_post_id" id="_post_id" value="<?php echo absint($post_id); ?>"/>
        <button type="submit" class="btn btn-primary rtcl-submit-btn">
            <?php
            if ($post_id > 0) {
                esc_html_e('Update', 'classified-listing');
            } else {
                esc_html_e('Submit', 'classified-listing');
            }
            ?>
        </button>
    </form>
    <!-- Display response -->
    <div class="rtcl-response"></div>
</div>