<?php

/**
 * Plugin Name: Reports
 *
 * @package WooCommerce\Admin
 */

/**
 * Register the JS and CSS.
 */
function add_extension_register_script()
{
	if (
		!method_exists('Automattic\WooCommerce\Admin\Loader', 'is_admin_or_embed_page') ||
		!\Automattic\WooCommerce\Admin\Loader::is_admin_or_embed_page()
	) {
		return;
	}


	$script_path       = '/build/index.js';
	$script_asset_path = dirname(__FILE__) . '/build/index.asset.php';
	$script_asset      = file_exists($script_asset_path)
		? require($script_asset_path)
		: array('dependencies' => array(), 'version' => filemtime($script_path));
	$script_url = plugins_url($script_path, __FILE__);

	wp_register_script(
		'reports',
		$script_url,
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	wp_register_style(
		'reports',
		plugins_url('/build/index.css', __FILE__),
		// Add any dependencies styles may have, such as wp-components.
		array(),
		filemtime(dirname(__FILE__) . '/build/index.css')
	);

	wp_enqueue_script('reports');
	wp_enqueue_style('reports');
}

add_action('admin_enqueue_scripts', 'add_extension_register_script');

// /**
//  * Register a WooCommerce Admin page.
//  */
// function add_extension_register_page()
// {
// 	if (!function_exists('wc_admin_register_page')) {
// 		return;
// 	}

// 	wc_admin_register_page(array(
// 		'id'       => 'my-example-page',
// 		'title'    => __('My Example Page', 'my-textdomain'),
// 		'parent'   => 'woocommerce',
// 		'path'     => '/example',
// 		'nav_args' => array(
// 			'order'  => 10,
// 			'parent' => 'woocommerce',
// 		),
// 	));
// }

// add_action('admin_menu', 'add_extension_register_page');

add_filter('woocommerce_analytics_taxes_select_query', function ($results, $args) {

	if ($results && isset($results->data) && !empty($results->data)) {
		global $wpdb;
		$date_after = '';
		$date_before = '';
		$total_net_sales = 0;
		if (isset($_GET['after'])) {
			$date_after = $_GET['after'];
		}
		if (isset($_GET['before'])) {
			$date_before = $_GET['before'];
		}
		if (isset($_GET['period']) && $_GET['period'] == 'today') {
			$date_after = date('y-m-d');
			$date_before = date('y-m-d');
		}
		if (isset($_GET['period']) && $_GET['period'] == 'yesterday') {
			$date_after = date('y-m-d', strtotime("-1 days"));
			$date_before = date('y-m-d', strtotime("-1 days"));
		}
		if (isset($_GET['period']) && $_GET['period'] == 'yesterday') {
			$date_after = date('y-m-d', strtotime("-1 days"));
			$date_before = date('y-m-d', strtotime("-1 days"));
		}
		if (isset($_GET['period']) && $_GET['period'] == 'month') {
			$current_date = date('y-m-d');
			$date_after = date('y-m-01', strtotime($current_date));
			$date_before = date('y-m-d');
		}
		if (isset($_GET['period']) && $_GET['period'] == 'last_month') {
			$date_after = date('y-m-d', strtotime('first day of last month'));
			$date_before = date('y-m-d', strtotime('last day of last month'));
		}
		foreach ($results->data as $key => $result) {
			$tax_rate_id = $result['tax_rate_id'];
			$sql = "SELECT order_id  FROM fsft_wc_order_tax_lookup WHERE tax_rate_id='$tax_rate_id'";
			$order_id = $wpdb->get_results($sql);
			$total = 0;
			foreach ($order_id as $key1 => $orderid) {
				$id = $orderid->order_id;
				$sql_1 = "SELECT net_total  FROM fsft_wc_order_stats WHERE order_id ='$id' and DATE(date_created) >= '$date_after' and DATE(date_created) <= '$date_before' and status='wc-completed' ";
				$order_subtotal = $wpdb->get_results($sql_1);
				foreach ($order_subtotal as $key1 => $subtotal) {
					$total += $subtotal->net_total;
				}
			}
			$total = number_format((float)$total, 2, '.', '');
			// $sql_1 = "SELECT net_total  FROM fsft_wc_order_stats WHERE order_id ='$order_id'";
			// $order_subtotal = $wpdb->get_results($sql_1);
			$total_net_sales += $total;
			$results->data[$key]['net_sale'] = $total;
		}
	}
	// $results->data['resusts']['total_values'] = $total_net_sales;
	return $results;
}, 10, 2);
function add_select_subquery($clauses)
{


	return $clauses;
}

add_filter('woocommerce_analytics_clauses_select_taxes_stats_total', 'add_select_subquery');
// add_filter('woocommerce_analytics_clauses_select_taxes_subquery', function ($results, $args) {
// 	if ($results && isset($results->data) && !empty($results->data)) {
// 		foreach ($results->data as $key => $result) {
// 			$order = wc_get_order($result['order_id']);

// 			//get the order item data here
// 			// ...........................

// 			//here is how i did it for the customers phone number
// 			$order_subtotal = $order->get_subtotal();
// 			// Get the correct number format (2 decimals)
// 			$order_subtotal = number_format($order_subtotal, 2);
// 			$results->data[$key]['net_sale'] = $order_subtotal;
// 		}
// 	}

// 	return $results;
// }, 10, 2);

/**
 * Add the phone number column to the CSV file
 * @param $export_columns
 * @return mixed
 */
add_filter('woocommerce_report_taxes_export_columns', function ($export_columns) {
	$export_columns['net_sale'] = 'Net Sales';
	return $export_columns;
});

/**
 * Add the phone number data to the CSV file
 * @param $export_item
 * @param $item
 * @return mixed
 */
add_filter('woocommerce_report_taxes_prepare_export_item', function ($export_item, $item) {
	$export_item['net_sale'] = $item['net_sale'];
	return $export_item;
}, 10, 2);
