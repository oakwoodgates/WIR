<?php
/**
 * WIR Core.
 *
 * @since   0.0.1
 * @package WIR
 */

/**
 * WIR Core.
 *
 * @since 0.0.1
 */
class WIR_Core {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.0.1
	 *
	 * @var   WIR
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.0.1
	 *
	 * @param  WIR $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.1
	 */
	public function hooks() {
		add_filter( 'bp_groups_default_extension', 	array( $this, 'groups_default_extension' ), 		10, 1 );
		add_action( 'bp_init', 						array( $this, 'default_bp_tab' ), 						4 );
		add_action( 'groups_group_create_complete', array( $this, 'create_list_category' ), 			10, 1 );
		add_action( 'cmb2_init',					array( $this, 'register_add_location_form' ) );
		add_action( 'cmb2_init',					array( $this, 'register_edit_location_form' ) );
		add_action( 'cmb2_init',					array( $this, 'register_bulk_add_form' ) );
		add_action( 'cmb2_after_init', 				array( $this, 'location_form_handler' ) );
		add_action( 'bp_enqueue_scripts', 			array( $this, 'enqueue_script' ) );
		add_action( 'admin_init', 					array( $this, 'force_caps') );
		add_filter( 'ajax_query_attachments_args', 	array( $this, 'show_only_current_user_attachments' ), 10, 1 );
		add_action( 'bp_actions', 					array( $this, 'delete' ) );
		add_action( 'wp_footer', 					array( $this, 'get_the_login' ) );
		add_action( 'wp_logout', 					array( $this, 'logout_redirect' ), 99 );
	}

	/**
	 * Changes default BP tab to our custom component
	 *
	 * @since 	0.0.1
	 */
	public function default_bp_tab() {
	//	if ( bp_is_my_profile() ) {
	//		define('BP_DEFAULT_COMPONENT', 'wir' );
	//	} else {
	//		define('BP_DEFAULT_COMPONENT', 'profile' );
	//	}
		define('BP_DEFAULT_COMPONENT', 'groups' );

	}

	public function groups_default_extension( $def ) {
		$bp = buddypress();
		$group = $bp->groups->current_group;
		if ( 'public' == $group->status )
			return 'locations';
		if ( is_user_logged_in() && groups_is_user_member( get_current_user_id(), $group->id ) )
			return 'locations';

		return $def;
	}

	public function show_only_current_user_attachments( $query = array() ) {
		// show all files for admins
		if ( current_user_can( 'install_plugins' ) )
			return $query;

		$user_id = get_current_user_id();
		// only show author their files
		if ( $user_id ) {
			$query['author'] = $user_id;
		}
		return $query;
	}

	public function force_caps() {
		// gets the author role
		$role = get_role( 'subscriber' );
		$role->add_cap( 'upload_files' );
	}

