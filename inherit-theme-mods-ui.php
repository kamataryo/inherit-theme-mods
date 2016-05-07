<?php
/**
 * @package inherit-theme-mods
 */
class Inherit_Theme_Mods_UI {

    const NONCE_ACTION = 'inherit_theme_mods_nonce_action';
    const NONCE_FIELD  = 'nonce';
    const CAPABILITY   = 'manage_options';

    private $itm;

    function __construct() {
        $this->itm = new Inherit_Theme_Mods();
        add_action( 'admin_menu',          array( $this, 'register_admin_menu' ) );
		add_action( 'admin_menu',          array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_ajax_ITM_inherit', array( $this, 'ajax_inherit' ) );
        add_action( 'wp_ajax_ITM_restore', array( $this, 'ajax_restore' ) );
    }

    function register_admin_menu()
    {
    	$page = add_options_page(
    		__( 'Inherit Theme Mods', ITM_TEXT_DOMAIN ),
    		__( 'Inherit Theme Mods', ITM_TEXT_DOMAIN ),
    		self::CAPABILITY,
    		ITM_TEXT_DOMAIN,
    		array( $this, 'describe_ui' )
    	);
    }

    function enqueue_scripts() {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script(
            'itm_script',
            ITM_Util::url( 'assets', 'inherit-theme-mods.js' ),
            array( 'jquery' )
        );
        wp_enqueue_style(
            'font-awesome',
            ITM_Util::url( 'lib', 'font-awesome', 'css', 'font-awesome.min.css' )
        );
        wp_enqueue_style(
            'itm_style',
            ITM_Util::url( 'assets', 'inherit-theme-mods.css' ),
            array( 'font-awesome' )
        );
        wp_localize_script( 'itm_script', 'ajax', array(
    		'endpoint' => admin_url( 'admin-ajax.php' ),
    		'actions' => array(
    			'inherit' => 'ITM_inherit',
    			'restore' => 'ITM_restore',
    		),
    		self::NONCE_FIELD => wp_create_nonce( self::NONCE_ACTION ),
    		// status texts for UI
    		'status' => array(
    			'updating..' => __( 'updating..', self::NONCE_ACTION ),
    			'finished!' => __( 'finished!', self::NONCE_ACTION ),
    		),
    	) );
    }

    function ajax_inherit() {
        $message = $this->check_ajax_not_acceptable( 'inherit' );
        if ( ! $message ) {
            $this->itm->inherit();
            wp_send_json_success( $this->get_new_table()->data );
        } else {
            wp_send_json_error( $message );
        }
    }

    function ajax_restore() {
        $message = $this->check_ajax_not_acceptable( 'restore' );
        if ( ! $message ) {
            $this->itm->restore();
            wp_send_json_success( $this->get_new_table()->data );
        } else {
            wp_send_json_error( $message );
        }
    }

    function describe_ui() {
        ?>
        <div id="ITM" class="wrap">
    		<h1 id="ITM-title"><?php _e( 'Inherit Theme Mods', ITM_TEXT_DOMAIN ); ?></h1>
            <?php
            if ( $this->itm->child_theme_slug === $this->itm->parent_theme_slug ) {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <?php _e( 'Active theme has no template and is not child theme.', ITM_TEXT_DOMAIN ); ?>
                    </p>
                </div>
                <?php
                $this->describe_list_table_area();
            } else {
                $this->describe_header_area();
                $this->describe_list_table_area();
            }
            ?>
        </div><!--#ITM-->
    	<?php
    }

    static function describe_header_area() {
        ?>
        <div id="ITM-notifier" class="ITM-visit-site notice notice-success">
            <p>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php _e('Visit Site', 'default'); ?>
                </a>
            </p>
        </div>
        <form class="ITM-form">
            <h2 class="ITM-action-header"><?php  _e( 'Inherit Properties', ITM_TEXT_DOMAIN ); ?></h2>
            <p><?php _e( "Copy parent theme's properties to child. The last child properties are stored at trash box once for backup.", ITM_TEXT_DOMAIN ); ?></p>
            <div class="ITM-action-table">
                <div class="ITM-action-block">
                    <div class="ITM-action-element ITM-button-col">
                        <a id="ITM-inherit" class="ITM-button button button-primary button-large"><?php echo __( 'inherit', ITM_TEXT_DOMAIN ); ?></a>
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
            <h2 class="ITM-action-header"><?php  _e( 'Restore Properties', ITM_TEXT_DOMAIN ); ?></h2>
            <p><?php _e( "Restore child properties from trash box.", ITM_TEXT_DOMAIN ); ?></p>
            <div class="ITM-action-table">
                <div class="ITM-action-block">
                    <div class="ITM-action-element ITM-button-col">
                        <a id="ITM-restore" class="ITM-button button button-primary button-large"><?php echo __( 'restore', ITM_TEXT_DOMAIN ); ?></a>
                    </div>
                    <div class="ITM-action-element ITM-picture-col">
                        <i class="fa fa-copy fa-fw fa-3x"></i>
                        <i class="fa fa-arrow-left fa-2x"></i>
                        <i class="fa fa-trash fa-fw fa-3x"></i>
                    </div>
                </div>
            </div>
        </form>
        <?php
    }

    function describe_list_table_area() {
    	// generate list table with Admin Table class

        $itmTable = $this->get_new_table();
    	$itmTable->prepare_items();
    	echo '<div id="ITM-Content" class="wrap">';
    	$itmTable->display();
    	echo '</div>';
    }

    function get_new_table() {
        return new Inherit_Theme_Mods_Table(
            $this->itm->child_theme_slug,
            $this->itm->parent_theme_slug
        );
    }

    static function verify_nonce() {
        if ( func_num_args() > 0 ) {
            return wp_verify_nonce( func_get_arg( 0 ) , self::NONCE_ACTION );
        } elseif ( isset( $_REQUEST[self::NONCE_FIELD] ) ) {
            return wp_verify_nonce( $_REQUEST[self::NONCE_FIELD] , self::NONCE_ACTION );
        } else {
            return false;
        }
    }

    public function check_ajax_not_acceptable( $method ) {
        if ( ! current_user_can( self::CAPABILITY ) ) {
            return __( 'You do not have sufficient permissions for the request.', ITM_TEXT_DOMAIN );

        } else if ( self::verify_nonce() ) {
            return __('Invalid request.', ITM_TEXT_DOMAIN );

        } else if ( ! $this->itm->is_child_theme_active() ) {
            return sprintf( __( 'No Child Theme to %1$s.', ITM_TEXT_DOMAIN ), $method );

        } else {
            return false;
        }
    }
}










// register this plugins menu at `setting` section
function inherit_theme_mods_admin_menu()
{
	$page = add_options_page(
		__( 'Inherit Theme Mods', ITM_TEXT_DOMAIN ),
		__( 'Inherit Theme Mods', ITM_TEXT_DOMAIN ),
		'manage_options',
		ITM_TEXT_DOMAIN,
		'describe_inherit_theme_mods_ui_contents'
	);
    var_dump($page);
}
// add_action( 'admin_menu', 'inherit_theme_mods_admin_menu' );


function inherit_theme_mods_enqueue_script()
{
    $scripts_path = array(
       'itm_script'   => ITM_Util::url( 'assets', 'inherit-theme-mods.js' ),
       'font-awesome' => ITM_Util::url( 'lib', 'font-awesome', 'css', 'font-awesome.min.css' ),
       'itm_style'    => ITM_Util::url( 'assets', 'inherit-theme-mods.css' )
    );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'itm_script',   $scripts_path['itm_script'], array( 'jquery' ) );
	wp_enqueue_style(  'font-awesome', $scripts_path['font-awesome'] );
	wp_enqueue_style(  'itm_style',    $scripts_path['itm_style'] );
	wp_localize_script( 'itm_script', 'ajax', array(
		'endpoint' => admin_url( 'admin-ajax.php' ),
		'actions' => array(
			'inherit' => 'ITM_inherit',
			'restore' => 'ITM_restore',
		),
		ITM_NONCE_FIELD => wp_create_nonce( ITM_NONCE_ACTION ),
		// status texts for UI
		'status' => array(
			'updating..' => __( 'updating..', ITM_NONCE_ACTION ),
			'finished!' => __( 'finished!', ITM_NONCE_ACTION ),
		),
	) );
}
// add_action( 'admin_enqueue_scripts', 'inherit_theme_mods_enqueue_script' );


