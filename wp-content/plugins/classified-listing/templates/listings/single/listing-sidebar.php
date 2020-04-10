<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.1.4
 */
?>

<!-- Seller / User Information -->
<div class="<?php echo esc_attr( $sidebar_class ); ?>">
    <div class="listing-sidebar">
		<?php $listing->the_user_info(); ?>
    </div>
</div>
