<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var array $pricing_options
 */


use Rtcl\Helpers\Functions;

?>
<table id="rtcl-checkout-form-data" class="form-group table table-hover table-stripped table-bordered">
    <tr>
        <th><?php esc_html_e("Payment Option", "classified-listing"); ?></th>
        <th><?php esc_html_e("Description", "classified-listing"); ?></th>
        <th><?php esc_html_e("Visibility", "classified-listing"); ?></th>
        <th><?php printf(__('Price [%s %s]', 'classified-listing'),
                Functions::get_currency(true),
                Functions::get_currency_symbol(null, true)); ?></th>
    </tr>
    <?php foreach ($pricing_options as $option) :
        $price = get_post_meta($option->ID, 'price', true);
        $visible = get_post_meta($option->ID, 'visible', true);
        $featured = get_post_meta($option->ID, 'featured', true);
        $top = get_post_meta($option->ID, '_top', true);
        $description = get_post_meta($option->ID, 'description', true);
        ?>
        <tr>
            <td class="form-check">
                <?php
                printf('<label><input type="radio" name="%s" value="%s" class="rtcl-checkout-payment-option" required data-price="%s"/> %s</label>',
                    'pricing_id', $option->ID, $price, $option->post_title);
                ?>
            </td>
            <td><?php echo esc_html($description); ?></td>
            <td>
                <?php
                printf('%s %s',
                    sprintf(_n('%s Day', '%s Days', absint($visible), 'classified-listing'), number_format_i18n(absint($visible))),
                    $featured ? '<span class="badge badge-info">' . __('Featured', 'classified-listing') . '</span>' : null
                );
                ?>
            </td>
            <td align="right"
                class="text-right"><?php echo Functions::get_formatted_amount($price, true); ?> </td>
        </tr>
    <?php endforeach; ?>
</table>