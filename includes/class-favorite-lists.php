<?php
/**
 * WIR Favorites.
 *
 * @since   0.0.1
 * @package WIR
 */

/**
 * WIR Favorites.
 *
 * @since 0.0.1
 */
class WIR_Favorite_Lists {
	public static $meta_key = '_wir_favorite_groups';

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    WIR
	 * @since  0.0.1
	 */
	protected static $single_instance = null;

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
	public static function hooks(){
		add_action( 'bp_group_header_actions', 			array( __CLASS__, 'favorite_group_button' ), 6 );
		add_action( 'bp_directory_groups_actions', 		array( __CLASS__, 'favorite_group_button' ), 11 );
		add_action( 'bp_before_member_groups_content',  array( __CLASS__, 'favorites_before_member_groups_content'));

		add_shortcode( 'wir_favorite_groups', 			array( __CLASS__, 'output' ) );

		add_action( 'wp_ajax_wir_flist_hook',	 		array( __CLASS__, 'handle_ajax' ) );
		add_action( 'wp_ajax_nopriv_wir_flist_hook',	array( __CLASS__, 'handle_ajax' ) );
	
		add_filter( 'bp_get_activity_show_filters', 	array( __CLASS__, 'favorite_group_filter' ), 12, 1 );
		add_filter( 'bp_ajax_querystring', 				array( __CLASS__, 'favorite_groups_activity_filter' ), 12, 2 );		
	}
	
	public static function favorites_before_member_groups_content() {
		echo self::output();
	}

	public static function handle_ajax(){
		
		check_ajax_referer( 'wir_a', 'nonce' );

	//	if( ! wp_verify_nonce( $_REQUEST['nonce'], 'wir_a' ) ){
	//		wp_send_json_error();
	//	}
		$new_nonce = wp_create_nonce( 'wir_a' );

		$x = $_POST['serialized'];
		$group_id 	= (int)sanitize_text_field( $x['gid'] );
		$type 		=      sanitize_text_field( $x['type'] );
		$lnonce 	=      sanitize_text_field( $x['l_nonce'] );

		if( ! wp_verify_nonce( $lnonce, 'wir_link_nonce' ) ){
			wp_send_json_error();
		}

		if ( $type == 'favorite' ){
			self::groups_favorite_group( $group_id );
		} else {
			self::groups_unfavorite_group( $group_id );
		}

		wp_send_json_success( array(
			'script_response'	=> 'AJAX Request Recieved' . $type,
			'nonce' 			=> $new_nonce,
			'b_nonce' 			=> $lnonce,
			'type' 				=> $type,
		) );
	}

	public static function groups_favorite_group( $group_id, $user_id = 0 ) {
		if ( ! $user_id )
			$user_id = bp_loggedin_user_id();
		$user_groups = self::get_user_favorite_groups( $user_id );
		$user_groups[] = $group_id;
		update_user_meta( $user_id, self::$meta_key, $user_groups );
	}
	
	public static function groups_unfavorite_group( $group_id, $user_id = 0 ) {
		if ( empty( $user_id ) )
			$user_id = bp_loggedin_user_id();

		$user_groups = self::get_user_favorite_groups( $user_id );

		$user_groups = array_diff( $user_groups, array( $group_id ) );
		update_user_meta( $user_id, self::$meta_key, $user_groups );
	}

	public static function favorite_group_button( $group = false ){
		echo self::get_favorite_group_button($group);
	}
	
	public static function get_favorite_group_button( $group = false ){
		global $groups_template;

		// Set group to current loop group if none passed
		if ( empty( $group ) )
			$group =& $groups_template->group;

		// List creation was not completed or status is unknown
		if ( empty( $group->status ) ) return false;

		// Already a member
		if ( ! empty( $group->is_member ) || 'public' == $group->status ) {
			
			$user_favorite_groups = get_user_meta( bp_loggedin_user_id(), self::$meta_key, true );

			if ( is_user_logged_in() ) {
				// Setup button attributes
				// Don't show button if previously banned
				if ( bp_group_is_user_banned( $group ) ) return false;

				if ( ! empty( $user_favorite_groups ) && in_array( $group->id, $user_favorite_groups ) ):
					// Setup button attributes
					$button = array(
						'id'                => 'favorite_group',
						'component'         => 'groups',
						'must_be_logged_in' => true,
						'block_self'        => false,
						'parent_attr' 		=> array( 
							'class' => '',
							'id'	=> 'groupbutton-' . $group->id
						),
						'button_attr' => array(
							'href' 	=> wp_nonce_url( bp_get_group_permalink( $group ) . 'unfavorite-list', 'wir_link_nonce' ),
							'class' => 'unfavorite-list wir_flist dashicons dashicons-star-filled',
							'title' => __( 'Unfavorite List', 'wir' ),
						),
					);
				else:
					// Setup button attributes
					$button = array(
						'id'                => 'favorite_group',
						'component'         => 'groups',
						'must_be_logged_in' => true,
						'block_self'        => false,
						'parent_attr' 		=> array( 
							'class' => '',
							'id'	=> 'groupbutton-' . $group->id
						),
						'button_attr' => array(
							'href' 	=> wp_nonce_url( bp_get_group_permalink( $group ) . 'favorite-list', 'wir_link_nonce' ),
							'class' => 'favorite-list wir_flist dashicons dashicons-star-empty',
							'title' => __( 'Favorite List', 'wir' ),
						),
					);

				endif;
			} else {

				$button = WIR_Core::login_button( '', 'dashicons dashicons-star-empty' );
			}
			

		} else {
			return false;
		}

		/**
		 * Filters the HTML button for favoriting a group.
		 *
		 * @since SP Favorite Groups (1.0.0)
		 *
		 * @param string $button HTML button for favoriting a group.
		 */
		return bp_get_button( $button );
	}
	
