<?php
/**
 * Plugin Name: Inherit Theme Mods
 * Version: 1.0
 * Description: This plugin simply copies theme mods from parental theme.
 * Author: KamataRyo
 * Author URI: http://biwako.io/
 * Plugin URI: http://biwako.io/
 * Text Domain: inherit-theme-mods
 * Domain Path: /languages
 * @package Inherit-theme-mods
 */

define( 'ITM_TEXT_DOMAIN',         'inherit-theme-mods' );
define( 'ITM_OPTION_PREFIX',       'theme_mods_' );
define( 'ITM_STORING_OPTION_NAME', 'inherit_theme_mods_stored_option' );
define( 'ITM_NONCE_FIELD',         'nonce' );
define( 'ITM_NONCE_ACTION',        'ITM_nonce' );

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
	// This function stores some texts only to provide for translation
	// they may appear at `wp_options` table and be not available to resolve the slug to translate.
	// Some of theme mod slug appears in official theme were picked, not all.
	private function __translation_store()
	{
		__( 'Header Image Data',     ITM_NONCE_ACTION );
		__( 'Nav Menu Locations',    ITM_NONCE_ACTION );
		__( 'Sidebars Widgets',      ITM_NONCE_ACTION );
		__( 'Color Scheme',          ITM_NONCE_ACTION ); # ベース配色 in ja
		__( 'Background Position X', ITM_NONCE_ACTION ); # 背景の位置 in ja
	}
}

new Inherit_Theme_Mods_UI();
