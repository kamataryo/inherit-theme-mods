<?php
/**
 * Plugin Name: Inherit Theme Mods
 * Version: 0.1-alpha
 * Description: This plugin simply copies theme mods from parental theme.
 * Author: KamataRyo
 * Author URI: http://biwako.io/
 * Plugin URI: http://biwako.io/
 * Text Domain: inherit-theme-mods
 * Domain Path: /languages
 * @package Inherit-theme-mods
 */

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inherit-theme-mods-ui.php';
define( 'INHERIT_THEME_MODS_TEXT_DOMAIN', 'inherit-theme-mods' );
define( 'INHERIT_THEME_MODS_STORING_OPTION_NAME', 'inherit_theme_mods_stored_option' );


function inherit_theme_mods_get_theme_mods_of( $slug )
{
    $result = get_option( "theme_mods_$slug", false );
    if ( $result ) {
        $result = maybe_unserialize( $result );
    }
    return $result;
}

function inherit_theme_mods_set_theme_mods_of( $slug, $option_value )
{
    if ( ! array_key_exists( $slug, wp_get_themes() ) ) {
        return false;
    }
    return update_option( "theme_mods_$slug", $option_value );
}

function inherit_theme_mods_inherit()
{
    $parent = wp_get_theme()->template;
    $child = wp_get_theme()->stylesheet;
    $is_child = $child !== $parent;

    if ( $is_child ) {
        // store values at first
        $store = inherit_theme_mods_get_theme_mods_of( $child );
        if ( get_option( INHERIT_THEME_MODS_STORING_OPTION_NAME ) ) {
            update_option( INHERIT_THEME_MODS_STORING_OPTION_NAME, $store ,'no' );
        } else {
            add_option( INHERIT_THEME_MODS_STORING_OPTION_NAME, $store, '' ,'no' );
        }

        // inherit
        $parent_mod = inherit_theme_mods_get_theme_mods_of( $parent );
        $result = inherit_theme_mods_set_theme_mods_of( $child, $parent_mod );
    } else {
        $result = false;
    }
    return $result;
}

function inherit_theme_mods_get_stored_mods()
{
    $result = get_option( INHERIT_THEME_MODS_STORING_OPTION_NAME );
    if ($result) {
        $result = maybe_unserialize( $result );
    }
    return $result;
}

function inherit_theme_mods_restore()
{
    $child = wp_get_theme()->stylesheet;
    $stored_mod = inherit_theme_mods_get_stored_mods();
    $result = inherit_theme_mods_set_theme_mods_of( $child, $stored_mod );
    return $result;
}


// helper_functions
function inherit_theme_mods_build_styleAttr( $styles )
{
    $result = '';
    foreach ($styles as $directive => $value) {
        $result .= "$directive:$value;";
    }
    return 'style="' . esc_attr( $result ) . '"';
}
