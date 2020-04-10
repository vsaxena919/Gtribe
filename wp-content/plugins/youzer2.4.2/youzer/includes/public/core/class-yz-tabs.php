<?php

class Youzer_Tabs {

    public function __construct() {
		$this->add_profile_tabs();
    }

    /**
     * Overview Screen.
     */
    function overview_screen() {

	    require_once YZ_PUBLIC_CORE . 'tabs/yz-tab-overview.php';

		$overview = new YZ_Overview_Tab();

        // Call Posts Tab Content.
        add_action( 'bp_template_content', array( $overview, 'tab' ) );

        // Load Tab Template
        bp_core_load_template( 'buddypress/members/single/plugins' );

    }

    /**
     * Info Screen.
     */
    function info_screen() {

	    require_once YZ_PUBLIC_CORE . 'tabs/yz-tab-info.php';

		$info = new YZ_Info_Tab();

        // Call Tab Content.
        add_action( 'bp_template_content', array( $info, 'tab' ) );

        // Load Tab Template
        bp_core_load_template( 'buddypress/members/single/plugins' );

    }

    /**
     * Info Screen.
     */
    function media_screen() {

	    require YZ_PUBLIC_CORE . 'tabs/yz-tab-media.php';
		
		$media = new YZ_Media_Tab();
		// $media->add_sub_tabs();
		// add_action( 'init', array( &$media, 'add_sub_tabs' ) );
		// add_action( 'bp_actions', array( &$media, 'add_sub_tabs' ) );
		// add_action( 'bp_setup_nav', array( &$media, 'add_sub_tabs' ) );

        // Call Tab Content.
        add_action( 'bp_template_content', array( $media, 'tab' ) );

        // Load Tab Template
        bp_core_load_template( 'buddypress/members/single/plugins' );

    }
    
    /**
     * Posts Screen.
     */
    function posts_screen() {

    	// Styling.
		yz_styling()->custom_styling( 'posts' );

	    require_once YZ_PUBLIC_CORE . 'tabs/yz-tab-posts.php';

		$posts = new YZ_Posts_Tab();

        // Call Posts Tab Content.
        add_action( 'bp_template_content', array( $posts, 'tab' ) );

        // Load Tab Template
        bp_core_load_template( 'buddypress/members/single/plugins' );

    }

    /**
     * Comments Screen.
     */
    function comments_screen() {

    	// Styling.
		yz_styling()->custom_styling( 'comments' );

	    require_once YZ_PUBLIC_CORE . 'tabs/yz-tab-comments.php';

		$comments = new YZ_Comments_Tab();

        // Call Posts Tab Content.
        add_action( 'bp_template_content', array( $comments, 'tab' ) );

        // Load Tab Template
        bp_core_load_template( 'buddypress/members/single/plugins' );

    }

	/**
	 * # Tab Core.
	 */
	function core( $args ) { ?>

		<div class="yz-tab <?php echo 'yz-' . $tab_name; ?>">
			<?php $this->{$args['tab_name']}->tab_content(); ?>
		</div>

		<?php

	}