	/**
	 * Enqueue comment js on single post screen
	 */
	public function enqueue_script () {

		if ( wir_is_single_post() ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	/**
	 * Creates a category for our List Group
	 * @param  int $group_id ID of newly created group
	 * @return void           insterts term in db
	 */
	public function create_list_category( $group_id ) {

		$term 		= 'list_' . $group_id;
		$taxonomy 	= 'list';

		wp_insert_term( $term, $taxonomy, $args = array() );
	}

	/**
	 * Register the form and fields for our front-end submission form
	 *
	 * @since  0.0.3
	 */
	public function register_add_location_form() {

		$cmb = new_cmb2_box( array(
			'id'           => 'wir_add_location',
			'object_types' => array( 'location' ),
			'hookup'       => false,
			'save_fields'  => false,
		) );

		WIR_Location::fields( $cmb );
	}

	/**
	 * Register the form and fields for our front-end submission form
	 *
	 * @since  0.0.3
	 */
	public function register_edit_location_form() {

		$cmb = new_cmb2_box( array(
			'id'           => 'wir_edit_location',
			'object_types' => array( 'location' ),
		) );

		WIR_Location::fields( $cmb );

	}

	/**
	 * Register bulk/csv upload form
	 * @since  0.0.4
	 */
	public function register_bulk_add_form() {

		$cmb = new_cmb2_box( array(
			'id'           => 'wir_bulk_add_location',
			'object_types' => array( 'location' ),
			'hookup'       => false,
			'save_fields'  => false,
		) );

		$cmb->add_field( array(
			'name'    => 'CSV Import <br /><a href="https://app.wheninroamtravelapp.com/uploading-a-csv/">CSV Upload How To</a>',
			'desc'    => 'Upload a CSV',
			'id'      => 'wir_csv',
			'type'    => 'file',
			'options' => array(
				'url' => false, // Hide the text input for the url
			),
			'text'    => array(
				'add_upload_file_text' => 'Upload CSV' // Change upload button text. Default: "Add or Upload File"
			),
			'query_args' => array(
				'type' => array( 'text/csv' ), // Make library only display CSVs.
			),
			'attributes'  => array(
				'required'    => 'required',
			),
		) );
	}

	/**
	 * Gets the cmb instance
	 *
	 * @since  0.0.3
	 * @return CMB2 object
	 */
	static function cmb2_get( $metabox_id = '', $object_id = '' ) {
		// Use ID of metabox in register_add_location_form
		if ( ! $metabox_id )
			$metabox_id = 'wir_add_location';

		// Post/object ID is not applicable if we're using this form for submission
		if ( ! $object_id )
			$object_id  = 'fake-id';

		// Get CMB2 metabox object
		return cmb2_get_metabox( $metabox_id, $object_id );
	}

	/**
	 * Add location form
	 * @since  0.0.3
	 * @return string       Form html
	 */
	public function form_add_location( $atts = array() ) {

		// Get CMB2 metabox object
		$cmb = self::cmb2_get();

		// Current user
		$user_id = get_current_user_id();

		// Initiate our output variable
		$output = '';

		// Get any submission errors
		if ( ( $error = $cmb->prop( 'submission_error' ) ) && is_wp_error( $error ) ) {
			// If there was an error with the submission, add it to our ouput.
			$output .= '<h3>' . sprintf( __( 'There was an error in the submission: %s', 'wir' ), '<strong>'. $error->get_error_message() .'</strong>' ) . '</h3>';
		}

		// If the post was submitted successfully, notify the user.
		if ( isset( $_GET['post_submitted'] ) && ( $post = get_post( absint( $_GET['post_submitted'] ) ) ) ) {

			// Get submitter's name
			$name = get_post_meta( $post->ID, 'submitted_author_name', 1 );
			$name = $name ? $name : '';

			// Add notice of submission to our output
			$output .= '<h3>' . sprintf( __( 'Thank you %s, your new post has been submitted.', 'wir' ), esc_html( $name ) ) . '</h3>';
		} else {

			// Get our form
			$output .= cmb2_get_metabox_form( $cmb, 'fake-id', array( 'save_button' => __( 'Submit Location', 'wir' ) ) );
		}

		return $output;
	}

	/**
	 * Edit Location form
	 * @since  0.0.3
	 * @return string       Form html
	 */
	public function form_edit_location( $atts = array() ) {

		$id = bp_action_variable(1);

		// Get CMB2 metabox object
		$cmb = self::cmb2_get( 'wir_edit_location', $id );

		// Initiate our output variable
		$output = '';

		// Get any submission errors
		if ( ( $error = $cmb->prop( 'submission_error' ) ) && is_wp_error( $error ) ) {
			// If there was an error with the submission, add it to our ouput.
			$output .= '<h3>' . sprintf( __( 'There was an error in the submission: %s', 'wir' ), '<strong>'. $error->get_error_message() .'</strong>' ) . '</h3>';
		}

		if ( ! ( isset( $_GET['post_submitted'] ) && ( $post = get_post( absint( $_GET['post_submitted'] ) ) ) ) ){
			// Get our form
			$output .= cmb2_get_metabox_form( $cmb, $id, array( 'save_button' => __( 'Update Locations', 'wir' ) ) );
		}

		return $output;
	}

	/**
	 * Bulk/CSV upload form
	 * @since  0.0.3
	 * @return string       Form html
	 */
	public function form_add_bulk_locations( $atts = array() ) {
		$cmb = self::cmb2_get( 'wir_bulk_add_location' );
		$user_id = get_current_user_id();

		// Initiate our output variable
		$output = '';
		// Get any submission errors
		if ( ( $error = $cmb->prop( 'submission_error' ) ) && is_wp_error( $error ) ) {
			// If there was an error with the submission, add it to our ouput.
			$output .= '<h3>' . sprintf( __( 'There was an error in the submission: %s', 'wir' ), '<strong>'. $error->get_error_message() .'</strong>' ) . '</h3>';
		}
		// Get our form
		$output .= cmb2_get_metabox_form( $cmb, 'fake-id', array( 'save_button' => __( 'Upload Locations', 'wir' ) ) );

		return $output;
	}

	/**
	 * Handles form submission on save. Redirects if save is successful, otherwise sets an error message as a cmb property
	 *
	 * @since  0.0.3
	 * @return void
	 */
	public function location_form_handler() {

		// If no form submission, bail
		if ( empty( $_POST ) || ! isset( $_POST['submit-cmb'], $_POST['object_id'] ) ) {
			return false;
		}

		$post_id = bp_action_variable(1);

		global $bp;

		// Get CMB2 metabox object
		if ( $post_id ) {
			$cmb = self::cmb2_get( 'wir_edit_location', $post_id );
		} elseif ( 'create' == bp_action_variable(0) ) {
			$cmb = self::cmb2_get( 'wir_add_location' );
		} elseif ( 'bulk' == bp_action_variable(0) ) {
			$cmb = self::cmb2_get( 'wir_bulk_add_location' );
		} else {
		//	return self::force_template_notice( 'Cannot load form' );
			return '';
		}

		// Check security nonce
		if ( ! isset( $_POST[ $cmb->nonce() ] ) || ! wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() ) ) {
			return self::force_template_notice( 'Security check failed' );
		}

		$post_data = array();

		// Fetch sanitized values
		$sanitized = $cmb->get_sanitized_values( $_POST );

		// Get user ID if logged in
		$user_id = get_current_user_id();

		$group = groups_get_current_group();
		$group_id = $group->id;

		$message = $response = 'success';

		if ( $post_id ) {

			if ( wir_user_can_edit ( $post_id, $user_id, $group->id ) ) {
				$cmb->process_fields();
				$message = 'Location successfully edited';
				$response = 'success';
			} else {
				$message = 'You do not have permission to edit this Location';
				$response = 'error';
			}

		} elseif ( 'create' == bp_action_variable(0) ) {

			if ( wir_current_user_can_post() ) {
				// Set our post data arguments
				$post_data = array(
					'post_title'	=> $sanitized['wir_location_title'],// temporary, will be replaced with post ID once we have it
					'post_author'	=> $user_id,
				);

				$post_id = self::create_location_post( $post_data, $sanitized, $group_id );

				$message = 'Location added successfully';
				$response = 'success';
			} else {
				$message = 'You do not have permission to add to this list';
				$response = 'error';
			}

		} elseif ( 'bulk' == bp_action_variable(0) ) {

			if( empty( $sanitized['wir_csv'] ) ) {
					$message = 'Import failed';
					$response = 'error';
			} else {
				$csv_to_array = self::csv_to_array( $sanitized['wir_csv'] );

				$x = 0;
				foreach ( $csv_to_array as $maybe_post ) {

					// reset vars
					$sanitized = $post_data = $post_id = '';
					$sanitized = array();

					// if we don't have a post title gtfo;
					// sanitize all the things
					if( ! empty( $maybe_post['Title'] ) ) {

						$sanitized['wir_location_title'] = sanitize_text_field( $maybe_post['Title'] );

						if( ! empty( $maybe_post['Description'] ) )
							$sanitized['wir_location_content'] = sanitize_text_field( $maybe_post['Description'] );

						if( ! empty( $maybe_post['Neighborhood'] ) )
							$sanitized['wir_location_address'] = sanitize_text_field( $maybe_post['Neighborhood'] );

						if( ! empty( $maybe_post['Link'] ) )
							$sanitized['wir_location_website'] = sanitize_text_field( $maybe_post['Link'] );

						if( ! empty( $maybe_post['Image'] ) )
							$sanitized['wir_location_image'] = sanitize_text_field( $maybe_post['Image'] );

						// default post data
						$post_data = array(
							'post_title'	=> $sanitized['wir_location_title'],// temporary, will be replaced with post ID once we have it
							'post_author'	=> $user_id,
						);

						// create the post
						$post_id = self::create_location_post( $post_data, $sanitized, $group_id );
						// update count for response
						if( $post_id )
							$x = $x + 1;
					}
				}

				$s = ( $x > 1 ) ? 's' : '';
				if ( $x ) {
					$message = 'Import of ' . $x . ' location' . $s . ' successful';
					$response = 'success';
				} else {
					$message = 'Import failed';
					$response = 'error';
				}
			}

		} else {
			$message = 'That\'s a no no';
			$response = 'error';
		}

		// If we hit a snag, update the user
		// doesn't work for our bulk case
		if ( is_wp_error( $post_id ) ) {
			$message = 'There was an error in the space-time continuum';
			$response = 'error';
		}

		$link = home_url( '/lists/' . $group->slug );

		wp_redirect( esc_url( $link ) );
		bp_core_add_message( $message, $response );
		exit;
	}

	/**
	 * Force a BP template notice right now
	 *
	 * @param  string $message Message to output
	 * @return string          html
	 */
	public static function force_template_notice( $message ) {
		global $bp;
		$bp->template_message      = $message;
		$bp->template_message_type = 'error';
		do_action( 'template_notices' );
	}

	/**
	 * Convert a comma separated file into an associated array.
	 * The first row should contain the array keys.
	 *
	 * @param string $filename Path to the CSV file
	 * @param string $delimiter The separator used in the file
	 * @param boolean $asHash Use header row as keys in the returned array
	 * @return array
	 * @link http://gist.github.com/385876
	 * @author Jay Williams <http://myd3.com/>
	 * @copyright Copyright (c) 2010, Jay Williams
	 * @license http://www.opensource.org/licenses/mit-license.php MIT License
	 */
	public static function csv_to_array( $filename = '', $delimiter = ',', $asHash = true ) {
		if ( ! ( is_readable( $filename ) || ( ( $status = get_headers( $filename ) ) && strpos( $status[0], '200' ) ) ) ) {
		    return FALSE;
		}

		$header = NULL;
		$data = array();
		if ( ( $handle = fopen( $filename, 'r') ) !== FALSE) {
			if ( $asHash ) {
				while ( $row = fgetcsv( $handle, 0, $delimiter ) ) {
					if ( ! $header ) {
						$header = $row;
					} else {
						$data[] = array_combine( $header, $row );
					}
				}
			} else {
				while ( $row = fgetcsv( $handle, 0, $delimiter ) ) {
					$data[] = $row;
				}
			}

			fclose($handle);
		}

		return $data;
	}

	/**
	 * Magic
	 * @param  array $post_data post data
	 * @param  array $sanitized also post data
	 * @return int|bool            post id on success, error on fail
	 */
	public static function create_location_post( $post_data, $sanitized, $group_id ) {
		// create new location post with our temporary post data
		$post_data['post_status'] = 'publish';
		$post_data['post_type'] = 'location';

		$post_id = wp_insert_post( $post_data, true );

		$img_url = '';
		if ( ! empty( $sanitized['wir_location_image'] ) ) {
			$img_url = $sanitized['wir_location_image'];
			unset( $sanitized['wir_location_image'] );
		}

		foreach( $sanitized as $key => $val ) {
			if( ! empty( $val ) )
				update_post_meta( $post_id, $key, $val );
		}

	//	$group = groups_get_current_group();
	//	$group_id = $group->id;
		$cat_name = 'list_' . $group_id;
		$cat = get_cat_ID( $cat_name );
		$term = get_term_by( 'slug', $cat_name, 'list' );
		if ( $term )
			wp_set_object_terms( $post_id, $term->term_id, 'list' );

		$img = '';
		// we have an image
		if ( $img_url ) {

			$old_img_url = get_post_meta( $post_id, 'wir_location_image', true );
			// there is a previous image
			if ( ! empty( $old_img_url ) ) {

				// previous image and new image are the different; upload
				if ( $old_img_url != $img_url ) {
					$img = self::do_media( $img_url, $post_id );
				}

			} else {
				$img = self::do_media( $img_url, $post_id );
			}
		}

		return $post_id;
	}

	/**
	 * Get the media from a link, upload and attach it
	 * @param  string $url     url of image file
	 * @param  int $post_id id of post to attach image to
	 * @return array          source and id of image
	 */
	public static function do_media( $url, $post_id ) {

		// Need to require these files
		if ( !function_exists('media_handle_upload') ) {
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			require_once(ABSPATH . "wp-admin" . '/includes/file.php');
			require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		}

		$tmp = download_url( $url );
		if( is_wp_error( $tmp ) ){
			// download failed, handle error
			self::force_template_notice('Download failed');
		}

		$file_array = array();

		// Set variables for storage
		// fix file filename for query strings
		preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches);
		if ( empty( $matches[0] ) ) {
			update_post_meta( $post_id, 'wir_location_image', $url );
			return;
		}
		$file_array['name'] = basename($matches[0]);
		$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink($file_array['tmp_name']);
			$file_array['tmp_name'] = '';
			self::force_template_notice('Error storing image');
		}

