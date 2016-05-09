<?php
/**
 * Plugin Name: Inherit Theme Mods
 * Version: 1.0
 * Description: This plugin simply copies theme mods from parental theme.
 * Author: KamataRyo
 * Author URI: http://biwako.io/
 * Plugin URI: http://biwako.io/
 * Text Domain: inherit-theme-mods
 * Domain Path: /languages/
 * @package Inherit-theme-mods
 */

define( 'ITM_TEXT_DOMAIN',         'inherit-theme-mods' );
define( 'ITM_OPTION_PREFIX',       'theme_mods_' );
define( 'ITM_STORING_OPTION_NAME', 'inherit_theme_mods_stored_option' );

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
		__( 'Header Image Data',       ITM_TEXT_DOMAIN, 'inherit-theme-mods' );
		__( 'Nav Menu Locations',      ITM_TEXT_DOMAIN, 'inherit-theme-mods' );
		__( 'Sidebars Widgets',        ITM_TEXT_DOMAIN, 'inherit-theme-mods' );
		__( 'Color Scheme',            ITM_TEXT_DOMAIN, 'inherit-theme-mods' );
		__( 'Background Position X',   ITM_TEXT_DOMAIN, 'inherit-theme-mods' );
		__( 'Main Text Color',         ITM_TEXT_DOMAIN, 'inherit-theme-mods' );
		__( 'Header Textcolor',        ITM_TEXT_DOMAIN, 'inherit-theme-mods' );
		__( 'Link Color',              ITM_TEXT_DOMAIN, 'inherit-theme-mods' );
		__( 'Sidebar Textcolor',       ITM_TEXT_DOMAIN, 'inherit-theme-mods' );
		__( 'Custom Logo',             ITM_TEXT_DOMAIN, 'inherit-theme-mods' );
		__( 'Featured Content Layout', ITM_TEXT_DOMAIN, 'inherit-theme-mods' );
	}
}

// describe UI for admin page
new Inherit_Theme_Mods_UI();