function describe_inherit_theme_mods_ui_contents() {
    $child_slug = wp_get_theme()->stylesheet;
    $parent_slug = wp_get_theme()->template;
    ?>
    <div id="ITM" class="wrap">
		<h1 id="ITM-title"><?php _e( 'Inherit Theme Mods', ITM_TEXT_DOMAIN ); ?></h1>
        <?php
        if ( $child_slug === $parent_slug ) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <?php _e( 'Active theme has no template and is not child theme.', ITM_TEXT_DOMAIN ); ?>
                </p>
            </div>
            <?php
            inherit_theme_mods_describe_list_table_area();
        } else {
            inherit_theme_mods_describe_header_area();
            inherit_theme_mods_describe_list_table_area();
        }
        ?>
    </div><!--#ITM-->
	<?php
}


function inherit_theme_mods_describe_header_area() {
    ?>
    <div id="ITM-notifier" class="ITM-visit-site notice notice-success is-dismissible">
        <p>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php _e('Visit Site', 'default'); ?>
            </a>
        </p>
    </div>
    <form class="ITM-form">
        <h2 class="ITM-action-header"><?php  _e( 'Inherit Properties', ITM_TEXT_DOMAIN ); ?></h2>
        <p><?php _e( "Copy parent theme's properties to child. The last child properties are stored at trash box once for backup.", ITM_TEXT_DOMAIN ); ?></p>
        <div class="ITM-action-table">
            <div class="ITM-action-block">
                <div class="ITM-action-element ITM-button-col">
                    <a id="ITM-inherit" class="ITM-button button button-primary button-large"><?php echo __( 'inherit', ITM_TEXT_DOMAIN ); ?></a>
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
        <h2 class="ITM-action-header"><?php  _e( 'Restore Properties', ITM_TEXT_DOMAIN ); ?></h2>
        <p><?php _e( "Restore child properties from trash box.", ITM_TEXT_DOMAIN ); ?></p>
        <div class="ITM-action-table">
            <div class="ITM-action-block">
                <div class="ITM-action-element ITM-button-col">
                    <a id="ITM-restore" class="ITM-button button button-primary button-large"><?php echo __( 'restore', ITM_TEXT_DOMAIN ); ?></a>
                </div>
                <div class="ITM-action-element ITM-picture-col">
                    <i class="fa fa-copy fa-fw fa-3x"></i>
                    <i class="fa fa-arrow-left fa-2x"></i>
                    <i class="fa fa-trash fa-fw fa-3x"></i>
                </div>
            </div>
        </div>
    </form>
    <?php
}

