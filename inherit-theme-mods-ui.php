<?php
/**
 * @package inherit-theme-mods
 */
 define( 'INHERIT_THEME_MODS_NONCE_FIELD', 'nonce' );


function inherit_theme_mods_admin_menu()
{
    $page = add_options_page(
        __( 'Inherit Theme Mods', INHERIT_THEME_MODS_TEXT_DOMAIN ),
        __( 'Inherit Theme Mods', INHERIT_THEME_MODS_TEXT_DOMAIN ),
        'manage_options',
        INHERIT_THEME_MODS_TEXT_DOMAIN,
        'describe_inherit_theme_mods_options_ui'
    );
}
add_action( 'admin_menu', 'inherit_theme_mods_admin_menu' );


function inherit_theme_mods_enqueue_script()
{
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script(
        'inherit_theme_mods_scripts',
        plugins_url( 'assets/inherit-theme-mods.js', __FILE__ ),
        array( 'jquery' )
    );
     wp_localize_script(
        'inherit_theme_mods_scripts',
        'ajax',
        array(
            'endpoint' => admin_url( 'admin-ajax.php' ),
            'actions' => array(
                'inherit' => 'ITM_inherit',
                'restore' => 'ITM_restore',
            ),
            'nonceField' => INHERIT_THEME_MODS_NONCE_FIELD,
        )
    );
}
add_action( 'admin_enqueue_scripts', 'inherit_theme_mods_enqueue_script' );


function describe_inherit_theme_mods_options_ui()
{
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', INHERIT_THEME_MODS_TEXT_DOMAIN ) );
    }
    $child_slug = wp_get_theme()->stylesheet;
    $parent_slug = wp_get_theme()->template;
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
            array_keys( $child_mods ),
            array_keys( $parent_mods ),
            array_keys( $stored_mods )
        )
    );

    $holders = array( &$child_mods, &$parent_mods, &$stored_mods );
    foreach ( $keys as $key ) {
        foreach ( $holders as $index => $holder ) {
            if ( ! array_key_exists( $key, $holder ) ) {
                $holders[$index][$key] = '';
            }
            $value = maybe_serialize( $holders[$index][$key] );
            # display color if color string
            $match = preg_match( '/^#?([0-9,a-f,A-F]{3}|[0-9,a-f,A-F]{6})$/', $value );
            if( $match === 1 ) {
                $colorStr = substr($value, 0, 1) === '#' ? $value : "#$value";
                $styleAttr = inherit_theme_mods_build_styleAttr( array(
                    'background-color' => $colorStr,
                    'padding' => '.5em 1em',
                ) );
                $value = "<span $styleAttr>$value</span>";
            }
            #display image if image url
            $match = preg_match( '/\.(jpg|jpeg|png|gif)$/i', $value );
            if( $match === 1 ) {
                $value = "<img src=\"$value\" style=\"max-width:200px\" alt=\"\" /><br /><span>$value</span>";
            }
            $holders[$index][$key] = $value;
        }
    }


    ?>
    <div id="ITMContent" class="wrap">
        <h1><?php echo __( 'Inherit Theme Mods', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?></h1>
        <form>
            <?php wp_nonce_field( 'ITMajax', INHERIT_THEME_MODS_NONCE_FIELD ); ?>
            <span><?php __( 'parent theme', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?></span>
            <a id="inherit">inherit</a>
            <span><?php echo __( 'current theme', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?></span>
            <a id="restore">restore</a>
            <span><?php echo __( 'trash', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?></span>
        </form>


        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th scope="col" id="keys" class="manage-column column-keys column-primary sortable desc">
                        <?php echo __( 'keys', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?>
                    </th>
                    <th scope="col" id="parent_theme"  class="manage-column column-parent_theme">
                        <?php echo __( 'parent theme', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?>
                    </th>
                    <th scope="col" id="current_theme" class="manage-column column-current_theme">
                        <?php echo __( 'current theme', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?>
                    </th>
                    <th scope="col" id="stored" class="manage-column column-stored">
                        <?php echo __( 'stored', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?>
                    </th>
                </tr>
            </thead>
            <tbody class="the-list">
                <?php foreach ( $keys as $key ): ?>
                    <tr id="key-<?php echo $key; ?>">
                        <th scope="row">
                            <?php echo $key; ?>
                            <button type="button" class="toggle-row"><span class="screen-reader-text">詳細を追加表示</span></button>
                        </th>
                        <td><?php echo $parent_mods[$key]; ?></td>
                        <td><?php echo $child_mods[$key]; ?></td>
                        <td><?php echo $stored_mods[$key]; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}


//define ajax actions
function inherit_theme_mods_ajax_inherit()
{
    inherit_theme_mods_inherit();
    describe_inherit_theme_mods_options_ui();
    die();
}
add_action( 'wp_ajax_ITM_inherit', 'inherit_theme_mods_ajax_inherit' );


function inherit_theme_mods_ajax_restore()
{
    inherit_theme_mods_restore();
    describe_inherit_theme_mods_options_ui();
    die();
}
add_action( 'wp_ajax_ITM_restore', 'inherit_theme_mods_ajax_restore' );
