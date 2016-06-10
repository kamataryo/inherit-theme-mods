<?php
/**
 * @package inherit-theme-mods
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Process Admin List Table View.
 */
class Inherit_Theme_Mods_Table extends WP_List_Table {
	// Option for Admin List Table Pagination
	const LIST_PER_PAGE = 20;
	var $child_slug;
	var $parent_slug;
	var $child_theme_name;
	var $parent_theme_name;
	var $is_inheritable;
	var $data;

	function __construct( $child_slug, $parent_slug ) {
		parent::__construct( array(
			'singular' => 'key',
			'plural'   => 'keys',
			'ajax'     => false,
		) );

		$this->child_slug = $child_slug;
		$this->parent_slug = $parent_slug;
		$this->is_inheritable = $child_slug !== $parent_slug;
		$this->child_theme_name = wp_get_theme( $child_slug )->Name;
		$this->parent_theme_name = wp_get_theme( $parent_slug )->Name;
		$this->generate_data_array();
	}

	function column_default( $item, $column_name ) {
		return maybe_serialize( $item[$column_name] );
	}

	function get_columns() {
		return $this->is_inheritable ?
			array(
				'key'          => __( 'Settings', 'inherit-theme-mods' ),
				'parent-theme' => '<span class="ITM-aside">' . esc_html( $this->parent_theme_name ) . '<small class="ITM-aside">' . __( '(Parent theme)', 'inherit-theme-mods' ) . '</small></span>',
				'child-theme'  => '<span class="ITM-aside">' . esc_html( $this->child_theme_name ) . '<small class="ITM-aside">' . __( '(Child theme)', 'inherit-theme-mods' ) . '</small></span>',
				'trashed'      => '<span class="ITM-aside">' . __( 'Trashed', 'inherit-theme-mods' ) . '</span>',
			) :
			array(
				'key'          => __( 'Key', 'inherit-theme-mods' ),
				'parent-theme' => '<span class="ITM-aside">' . esc_html( $this->parent_slug ) . '</span>',
				'trashed'      => '<span class="ITM-aside">' . __( 'Trashed', 'inherit-theme-mods' ) . '</span>',
			);
	}

	function get_sortable_columns() {
		return array( 'key' => array( 'Key', false ) );
	}

	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		function usort_reorder( $a, $b ){
			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'key'; //If no sort, default to title
			$order   = ( ! empty( $_REQUEST['order']   ) ) ? $_REQUEST['order']   : 'desc'; //If no order, default to asc
			$result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
			return ( $order === 'asc' ) ? $result : - $result; //Send final sort direction to usort
		}
		usort( $this->data, 'usort_reorder' );
		$current_page = $this->get_pagenum();
		$total_items = count( $this->data );
		$this->data = array_slice(
			$this->data,
			( ( $current_page - 1 ) * self::LIST_PER_PAGE ),
			self::LIST_PER_PAGE
		);
		$this->items = $this->data;
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => self::LIST_PER_PAGE,
			'total_pages' => ceil( $total_items /self::LIST_PER_PAGE ),
		) );

	}

	function generate_data_array() {
		$child_mods = Inherit_Theme_Mods::get_theme_mods_of( $this->child_slug );
		$parent_mods = Inherit_Theme_Mods::get_theme_mods_of( $this->parent_slug );
		$stored_mods = Inherit_Theme_Mods::get_stored_mods();

		if ( ! $child_mods ) {
			$child_mods = array();
		}
		if ( ! $parent_mods ) {
			$parent_mods = array();
		}
		if ( ! $stored_mods ) {
			$stored_mods = array();
		}

		$keys = array_unique(
			array_merge(
				array_keys( $child_mods  ),
				array_keys( $parent_mods ),
				array_keys( $stored_mods )
			)
		);

		// helper function
		function decorate_mod( $key, $mods, $col ) {

			$data_key = 'data-key="' . esc_attr( $key ) . '"';
			$data_col = 'data-col="' . esc_attr( $col ) . '"';

			if ( ! array_key_exists( $key, $mods ) ) {
				return "<span class=\"ITM-list-data\" $data_key $data_col><small class=\"no-value\">" . __( '(no value)', 'inherit-theme-mods' ) . '</small></span>';
			}

			$value = esc_html( maybe_serialize( $mods[$key] ) );
			$match_color     = preg_match( '/^#?([0-9,a-f,A-F]{3}|[0-9,a-f,A-F]{6})$/', $value );
			$match_inmageURL = preg_match( '/\.(jpg|jpeg|png|gif)$/i', $value );

			if( 1 === $match_color ) {
				# display color if color string
				$color_str = substr($value, 0, 1) === '#' ? $value : "#$value";
				$style_attr = ITM_Util::style_attr( array(
					'background-color' => $color_str
				) ); # xss OK
				$value = esc_html( $value );
				$value = "<div class=\"ITM-color-indication\" $style_attr></div><span class=\"ITM-list-data\" $data_key $data_col>$value</span>";

			} else if ( 1 === $match_inmageURL ) {
				# display image if image url
				$value = esc_url( $value );
				$value = "<img src=\"$value\" class=\"ITM-image-indication\" alt=\"\" /><br /><span class=\"ITM-list-data\" $data_key $data_col>$value</span>";# xss OK

			} else {
				$value = "<span class=\"ITM-list-data ITM-serialized-text\" $data_key $data_col>" . esc_html( $value ) . '</span>';
			}

			return $value;
		}

		$result = array();
		foreach ( $keys as $key ) {
			// Eliminate unnecessary field to see
			if ( is_int( $key ) ) {
				continue;
			}
			// transform slug into maybe translatable text
			// 'translate_word' -> 'Translate Word'
			// (is there any slugify/unslugify standard function?)
			$key_elements = explode( '_', $key );
			foreach ( $key_elements as $index => $element ) {
				$key_elements[$index] =
					strtoupper( substr( $element, 0,  1 ) ) .
								substr( $element, 1 );
			}
			// try translate
			$key_parsed = ITM_Util::__chained(
				implode( ' ', $key_elements ),
				array(
					$this->child_slug,
					$this->parent_slug,
					'inherit-theme-mods',
					'default',
				)
			);

			array_push( $result, array(
				'Key'          => $key_parsed, # WP_List_Table require 'Key' for sortable column, instead 'key'.
				'key'          => $key_parsed,
				'native_key'   => $key,
				'parent-theme' => decorate_mod( $key, $parent_mods, 'parent-theme' ),
				'child-theme'  => decorate_mod( $key, $child_mods,  'child-theme'  ),
				'trashed'      => decorate_mod( $key, $stored_mods, 'trashed' ),
			) );
		}
		$this->data = $result;
	}
}
