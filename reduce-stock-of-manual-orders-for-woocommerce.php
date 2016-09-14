<?php
/**
 * Plugin Name: Reduce stock of manual orders for WooCommerce
 * Plugin URI: https://github.com/claudiosmweb/reduce-stock-of-manual-orders-for-woocommerce
 * Description: Auto reduces/increases stock levels of orders saved on the admin interface.
 * Author: Claudio Sanches
 * Author URI: https://claudiosmweb.com/
 * Version: 0.0.1
 * License: GPLv2 or later
 * Text Domain: reduce-stock-of-manual-orders-for-woocommerce
 * Domain Path: /languages/
 *
 * @package RSMO_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RSMO_WooCommerce' ) ) :

	/**
	 * RSMO_WooCommerce main class.
	 */
	class RSMO_WooCommerce {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		const VERSION = '0.0.1';

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Initialize the plugin public actions.
		 */
		private function __construct() {
			// Load plugin text domain.
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Make sure that is possible to reduce order stock.
			add_filter( 'woocommerce_can_reduce_order_stock', '__return_true', 999 );

			// Reduce or increase order stock when changing the order status on the admin screen.
			add_action( 'woocommerce_process_shop_order_meta', array( $this, 'admin_reduce_or_increase_order_stock' ), 45 );
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null === self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Load the plugin text domain for translation.
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'reduce-stock-of-manual-orders-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Reduce order stock.
		 *
		 * @param int $order_id Order ID.
		 */
		protected function reduce_order_stock( $order_id ) {
			// Support for WooCommerce 2.7.
			if ( function_exists( 'wc_reduce_stock_levels' ) ) {
				wc_reduce_stock_levels( $order_id );
			} else {
				$order = wc_get_order( $order_id );
				$order->reduce_order_stock();
			}

			add_post_meta( $order_id, '_order_stock_reduced', '1', true );
		}

		/**
		 * Reduce or increase order stock in the admin screen.
		 *
		 * @param int $order_id Order ID.
		 */
		public function admin_reduce_or_increase_order_stock( $order_id ) {
			$status = filter_input( INPUT_POST, 'order_status' );

			if ( in_array( $status, array( 'wc-processing', 'wc-completed' ), true ) && '1' !== get_post_meta( $order_id, '_order_stock_reduced', true ) ) {
				$this->reduce_order_stock( $order_id );
			}
		}
	}

	add_action( 'plugins_loaded', array( 'RSMO_WooCommerce', 'get_instance' ) );

endif;
