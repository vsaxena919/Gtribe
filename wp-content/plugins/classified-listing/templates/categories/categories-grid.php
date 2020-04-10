<?php
/**
 *
 * @author        RadiusTheme
 * @package    classified-listing/templates
 * @version     1.0.0
 */


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

?>

<div class="rtcl rtcl-categories rtcl-categories-grid<?php echo esc_attr($settings['equal_height'] ? " rtcl-equal-height" : ''); ?>">
    <div class="row rtcl-no-margin">
		<?php
		$span = 'col-md-' . floor( 12 / $settings['columns'] );
		$i    = 0;
		foreach ( $terms as $term ) {

			$count = 0;
			if ( ! empty( $settings['hide_empty'] ) || ! empty( $settings['show_count'] ) ) {
				$count = Functions::get_listings_count_by_taxonomy( $term->term_id, rtcl()->category,
					$settings['pad_counts'] );

				if ( ! empty( $settings['hide_empty'] ) && 0 == $count ) {
					continue;
				}
			}


			echo '<div class="cat-item-wrap equal-item ' . $span . '">';
			echo '<div class="cat-details text-center">';
			echo "<div class='icon'>";
			$icon_id = get_term_meta( $term->term_id, '_rtcl_icon', true );
			if ( $icon_id && $settings['icon'] ) {
				printf( '<a href="%s" title="%s"><span class="rtcl-icon rtcl-icon-%s"></span></a>',
					Link::get_category_page_link( $term ),
					sprintf( __( "View all posts in %s", 'classified-listing' ), $term->name ),
					$icon_id
				);
			}
			echo "</div>";
			printf( "<h3><a href='%s' title='%s'>%s</a></h3>",
				Link::get_category_page_link( $term ),
				sprintf( __( "View all posts in %s", 'classified-listing' ), $term->name ),
				$term->name
			);

			if ( ! empty( $settings['show_count'] ) ) {
				printf( "<div class='views'>(%d)</div>", $count );
			}
			if ( $settings['description'] && $term->description ) {
				printf( "<p>%s</p>", esc_html( $term->description ) );
			}
			echo '</div>';
			echo '</div>';
		}
		?>
    </div>
</div>