	/**
	 * Add Profile Tabs.
	 */
	function add_profile_tabs() {

	    global $bp;

	    $user_domain = bp_loggedin_user_domain();

	    $overview_args = apply_filters( 'yz_profile_overview_tab_args', array( 
	        'position' => 1,
	        'slug' => 'overview', 
	        'default_subnav_slug' => 'overview',
	        'parent_slug' => $bp->profile->slug,
	        'name' => __( 'Overview', 'youzer' ), 
	        'screen_function' => array( $this, 'overview_screen' ), 
	        'parent_url' => $user_domain . '/overview/'
	    ) );

	    $info_args = apply_filters( 'yz_profile_info_tab_args', array( 
	        'position' => 2,
	        'slug' => 'info', 
	        'parent_slug' => $bp->profile->slug,
	        'name' => __( 'Info', 'youzer' ), 
	        'screen_function' => array( $this, 'info_screen' ),
	        'parent_url' => $user_domain . '/info/'
	    ) );

	    $posts_args = apply_filters( 'yz_profile_posts_tab_args', array( 
	        'position' => 14,
	        'slug' => 'posts', 
	        'parent_slug' => $bp->profile->slug,
	        'name' => __( 'Posts', 'youzer' ), 
	        'screen_function' => array( $this, 'posts_screen' ), 
	        'parent_url' => $user_domain . '/posts/'
	    ) );
	    
	    $comments_args = apply_filters( 'yz_profile_comments_tab_args', array( 
	        'position' => 15,
	        'slug' => 'comments', 
	        'parent_slug' => $bp->profile->slug,
	        'name' => __( 'Comments', 'youzer' ), 
	        'screen_function' => array( $this, 'comments_screen' ), 
	        'parent_url' => $user_domain . '/comments/'
	    ) );


	    // Add Overview Tab.
	    bp_core_new_nav_item( $overview_args );

	    // Add Infos Tab.
	    bp_core_new_nav_item( $info_args );

	    // Add Posts Tab.
	    // bp_core_new_nav_item( $posts_args );
	    bp_core_new_nav_item( $posts_args );

	    // Add Comments Tab.
	    bp_core_new_nav_item( $comments_args );

	    if ( bp_is_active( 'activity' ) ) {

		    // Get Media Slug.        
		    $media_slug = yz_profile_media_slug();

		    $media_args = apply_filters( 'yz_profile_media_tab_args', array( 
		        'position' => 3,
		        'slug' => $media_slug, 
		        'parent_slug' => $bp->profile->slug,
		        'default_subnav_slug' => apply_filters( 'yz_profile_media_default_tab', 'all' ),
		        'name' => __( 'Media', 'youzer' ), 
		        'screen_function' => array( $this, 'media_screen' ), 
		        'parent_url' => $user_domain . '$media_slug/'
		    ) );

		    // Add Media Tab.
		    bp_core_new_nav_item( $media_args );

	        if ( bp_is_current_component( $media_slug ) ) {

			    $sub_tabs = apply_filters( 'yz_profile_media_subtabs', array(
			        'all' => array(
			            'title' => __( 'All', 'youzer' ),
			            'slug' => 'all'
			        ),
			        'photos' => array(
			            'title' => __( 'Photos', 'youzer' ),
			            'slug' => 'photos'
			        ),
			        'videos' => array(
			            'title' => __( 'Videos', 'youzer' ),
			            'slug' => 'videos'
			        ),
			        'audios' => array(
			            'title' => __( 'Audios', 'youzer' ),
			            'slug' => 'audios'
			        ),
			        'files' => array(
			            'title' => __( 'Files', 'youzer' ),
			            'slug' => 'files'
			        )
			    ) );

		        // Add Media Sub Pages.
		        foreach ( $sub_tabs as $page ) {

		            if (  $page['slug'] != 'all' && 'on' != yz_option( 'yz_show_profile_media_tab_' . $page['slug'], 'on' ) ) {
		                continue;
		            }

		            bp_core_new_subnav_item( array(
		                    'slug' => $page['slug'],
		                    'name' => $page['title'],
		                    'parent_slug' => $media_slug,
		                    'parent_url' => bp_displayed_user_domain() . "$media_slug/",
		                    'screen_function' => array( $this, 'media_subtabs' ),
		                )
		            );
		        }

	        }
		    if ( $this->is_user_can_see_bookmarks() ) {

			    bp_core_new_nav_item(
			    	array( 
				        'position' => 200,
				        'slug' => 'bookmarks', 
				        'name' => __( 'Bookmarks' , 'youzer' ), 
				        'default_subnav_slug' => 'activities',
				        'parent_slug' => $bp->profile->slug,
				        'screen_function' => array( $this, 'bookmarks_screen' ), 
				        'parent_url' => bp_displayed_user_domain() . "bookmarks/"
				    )
			    );
		    
		    }

	    }

	    // Add My Profile Page.
	    bp_core_new_nav_item(
	        array(
	            'position' => 200,
	            'slug' => 'yz-home', 
	            'parent_slug' => $bp->profile->slug,
	            'show_for_displayed_user' => bp_core_can_edit_settings(),
	            'default_subnav_slug' => 'yz-home',
	            'name' => __( 'My Profile', 'youzer' ), 
	            'parent_url' => bp_loggedin_user_domain() . '/yz-home/'
	        )
	    );

	    // Get Custom Tabs.
	    $custom_tabs = yz_option( 'yz_custom_tabs' );

	    if ( ! empty( $custom_tabs ) ) {

		    foreach ( $custom_tabs as $tab_id => $data ) {

		        // Hide Tab For Non Logged-In Users.
		        if ( 'false' == $data['display_nonloggedin'] && ! is_user_logged_in() ) {
		            continue;
		        }

		        // Get Slug.
		        $tab_slug = $data['type'] == 'shortcode' ? $data['slug'] : $tab_id;

		        // Add New Tab.
		        bp_core_new_nav_item(
		            array( 
		                'position' => 100,
		                'slug' => $tab_slug, 
		                'name' => $data['title'], 
		                'default_subnav_slug' => $tab_slug,
		                'parent_slug' => $bp->profile->slug,
		                'screen_function' => array( $this, 'custom_tabs_screen' ), 
		                'parent_url' => bp_loggedin_user_domain() . "/$tab_slug/"
		            )
		        );
	    	}
	    }
	    
	    do_action( 'yz_add_new_profile_tabs' );
	}

