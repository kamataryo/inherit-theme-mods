<?php
/**
 * @package inherit-theme-mods
 */

/**
 * Control I/O of this plugin via UI
 */
class Inherit_Theme_Mods_UI {
	// nonce for this plugin
	const NONCE_ACTION = 'inherit_theme_mods_nonce_action';
	// default capability to access this plugin and its functions
	const CAPABILITY   = 'manage_options';
	static private $ajax_actions = array(
		'inherit' => 'ITM_inherit',
		'restore' => 'ITM_restore',
		'overwrite' => 'ITM_overwrite',
	);
	private $itm;

	function __construct() {
		$this->itm = new Inherit_Theme_Mods();
		add_action( 'plugins_loaded', array( $this, 'register_textdomain' ) );
		add_action( 'admin_menu',     array( $this, 'register_admin_menu' ) );
		add_action( 'wp_ajax_' . self::$ajax_actions['inherit'],   array( $this, 'ajax_inherit' ) );
		add_action( 'wp_ajax_' . self::$ajax_actions['overwrite'], array( $this, 'ajax_overwrite' ) );
		add_action( 'wp_ajax_' . self::$ajax_actions['restore'],   array( $this, 'ajax_restore' ) );
	}

	function register_textdomain() {
		load_plugin_textdomain(
			'inherit-theme-mods',
			false,
			basename( dirname( __FILE__ ) ) . '/languages'
		);
	}

