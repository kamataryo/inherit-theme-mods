<?php
/**
 * Plugin Name: Inherit Theme Mods
 * Version: 2.1.2
 * Description: Inherit Theme Mods enable to copy child theme property from that of parent.
 * Author: KamataRyo
 * Author URI: http://biwako.io/
 * Plugin URI: https://github.com/KamataRyo/inherit-theme-mods
 * Text Domain: inherit-theme-mods
 * Domain Path: /languages/
 *
 * @package Inherit-theme-mods
 */

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inherit-theme-mods.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inherit-theme-mods-table.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inherit-theme-mods-ui.php' );

/**
 * Utility functions
 */
class ITM_Util {
	// transform valiable arguments into plugin path.
	public static function url(){
		$dirs = func_get_args();
		return plugins_url( implode( $dirs, DIRECTORY_SEPARATOR ), __FILE__ );
	}

	// build style attribute value from array
	public static function style_attr( $styles )
	{
		$result = '';
		foreach ($styles as $directive => $value) {
			$result .= "$directive:$value;";
		}
		return 'style="' . esc_attr( $result ) . '"';
	}

	// try translate one by one in order to $slugs as context.
	static function __chained( $text, $slugs ) {
		do {
			# Use 'translate' function to avoid to be caught by gettext.
			$translated_text = translate( $text, array_shift( $slugs ) );
		} while ( 0 !== count( $slugs ) && $text === $translated_text );
		return $translated_text;
	}

	// This plugin tries to solve original name for translation, but sometime fails.
	// So this function only stores texts for gettext.
	// Terms below may appear in some famous themes.
	private function __translation_store() {
		__( 'Header Image Data',       'inherit-theme-mods' );
		__( 'Nav Menu Locations',      'inherit-theme-mods' );
		__( 'Sidebars Widgets',        'inherit-theme-mods' );
		__( 'Color Scheme',            'inherit-theme-mods' );
		__( 'Background Position X',   'inherit-theme-mods' );
		__( 'Main Text Color',         'inherit-theme-mods' );
		__( 'Header Textcolor',        'inherit-theme-mods' );
		__( 'Link Color',              'inherit-theme-mods' );
		__( 'Sidebar Textcolor',       'inherit-theme-mods' );
		__( 'Custom Logo',             'inherit-theme-mods' );
		__( 'Featured Content Layout', 'inherit-theme-mods' );
	}
}
// var_dump(Inherit_Theme_Mods::get_stored_mods());
// describe UI for admin page
new Inherit_Theme_Mods_UI();