	public static function set_favorite_group() {

		$type = sanitize_text_field( $_POST['type'] );
		$group_id = (int)$_POST['gid'];

		if ( $type=='favorite' ){
			self::groups_favorite_group( $group_id );
		} else {
			self::groups_unfavorite_group( $group_id );
		}

		exit;
	}
	
	public static function get_user_favorite_groups( $user_id ) {
		$groups = get_user_meta( $user_id, self::$meta_key, true );
		$groups = ( ! is_array( $groups ) ? array() : $groups );
		return $groups;
	}
	
	public static function output( $atts = array() ){

		if ( empty( $atts['user_id'] ) ){
			$user_id = bp_loggedin_user_id();
		} else {
			$user_id = $atts['user_id'];
		}

		$user_groups = self::get_user_favorite_groups( bp_displayed_user_id() );

		if( empty( $user_groups ) ) {
			return '<div class="sp-fav-groups-none">'.__('No favorite lists found', 'wir').'</div>';
		}

		$include = implode( ',', $user_groups );
		ob_start();
	//	print_r($include);
		$args = '';
		$args = array(
			'include' => $include
		);
		if ( bp_has_groups( bp_ajax_querystring( 'groups' ).'&include='.$include.'&user_id=false' ) ) :	?>

			<div id="buddypress">
			<h3>Favorite Lists</h3>
				<ul id="groups-list favoirite-list" class="item-list">

					<?php while ( bp_groups() ) : bp_the_group(); ?>
						<?php wir_load_template( 'favorites.php' ); ?>
					<?php endwhile; ?>

				</ul>
			</div>

		<?php else: ?>
			<div class="sp-fav-groups-none"><?php _e( 'No favorite lists found', 'wir' ); ?></div>
		<?php endif;

		$output_string = ob_get_contents();
		ob_end_clean();
		return apply_filters( 'favorite_groups_html', $output_string, $user_groups );
	}
	
	public static function favorite_group_filter( $output ){
		global $bp;
		if( $bp->current_component == 'activity' ){
			$output .= '<option value="favorite_group">' . __( 'Favorite Groups', 'wir' ) . '</option>' . "\n";
		}
		return $output;
	}
	public static function favorite_groups_activity_filter( $qs = false, $object = false ) {

		if ( $object != 'activity' )
			return $qs;
	
		$args = wp_parse_args( $qs );

		if( empty( $args['type'] ) || $args['type'] != 'favorite_group' ) 
			return $qs;
		
		$user_id = bp_loggedin_user_id();
		$user_groups = self::get_user_favorite_groups( $user_id );
		if(empty($user_groups))
			return $qs;
		$include = implode( ',', $user_groups );
		if ( ! empty( $user_groups ) ):
			$args['primary_id'] = $user_groups;
			$args['object'] = 'groups';
			unset( $args['type'] );
			unset( $args['action'] );
		endif;
		
		$qs = build_query( $args );
		return $qs;
	}

	/**
	 * Hook into wp_ajax_ to save post ids, then display those posts using get_posts() function
	 *
	 * @access public
	 * @return mixed
	 */
	public function read_me_later() {
	
		check_ajax_referer( 'rml-nonce', 'security' );
		$rml_post_id = $_POST['post_id']; 
		$echo = array();
		
		if( get_user_meta( wp_get_current_user()->ID, 'rml_post_ids', true ) !== null ) {
			$value = get_user_meta( wp_get_current_user()->ID, 'rml_post_ids', true );
		}
		
		if( $value ) {
			$echo = $value;
			array_push( $echo, $rml_post_id );
		}
		else {
			$echo = array( $rml_post_id );
		}
		
		update_user_meta( wp_get_current_user()->ID, 'rml_post_ids', $echo );
		$ids = get_user_meta( wp_get_current_user()->ID, 'rml_post_ids', true );
		
		function limit_words($string, $word_limit) {
			$words = explode(' ', $string);
			return implode(' ', array_slice($words, 0, $word_limit));
		}
		
		// Query read me later posts
		$args = array( 
			'post_type' => 'post',
			'orderby' => 'DESC', 
			'posts_per_page' => -1, 
			'numberposts' => -1,
			'post__in' => $ids
		);
		
		$rmlposts = get_posts( $args );
		if( $ids ) :
			global $post;
			foreach ( $rmlposts as $post ) :
				setup_postdata( $post );
				$img = wp_get_attachment_image_src( get_post_thumbnail_id() ); 
				?>			
				<div class="rml_posts">					
					<div class="rml_post_content">
						<h5><a href="<?php echo get_the_permalink(); ?>"><?php the_title(); ?></a></h5>
						<p><?php echo limit_words(get_the_excerpt(), '20'); ?></p>
					</div>
					<img src="<?php echo $img[0]; ?>" alt="<?php echo get_the_title(); ?>" class="rml_img">					
				</div>
			<?php 
			endforeach; 
			wp_reset_postdata(); 
		endif;		
		// Always die in functions echoing Ajax content
		die();
		
	} 	
}