// display mods as WP Admin Table
function inherit_theme_mods_describe_list_table_area() {
	// generate list table with Admin Table class
	$child_slug = wp_get_theme()->stylesheet;
	$parent_slug = wp_get_theme()->template;
	$listTable = new Inherit_Theme_Mods_Table( $child_slug, $parent_slug );
	$listTable->prepare_items();
	echo '<div id="ITM-Content" class="wrap">';
	$listTable->display();
	echo '</div>';
}

// same as inherit_theme_mods_describe_list_table_area but return array
function inherit_theme_mods_get_mods_array() {
	$child_slug = wp_get_theme()->stylesheet;
	$parent_slug = wp_get_theme()->template;
	if ( $child_slug === $parent_slug ) {
		echo '<p>' . __( 'Active theme has no template and is not child theme.', ITM_TEXT_DOMAIN ) . '</p>';
		return;
	}
	// generate list table with Admin Table class
	$listTable = new Inherit_Theme_Mods_Table( $child_slug, $parent_slug );
	return $listTable->data;
}

// check ajax nonce here
function inherit_theme_mods_ajax_verify_nonce()
{
	return wp_verify_nonce( $_REQUEST[ITM_NONCE_FIELD], ITM_NONCE_ACTION );
}

// register ajax actions
function inherit_theme_mods_register_ajax_inherit()
{
	if ( inherit_theme_mods_ajax_verify_nonce() ) {
        ( new Inherit_Theme_Mods() )->inherit();
        wp_send_json( inherit_theme_mods_get_mods_array() );
	} else {
        echo '<p>' . __('Request is not acceptable.', ITM_TEXT_DOMAIN ) . '</p>';
    }
    die();
}
// add_action( 'wp_ajax_ITM_inherit', 'inherit_theme_mods_register_ajax_inherit' );

function inherit_theme_mods_register_ajax_restore()
{
	if ( inherit_theme_mods_ajax_verify_nonce() ) {
        ( new Inherit_Theme_Mods() )->restore();
        wp_send_json( inherit_theme_mods_get_mods_array() );
	} else {
        echo '<p>' . __('Request is not acceptable.', ITM_TEXT_DOMAIN ) . '</p>';
    }
    die();
}
// add_action( 'wp_ajax_ITM_restore', 'inherit_theme_mods_register_ajax_restore' );
