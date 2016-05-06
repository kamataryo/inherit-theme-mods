<?php
/**
 * @package inherit-theme-mods
 */
define( 'INHERIT_THEME_MODS_NONCE_FIELD', 'nonce' );
define( 'INHERIT_THEME_MODS_NONCE_ACTION', 'ITM_nonce' );
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class_Mods_List_Table.php';


// register this plugins menu at `setting` section
function inherit_theme_mods_admin_menu()
{
    $page = add_options_page(
        __( 'Inherit Theme Mods', INHERIT_THEME_MODS_TEXT_DOMAIN ),
        __( 'Inherit Theme Mods', INHERIT_THEME_MODS_TEXT_DOMAIN ),
        'manage_options',
        INHERIT_THEME_MODS_TEXT_DOMAIN,
        'describe_inherit_theme_mods_ui_content_header'
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
            // status texts for UI
            'status' => array(
                'updating..' => __( 'updating..', INHERIT_THEME_MODS_NONCE_ACTION ),
                'finished!' => __( 'finished!', INHERIT_THEME_MODS_NONCE_ACTION ),
            ),
        )
    );
}
add_action( 'admin_enqueue_scripts', 'inherit_theme_mods_enqueue_script' );


function describe_inherit_theme_mods_ui_content_header()
{
    ?>
    <div class="wrap">
        <h1 id="ITM-title"><?php _e( 'Inherit Theme Mods', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?></h1>
        <form class="ITM-form">
            <div class="ITM-action-table">
                <div class="ITM-action-block">
                    <div class="ITM-action-element ITM-button-col">
                        <a id="ITM-inherit" class="ITM-button button button-primary button-large"><?php echo __( 'inherit', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?></a>
                    </div>
                    <div class="ITM-action-element ITM-picture-col">
                        <i class="fa fa-file-o fa-fw fa-3x"></i>
                        <i class="fa fa-arrow-right fa-2x"></i>
                        <i class="fa fa-copy fa-fw fa-3x"></i>
                        <i class="fa fa-arrow-right fa-2x"></i>
                        <i class="fa fa-trash-o fa-fw fa-3x"></i>
                    </div>
                </div>
            </div>
            <p><?php _e( "Copy parent theme's properties to child. The last child properties are stored at trash once for backup.", INHERIT_THEME_MODS_TEXT_DOMAIN ); ?></p>
            <div class="ITM-action-table">
                <div class="ITM-action-block">
                    <div class="ITM-action-element ITM-button-col">
                        <a id="ITM-restore" class="ITM-button button button-primary button-large"><?php echo __( 'restore', INHERIT_THEME_MODS_TEXT_DOMAIN ); ?></a>
                    </div>
                    <div class="ITM-action-element ITM-picture-col">
                        <i class="fa fa-copy fa-fw fa-3x"></i>
                        <i class="fa fa-arrow-left fa-2x"></i>
                        <i class="fa fa-trash fa-fw fa-3x"></i>
                    </div>
                </div>
            </div>

            <p><?php _e( "Restore child properties from trash box.", INHERIT_THEME_MODS_TEXT_DOMAIN ); ?></p>

        </form>
    </div>
    <?php
    inherit_theme_mods_ui_update_view();
}


function inherit_theme_mods_ui_update_view()
{
    if ( !current_user_can( 'manage_options' ) )  {
        echo '<p>' . __( 'You do not have sufficient permissions to access this page.', INHERIT_THEME_MODS_TEXT_DOMAIN ) . '</p>';
        return;
    }

    $child_slug = wp_get_theme()->stylesheet;
    $parent_slug = wp_get_theme()->template;

    if ( $child_slug === $parent_slug ) {
        echo '<p>' . __( 'Active theme has no template and is not child theme.', INHERIT_THEME_MODS_TEXT_DOMAIN ) . '</p>';
        return;
    }
    // generate list table with Admin Table class
    $listTable = new Mods_List_Table( $child_slug, $parent_slug );
    $listTable->prepare_items();
    echo '<div id="ITM-Content" class="wrap">';
    $listTable->display();
    echo '</div>';
}


// check ajax nonce here
function inherit_theme_mods_check_ajax_nonce( $callback )
{
    $verified = wp_verify_nonce( $_REQUEST['nonce'], INHERIT_THEME_MODS_NONCE_ACTION );
    if ( ! $verified ) {
        echo '<p>' . __('Request is not acceptable.', INHERIT_THEME_MODS_TEXT_DOMAIN ) . '</p>';
    } else {
        if ( function_exists( $callback) ) {
            $callback();
        }
        inherit_theme_mods_ui_update_view();
    }
    die();
}

// ajax gateways' gateways
function inherit_theme_mods_ajax_inherit()
{
    inherit_theme_mods_check_ajax_nonce( 'inherit_theme_mods_inherit' );
}
add_action( 'wp_ajax_ITM_inherit', 'inherit_theme_mods_ajax_inherit' );


function inherit_theme_mods_ajax_restore()
{
    inherit_theme_mods_check_ajax_nonce( 'inherit_theme_mods_restore' );
}
add_action( 'wp_ajax_ITM_restore', 'inherit_theme_mods_ajax_restore' );
