<?php
/**
 * @package inherit-theme-mods
 */



// class Inherit_Theme_Mods_Admin_UI
// {
//     var $child_theme;
//     var $parent_theme;
//     function __construct() {
//
//     }
// }


//helper function
function itm_build_path(){
	$dirs = func_get_args();
	return plugins_url( implode( $dirs, DIRECTORY_SEPARATOR ), __FILE__ );
}
$scripts_path = array(
	'itm_script'   => itm_build_path( 'assets', 'inherit-theme-mods.js' ),
	'font-awesome' => itm_build_path( 'lib', 'font-awesome', 'css', 'font-awesome.min.css' ),
	'itm_style'    => itm_build_path( 'assets', 'inherit-theme-mods.css' )
);



// helper_functions
function inherit_theme_mods_build_styleAttr( $styles )
{
	$result = '';
	foreach ($styles as $directive => $value) {
		$result .= "$directive:$value;";
	}
	return 'style="' . esc_attr( $result ) . '"';
}



// register this plugins menu at `setting` section
function inherit_theme_mods_admin_menu()
{
	$page = add_options_page(
		__( 'Inherit Theme Mods', ITM_TEXT_DOMAIN ),
		__( 'Inherit Theme Mods', ITM_TEXT_DOMAIN ),
		'manage_options',
		ITM_TEXT_DOMAIN,
		'describe_inherit_theme_mods_ui_content_header'
	);
}
add_action( 'admin_menu', 'inherit_theme_mods_admin_menu' );


function inherit_theme_mods_enqueue_script()
{
	global $scripts_path;
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'itm_script',   $scripts_path['itm_script'], array( 'jquery' ) );
	wp_enqueue_style(  'font-awesome', $scripts_path['font-awesome'] );
	wp_enqueue_style(  'itm_style',    $scripts_path['itm_style'], array( 'font-awesome' ) );
	wp_localize_script(
		'itm_script',
		'ajax',
		array(
			'endpoint' => admin_url( 'admin-ajax.php' ),
			'actions' => array(
				'inherit' => 'ITM_inherit',
				'restore' => 'ITM_restore',
			),
			'nonce' => wp_create_nonce( ITM_NONCE_ACTION ),
			// status texts for UI
			'status' => array(
				'updating..' => __( 'updating..', ITM_NONCE_ACTION ),
				'finished!' => __( 'finished!', ITM_NONCE_ACTION ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'inherit_theme_mods_enqueue_script' );


function describe_inherit_theme_mods_ui_content_header()
{
	$child_slug = wp_get_theme()->stylesheet;
	$parent_slug = wp_get_theme()->template;
	?>
	<div class="wrap">
		<h1 id="ITM-title"><?php _e( 'Inherit Theme Mods', ITM_TEXT_DOMAIN ); ?></h1>
	<?php
		if ( $child_slug === $parent_slug ) {
			echo '<p>' . __( 'Active theme has no template and is not child theme.', ITM_TEXT_DOMAIN ) . '</p>';
		} else {
		# section below are skipped without child theme
	?>
		<div class="ITM-visit-site">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php _e('Visit Site', 'default'); ?>
			</a>
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
	</div>
	<?php
		}

	inherit_theme_mods_ui_update_view();
}

// display mods as WP Admin Table
function inherit_theme_mods_ui_update_view()
{
	if ( !current_user_can( 'manage_options' ) )  {
		echo '<p>' . __( 'You do not have sufficient permissions to access this page.', ITM_TEXT_DOMAIN ) . '</p>';
		return;
	}

	// generate list table with Admin Table class
	$child_slug = wp_get_theme()->stylesheet;
	$parent_slug = wp_get_theme()->template;
	$listTable = new Mods_List_Table( $child_slug, $parent_slug );
	$listTable->prepare_items();
	echo '<div id="ITM-Content" class="wrap">';
	$listTable->display();
	echo '</div>';
}

// same as inherit_theme_mods_ui_update_view but return array
function inherit_theme_mods_get_mods_array()
{
	if ( !current_user_can( 'manage_options' ) )  {
		echo '<p>' . __( 'You do not have sufficient permissions to access this page.', ITM_TEXT_DOMAIN ) . '</p>';
		return;
	}

	$child_slug = wp_get_theme()->stylesheet;
	$parent_slug = wp_get_theme()->template;
	if ( $child_slug === $parent_slug ) {
		echo '<p>' . __( 'Active theme has no template and is not child theme.', ITM_TEXT_DOMAIN ) . '</p>';
		return;
	}
	// generate list table with Admin Table class
	$listTable = new Mods_List_Table( $child_slug, $parent_slug );
	return $listTable->data;
}


// check ajax nonce here
function inherit_theme_mods_ajax_callback( $callback )
{
	$verified = wp_verify_nonce( $_REQUEST['nonce'], ITM_NONCE_ACTION );
	if ( ! $verified ) {
		echo '<p>' . __('Request is not acceptable.', ITM_TEXT_DOMAIN ) . '</p>';
	} else {
		$itm = new Inherit_Theme_Mods();
		if ( method_exists( $itm, $callback ) ) {
			// $imt->$callback();
			wp_send_json( inherit_theme_mods_get_mods_array() );
		}
	}
	die();
}

// ajax gateways' gateways
function inherit_theme_mods_register_ajax_inherit()
{
	inherit_theme_mods_ajax_callback( 'inherit' );
}
add_action( 'wp_ajax_ITM_inherit', 'inherit_theme_mods_register_ajax_inherit' );

function inherit_theme_mods_register_ajax_restore()
{
	inherit_theme_mods_ajax_callback( 'restore' );
}
add_action( 'wp_ajax_ITM_restore', 'inherit_theme_mods_register_ajax_restore' );

// This function stores some texts only to provide for translation
// they may appear at `wp_options` table and be not available to resolve the slug to translate.
// Some of theme mod slug appears in official theme were picked, not all.
function __translation_store()
{
	__( 'Header Image Data',     ITM_NONCE_ACTION );
	__( 'Nav Menu Locations',    ITM_NONCE_ACTION );
	__( 'Sidebars Widgets',      ITM_NONCE_ACTION );
	__( 'Color Scheme',          ITM_NONCE_ACTION ); # ベース配色 in ja
	__( 'Background Position X', ITM_NONCE_ACTION ); # 背景の位置 in ja
}
