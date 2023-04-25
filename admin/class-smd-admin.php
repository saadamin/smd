<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.saadamin.com
 * @since      1.0.0
 *
 * @package    Smd
 * @subpackage Smd/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Smd
 * @subpackage Smd/admin
 * @author     Saad Amin <saadvi@gmail.com>
 */
class Smd_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smd_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smd_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smd-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smd_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smd_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/smd-admin.js', array( 'jquery' ), $this->version, false );

	}
	//This function is used to add a metabox to the category taxonomy
	public function cmb2_add_metabox() {

		$prefix = '_smd_';
	
		$cmb = new_cmb2_box( array(
			'id'           => $prefix . 'add_an_image',
			'title'        => __( 'add an image', 'cmd' ),
			'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'       => array( 'category' ), // Tells CMB2 which taxonomies should have these fields
			'context'      => 'advanced',
			'priority'     => 'high',
		) );
	
		$cmb->add_field( array(
			'name'    => 'Image',
			'desc'    => 'Upload an image',
			'id'      => 'test_image',
			'type'    => 'file',
			// Optional:
			'options' => array(
				'url' => false, // Hide the text input for the url
			),
			'text' => array(
				'add_upload_files_text' => 'Add an image', // default: "Add or Upload Files"
				'remove_image_text' => 'Remove an image', // default: "Remove Image"
				'file_text' => 'Image', // default: "File:"
				'remove_text' => 'Replace image', // default: "Remove"
			),
			// query_args are passed to wp.media's library query.
			'query_args' => array(
				'type' => array(
					'image/jpeg',
					'image/png',
				),
			),
			'preview_size' => 'large', // Image size to use when previewing in the admin.
		)  );
	
	}
	//This function is used to add a metabox to the post type
	private function check_for_content_usage($post_id) {
		$post_content = get_post($post_id)->post_content;
		$media_ids = get_attached_media('image', $post_id);
		foreach($media_ids as $media) {
		  $media_url = wp_get_attachment_url($media->ID);
		  if(strpos($post_content, $media_url) !== false) {
			return true;
		  }
		}
		return false;
	  }

	   /**
	   	* Prevent the deletion of an image if it is being used as a Featured Image in an article:
		* To achieve this, you can use the 'delete_attachment' filter to check if the image is being used as a
 		* featured image in any posts. If it is, you can prevent the deletion by returning a custom error message.
		*/

	  public function prevent_featured_image_deletion($post_id) {
		$attachment_id = get_post_thumbnail_id($post_id);
		if (!empty($attachment_id)) {
		  // Check if attachment is being used as a featured image
		  $posts = get_posts(array(
			'meta_key' => '_thumbnail_id',
			'meta_value' => $attachment_id,
			'post_type' => 'any',
			'post_status' => 'publish,private,draft',
			'posts_per_page' => -1
		  ));
		  if (!empty($posts)) {
			wp_die(__('This image is being used as a featured image in one or more posts. Please remove the featured image before deleting this image.'));
		  }
		}
	  }
	  
	
}
