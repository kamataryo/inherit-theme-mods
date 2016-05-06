<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * @package inherit-theme-mods
 */
class Mods_List_Table extends WP_List_Table
{
    var $child_slug;
    var $parent_slug;
    function __construct( $child_slug, $parent_slug )
    {
        $this->child_slug = $child_slug;
        $this->parent_slug = $parent_slug;
        parent::__construct( array(
            'singular' => __( 'key', INHERIT_THEME_MODS_TEXT_DOMAIN, 'inherit-theme-mods' ),
            'plural'   => __( 'keys', INHERIT_THEME_MODS_TEXT_DOMAIN, 'inherit-theme-mods' ),
            'ajax'     => true,
        ) );
    }

    function column_default( $item, $column_name )
    {
        return maybe_serialize( $item[$column_name] );
    }

    function get_columns()
    {
        return array(
            'key'          => __( 'Key', INHERIT_THEME_MODS_TEXT_DOMAIN, 'inherit-theme-mods' ),
            'parent-theme' => '<i class="fa fa-file-o fa-2x"></i><span class="ITM-table-header-text">Parent Theme</span>',
            'child-theme'  => '<i class="fa fa-copy fa-2x"></i><span class="ITM-table-header-text">Child Theme</span>',
            'trashed'      => '<i class="fa fa-trash fa-2x"></i><span class="ITM-table-header-text">Trashed</span>',
        );
    }

    function get_sortable_columns()
    {
        return array(
            'key' => array( 'Key', false ),
        );
    }

    public function prepare_items()
    {

        $per_page = 3;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $data = $this->inherit_theme_mods_generate_data_array( $this->child_slug, $this->parent_slug );

        function usort_reorder( $a, $b ){
            $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'key'; //If no sort, default to title
            $order   = ( ! empty( $_REQUEST['order']   ) ) ? $_REQUEST['order']   : 'desc'; //If no order, default to asc
            $result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
            return ( $order === 'asc' ) ? $result : -$result; //Send final sort direction to usort
        }
        usort( $data, 'usort_reorder' );
        $current_page = $this->get_pagenum();
        $total_items = count( $data );
        $data = array_slice( $data,( ( $current_page - 1 ) * $per_page ), $per_page );
        $this->items = $data;
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items /$per_page ),
        ) );

    }

    private function inherit_theme_mods_generate_data_array( $child_slug, $parent_slug )
    {
        $child_mods = inherit_theme_mods_get_theme_mods_of( $child_slug );
        $parent_mods = inherit_theme_mods_get_theme_mods_of( $parent_slug );
        $stored_mods = inherit_theme_mods_get_stored_mods();

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

        function transform_mod($key, $mods)
        {
            // attach empty value
            if ( ! array_key_exists( $key, $mods ) ) {
                $mods[$key] = '';
            } else {
                $mods[$key] = maybe_serialize( $mods[$key] );
            }

            $value = $mods[$key];
            $match_color     = preg_match( '/^#?([0-9,a-f,A-F]{3}|[0-9,a-f,A-F]{6})$/', $value );
            $match_inmageURL = preg_match( '/\.(jpg|jpeg|png|gif)$/i', $value );

            if( $match_color === 1 ) {
                # display color if color string
                $colorStr = substr($value, 0, 1) === '#' ? $value : "#$value";
                $styleAttr = inherit_theme_mods_build_styleAttr( array(
                    'background-color' => $colorStr
                ) ); # xss OK
                $value = "<span class=\"ITM-color-indication\" $styleAttr></span><span>" . esc_html( $value ) . "</span>";
            } else if ( $match_inmageURL === 1 ) {
                # display image if image url
                $value = esc_url( $value );
                $value = "<img src=\"$value\" class=\"ITM-image-indication\" alt=\"\" /><br /><span>$value</span>"; # xss OK
            } else {
                $value = '<span class="ITM-serialized-text">' . esc_html( $value ) . '</span>';
            }

            return $value;
        }

        // try translate in order to Array $slugs.
        function __chain( $text, $slugs )
        {
            $translated = false;
            $translated_text = '';
            while ( count( $slugs ) !== 0 && ! $translated ) {
                $slug = array_shift( $slugs );
                $translated_text = __( $text, $slug, 'inherit-theme-mods' );
                $translated = $text !== $translated_text;
            }
            return esc_html( $translated_text );
        }


        $result = array();

        foreach ( $keys as $key ) {
            // slug into translatable text (is there any slugify/unslugify standard function?)

            $key_elements = explode( '_', $key );
            foreach ( $key_elements as $index => $element ) {
                $key_elements[$index] =
                    strtoupper( substr( $element, 0,  1 ) ) .
                                substr( $element, 1 );
            }
            $key_parsing = implode( ' ', $key_elements );
            $key_parsed = __chain(
                $key_parsing,
                array(
                    $child_slug,
                    $parent_slug,
                    INHERIT_THEME_MODS_TEXT_DOMAIN,
                    'default',
                )
            ); # xss OK

            // $key_parsed = $key_parsed . '<br /><small>(' . esc_html( $key ) . ')</small>';

            array_push( $result, array(
                'Key'          => $key_parsed, # WordPress Internal Error? WP_List_Table require 'Key' for sortable column, instead 'key'.
                'key'          => $key_parsed,
                'parent-theme' => transform_mod( $key, $parent_mods ),
                'child-theme'  => transform_mod( $key, $child_mods  ),
                'trashed'      => transform_mod( $key, $stored_mods ),
            ) );
        }
        return $result;
    }
}
