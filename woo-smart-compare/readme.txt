=== WPC Smart Compare for WooCommerce ===
Contributors: wpclever
Donate link: https://wpclever.net
Tags: woocommerce, woo, smart, compare, product compare, smart compare, wpc
Requires at least: 4.0
Tested up to: 5.2.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WPC Smart Compare plugin is an extension of WooCommerce plugin that allows your users to compare some products of your shop. You can quickly access to the compare table (powerful AJAX without open new page or iframe), rearrange the compare products with drag/drop.

== Description ==

WPC Smart Compare plugin is an extension of WooCommerce plugin that allows your users to compare some products of your shop. You can quickly access to the compare table (powerful AJAX without open new page or iframe), rearrange the compare products with drag/drop.

= Live demo =

Click to see [live demo](https://demo.wpclever.net/woosc/ "live demo")

= Features =

- Powerful AJAX functions (don't need to open new page or iframe)
- Drag/drop to rearrange products
- Fully responsive & friendly with touch devices
- Only show the Compare button for products in selected categories (NEW)
- Save data for each logged user (like the Wishlist functionally)
- The button to search product and add to compare list immediately
- Support all attributes (Premium Version)
- Support custom fields (Premium Version)
- Unlimited colors
- WPML integration

= Translators =

Available languages: English (Default), Italian, Vietnamese

If you have created your own language pack, or have an update for an existing one, you can send [gettext PO and MO file](http://codex.wordpress.org/Translating_WordPress "Translating WordPress") to [us](https://wpclever.net/contact "WPclever.net") so we can bundle it into WPC Smart Compare.

= Need more features? =

Please try other plugins from us

- [WPC Smart Wishlist](https://wordpress.org/plugins/woo-smart-wishlist/ "WPC Smart Wishlist")
- [WPC Smart Quick View](https://wordpress.org/plugins/woo-smart-quick-view/ "WPC Smart Quick View")

= Need support? =

Visit [plugin documentation website](https://wpclever.net "plugin documentation")

== Installation ==

1. Please make sure that you installed WooCommerce
2. Go to Plugins in your dashboard and select "Add New"
3. Search for "WPC Smart Compare", Install & Activate it
4. Now you can see the Compare button on each product
5. Go to settings page to configure the compare button/bar/table as you want

== Frequently Asked Questions ==

= How to integrate with my theme? =

To integrate with a theme, please use bellow filter to hide the default buttons.

`add_filter( 'filter_wooscp_button_archive', function() {
    return '0';
} );
add_filter( 'filter_wooscp_button_single', function() {
    return '0';
} );`

After that, use the shortcode to display the button where you want.

`echo do_shortcode('[wooscp id="{product_id}"]');`

== Changelog ==

= 2.7.0 =
* Added: The button to search product and add to compare list immediately (Free Version)
* Added: Support custom fields (Premium Version)
* Updated: Optimized the code

= 2.6.9 =
* Added: Filter for button html 'wooscp_button_html'
* Updated: Optimized the code

= 2.6.8 =
* Fixed: Default fields on the first install
* Added: Italian translation

= 2.6.7 =
* Added: Choose image size option
* Updated: Compatible with WooCommerce 3.6.x

= 2.6.6 =
* Updated: Display attributes list with checkbox in settings page (Premium Version)
* Updated: Optimized the code

= 2.6.5 =
* Updated: Optimized the code

= 2.6.4 =
* Added: Option to limit the product on compare table
* Added: Option to turn on/off freeze row or column
* Added: Filter for attributes

= 2.6.3 =
* Added: Only show the Compare button for products in selected categories
* Fixed: Button text can be translated

= 2.6.2 =
* Updated: Optimized the code

= 2.6.1 =
* Updated: Compatible with WordPress 5.0.2

= 2.6.0 =
* Fixed: Minor JS issue

= 2.5.9 =
* Updated: Changed plugin name
* Updated: Optimized the code

= 2.5.8 =
* Updated: Compatible with WooCommerce 3.5.0

= 2.5.7 =
* Updated: Optimize the code to reduce the loading time

= 2.5.6 =
* Fixed: Error when WooCommerce is not active

= 2.5.5 =
* Fixed: Error when have no product on the compare bar

= 2.5.4 =
* Fixed: JS trigger
* Fixed: Some minor CSS issues

= 2.5.3 =
* Updated: Compatible with WooCommerce 3.4.4

= 2.5.2 =
* Updated: The settings page style

= 2.5.1 =
* Added: Option "Hide if empty"
* Added: Option "Click outside to hide"

= 2.5.0 =
* Added: Button remove all products
* Added: Option to choose categories for search function (Premium Version)

= 2.4.0 =
* Updated: Optimized the code
* Added: The button to search product and add to compare list immediately (Premium Version)

= 2.3.2 =
* Fixed: Remove the warning on the line 883

= 2.3.1 =
* Added: The compare menu item, click to open the compare table
* Updated: Compatible with WooCommerce 3.4.2

= 2.3.0 =
* Updated: Compatible with WooCommerce 3.3.5

= 2.2.9 =
* Updated: Compatible with WordPress 4.9.5

= 2.2.8 =
* Added: Close button (optional)
* Added: Content field

= 2.2.7 =
* Added: Placeholder columns when have smaller than 3 products on compare table

= 2.2.6 =
* Updated: Compatible with WooCommerce 3.3.3
* Fixed: Saved fields in settings page

= 2.2.5 =
* Updated: Compatible with WordPress 4.9.4
* Updated: Compatible with WooCommerce 3.3.1

= 2.2.4 =
* Added: Option to remove product when clicking again

= 2.2.3 =
* Added: Button to open compare
* Fixed: Problem with Cookie

= 2.2.2 =
* Fixed: CSS & JS issues

= 2.2.1 =
* Fixed: Error when product is not exists

= 2.2.0 =
* New: WPML integration

= 2.1.2 =
* Fixed: Just load scripts in WooCommerce Smart Compare settings page

= 2.1.1 =
* Tested up to WordPress 4.9.1 & WooCommerce 3.2.5

= 2.1.0 =
* Added welcome page
* Tested up to WordPress 4.9
* Tested up to WooCommerce 3.2.4

= 2.0.0 =
* Tested up to WordPress 4.8.3
* Tested up to WooCommerce 3.2.2

= 1.2.2 =
* Tested up to WordPress 4.8.2

= 1.2.1 =
* Close compare when click outside

= 1.2.0 =
* Optimized code

= 1.1.0 =
* Tested up to WordPress 4.8

= 1.0.0 =
* Released