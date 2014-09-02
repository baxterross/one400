=== Simple Google Map ===
Contributors: taylor_CNP
Donate link: 
Tags: google, google map, google maps, simple google map, no api key
Requires at least: 2.5
Tested up to: 2.8.5
Stable tag: 2.0

This plugin will embed a google map using shortcode or as a widget.

== Description ==

With this plugin you can insert a google map into your posts, pages, or wigitized sidebar *without an API key*.  Google recently released version 3 of their Maps API. They made it smaller and faster, but with less features. The biggest advantage is that it doesn’t require an API key. So all you have to do is install this plugin and set a few options! Lucky you.

**FEATURES**

* Insert into posts or pages using only shortcode
* Insert into your sidebar as a widget.
* Set default options = less necessary shortcode and widget options.
* Modify the default CSS or turn it off completely and style the map yourself.
* Add a form for getting directions by simply adding your destination address.
* It's simple!

**ADDITIONAL NOTES**

* There can only be one map one a page. A widget map counts as one.
* The size of the info window (speech bubble) is dictated by the size of the map, thus the size of the containing div, div#SGM
* If you wish to use html in the content value in shortcode, be sure and type it in visual mode (not HTML mode). the pointy brackets will be special html characters in HTML mode and are converted back into pointy brackets by the plugin.

For an example and more visit [the plugin's homepage](http://clarknikdelpowell.com/wordpress/simple-google-map/ "Simple Google Map by Clark Nikdel Powell").

== Installation ==

1. Upload `simple-google-map.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Insert the shortcode into your posts or pages or add the widget to your sidebar.

The shortcode name is SGM and here are the options..

* lat [int] – the latitude of the marker and the center of the map.
* lng [int] – the longitude of the marker and the center of the map.
* zoom [int] – the zoom level (1-19).
* type [int] – the starting map type. possible values are only ROADMAP, SATELLITE, HYBRID, or TERRAIN and must be in uppercase
* directionsto [string] – the destination address for getting directions. obviously you want this to be the address of your latitude longitude coordinates.
* content – what goes inside the infoWindow (speech bubble) that appears when the marker is clicked.

== Changelog ==

= 2.0 =
* Added option to specify default map type (roadmap, satellite, etc).
* Created widget capability.
* Added default CSS styling. Now you don't have to be a programmer to use the plugin!
* Added settings page in admin to manage global default options, edit styles, or turn off styles.
* Made directions form optional.
* Escaped necessary characters from info window content.
* Fixed a bug on windows hosting that caused php code to show up at the top of the admin pages (changed all <? to <?php).
* Changed submit button from "Get directions" to "Directions" to save space.

= 1.0 =
* Initial release