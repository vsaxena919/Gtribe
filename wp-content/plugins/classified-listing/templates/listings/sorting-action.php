<?php

/**
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;
use Rtcl\Helpers\Pagination;

$general_settings = Functions::get_option( 'rtcl_general_settings' );
$orderby          = ! empty( $general_settings['orderby'] ) ? $general_settings['orderby'] : 'date';
$order            = ! empty( $general_settings['order'] ) ? $general_settings['order'] : 'DESC';
$current_order    = Pagination::get_listings_current_order( $orderby . '-' . $order );
$views            = Options::get_listings_view_options();
$view             = ( ! empty( $_GET['view'] ) && array_key_exists($_GET['view'], $views) ) ? $_GET['view'] : "list";
?>
<div class="rtcl listing-sorting text-right">
    <div class="ordering-controller mr-3">
        <button class="sortby-btn btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"
                aria-expanded="false"><?php _e( "Sort By", "classified-listing" ); ?>
        </button>
        <div class="dropdown-menu">
            <?php $options = Options::get_listings_orderby_options();

            foreach ( $options as $value => $label ) :
                $active_class = ( $value == $current_order ) ? ' active' : '';
                ?>
                <a class="dropdown-item <?php echo esc_attr($active_class); ?>"
                   href="<?php echo add_query_arg( 'sort', $value ) ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>