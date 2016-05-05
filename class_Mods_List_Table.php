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
            'singular' => __( 'key', INHERIT_THEME_MODS_TEXT_DOMAIN ),
            'plural'   => __( 'keys', INHERIT_THEME_MODS_TEXT_DOMAIN ),
            'ajax'     => false,
        ) );
    }

    function column_default( $item, $column_name )
    {
        return maybe_serialize( $item[$column_name] );
    }

    function get_columns()
    {
        return array(
            'key'          => 'Key',
            'parent theme' => '<i class="fa fa-file-o fa-2x"></i><span class="ITM-table-header-text">Parent Theme</span>',
            'child theme'  => '<i class="fa fa-copy fa-2x"></i><span class="ITM-table-header-text">Child Theme</span>',
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
        $per_page = 10;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $data = $this->inherit_theme_mods_generate_data_array( $this->child_slug, $this->parent_slug );

        function usort_reorder( $a,$b ){
            $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'key'; //If no sort, default to title
            $order   = ( ! empty( $_REQUEST['order']   ) ) ? $_REQUEST['order']   : 'asc'; //If no order, default to asc
            $result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort( $data, 'usort_reorder' );
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
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
                    'background-color' => $colorStr,
                    'display' => 'inline-block',
                    'width' => '25px',
                    'height' => '25px',
                    'margin-right' => '.5em'
                ) );
                $value = "<span $styleAttr></span><span>$value</span>";
            } else if ( $match_inmageURL === 1 ) {
                #display image if image url
                $value = "<img src=\"$value\" style=\"width:100%\" alt=\"\" /><br /><span>$value</span>";
            }

            return $value;
        }

        $result = array();
        foreach ( $keys as $key ) {
            array_push( $result, array(
                'Key'          => $key, # WordPress Internal Error?
                'key'          => $key,
                'parent theme' => transform_mod( $key, $parent_mods ),
                'child theme'  => transform_mod( $key, $child_mods  ),
                'trashed'      => transform_mod( $key, $stored_mods ),
            ) );
        }
        return $result;
    }
}
