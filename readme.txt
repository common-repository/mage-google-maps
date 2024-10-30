=== Mage Google Maps ===
Contributors: Maximilian Ruthe
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LBB5QL9QV2Y86
Tags: map, maps, google, gmap, address, auto, marker, markers, query, wp, mage, directions, easy, plugin, latitude, location, longitude, widget, shortcode, polygons, polylines, routes, store, locator, streetview
Requires at least: 3.0.1
Tested up to: 4.7
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress Google Maps plugin with automatic single/multi address marker display via custom meta & shortcode.

== Description ==

Automatically display Google Maps with multiple location markers in your category/archive or single marker maps on single pages via addresses from custom fields on posts or custom post types. 

= Newest Features =
You can now preview the map from the add/edit screens of corresponding post types.

= Features Include =
* Create Single Maps with simple address entry
* OR make automatic map address markers from post type content
* Display using super simple Widgets and Shortcodes
* Simply add Map to sidebar, any post types and custom taxonomy archives
* Auto shifts from multi-address or single-address display based placement
* Change any default settings and behaviour simply in admin options
* Easily change width, height and designs of any maps
* Customize Zoom, User Interface and more without any coding
* Custom Post Type & Taxonomy Compatible
* Choose what content a Map should take an address from with a click

= Simple Map Shortcode =
Display your Google Map anywhere using the [map] shortcode and choose a default address if not address is found in content from the plugin options, or simply type one using address, for example: [map address="Mountain View, CA"].

Choose Auto Map Marker creation from plugin settings, choose posts, pages and/or any custom post types and custom fields you want to use.

== Installation ==

1. Extract and Upload Content of `mage-google-maps.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

OR

1. Search 'mage maps' through the 'Plugins' menu in WordPress
2. Install 'Mage Google Maps' from search results.

== Frequently Asked Questions ==

= Static Map Display =
Display your Google Map anywhere using the widget or [map] shortcode and choose a default address if not address is found in content from the plugin options, or simply type one using address, for example: [map address="Mountain View, CA"].

= Address Field Order Priority =
Mage Google Maps retrieves the address in the following order, until an address value is returned and not empty.
* The address parameter in the shortcode, or address field when using the widget. For Auto-Displaying of addresses, these should be left empty.
* When displayed on selected Post Types, both shortcode and widget will take address data from the fields specified on the Address Data settings page.
* Lastly, if no address data is received from the above options, Mage Google Maps will use the Default Address option as a fallback.

= Multi-Address Archives =
Whenever the [map] shortcode or widget are on an archive page, Mage Google Maps will attempt to crawl all archive items and add a map marker for each supported post type with an address.

== Screenshots ==

1. Snapshot of [Restaurant Girl](http://www.restaurantgirl.com/reviews/ "New York Restaurant Reviews") with auto map display from archive posts.
2. Singl Map auto display on [Dogsniffer](http://dogsniffer.com/ "Dog Friendly Businesses Reviews") from listing address meta.

== Changelog ==

= 1.1.2 =
* Map should now display multi-locations correctly when on an archive / blog, and display a single address when on a single page.
* You can now enable clickable map markers that link to the posts page, when displaying the map on an archive.
* Option to upload your own custom map marker as the default map marker.
* Mage Maps widget now works properly again
* General improvements

= 1.1.1 =
* Mage Maps is now compatible with Mage Reviews and Mage Forms again.
* Added a Google Maps API Key field to use if desired.

= 1.1.0 =
Fixes

= 1.0.9 =
Preview Map on Add / Edit Post Types

= 1.0.8 =
New Dashboard and Fixes

= 1.0.7 =
Minor Bug fix and core library updates

== Upgrade Notice ==

= 1.1.1 =
* Mage Maps is now compatible with Mage Reviews and Mage Forms again.
* Added a Google Maps API Key field to use if desired.

= 1.1.0 =
Fixes for current error messages.

= 1.0.9 =
Preview Map on Add / Edit Post Types

= 1.0.8 =
New Dashboard and Fixes

== Upgrade Notice ==

= 1.1.2 =
* Map should now display multi-locations correctly when on an archive / blog, and display a single address when on a single page.
* You can now enable clickable map markers that link to the posts page, when displaying the map on an archive.
* Option to upload your own custom map marker as the default map marker.
* Mage Maps widget now works properly again
* General improvements