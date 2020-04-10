<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var string $phone
 * @var string $email
 * @var string $website
 * @var bool   $has_contact_form
 * @var string $email_to_seller_form
 * @var int    $listing_id Listing id
 *
 */

use Rtcl\Helpers\Functions;

?>
<div class="rtcl-listing-user-info">
    <div class="rtcl-listing-side-title">
        <h3><?php esc_html_e("Contact", 'classified-listing'); ?></h3>
    </div>
    <?php if (count($locations) || $phone || $email || $website) : ?>
        <div class="list-group">
            <?php
            if (!empty($locations)) : ?>
                <div class='list-group-item'>
                    <div class='media'>
                        <span class='rtcl-icon rtcl-icon-location mr-3'></span>
                        <div class='media-body'><span><?php _e("Location", "classified-listing") ?></span>
                            <div class='locations'><?php echo implode('<span class="rtcl-delimiter">,</span> ',
                                    $locations) ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($phone) :
                $safe_phone = substr_replace($phone, 'XXX', -3);
                $mobileClass = wp_is_mobile() ? " rtcl-mobile" : null;
                $phone_options = [
                    'safe_phone'   => $safe_phone,
                    'phone_hidden' => substr($phone, -3)
                ];
                ?>
                <div class='list-group-item reveal-phone<?php echo esc_attr($mobileClass); ?>'
                     data-options="<?php echo htmlspecialchars(wp_json_encode($phone_options)); ?>">
                    <div class='media'>
                        <span class='rtcl-icon rtcl-icon-phone mr-3'></span>
                        <div class='media-body'><span><?php esc_html_e("Contact Number",
                                    "classified-listing"); ?></span>
                            <div class='numbers'><?php echo esc_html($safe_phone); ?></div>
                            <small class='text-muted'><?php esc_html_e("Click to reveal phone number",
                                    "classified-listing") ?></small>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if ($has_contact_form && $email) : ?>
                <div class='rtcl-do-email list-group-item'>
                    <div class='media'>
                        <span class='rtcl-icon rtcl-icon-mail mr-3'></span>
                        <div class='media-body'><a class="rtcl-do-email-link" href='#'><span><?php esc_html_e("Email",
                                        "classified-listing") ?></span></a>
                        </div>
                    </div>
                    <?php Functions::print_html($email_to_seller_form, true); ?>
                </div>
            <?php endif; ?>

            <?php do_action('rtcl_add_user_information', $listing_id); ?>

            <?php if ($website) : ?>
                <div class='rtcl-website list-group-item'>
                    <a class="rtcl-website-link btn btn-primary" href="<?php echo esc_url($website); ?>"
                       target="_blank"<?php echo Functions::is_external($website) ? ' rel="nofollow"' : ''; ?>><span
                                class='rtcl-icon rtcl-icon-globe text-white'></span><?php esc_html_e("Visit Website", "classified-listing") ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

