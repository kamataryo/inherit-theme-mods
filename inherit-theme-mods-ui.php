<?php
/**
 * @package inherit-theme-mods
 */
 define( 'INHERIT_THEME_MODS_NONCE_FIELD', 'nonce' );
 define( 'INHERIT_THEME_MODS_NONCE_ACTION', 'ITM_nonce' );


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
    wp_enqueue_style(
        'font-awesome',
        plugins_url( 'lib/font-awesome/css/font-awesome.min.css', __FILE__ )
    );
    wp_enqueue_style(
        'inherit_theme_mods_style',
        plugins_url( 'assets/inherit-theme-mods.css', __FILE__ ),
        array( 'font-awesome' )
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
            'nonce' => wp_create_nonce( INHERIT_THEME_MODS_NONCE_ACTION ),
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

    if ( $child_slug === $parent_slug ) {
        echo '<p>' . __( 'Active theme has no template and is not child theme.', INHERIT_THEME_MODS_TEXT_DOMAIN ) . '</p>';
        return;
    }

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

    // check and process each value of mods, if needed
    foreach ( $keys as $key ) {
        foreach ( $holders as $index => $holder ) {

            // attach empty value
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
            <div id="action_inherit">
                <a id="inherit">inherit
                    <span>
                        <i class="fa fa-file-o fa-3x"></i>
                        <i class="fa fa-rotate-right fa-3x"></i>
                        <i class="fa fa-copy fa-3x"></i>
                        <i class="fa fa-rotate-right fa-3x"></i>
                        <i class="fa fa-trash fa-3x"></i>
                    </span>
                </a>
            </div>
            <div id="action_restore">
                <a id="restore">restore
                    <span>
                        <i class="fa fa-copy fa-3x"></i>
                        <i class="fa fa-rotate-left fa-3x"></i>
                        <i class="fa fa-trash fa-3x"></i>
                    </span>
                </a>
            </div>

        </form>

        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th scope="col" id="keys" class="manage-column column-keys column-primary sortable desc">
                        <?php echo __( 'keys', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?>
                    </th>
                    <th scope="col" id="parent_theme"  class="manage-column column-parent_theme">
                        <i class="fa fa-file-o fa-3x"></i>
                        <?php echo __( 'parent theme', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?>
                    </th>
                    <th scope="col" id="current_theme" class="manage-column column-current_theme">
                        <i class="fa fa-copy fa-3x"></i>
                        <?php echo __( 'current theme', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?>
                    </th>
                    <th scope="col" id="trashed" class="manage-column column-trashed">
                        <i class="fa fa-trash fa-3x"></i>
                        <?php echo __( 'trashed', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?>
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

    $verified = wp_verify_nonce( $_REQUEST['nonce'], INHERIT_THEME_MODS_NONCE_ACTION );
    if ( ! $verified ) {
        echo '<p>' . __('Request is not acceptable.', INHERIT_THEME_MODS_TEXT_DOMAIN ) . '</p>';
    } else {
        inherit_theme_mods_inherit();
        describe_inherit_theme_mods_options_ui();
    }
    die();
}
add_action( 'wp_ajax_ITM_inherit', 'inherit_theme_mods_ajax_inherit' );


function inherit_theme_mods_ajax_restore()
{
    $verified = wp_verify_nonce( INHERIT_THEME_MODS_NONCE_ACTION, $_REQUEST['nonce'] );
    if ( ! $verified ) {
        echo '<p>' . __('Request is not acceptable.', INHERIT_THEME_MODS_TEXT_DOMAIN ) . '</p>';
    } else {
        inherit_theme_mods_restore();
        describe_inherit_theme_mods_options_ui();
    }
    die();
}
add_action( 'wp_ajax_ITM_restore', 'inherit_theme_mods_ajax_restore' );
