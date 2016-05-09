<?php
/**
 * Plugin Name: Inherit Theme Mods
 * Version: 1.0.0
 * Description: Inherit Theme Mods enable to copy child theme property from that of parent.
 * Author: KamataRyo
 * Author URI: http://biwako.io/
 * Plugin URI: https://github.com/KamataRyo/inherit-theme-mods
 * Text Domain: inherit-theme-mods
 * Domain Path: /languages/
 * @package Inherit-theme-mods
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inherit-theme-mods.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inherit-theme-mods-table.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inherit-theme-mods-ui.php' );

class ITM_Util {
	public static function url(){
		$dirs = func_get_args();
		return plugins_url( implode( $dirs, DIRECTORY_SEPARATOR ), __FILE__ );
	}

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
			$translated_text = __( $text, array_shift( $slugs ), 'inherit-theme-mods' );
		} while ( 0 !== count( $slugs ) && $text === $translated_text );
		return $translated_text;
	}


	// This function only stores texts in some famous themes only to provide for translation
	// they may appear at `wp_options` table and it's translation could not be resolved automatically.
	private function __translation_store()
	{
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

// describe UI for admin page
new Inherit_Theme_Mods_UI();