    /**
     * Bookmarks Screen.
     */
    function bookmarks_screen() {

	    require YZ_PUBLIC_CORE . 'tabs/yz-tab-bookmarks.php';
		
		$bookmarks = new YZ_Bookmarks_Tab();

        // Call Posts Tab Content.
        add_action( 'bp_template_content', array( $bookmarks, 'tab' ) );

        // Load Tab Template
        bp_core_load_template( 'buddypress/members/single/plugins' );

    }

    /**
     * Custom Tabs
     */
    function custom_tabs_screen() {

        require_once YZ_PUBLIC_CORE . 'tabs/yz-custom-tabs.php';

		$custom = new YZ_Custom_Tabs();

        // Call Posts Tab Content.
        add_action( 'bp_template_content', array( $custom, 'tab' ) );

        // Load Tab Template
        bp_core_load_template( 'buddypress/members/single/plugins' );

    }

	/**
	 * Sub Tabs Screen.
	 */
    function media_subtabs() {

    	if ( ! class_exists( 'YZ_Media_Tab' ) ) {
	    	require YZ_PUBLIC_CORE . 'tabs/yz-tab-media.php';
    	}
		
		$media = new YZ_Media_Tab();

        // Call Posts Tab Content.
        add_action( 'bp_template_content', array( $media, 'subtab_content' ) );

        // Load Tab Template
        bp_core_load_template( 'buddypress/members/single/plugins' );

    }

	/**
	 * Check is User Can see Bookmarks.
	 */
	function is_user_can_see_bookmarks() {
		
		// Init var.
		$visibility = false;

		if ( bp_core_can_edit_settings() ) {
			$visibility = true;
		} else {

			// Get Who can see bookmarks.
			$privacy = yz_option( 'yz_enable_bookmarks_privacy', 'private' );

			switch ( $privacy ) {

				case 'public':
					$visibility = true;
					break;
				
				case 'private':
					$visibility = bp_core_can_edit_settings() ? true : false;
					break;

				case 'loggedin':
					$visibility = is_user_logged_in() ? true : false;
					break;

				case 'friends':

					if ( bp_is_active( 'friends' ) ) {

						// Get User ID
						$loggedin_user = bp_loggedin_user_id();

						// Get Profile User ID
						$profile_user = bp_displayed_user_id();

						$visibility = friends_check_friendship( $loggedin_user, $profile_user ) ? true : false;

					}

					break;
				
				default:
					$visibility = false;
					break;

			}

		}

		return apply_filters( 'yz_is_user_can_see_bookmarks', $visibility );

	}

}

$tabs = new Youzer_Tabs();