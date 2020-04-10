<?php

use Rtcl\Helpers\Functions;

?>
<div class="panel-block">
    <form class="rtcl-filter-form" method="GET">
		<?php do_action( 'rtcl_widget_before_filter_form' ) ?>
        <div class="ui-accordion">
			<?php Functions::print_html( $category_filter, true ); ?>
			<?php Functions::print_html( $location_filter, true ); ?>
			<?php Functions::print_html( $ad_type_filter, true ); ?>
			<?php Functions::print_html( $price_filter, true ); ?>
			<?php do_action( 'rtcl_widget_filter_form' ) ?>
        </div>
		<?php do_action( 'rtcl_widget_after_filter_form' ) ?>
    </form>
</div>
