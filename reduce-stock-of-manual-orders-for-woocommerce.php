<?php
/**
 * Plugin Name: Reduce stock of manual orders for WooCommerce
 * Plugin URI: https://github.com/claudiosmweb/reduce-stock-of-manual-orders-for-woocommerce
 * Description: Automatically reduce or increase stock levels of manual orders in WooCommerce.
 * Author: Claudio Sanches
 * Author URI: https://claudiosmweb.com/
 * Version: 1.0.0
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
		const VERSION = '1.0.0';

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
			add_action( 'woocommerce_process_shop_order_meta', array( $this, 'admin_manage_stock' ), 45 );

			// Allow reduce or increase stock using bulk or the actions buttons on the order list screen.
			add_action( 'woocommerce_order_edit_status', array( $this, 'admin_bulk_manage_stock' ), 20, 2 );
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
		 * Increase order stock.
		 *
		 * @param int $order_id Order ID.
		 */
		protected function increase_order_stock( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( 'yes' === get_option( 'woocommerce_manage_stock' ) && $order && 0 < count( $order->get_items() ) ) {
				foreach ( $order->get_items() as $item ) {
					// Support for WooCommerce 2.7.
					if ( is_callable( array( $item, 'get_id' ) ) ) {
						$product_id = $item->get_id();
					} else {
						$product_id = $item['product_id'];
					}

					if ( 0 < $product_id ) {
						$product = $order->get_product_from_item( $item );

						if ( $product && $product->exists() && $product->managing_stock() ) {
							$old_stock = $product->stock;

							// Support for WooCommerce 2.7.
							if ( is_callable( array( $item, 'get_quantity' ) ) ) {
								$quantity = apply_filters( 'woocommerce_order_item_quantity', $item->get_quantity(), $order, $item );
							} else {
								$quantity = apply_filters( 'woocommerce_order_item_quantity', $item['qty'], $order, $item );
							}

							$new_stock = $product->increase_stock( $quantity );
							$item_name = $product->get_sku() ? $product->get_sku() : $item['product_id'];

							if ( ! empty( $item['variation_id'] ) ) {
								$order->add_order_note( sprintf( __( 'Item %1$s variation #%2$s stock increased from %3$s to %4$s.', 'reduce-stock-of-manual-orders-for-woocommerce' ), $item_name, $item['variation_id'], $old_stock, $new_stock ) );
							} else {
								$order->add_order_note( sprintf( __( 'Item %1$s stock increased from %2$s to %3$s.', 'reduce-stock-of-manual-orders-for-woocommerce' ), $item_name, $old_stock, $new_stock ) );
							}

							delete_post_meta( $order_id, '_order_stock_reduced' );
						}
					}
				}
			}
		}

		/**
		 * Check if can reduce stock.
		 *
		 * @param int    $order_id Order ID.
		 * @param string $status Order status.
		 */
		protected function can_reduce_stock( $order_id, $status ) {
			return in_array( $status, array( 'wc-processing', 'wc-completed' ), true ) && '1' !== get_post_meta( $order_id, '_order_stock_reduced', true );
		}

		/**
		 * Check if can increase stock.
		 *
		 * @param int    $order_id Order ID.
		 * @param string $status Order status.
		 */
		protected function can_increase_stock( $order_id, $status ) {
			return 'wc-cancelled' === $status && '1' === get_post_meta( $order_id, '_order_stock_reduced', true );
		}

		/**
		 * Reduce or increase order stock in the admin screen.
		 *
		 * @param int $order_id Order ID.
		 */
		public function admin_manage_stock( $order_id ) {
			$status = filter_input( INPUT_POST, 'order_status' );

			if ( $this->can_reduce_stock( $order_id, $status ) ) {
				$this->reduce_order_stock( $order_id );
			} elseif ( $this->can_increase_stock( $order_id, $status ) ) {
				$this->increase_order_stock( $order_id );
			}
		}

		/**
		 * Reduce or increase order stock using bulk or action buttons on the orders list screen.
		 *
		 * @param int    $order_id Order ID.
		 * @param string $status Order status.
		 */
		public function admin_bulk_manage_stock( $order_id, $status ) {
			if ( $this->can_reduce_stock( $order_id, $status ) ) {
				$this->reduce_order_stock( $order_id );
			}
		}
	}

	add_action( 'plugins_loaded', array( 'RSMO_WooCommerce', 'get_instance' ) );

endif;
