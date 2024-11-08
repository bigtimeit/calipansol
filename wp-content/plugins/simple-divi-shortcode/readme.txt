=== Simple Divi Shortcode ===
Contributors: creaweb2b
Tags: Divi, Divi theme, Divi Modules, Divi Library, Divi Sections, Divi Builder, Elegant Themes, Shortcode
Donate link: https://www.creaweb2b.com
Requires at least: 4.0
Tested up to: 6.0.1
Requires PHP: 5.6 or higher (tested up to 8.1)
Stable tag: trunk.
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to use a shortcode to insert a section, a module or a layout from the DIVI Library inside another module content or inside a template using a shortcode 
`[showmodule id="xxx"]` where xxx is the ID of the section, module or layout inside the DIVI Library. (Read description to learn how to find out this ID)

== Description ==
Using this tool you will be able to embed any Divi Library item inside another module content or inside a php template by using a simple shortcode.

You just need to build a layout, section or module inside the Divi library.

The item ID can be found by navigating to the layout editor and looking at the URL. For example, let's have a look at the following URL : 
https://mywebsite.com/wp-admin/post.php?post=866&action=edit. 
Here the item ID is : 866.

The ID is also available by hovering over the word Edit in the layout page : ID is shown on the link displayed at the bottom of the screen.

Once you get the item ID, just call it by using a shortcode `[showmodule id="866"]`

I made a tutorial explaining how to use it : "How to add a DIVI section or module inside another module" available at the following URL :
https://www.creaweb2b.com/en/how-to-add-a-divi-section-or-module-inside-another-module/

A premium version of the plugin, offering some more functionnality is available for purchase at the following URL :
https://www.creaweb2b.com/en/plugins/simple-divi-shortcode-en/

= Requirements =

This plugin need DIVI or EXTRA theme to work, or the Divi Builder plugin from Elegant Themes

= Supporting Simple Divi Shortcode =

If you found this plugin helpful, please support the developer with a small donation :

* [Buy me a coffee](https://ko-fi.com/fabriceesquirol_creaweb2b)

= Credit =

Simple Divi Shortcode created by Fabrice ESQUIROL - creaweb2b.com


== Installation ==
Install the plugin like any other plugin, directly from your plugins page but looking it up in the WordPress.org repository, or manually upload it to your server to the /wp-content/plugins/ folder.

Activate the plugin using the "activate" button at the end of install process, or through the "Plugins" menu in WordPress.

== Changelog ==
- 0.9 - Initial release
- 1.0 - Updated deprecated extract attribute method