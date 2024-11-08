<?php

/*
Plugin Name: Simple Divi Shortcode 
Plugin URI:  https://www.creaweb2b.com/plugins/
Description: Plugin creating a shortcode allowing you to embed any Divi Library item within php template or within another Divi module/section content
Author:      Fabrice ESQUIROL - Creaweb2b
Text Domain: simple-divi-shortcode
Version:     1.0
Author URI:  https://www.creaweb2b.com
License:     GPL2

Copyright 2017 Fabrice ESQUIROL (email : admin@creaweb2b.com)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation; either version 2 of the License,
or any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

//Exit if direct access
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
// Function to show the module
function showmodule_shortcode($atts) {
	$atts = shortcode_atts(array('id' => ''), $atts);
	return do_shortcode('[et_pb_section global_module="'.$atts['id'].'"][/et_pb_section]');	
}
add_shortcode('showmodule', 'showmodule_shortcode');
  
?>