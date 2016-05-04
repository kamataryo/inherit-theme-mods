<?php
/**
 * Plugin Name: Inherit-theme-mods
 * Version: 0.1-alpha
 * Description: This plugin simply copy theme mods from parental theme.
 * Author: KamataRyo
 * Author URI: http://biwako.io/
 * Plugin URI: http://biwako.io/
 * Text Domain: inherit-theme-mods
 * Domain Path: /languages
 * @package Inherit-theme-mods
 */


function inherit_theme_mods_get_theme_mods_of( $slug1 )
{
    var_dump($wpdb);
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->options} LIMIT 10");
    var_dump($result);

    $slug0 = wp_get_theme()->stylesheet;
    switch_theme( $slug1 );
    $result = get_theme_mods();
    switch_theme( $slug0 );
    return $result;
}

function inherit_theme_mods_set_theme_mods_of( $slug1, $mods )
{
    $slug0 = wp_get_theme()->stylesheet;
    switch_theme( $slug1 );
    remove_theme_mods();
    foreach ($mods as $name => $value) {
        set_theme_mod( $name, $value );
    }
    switch_theme( $slug0 );
    return true;
}

function inherit_theme_mods_inherit()
{
    $parent = wp_get_theme()->template;
    $child = wp_get_theme()->stylesheet;
    $is_child = $child !== $parent;
    $result = false;

    if ($is_child) {
        try {
            $result = inherit_theme_mods_set_theme_mods_of(
                $child,
                inherit_theme_mods_get_theme_mods_of(
                    $parnt
                )
            );
        } catch (Exception $e) {
            $result = $e;
        }
    }
    return $result;
}
