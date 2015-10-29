<?php
/*
Plugin Name: CFS Icon (from Font) Field
Plugin URI: http://wordpress.org/plugins/cfs-ninja-forms-selector/
Description: Adds an icon (from an icon font) field to Custom Field Suite
Version: 1.0.0
Author: Jonathan Christopher
Author URI: http://mondaybynoon.com/
Text Domain: cfsifff
License: GPL2

Copyright 2015 Jonathan Christopher

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CfsIconFromFontField {

	function __construct() {
		add_filter( 'cfs_field_types', array( $this, 'cfs_field_types' ) );
	}

	function cfs_field_types( $field_types ) {
		$field_types['icon_from_font_field'] = dirname( __FILE__ ) . '/cfs-field-icon-from-font.php';

		return $field_types;
	}
}

new CfsIconFromFontField();