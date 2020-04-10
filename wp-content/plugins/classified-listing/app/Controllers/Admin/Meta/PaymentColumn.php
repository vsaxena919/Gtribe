<?php

namespace Rtcl\Controllers\Admin\Meta;


use Rtcl\Helpers\Functions;
use Rtcl\Models\Payment;
use Rtcl\Resources\Options;

class PaymentColumn
{

	public function __construct() {
		add_action('manage_edit-' . rtcl()->post_type_payment . '_columns',
			array($this, 'payment_get_columns'));
		add_action('manage_' . rtcl()->post_type_payment . '_posts_custom_column',
			array($this, 'payment_custom_column_content'), 10, 2);
		add_action('manage_edit-' . rtcl()->post_type_payment . '_sortable_columns',
			array($this, 'get_sortable_columns'));
		add_action('parse_query', array($this, 'parse_query'));
		add_action('restrict_manage_posts', array($this, 'restrict_manage_posts'));
//		add_action( 'before_delete_post', array( $this, 'before_delete_post' ) );
		add_action('post_row_actions', array($this, 'remove_row_actions'), 10, 2);
	}

	function payment_get_columns() {
		return array(
			'cb'             => '<input type="checkbox" />', // Render a checkbox instead of text
			'ID'             => __('Order ID', 'classified-listing'),
			'type'           => __('Type', 'classified-listing'),
			'total'          => __('Total', 'classified-listing'),
			'transaction_id' => __('Transaction ID', 'classified-listing'),
			'date'           => __('Date', 'classified-listing'),
			'status'         => __('Status', 'classified-listing')
		);
	}

	public function payment_custom_column_content($column, $post_id) {

		global $post;

		$payment = rtcl()->factory->get_order($post_id);
		switch ($column) {
			case 'ID' :
				if ($payment->get_customer_id() && $user = get_user_by('id', $payment->get_customer_id())) {
					$username = '<a href="user-edit.php?user_id=' . absint($payment->get_customer_id()) . '">';
					$username .= esc_html(ucwords($user->display_name));
					$username .= '</a>';
					$userEmail = sprintf('<small class="meta email"><a href="%s">%s</a></small>',
						esc_url('mailto:' . $user->user_email),
						esc_html($user->user_email)
					);
				} else {
					$userEmail = '';
					$username = __('Guest', 'classified-listing');
				}

				/* translators: 1: order and number (i.e. Order #13) 2: user name */
				printf('<a href="%s">#%d</a> by %s %s',
					get_edit_post_link($post_id),
					$post_id,
					$username,
					$userEmail
				);
				break;
			case 'type' :
				$types = Options::get_pricing_types();
				$type = $payment->pricing->getType();
				echo $types[$type];
				break;
			case 'total' :
				$title = $payment->get_payment_method_title();
				printf("%s<small class='meta'>%s</small>",
					Functions::get_formatted_price($payment->get_total(), true),
					$payment->get_total() === 0 ? $title : sprintf(__('Pay via <strong>%s</strong>', 'classified-listing'), $title)
				);
				break;
			case 'transaction_id' :
				echo $payment->get_transaction_id();
				break;
			case 'date' :
				$date = strtotime($post->post_date);
				$value = date_i18n(get_option('date_format'), $date);

				echo $value;
				break;
			case 'status' :
				echo Functions::get_status_i18n($payment->get_status());
				break;
		}

	}

	public function get_sortable_columns() {

		return array(
			'ID'    => 'ID',
			'total' => 'amount',
			'date'  => 'date'
		);

	}

	function parse_query($query) {

		global $pagenow, $post_type;

		if ('edit.php' == $pagenow && rtcl()->post_type_payment == $post_type) {

			// Filter by post meta "payment_status"
			if (isset($_GET['payment_status']) && $_GET['payment_status'] != '') {
				$query->query_vars['meta_key'] = 'payment_status';
				$query->query_vars['meta_value'] = sanitize_key($_GET['payment_status']);
			}

		}
	}

	public function restrict_manage_posts() {

		global $typenow, $wp_query;

		if (rtcl()->post_type_payment == $typenow) {

			// Restrict by payment status
			$statuses = Options::get_payment_status_list();
			$current_status = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';

			echo '<select name="payment_status">';
			echo '<option value="all">' . __("All payments", 'classified-listing') . '</option>';
			foreach ($statuses as $value => $title) {
				printf('<option value="%s"%s>%s</option>', $value,
					($value == $current_status ? ' selected="selected"' : ''), $title);
			}
			echo '</select>';

		}

	}

	public function remove_row_actions($actions, $post) {

		global $current_screen;

		if (is_object($current_screen) && $current_screen->post_type === rtcl()->post_type_payment) {
			unset($actions['view']);
			unset($actions['inline hide-if-no-js']);

			return $actions;
		}

		return $actions;

	}

}