		// do the validation and storage stuff
		$id = media_handle_sideload( $file_array, $post_id );

		// If error storing permanently, unlink
		if ( is_wp_error($id) ) {
			@unlink($file_array['tmp_name']);
			self::force_template_notice('Error storing image');
			return $id;
		}

		$src = wp_get_attachment_url( $id );

		update_post_meta( $post_id, 'wir_location_image', $src );
		update_post_meta( $post_id, 'wir_location_image_id', $id );

		return array(
			'src' => $src,
			'id' => $id,
		);
	}

    /**
     * delete Post screen
     */
    public function delete() {

		if ( ! wir_is_component() || ! bp_is_action_variable( 'delete' )  ) {
			return;
		}

        $post_id = bp_action_variable( 1 );

        if ( ! $post_id ) {
            return;
		}

	    $group_id = bp_get_current_group_id();

		if ( wir_user_can_delete( $post_id,  get_current_user_id(), $group_id ) ) {
			wp_delete_post( $post_id, true );
			bp_core_add_message ( __( 'Post deleted successfully' ), 'wir' );
			//redirect
			wp_redirect( wir_get_home_url() );//hardcoding bad
			exit( 0 );
		} else {
			bp_core_add_message ( __( 'You should not perform unauthorized actions', 'wir' ),'error');
		}

    }

    public function get_the_login() {
    	if ( ! is_user_logged_in() )
    		echo $this->the_login();
    }

    public function the_login() {
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
    	ob_start();
    	?>

		<div id="wir_login" class="hidden" style="max-width:800px">
		   <div style="float:left;width:50%">
		   		<?php echo do_shortcode( '[theme-my-login default_action="login"]' ); ?>
		   </div>
   		   <div style="float:right;width:50%">
				<?php echo do_shortcode( '[theme-my-login default_action="register"]' ); ?>
			</div>
		</div>
		<script>
		jQuery(document).ready(function ($) {
		  // initalise the dialog
		  $('#wir_login').dialog({
		    title: 'Login/Register',
		    dialogClass: 'wp-dialog',
		    autoOpen: false,
		    draggable: false,
		    width: 800,
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
		        $('#wir_login').dialog('close');
		      })
		    },
		    create: function () {
		      // style fix for WordPress admin
		      $('.ui-dialog-titlebar-close').addClass('ui-button');
		    },
		  });
		  // bind a button or a link to open the dialog
		 $('a.wir_lb').click(function(e) {
		    e.preventDefault();
		    $('#wir_login').dialog('open');
		  });
		});
		</script>
    	<?php
    	$r = ob_get_contents();
    	ob_end_clean();
    	return $r;
    }

    public static function login_button( $title = '', $class = '' ) {
		$button = array(
			'id'                => 'clone_location_popup',
			'component'         => 'groups',
			'must_be_logged_in' => false,
			'block_self'        => false,
			'link_text'         => $title,
			'parent_attr' 		=> array(
				'class' => '',
			),
			'button_attr' => array(
				'class' => 'wir_lb ' . $class,
				'title' => $title,
			),
		);
	//	return bp_get_button( $button );
		return $button;
    }

	public function logout_redirect( $allowed ) {
	//	$allowed[] = 'wheninroamtravelapp.com';
	//	return $allowed;
		wp_redirect( 'http://wheninroamtravelapp.com' );
		exit();
	}
}
