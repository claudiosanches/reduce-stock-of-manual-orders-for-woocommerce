<?php
/**
 * Reduce stock of manual orders for WooCommerce main class.
 *
 * @package RSMO_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * RSMO_WooCommerce main class.
 */
class RSMO_WooCommerce {

	/**
	 * Initialize the plugin public actions.
	 */
	public static function init() {
		// Load plugin text domain.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );

		// Make sure that is possible to reduce order stock.
		add_filter( 'woocommerce_can_reduce_order_stock', '__return_true', 999 );

		// Reduce or increase order stock when changing the order status on the admin screen.
		add_action( 'woocommerce_process_shop_order_meta', array( __CLASS__, 'admin_manage_stock' ), 45 );

		// Allow reduce or increase stock using bulk or the actions buttons on the order list screen.
		add_action( 'woocommerce_order_edit_status', array( __CLASS__, 'admin_bulk_manage_stock' ), 20, 2 );
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'reduce-stock-of-manual-orders-for-woocommerce', false, dirname( plugin_basename( RSMOW_PLUGIN_FILE ) ) . '/languages/' );
	}

	/**
	 * Nornalize order status.
	 *
	 * @param  string $status Order status.
	 *
	 * @return string
	 */
	protected static function normalize_order_status( $status ) {
		return 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
	}

	/**
	 * Reduce order stock.
	 *
	 * @param int $order_id Order ID.
	 */
	protected static function reduce_order_stock( $order_id ) {
		wc_reduce_stock_levels( $order_id );

		$data_store = WC_Data_Store::load( 'order' );
		$data_store->set_stock_reduced( $order_id, true );
	}

	/**
	 * Increase order stock.
	 *
	 * @param int $order_id Order ID.
	 */
	protected static function increase_order_stock( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( 'yes' === get_option( 'woocommerce_manage_stock' ) && $order && 0 < count( $order->get_items() ) ) {
			foreach ( $order->get_items() as $item ) {
				$product_id = $item->get_id();

				if ( 0 < $product_id ) {
					$product = $order->get_product_from_item( $item );

					if ( $product && $product->exists() && $product->managing_stock() ) {
						$old_stock = $product->get_stock_quantity();
						$quantity  = apply_filters( 'woocommerce_order_item_quantity', $item->get_quantity(), $order, $item );
						$new_stock = wc_update_product_stock( $product, $quantity, 'increase' );

						if ( ! empty( $item['variation_id'] ) ) {
							/* translators: 1: product name 2: variation ID 3: old stock level 4: new stock level */
							$order->add_order_note( sprintf( __( 'Item %1$s variation #%2$s stock increased from %3$s to %4$s.', 'reduce-stock-of-manual-orders-for-woocommerce' ), $product->get_formatted_name(), $item['variation_id'], $old_stock, $new_stock ) );
						} else {
							/* translators: 1: product name 2: old stock level 3: new stock level */
							$order->add_order_note( sprintf( __( 'Item %1$s stock increased from %2$s to %3$s.', 'reduce-stock-of-manual-orders-for-woocommerce' ), $product->get_formatted_name(), $old_stock, $new_stock ) );
						}

						$order->get_data_store()->set_stock_reduced( $order_id, false );
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
	 *
	 * @return bool
	 */
	protected static function can_reduce_stock( $order_id, $status ) {
		$status     = self::normalize_order_status( $status );
		$statuses   = apply_filters( 'rsmo_wc_reduce_stock_statuses', array( 'processing', 'completed' ) );
		$data_store = WC_Data_Store::load( 'order' );

		return in_array( $status, $statuses, true ) && ! $data_store->get_stock_reduced( $order_id );
	}

	/**
	 * Check if can increase stock.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $status Order status.
	 *
	 * @return bool
	 */
	protected static function can_increase_stock( $order_id, $status ) {
		$status     = self::normalize_order_status( $status );
		$statuses   = apply_filters( 'rsmo_wc_increase_stock_statuses', array( 'cancelled' ) );
		$data_store = WC_Data_Store::load( 'order' );

		return in_array( $status, $statuses, true ) && $data_store->get_stock_reduced( $order_id );
	}

	/**
	 * Reduce or increase order stock in the admin screen.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function admin_manage_stock( $order_id ) {
		$status = filter_input( INPUT_POST, 'order_status' );

		if ( self::can_reduce_stock( $order_id, $status ) ) {
			self::reduce_order_stock( $order_id );
		} elseif ( self::can_increase_stock( $order_id, $status ) ) {
			self::increase_order_stock( $order_id );
		}
	}

	/**
	 * Reduce or increase order stock using bulk or action buttons on the orders list screen.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $status Order status.
	 */
	public static function admin_bulk_manage_stock( $order_id, $status ) {
		if ( self::can_reduce_stock( $order_id, $status ) ) {
			self::reduce_order_stock( $order_id );
		}
	}
}
