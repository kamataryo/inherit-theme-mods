<?php
/**
 * @package inherit-theme-mods
 */

class Inherit_Theme_Mods {

	public $parent_theme_slug;
	public $child_theme_slug;

	function __construct() {
		$this->parent_theme_slug = wp_get_theme()->template;
		$this->child_theme_slug  = wp_get_theme()->stylesheet;
	}

	static function get_theme_mods_of( $slug ) {
		return maybe_unserialize( get_option( ITM_OPTION_PREFIX . $slug, false ) );
	}

	static function set_theme_mods_of( $slug, $values ) {
		return self::is_installed_theme( $slug ) ?
			update_option( ITM_OPTION_PREFIX . $slug, $values ) : false;
	}

	static function merge_theme_mods_of( $slug, $overwriter ) {
		return self::is_installed_theme( $slug ) ?
			update_option(
				ITM_OPTION_PREFIX . $slug,
				array_merge(
					get_option( ITM_OPTION_PREFIX . $slug ),
					$overwriter
				)
			) : false;
	}

	static function get_stored_mods(){
		return maybe_unserialize( get_option( ITM_STORING_OPTION_NAME ) );
	}

	static function is_installed_theme( $slug ) {
		return array_key_exists( $slug, wp_get_themes() );
	}

	public function is_child_theme_active() {
		return $this->parent_theme_slug !== $this->child_theme_slug;
	}

	public function inherit() {

		if ( $this->is_child_theme_active() ) {

			// store values at first
			$storing_value = self::get_theme_mods_of( $this->child_theme_slug );
			if ( get_option( ITM_STORING_OPTION_NAME ) ) {
				update_option( ITM_STORING_OPTION_NAME, $storing_value ,'no' );
			} else {
				add_option( ITM_STORING_OPTION_NAME, $storing_value, '' ,'no' );
			}

			// inherit
			return self::set_theme_mods_of(
				$this->child_theme_slug,
				self::get_theme_mods_of( $this->parent_theme_slug )
			);

		} else {
			return false;
		}
	}

	public function restore()
	{
		return self::set_theme_mods_of(
			$this->child_theme_slug,
			self::get_stored_mods()
		);
	}
}
