<?php
/**
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 */


use Rtcl\Helpers\Link;
use Rtcl\Models\Listing;

global $post;
$listing = new Listing($post->id);

if (!$listing->can_show_date() && !$listing->can_show_user() && !$listing->can_show_category() && !$listing->can_show_location() && !$listing->can_show_views()) {
    return;
}
?>

<div class="rtcl-listing-meta-data">
    <?php if ($listing->can_show_date()): ?>
        <span class="updated"><i class="rtcl-icon rtcl-icon-clock"></i>&nbsp;<?php $listing->the_time(); ?></span>
    <?php endif; ?>
    <?php if ($listing->can_show_user()): ?>
        <span class="author">
			<?php esc_html_e('by ', 'classified-listing'); ?>
            <?php $listing->the_author(); ?>
            </span>
    <?php endif; ?>
    <?php if ($listing->has_category() && $listing->can_show_category()):
        $category = $listing->get_categories();
        $category = end($category);
        ?><span class="rt-categories"><i class="rtcl-icon rtcl-icon-tags"></i>
        &nbsp;<?php echo esc_html($category->name) ?></span>
    <?php endif; ?>
    <?php if ($listing->has_location() && $listing->can_show_location()): ?>
        <span class="rt-location">
            <i class="rtcl-icon rtcl-icon-location"></i> <?php $listing->the_locations() ?>
        </span>
    <?php endif; ?>
    <?php if ($listing->can_show_views()): ?>
        <span class="rt-views">
            <i class="rtcl-icon rtcl-icon-eye"></i>
            <?php echo sprintf(_n("%s view", "%s views", $listing->get_view_counts(), 'classified-listing'), number_format_i18n($listing->get_view_counts())); ?>
        </span>
    <?php endif; ?>
</div>
