<?php
/**
 * @package inherit-theme-mods
 * provides core functions for WordPress Options API
 * Instance
 */

class Inherit_Theme_Mods {

	const OPTION_PREFIX    = 'theme_mods_';
	public const STORE_KEY = 'inherit_theme_mods_stored_option';


	public $parent_theme_slug;
	public $child_theme_slug;

	function __construct() {
		$this->parent_theme_slug = wp_get_theme()->template;
		$this->child_theme_slug  = wp_get_theme()->stylesheet;
	}

	static function get_theme_mods_of( $slug ) {
		return maybe_unserialize( get_option( self::OPTION_PREFIX . $slug, false ) );
	}

	static function set_theme_mods_of( $slug, $values ) {
		return self::is_installed_theme( $slug ) ?
			update_option( self::OPTION_PREFIX . $slug, $values ) : false;
	}

	static function merge_theme_mods_of( $slug, $overwriter ) {
		return self::is_installed_theme( $slug ) ?
			update_option(
				self::OPTION_PREFIX . $slug,
				array_merge(
					get_option( self::OPTION_PREFIX . $slug ),
					$overwriter
				)
			) : false;
	}

	static function get_stored_mods(){
		return maybe_unserialize( get_option( self::STORE_KEY ) );
	}

	static function is_installed_theme( $slug ) {
		return array_key_exists( $slug, wp_get_themes() );
	}

	public function is_child_theme_active() {
		return $this->parent_theme_slug !== $this->child_theme_slug;
	}

	public function inherit() {

		if ( $this->is_child_theme_active() ) {

			// store first
			$this->store();
			// inherit
			return self::merge_theme_mods_of(
				$this->child_theme_slug,
				self::get_theme_mods_of( $this->parent_theme_slug )
			);

		} else {
			return false;
		}
	}

	public function overwrite() {

		if ( $this->is_child_theme_active() ) {

			// store first
			$this->store();
			// inherit
			return self::set_theme_mods_of(
				$this->child_theme_slug,
				self::get_theme_mods_of( $this->parent_theme_slug )
			);

		} else {
			return false;
		}
	}

	public function store() {

		if ( $this->is_child_theme_active() ) {

			$storing_value = self::get_theme_mods_of( $this->child_theme_slug );
			return get_option( self::STORE_KEY ) ?
				update_option( self::STORE_KEY, $storing_value ,'no' ) :
				add_option( self::STORE_KEY, $storing_value, '' ,'no' );

		} else {
			return false;
		}
	}

	public function restore() {

		if ( $this->is_child_theme_active() ) {

			return self::set_theme_mods_of(
				$this->child_theme_slug,
				self::get_stored_mods()
			);
		} else {
			return false;
		}
	}
}
