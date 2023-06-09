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
		wp_enqueue_script( 'sweet_alert', plugin_dir_url( __FILE__ ) . 'js/sweetalert2@11.js', array( 'jquery' ), 11, false );

	}
	/*
		To force WordPress to allow only JPG and PNG file uploads ONLY in terms page, you can add the following code
		CMB2 restriction on file types is NOT working. So, I have to use this code to restrict file types.
		This code adds the jpg, jpeg, and png file extensions to the list of allowed file types for uploads. 
		Any other file types will be blocked by WordPress. Note that this only affects uploads made through the terms page; 
		it does not affect other file uploads on your website. 
	*/
	public function force_mime_types( $mimes ) {
		$mime_types['jpg'] = 'image/jpeg';
		$mime_types['jpeg'] = 'image/jpeg';
		$mime_types['png'] = 'image/png';
		return $mime_types;
	}

	//This function is used to add a metabox to the category taxonomy
	public function cmb2_add_metabox() {
		add_filter('upload_mimes', array($this,'force_mime_types'));  //CMB2 restriction on file types is NOT working. So, I have to use this code to restrict file <types class=""></types>

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

	   /**
	   	* Prevent the deletion of an image if it is being used as a Featured Image in an article:
		* To achieve this, you can use the 'delete_attachment' filter to check if the image is being used as a
 		* featured image in any posts. If it is, you can prevent the deletion by returning a custom error message.
		*/

	public function prevent_featured_image_deletion($post_id_first,$post=null,$return = 'backend') {
		$post_id = $post ? $post->ID : $post_id_first ;
		// Search for the media file in post meta
		$args = array(
			'post_type' => 'any',
			'post_status' => 'publish,private,draft',
			'meta_query' => array(
				array(
					'key' => '_thumbnail_id',
					'value' => $post_id,
					'compare' => '='
				)
			)
		);
		$query = new WP_Query($args);
		if ($query->have_posts()) {
			if($return=='admin-front-end') {//return the html only when the function is called from the add_image_details_link function
				return $this->getHtmlArticleList('featured_image',$query->posts);
			}else if($return=='api'){//Rest api calls
				return array_column($query->posts, 'ID');
			}else{//backend calls only
				if(defined('DOING_PHPUNIT')){
					return false;
				}
				wp_send_json_error(__('This image is being used as a featured image in one or more posts with these id '.implode(',',array_column($query->posts, 'ID')).'. Please remove the featured image before deleting this image.'));
			}
		}
	}


	// Prevent the deletion of an image if it is being used in the content of a post (Post Body):
	public function prevent_post_content_image_deletion($attachment_id,$attachment=null,$return = 'backend') {

		$attachment_url = $attachment ? $attachment->guid : wp_get_attachment_url( $attachment_id ) ;

		// Get the global WordPress database object
		global $wpdb;
	
		// Define the termmeta table name
		$post_table = $wpdb->prefix . 'posts';

		// Query the termmeta table for the search value
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $post_table WHERE post_type IN ( %s , %s ) AND post_content LIKE %s",
				array('post','page','%' . $wpdb->esc_like( $attachment_url ) . '%')
			)
		);
		
		if ($results) {
			if($return=='admin-front-end') {//return the html only when the function is called from the add_image_details_link function
				return $this->getHtmlArticleList('post_content',$results);
			}else if($return=='api'){//Rest api calls
				return array_column($results, 'ID');
			}else{//backend calls only
				if(defined('DOING_PHPUNIT')){
					return false;
				}
				wp_send_json_error(__('This image is being used as a content in one or more posts with these id '.implode(',',array_column($results, 'ID')).'. Please remove the content image before deleting this image.'));
			}
		}
		return $attachment ? $attachment_id : '';//call from pre_delete_attachment will get attachment_id variable only.
	}
	public function check_image_before_deletion($attachment_id,$attachment){
		$in_featured_image = $this->prevent_featured_image_deletion($attachment_id,$attachment);
		$in_post_content = $this->prevent_post_content_image_deletion($attachment_id,$attachment);
		$in_term = $this->prevent_term_image_deletion($attachment_id,$attachment);
		if (in_array(false, array($in_featured_image, $in_post_content, $in_term), true)) {//Only PHPunit tests will return false
			return false;
		} else {
			return $attachment_id;
		}
	}
	//Prevent the deletion of an image if it is being used in a Term
	public function prevent_term_image_deletion($attachment_id,$attachment=null,$return = 'backend') {
		$attachment_url = $attachment ? $attachment->guid : wp_get_attachment_url( $attachment_id ) ;
		
		// Get the global WordPress database object
		global $wpdb;
	
		// Define the termmeta table name
		$termmeta_table = $wpdb->prefix . 'termmeta';

		// Query the termmeta table for the search value
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT term_id as ID FROM $termmeta_table WHERE meta_key = %s AND meta_value LIKE %s",
				'test_image','%' . $wpdb->esc_like( $attachment_url ) . '%'
			)
		);
		
		if ($results) {
			if($return=='admin-front-end') {//return the html only when the function is called from the add_image_details_link function
				return $this->getHtmlArticleList('term',$results);
			}else if($return=='api'){//Rest api calls
				return array_column($results, 'ID');
			}else{//backend calls only
				if(defined('DOING_PHPUNIT')){
					return false;
				}
				wp_send_json_error(__('This image is being used as a term in these id '.implode(',',array_column($results, 'ID'))));
			}
		}
		return $attachment ? $attachment_id : '';//call from pre_delete_attachment will get attachment_id variable only.
	}

	private function getHtmlArticleList($type,$results,$html=''){
		foreach($results as $result) {
			$url = $type == 'term' ? get_edit_term_link($result->ID) : get_edit_post_link($result->ID);
			$id = $type == 'term' ? $result->ID : $result->ID;
			$html .= '&nbsp;<a class="smd_attachment_image_link" type="'.$type.'" target="_blank" href="'.$url.'">'.$id.'</a>,'; 
		}
		return $html;
	}
	/*
	The interface should display IDs of the post(s) or term(s) with an edit link https://i.imgur.com/DeYUWTl.jpeg. The user should be able to determine whether the given ID is 
	for a post or for a term. This message should appear in every place in  WordPress from where images can be deleted, such as the Media List table https://i.imgur.com/WhqWd6D.jpeg, 
	Media Library Popup https://i.imgur.com/DeYUWTl.jpeg etc.
	*/
	public function add_image_details_link($form_fields, $post) {
		//check if the attachment is a jpeg or png image
		if($post->post_mime_type == 'image/jpeg' || $post->post_mime_type == 'image/png'){
			$html = $this->get_html_of_linked_object($post->ID);
			if($html){
				$form_fields['image_details_link'] = array(
					'label' => 'Linked Articles',
					'input' => 'html',
					'html' => $html
				);
			}
		}
		return $form_fields;
	}
	
	public function add_image_linked_object_column($columns) {
		$columns['image_linked_object'] = 'Linked Object';
		return $columns;
	}

	/*
	In the Media Library Table, add a column named "Attached Objects" https://i.imgur.com/WhqWd6D.jpeg that displays a comma-separated list of IDs (linked to the corresponding edit page).
	The user should be able to determine whether the given ID is for a post or for a term.
	*/
	public function image_linked_object_column_content($column_name, $attachment_id) {
		if ($column_name == 'image_linked_object') {
			$list =$this->get_html_of_linked_object($attachment_id);
			echo $list ? 'Articles<br>'.$list : 'No linked objects';
		}
	}
	private function get_html_of_linked_object($attachment_id){
		$html = $this->prevent_featured_image_deletion($attachment_id,null,'admin-front-end');
		$html .= $this->prevent_post_content_image_deletion($attachment_id,null,'admin-front-end');
		$html .= $this->prevent_term_image_deletion($attachment_id,null,'admin-front-end');
		return rtrim($html, ',');
	}
}
