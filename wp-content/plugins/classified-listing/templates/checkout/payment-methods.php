<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 */

use Rtcl\Helpers\Functions;

?>


<div id="rtcl-payment-methods">
    <h4 class="pm-heading"><?php esc_html_e('Choose payment method', 'classified-listing'); ?></h4>
    <?php
    $gateways = rtcl()->payment_gateways();
    $list = array();
    if (!empty($gateways)) { ?>
        <ul class="list-group form-group"> <?php
            foreach ($gateways as $gateway) {
                if ('yes' === $gateway->enabled) {
                    Functions::get_template('checkout/payment-method', ['gateway' => $gateway]);
                }
            } ?>
        </ul>
    <?php } ?>
</div>
