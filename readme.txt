=== Reduce stock of manual orders for WooCommerce ===
Contributors: claudiosanches
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RBVENSVSKY7JC
Tags: woocommerce, reduce, increase, stock
Requires at least: 4.0
Tested up to: 4.9
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically reduce or increase stock levels of manual orders in WooCommerce

== Description ==

Automatically reduce stock levels when:

- Order status is changed to *Processing* or *Completed* in order screen or using the bulk actions on orders list screen.

Automatically increase stock levels when:

- Order status is changed to *Canceled* in order screen or using the bulk actions on orders list screen.

#### Instructions ####

1. This plugin does not require any kind of configuration, just install and start update the orders statuses in the admin screen to update the stock levels too.
2. Requires WooCommerce 3 or later to work.

#### Contribute ####

You can contribute to the source code in our [GitHub](https://github.com/claudiosanches/reduce-stock-of-manual-orders-for-woocommerce) page.

== Installation ==

* Upload plugin files to your plugins folder, or install using WordPress built-in Add New Plugin installer;
* Activate the plugin.

Note that is not necessary any kind of configuration.

== Changelog ==

= 2.0.0 - 2018/05/11 =

- Full support to WooCommerce 3.x.
- Dropped support for legacy versions of WooCommerce, like WooCommerce 2.7.

= 1.0.1 - 2017/01/08 =

- Fixed how reduce order when using the order quick action buttons.
- Introduced the `rsmo_wc_reduce_stock_statuses` and `rsmo_wc_increase_stock_statuses` filters.

= 1.0.0 - 2016/09/14 =

- Initial plugin version.

== Upgrade Notice ==

= 2.0.0 =

- Full support to WooCommerce 3.x.
- Dropped support for legacy versions of WooCommerce, like WooCommerce 2.7.
