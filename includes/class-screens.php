<?php
/**
 * WIR Screens.
 *
 * @since   0.0.1
 * @package WIR
 */

/**
 * WIR Screens.
 *
 * @since 0.0.1
 */
class WIR_Screens {
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
		
		add_action( 'groups_setup_nav',				array( $this, 'setup_nav' ) );
		add_filter( 'bp_groups_default_extension', 	array( $this, 'groups_default_extension' ), 10, 1 );
		add_action( 'bp_init', 						array( $this, 'create_new_list_link' ) );

		//add_action( 'bp_ready', array( $this, 'screen_group_blog_single_post' ), 5 );
		
	}

	public function create_new_list_link() {
		// this is kinda hacky and should be replaced sometime
		bp_core_new_subnav_item( array(
			'name' 		      => __( 'Create New List', 'wir' ),
			'slug' 		      => '/lists/create/step/group-details/',
			'parent_slug'     => bp_get_groups_slug(),
			'parent_url' 	  => site_url(),
			'screen_function' => 'wir_idk',
			'position' 	      => 40,
			'user_has_access' => true
		) );
	}

	public function groups_default_extension( $home ) {
		if ( groups_is_user_member( get_current_user_id(), bp_get_current_group_id() ) )
			return WIR_SLUG;
		return $home;

	}

	//setup nav
	public function setup_nav ( $current_user_access ) {
		
		$bp = buddypress();
		
		if ( ! bp_is_group() ) {
			return;
		}

		$group_id = bp_get_current_group_id();

		$current_group = groups_get_current_group();

		$group_link = bp_get_group_permalink( $current_group );

		bp_core_new_subnav_item( array(
			'name'				=> __( 'Locations', 'wir' ),
			'slug'				=> WIR_SLUG,
			'parent_url'		=> $group_link,
			'parent_slug'		=> $current_group->slug,
			'screen_function'	=> array( $this, 'display' ),
			'position'			=> 10,
			'user_has_access'	=> $current_user_access,
			'item_css_id'		=> 'locations'
		) );

		if ( groups_is_user_member( get_current_user_id(), $group_id ) )
			bp_core_remove_subnav_item( $current_group->slug, 'home' );

	}


	
	public function display() {
		//switch based on current view
		$current_action = bp_action_variable( 0 );
		
		if ( $current_action == 'create' ) {
			$this->view_create();
		} elseif( $current_action == 'edit' ) {
			$this->view_edit();
		} elseif( $current_action == 'bulk' ) {
			$this->view_bulk();
		}elseif ( wir_is_single_post() ) {
			$this->view_single();
		} else {
			$this->view_blog();
		}
		//just load the plugins template, above functions will attach the content generators
		bp_core_load_template( 'groups/single/plugins' );
	}
	
	
	public function view_blog () {
		add_action( 'bp_template_content', array( $this, 'get_blog_contents' ) );
	}
	
	public function view_create () {
		add_action( 'bp_template_content', array( $this, 'get_new_post_contents' ) );
	}

	public function view_edit () {
		add_action( 'bp_template_content', array( $this, 'get_edit_post_contents' ) );
	}

	public function view_bulk () {
		add_action( 'bp_template_content', array( $this, 'get_bulk_post_contents' ) );
	}

	//for single post screen
	public function view_single () {
		
		$bp = buddypress();
		
		if ( function_exists( 'bp_is_group' ) && !bp_is_group() ) {
			return;
		}

		//do not catch the request for creating new post
		if ( bp_is_action_variable( 'create', 0 ) ) {
			return;
		}

		$current_group = groups_get_current_group();

		//if the group is private/hidden and user is not member, return
		if ( ( $current_group->status == 'private' || $current_group->status == 'hidden' ) && (! is_user_logged_in() || ! groups_is_user_member( bp_loggedin_user_id(), $current_group->id ) ) ) {
			return; //avoid prioivacy troubles
		}

		if ( wir_is_component() && ! empty( $bp->action_variables[0] ) ) {
			//should we check for the existence of the post?
			
			add_action( 'bp_template_content', array( $this, 'get_single_post_contents' ) );
		}
	}

	public function get_blog_contents() {
		$this->display_options_nav();
		wir_load_template( 'posts.php' );
	}
	public function get_edit_post_contents() {
		$this->display_options_nav();
		wir_load_template( 'edit.php' );
	}
	
	public function get_new_post_contents() {
		$this->display_options_nav();
		wir_load_template( 'add-location.php' );
	}
	public function get_single_post_contents() {
		$this->display_options_nav();
		wir_load_template( 'single-post.php' );	
	}

	public function get_bulk_post_contents() {
		$this->display_options_nav();
		wir_load_template( 'bulk.php' );	
	}
	
	public function display_options_nav() {?>
		<div id="subnav" class="item-list-tabs no-ajax">
			<ul>
                <?php wir_get_options_menu();?>
			</ul>
		</div>
	<?php }
}
