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
define( 'ITM_ADMIN_LIST_PER_PAGE', 10 );

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inherit-theme-mods.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class_Mods_List_Table.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inherit-theme-mods-ui.php' );


// $itm = new Inherit_Theme_Mods( $parent_theme_slug, $child_theme_slug );
