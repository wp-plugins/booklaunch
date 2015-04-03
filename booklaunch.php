<?php
   /*
   Plugin Name: Booklaunch for Wordpress
   Plugin URI: https://booklaunch.io
   Description: A plugin that integrates your Booklaunch page as a page template on your Wordpress website.
   Version: 1.0.3
   Author: The Booklaunch Team
   Author URI: https://booklaunch.io
   License: GPL2
   */

class PageTemplater {
	
	protected $plugin_slug;
    private static $instance;
    protected $templates;

    
    public static function get_instance() {

        if( null == self::$instance ) {
                self::$instance = new PageTemplater();
        } 

        return self::$instance;

    } 

    /**
     * Initializes the plugin by setting filters and administration functions.
     */
    private function __construct() {

        $this->templates = array();


        // Adds a filter to the attributes metabox to inject template into the cache.
        add_filter(
			'page_attributes_dropdown_pages_args',
			 array( $this, 'register_booklaunch_templates' ) 
		);


        // Adds  filter to the save post to inject out template into the page cache
        add_filter(
			'wp_insert_post_data', 
			array( $this, 'register_booklaunch_templates' ) 
		);


        add_filter(
			'template_include', 
			array( $this, 'view_booklaunch_template') 
		);


        $this->templates = array(
                'page-booklaunch.php'     => 'Booklaunch Page',
        );
			
    } 


    /**
     * Adds our template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     *
     */
    public function register_booklaunch_templates( $atts ) {

        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
                $templates = array();
        } 

        wp_cache_delete( $cache_key , 'themes');

        $templates = array_merge( $templates, $this->templates );

        wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        return $atts;

    } 

    /**
     * Checks if the template is assigned to the page
     */
    public function view_booklaunch_template( $template ) {

            global $post;

            if (!isset($this->templates[get_post_meta( 
				$post->ID, '_wp_page_template', true 
			)] ) ) {
				
                    return $template;
					
            } 

            $file = plugin_dir_path(__FILE__). get_post_meta( 
				$post->ID, '_wp_page_template', true 
			);
			
            if( file_exists( $file ) ) {
                    return $file;
            } 
			else { echo $file; }

            return $template;

    } 

}

add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );

add_action( 'load-post.php', 'add_booklaunch_meta' );
add_action( 'load-post-new.php', 'add_booklaunch_meta' );


/* Meta box setup function. */
function add_booklaunch_meta( $object ) {
		
	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action( 'add_meta_boxes', 'booklaunch_add_post_meta_boxes' );
	
	add_action( 'save_post', 'booklaunch_save_post_class_meta', 10, 2 );
	
	function booklaunch_add_post_meta_boxes() {
				
		add_meta_box(
			'booklaunch_options',
			'Booklaunch Page Options',
			'booklaunch_post_class_meta_box',
			'page',
			'normal',
			'default'
		);
		
	}
			
}

add_action('admin_enqueue_scripts', 'my_admin_script');
function my_admin_script(){
    wp_enqueue_script('my-admin', plugins_url( 'js/booklaunch_admin.js', __FILE__ ), array('jquery'));
}

/* Display the post meta box. */
function booklaunch_post_class_meta_box( $object, $box ) { ?>
	
  <?php wp_nonce_field( basename( __FILE__ ), 'booklaunch_post_class_nonce' ); ?>

  <p>
    <label for="booklaunch-page-url">Copy & Paste your Booklaunch page's URL.</label>
    <br />
    <input class="widefat" placeholder="https://booklaunch.io/{author}/{book}" type="text" name="booklaunch_page_url" id="booklaunch_page_url" value="<?php echo esc_attr( get_post_meta( $object->ID, 'booklaunch_page_url', true ) ); ?>" size="30" />
  </p>
  
<?php }

/* Save the meta box's post metadata. */
function booklaunch_save_post_class_meta( $post_id, $post ) {
	
	/* Verify the nonce before proceeding. */
	if ( !isset( $_POST['booklaunch_post_class_nonce'] ) || !wp_verify_nonce( $_POST['booklaunch_post_class_nonce'], basename( __FILE__ ) ) )
	return $post_id;
	
	/* Get the post type object. */
	$post_type = get_post_type_object( $post->post_type );
	
	/* Check if the current user has permission to edit the post. */
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
	return $post_id;
		
	/* Get the posted data and sanitize it for use as an HTML class. */
	$new_meta_value = $_POST['booklaunch_page_url'];
		
	/* Get the meta key. */
	$meta_key = 'booklaunch_page_url';
	
	/* Get the meta value of the custom field key. */
	$meta_value = get_post_meta( $post_id, $meta_key, true );
	
	/* If a new meta value was added and there was no previous value, add it. */
	if ( $new_meta_value && '' == $meta_value )
	add_post_meta( $post_id, $meta_key, $new_meta_value, true );
	
	/* If the new meta value does not match the old value, update it. */
	elseif ( $new_meta_value && $new_meta_value != $meta_value )
	update_post_meta( $post_id, $meta_key, $new_meta_value );
	
	/* If there is no new meta value but an old value exists, delete it. */
	elseif ( '' == $new_meta_value && $meta_value )
	delete_post_meta( $post_id, $meta_key, $meta_value );
}