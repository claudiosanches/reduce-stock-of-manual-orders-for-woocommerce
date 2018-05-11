<?php
/**
 * Plugin Name:          Reduce stock of manual orders for WooCommerce
 * Plugin URI:           https://github.com/claudiosanches/reduce-stock-of-manual-orders-for-woocommerce
 * Description:          Automatically reduce or increase stock levels of manual orders in WooCommerce.
 * Author:               Claudio Sanches
 * Author URI:           https://claudiosanches.com
 * Version:              2.0.0
 * License:              GPLv3
 * Text Domain:          reduce-stock-of-manual-orders-for-woocommerce
 * Domain Path:          /languages
 * WC requires at least: 3.0.0
 * WC tested up to:      3.4.0
 *
 * Reduce stock of manual orders for WooCommerce is free software:
 * you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or any later version.
 *
 * Reduce stock of manual orders for WooCommerce is distributed in the hope
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Reduce stock of manual orders for WooCommerce. If not, see
 * <https://www.gnu.org/licenses/gpl-3.0.txt>.
 *
 * @package RSMO_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

define( 'RSMOW_VERSION', '2.0.0' );
define( 'RSMOW_PLUGIN_FILE', __FILE__ );

if ( ! class_exists( 'RSMO_WooCommerce' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-rsmo-woocommerce.php';

	add_action( 'plugins_loaded', array( 'RSMO_WooCommerce', 'init' ) );
}
