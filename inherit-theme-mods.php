<?php
/**
 * @package inherit-theme-mods
 */

/**
* Wrapper for Options API
*/
class Inherit_Theme_Mods {
	// prefix for theme mods defined by WordPress
	const OPTION_PREFIX = 'theme_mods_';
	// option name to store mods in trash box
	const STORE_KEY = 'inherit_theme_mods_stored_option';
	public $parent_theme_slug;
	public $child_theme_slug;

	function __construct() {
		$this->parent_theme_slug = wp_get_theme()->template;
		$this->child_theme_slug  = wp_get_theme()->stylesheet;
	}

	// This substitutes get_theme_mod.
	static function get_theme_mods_of( $slug ) {
		$result = get_option( self::OPTION_PREFIX . $slug, false );
		return ( '' === $result ) ? array() : $result;
	}

	// This substitutes set_theme_mod.
	static function set_theme_mods_of( $slug, $values ) {
		return self::is_installed_theme( $slug ) ?
			update_option( self::OPTION_PREFIX . $slug, $values ) : false;
	}

	// This substitutes set_theme_mod and merges options.
	static function merge_theme_mods_of( $slug, $overwriter ) {
		return self::is_installed_theme( $slug ) ?
			self::set_theme_mods_of(
				$slug,
				array_merge(
					maybe_unserialize( get_option( self::OPTION_PREFIX . $slug ) ),
					$overwriter
				)
			) : false;
	}

	// substitutes get_theme_mod, virtually for 'trash box'
	static function get_stored_mods(){
		return get_option( self::STORE_KEY, false );
	}

	static function is_installed_theme( $slug ) {
		return array_key_exists( $slug, wp_get_themes() );
	}

	public function is_child_theme_active() {
		return $this->parent_theme_slug !== $this->child_theme_slug;
	}

	// inherit theme mods from parent into child
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

	// overwrite theme mods from parent into child
	public function overwrite() {

		if ( $this->is_child_theme_active() ) {

			// store first
			$this->store();
			// overwrite
			return self::set_theme_mods_of(
				$this->child_theme_slug,
				self::get_theme_mods_of( $this->parent_theme_slug )
			);

		} else {
			return false;
		}
	}

	// store child theme mods into trash box
	public function store() {

		if ( $this->is_child_theme_active() ) {

			delete_option( self::STORE_KEY );
			return add_option(
				self::STORE_KEY,
				self::get_theme_mods_of( $this->child_theme_slug ),
				'',
				'no'
			);

		} else {
			return false;
		}
	}

	// restore child theme mods from trash box
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
