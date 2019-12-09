<?php
/**
 * WIR Clone Location.
 *
 * @since   0.0.1
 * @package WIR
 */

/**
 * WIR Clone Location.
 *
 * @since 0.0.1
 */
class WIR_Clone_Location {
	/**
	 * Singleton instance of plugin.
	 *
	 * @var    WIR
	 * @since  0.0.1
	 */
	protected static $single_instance = null;

	public static $post_id;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.0.1
	 * @return  A single instance of this class.
	 */

	public function __construct() {
		if ( null === self::$single_instance ) {
			self::$single_instance = true;
			self::hooks();
		}

		return self::$single_instance;
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.1
	 */
	public function hooks() {
		add_action( 'wp_ajax_wir_cloc_hook', 		array( __CLASS__, 'vars' ) );
		add_action( 'wp_ajax_nopriv_wir_cloc_hook', array( __CLASS__, 'vars' ) );

		add_action( 'wp_ajax_wir_cloc', 			array( __CLASS__, 'vars' ) );
		add_action( 'wp_ajax_nopriv_wir_cloc', 		array( __CLASS__, 'vars' ) );

		add_action( 'wp_footer', 					array( __CLASS__, 'popup_html' ) );
		add_action( 'cmb2_init', 					array( __CLASS__, 'register_form_clone'  ) );	

		add_action( 'wp_ajax_webdev', 				array( __CLASS__, 'd_handle_ajax' ) );
		add_action( 'wp_ajax_nopriv_webdev', 		array( __CLASS__, 'd_handle_ajax' ) );
	}



	static function d_handle_ajax(){
		if( ! wp_verify_nonce( $_REQUEST['nonce'], 'wir_a' ) ){
			wp_send_json_error();
		}

		$new_nonce = wp_create_nonce( 'wir_a' );

		$x = $_POST['serialized'];
		$group_id    = (int)sanitize_text_field( $x['gid'] );
		$old_post_id = (int)sanitize_text_field( $x['pid'] );

		$group = groups_get_group( $group_id );

		if( ! empty( get_post_meta( $old_post_id, 'wir_location_title', true ) ) ) 
			$data['wir_location_title'] = get_post_meta( $old_post_id, 'wir_location_title', true );

		if( ! empty( get_post_meta( $old_post_id, 'wir_location_content', true ) ) ) 
			$data['wir_location_content'] = get_post_meta( $old_post_id, 'wir_location_content', true );

		if( ! empty( get_post_meta( $old_post_id, 'wir_location_address', true ) ) ) 
			$data['wir_location_address'] = get_post_meta( $old_post_id, 'wir_location_address', true );

		if( ! empty( get_post_meta( $old_post_id, 'wir_location_website', true ) ) ) 
			$data['wir_location_website'] = get_post_meta( $old_post_id, 'wir_location_website', true );

		if( ! empty( get_post_meta( $old_post_id, 'wir_location_image', true ) ) ) 
			$data['wir_location_image'] = get_post_meta( $old_post_id, 'wir_location_image', true );

		$post_data = array(
			'post_title'	=> get_the_title( $old_post_id ),
			'post_author'	=> get_current_user_id(),
		);

		$post_id = WIR_Core::create_location_post( $post_data, $data, $group_id );

		$message = 'Location added successfully';
		$response = 'success';
			
		// If we hit a snag, update the user
		// doesn't work for our bulk case
		if ( is_wp_error( $post_id ) ) {
			$message = 'There was an error in the space-time continuum';
			$response = 'error';
		} 

		$message = 'Successfully added to list';
		$response = 'success';

		$link = home_url( '/lists/' . $group->slug );	

	//	return wp_redirect( esc_url( $link ) );
		bp_core_add_message( $message, $response );
		wp_send_json_success( array(
			'script_response' => 'AJAX Request Recieved d_handle_ajax',
			'nonce'           => $new_nonce,
			'redirect' 	=> $link,
			'post_id' 	=> $post_id,
		//	'stuff' 	=> $x,
			'group' 	=> $group,
			'gid' 		=> $group_id,
			'old_pid' 	=> $old_post_id,
		) );

	}

	public static function register_form_clone() {

		$cmb = new_cmb2_box( array(
			'id'           => 'wir_clone_locaton',
			'object_types' => array( 'location' ),
			'title' => 'lp',
			'hookup'       => false,
			'save_fields'  => false,
		) );

		$cmb->add_hidden_field( array(
			'field_args'  => array(
				'id'    => "yes_id",
				'type'  => 'hidden',
				'default' => self::$post_id,
			),
		) );
		$cmb->add_field( array(
			'name'             => 'Add this location to a list',
			'desc'             => 'Select an option',
			'id'               => 'wir_group_select',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => 'new',
			'attributes'  => array(
				'required'    => 'required',
			),
			'options'          => self::form_clone_group_select()
		) );
	}

	public static function form_clone_group_select() {
		$group_ids = groups_get_user_groups( get_current_user_id() );
		$options = array();
		foreach ( $group_ids['groups'] as $id ) {
			$group = groups_get_group( array( 'group_id' => $id ) );
			$options["$id"] = $group->name;
		}
	//	$options['new'] = 'Create new list';
		return $options;
	}

	public static function popup_clone_button(){
		echo self::get_popup_clone_button();
	}
	
	public static function vars() {

		check_ajax_referer( 'wir_a', 'nonce' );

	//	if( ! wp_verify_nonce( $_REQUEST['nonce'], 'wir_a' ) ){
	//		wp_send_json_error();
	//	}
		$new_nonce = wp_create_nonce( 'wir_a' );

		$x = $_POST['serialized'];
		$group_id 	= (int)sanitize_text_field( $x['gid'] );
		$lnonce 	=      sanitize_text_field( $x['l_nonce'] );
		$post_id  	= (int)sanitize_text_field( $x['pid'] );

		if( ! wp_verify_nonce( $lnonce, 'wir_link_nonce' ) ){
			wp_send_json_error();
		}
		self::$post_id = $post_id;
		// Get user ID if logged in
		$user_id = get_current_user_id();

	//	$check_group = groups_get_current_group();
	//	$check_group_id = $check_group->id;

	//	$groups =  groups_get_user_groups( get_current_user_id() );

		wp_send_json_success( array(
			'script_response'	=> 'AJAX Request Recieved - button pressed',
			'nonce' 			=> $new_nonce,
			'b_nonce' 			=> $lnonce,
			'post_id' 			=> self::$post_id,
			'gid' 				=> $group_id,
		) );
	}


	public static function clone_location() {

	}

	public static function get_popup_clone_button(){
		global $groups_template;

		$group =& $groups_template->group;

		// Don't show button if not logged in
	//	if ( ! is_user_logged_in() ) return false;

		// List creation was not completed or status is unknown
		if ( empty( $group->status ) ) return false;

		// Already a member
		if ( ! empty( $group->is_member ) ||  $group->status=='public') {
			
			global $post;

			$id = $post->ID . '-' . $group->id;

			if ( is_user_logged_in() ) {
				// Setup button attributes
				// Don't show button if previously banned
				if ( bp_group_is_user_banned( $group ) ) return false;

				$button = array(
					'id'                => 'clone_location_popup',
					'component'         => 'groups',
					'must_be_logged_in' => true,
					'block_self'        => false,
					'link_text'         => 'Copy Location',
					'parent_attr' 		=> array( 
						'class' => 'wir_login_dialog',
						'id'	=> $id
					),
					'button_attr' => array(
						'href' 	=> wp_nonce_url( bp_get_group_permalink( $group ) . 'clone-location', 'wir_link_nonce' ),
						'class' => 'clone-location wir_cloc',
						'title' => __( 'Copy Location', 'wir' ),
					),
				);
			} else {
				$button = WIR_Core::login_button( 'Copy Location', 'wir_cloc' );
			}

		} else {
			return false;
		}

		return bp_get_button( $button );
	}

	public static function popup_html() {
		if( ! is_user_logged_in() )
			return false;
		wp_enqueue_script( 'jquery-ui-dialog' ); 
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		ob_start(); ?>

			<div id="my-dialog" class="hidden" style="max-width:800px">
			  <h3>Copy Location</h3>
				<?php 
				$cmb = cmb2_get_metabox( 'wir_clone_locaton', 'cloc-id' );
				echo   cmb2_get_metabox_form( $cmb, 'cloc-id', array( 'save_button' => __( 'Clone', 'rh2' ) ) ); ?>
			</div>
			<script>
			jQuery(document).ready(function ($) {
			  // initalise the dialog
			  $('#my-dialog').dialog({
			    title: '',
			    dialogClass: 'wp-dialog',
			    autoOpen: false,
			    draggable: false,
			    width: 'auto',
			    modal: true,
			    resizable: false,
			    closeOnEscape: true,
			    position: {
			      my: "center",
			      at: "center",
			      of: window
			    },
			    open: function () {
				console.log('in');
			      // close dialog by clicking the overlay behind it
			      $('.ui-widget-overlay').bind('click', function(){
			        $('#my-dialog').dialog('close');
			      })
			    },
			    create: function () {
			      // style fix for WordPress admin
			      $('.ui-dialog-titlebar-close').addClass('ui-button');
			    },
			  });
			  // bind a button or a link to open the dialog
			 $('a.wir_cloc').click(function(e) {
			    e.preventDefault();
			    $('#my-dialog').dialog('open');
			  });
			});
			</script>
			<?php 
			$html = ob_get_contents();

		ob_end_clean();
		echo $html;
	}

}