	function register_admin_menu() {
		$hook = add_options_page(
			__( 'Inherit Theme Mods', 'inherit-theme-mods' ),
			__( 'Inherit Theme Mods', 'inherit-theme-mods' ),
			self::CAPABILITY,
			'inherit-theme-mods',
			array( $this, 'describe_ui' )
		);
		add_action( "admin_head-$hook", array( $this, 'enqueue_scripts' ) );
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
			'nonce'    => wp_create_nonce( self::NONCE_ACTION ),
			'status'   => array(
				'success'      => __( 'Processed successfully.', 'inherit-theme-mods' ) . '<a href="' . esc_url( home_url( '/' ) ) . '" class="ITM-aside">' . __( 'Visit Site', 'inherit-theme-mods' ) . '</a>',
				'unknownError' => __( 'Unknown error', 'inherit-theme-mods' ),
			),
		) );
	}

	private function ajax( $action ) {
		$message = $this->check_ajax_not_acceptable( $action );
		if ( ! $message ) {
			// $this->itm->$action(); # >PHP5.4
			call_user_func( array( $this->itm, $action ) ); # PHP5.3
			$data = $this->get_new_table()->data;
			foreach ($data as $index => $datum ) {
				unset( $data[$index]['key'] );
				unset( $data[$index]['Key'] );
			}
			wp_send_json_success( $data ); # xss OK
		} else {
			wp_send_json_error( esc_html( $message ) );
		}
	}

	public function ajax_inherit()   { $this->ajax( 'inherit' ); }

	public function ajax_overwrite() { $this->ajax( 'overwrite' ); }

	public function ajax_restore()   { $this->ajax( 'restore' ); }

	public function describe_ui() {
		?>
		<div id="ITM" class="wrap">
			<h1 id="ITM-title"><?php _e( 'Inherit Theme Mods', 'inherit-theme-mods' ); ?>
				<span id="ITM-instant-notifier" class="ITM-status-notifier ITM-aside"></span>
			</h1>
			<?php
			$itm = new Inherit_Theme_Mods();
			if ( ! $itm->is_child_theme_active() ) { # PHP5.3
				?>
				<div id="ITM-notifier" class="notice notice-warning">
					<p>
						<?php _e( 'The active theme is not child theme. This plugin is simply working as inspector.', 'inherit-theme-mods' ); ?>
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
					<?php _e( 'Visit Site', 'inherit-theme-mods' ); ?>
				</a>
			</p>
		</div>
		<form class="ITM-form">

			<h2 class="ITM-action-header"><?php  _e( 'Inherit Properties', 'inherit-theme-mods' ); ?></h2>
			<p><?php _e( "Copy and inherit parent theme's properties to child. Original child properties will be preserved. The last child properties are stored at trash box once for backup.", 'inherit-theme-mods' ); ?></p>
			<div class="ITM-action-table">
				<div class="ITM-action-block">
					<div class="ITM-action-element ITM-button-col">
						<a id="ITM-inherit" class="ITM-button button button-primary button-large" data-action="<?php echo esc_attr( self::$ajax_actions['inherit']); ?>">
							<?php _e( 'inherit', 'inherit-theme-mods' ); ?>
						</a>
					</div>
					<div class="ITM-action-element ITM-picture-col">
						<i class="fa fa-file-o fa-fw fa-3x"></i>
						<i class="fa fa-arrow-right fa-2x"></i>
						<i class="fa fa-copy fa-fw fa-3x"></i>
						<i class="fa fa-arrow-right"></i>
						<i class="fa fa-trash-o fa-fw fa-2x"></i>
					</div>
				</div>
			</div>

			<h2 class="ITM-action-header"><?php  _e( 'Overwrite Properties', 'inherit-theme-mods' ); ?></h2>
			<p><?php _e( "Copy and overwrite parent theme's properties to child. Original child properties will be aborted. The last child properties are stored at trash box once for backup.", 'inherit-theme-mods' ); ?></p>
			<div class="ITM-action-table">
				<div class="ITM-action-block">
					<div class="ITM-action-element ITM-button-col">
						<a id="ITM-overwrite" class="ITM-button button button-primary button-large" data-action="<?php echo esc_attr( self::$ajax_actions['overwrite']); ?>">
							<?php _e( 'overwrite', 'inherit-theme-mods' ); ?>
						</a>
					</div>
					<div class="ITM-action-element ITM-picture-col">
						<i class="fa fa-file fa-fw fa-3x"></i>
						<i class="fa fa-arrow-right fa-2x"></i>
						<i class="fa fa-copy fa-fw fa-3x"></i>
						<i class="fa fa-arrow-right"></i>
						<i class="fa fa-trash-o fa-fw fa-2x"></i>
					</div>
				</div>
			</div>


			<h2 class="ITM-action-header"><?php  _e( 'Restore Properties', 'inherit-theme-mods' ); ?></h2>
			<p><?php _e( "Restore child properties from trash box.", 'inherit-theme-mods' ); ?></p>
			<div class="ITM-action-table">
				<div class="ITM-action-block">
					<div class="ITM-action-element ITM-button-col">
						<a id="ITM-restore" class="ITM-button button button-primary button-large" data-action="<?php echo esc_attr( self::$ajax_actions['restore']); ?>">
							<?php _e( 'restore', 'inherit-theme-mods' ); ?>
						</a>
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

	// generate list table with Admin Table class
	function describe_list_table_area() {

		$itmTable = $this->get_new_table();
		$itmTable->prepare_items();
		echo '<div id="ITM-Content" class="wrap">';
		$itmTable->display();
		echo '</div>';
	}

	// refresh table
	function get_new_table() {
		return new Inherit_Theme_Mods_Table(
			$this->itm->child_theme_slug,
			$this->itm->parent_theme_slug
		);
	}

	static function verify_nonce() {
		if ( func_num_args() > 0 ) {
			return wp_verify_nonce( func_get_arg( 0 ) , self::NONCE_ACTION );
		} elseif ( isset( $_REQUEST['nonce'] ) ) {
			return wp_verify_nonce( $_REQUEST['nonce'] , self::NONCE_ACTION );
		} else {
			return false;
		}
	}

	private function check_ajax_not_acceptable( $method ) {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return __( 'You do not have sufficient permissions for the request.', 'inherit-theme-mods' );

		} else if ( ! self::verify_nonce() ) {
			return __( 'Invalid request.', 'inherit-theme-mods' );

		} else if ( ! $this->itm->is_child_theme_active() ) {
			return sprintf( __( 'No Child Theme has been activated.', 'inherit-theme-mods' ) );
		} else {
			return false;
		}
	}
}
