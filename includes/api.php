<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.saadamin.com
 * @since      1.0.0
 *
 * @package    Smd
 * @subpackage Smd/admin
 * This code registers a REST API endpoint /assignment/v1/image/{id} which accepts a GET request and returns details of the image with the specified ID. 
 * The code first checks if the specified ID is valid and if it corresponds to an image of type PNG or JPEG. Then, it retrieves the post and term IDs to which the image is attached, 
 * and returns all the required details in a JSON object. The function rest_ensure_response is used to convert the response to a proper REST response format.
 */
add_action( 'rest_api_init', 'smd_register_image_api' );

function smd_register_image_api() {
    register_rest_route( 'assignment/v1', '/image/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'smd_get_image_details',
        'args' => array(
            'id' => array(
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param) && intval($param) > 0;
                }
            )
        )
    ) );
}

function smd_get_image_details($request) {
    $image_id = intval($request->get_param('id'));
    $image = get_post($image_id);

    if (!$image || $image->post_type !== 'attachment' || !in_array($image->post_mime_type, array('image/png', 'image/jpeg'))) {
        return new WP_Error( 'invalid_image_id', 'Invalid image ID.', array( 'status' => 404 ) );
    }

    $attached_objects = array();
    $Smd = new Smd();
    $plugin_admin = new Smd_Admin( $Smd->get_plugin_name(), $Smd->get_version() );


    $attached_objects['posts'] = $plugin_admin->prevent_featured_image_deletion($image_id,'api');
    $attached_objects['terms'] = $plugin_admin->prevent_term_image_deletion($image_id,'api');
    $attached_objects['featured_image'] = $plugin_admin->prevent_post_content_image_deletion($image_id,'api');

    $image_details = array(
        'ID' => $image->ID,
        'Date' => $image->post_date,
        'Slug' => $image->post_name,
        'Type' => $image->post_mime_type,
        'Link' => wp_get_attachment_url( $image_id ),
        'Alt text' => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
        'Attached Objects' => $attached_objects,
    );

    return rest_ensure_response( $image_details );
}

/*
This code registers a custom REST API endpoint at /assignment/v1/delete-image/{id} which expects a DELETE request with the id parameter representing the ID of the image to be deleted. 
The callback function first checks if the attachment with the given ID exists and is an image. If not, it returns a 404 error. Then, it checks if the attachment is attached to any post
or term. If it is, it returns a 403 error with a message indicating that the image is being used in a post or term. Otherwise, it deletes the attachment using the wp_delete_attachment()
function and returns a success message. Note that this code assumes that the current user has appropriate capabilities to delete attachments. You can adjust the permission_callback 
parameter to restrict access to this endpoint as needed.
*/

add_action( 'rest_api_init', 'smd_register_assignment_api_routes' );

function smd_register_assignment_api_routes() {
    register_rest_route( 'assignment/v1', '/delete-image/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'smd_delete_image_api_callback',
        'permission_callback' => function() {
            current_user_can( 'manage_options' ); // replace with appropriate capability
        }
    ) );
}

function smd_delete_image_api_callback( WP_REST_Request $request ) {
    $image_id = $request->get_param( 'id' );
    $Smd = new Smd();
    $plugin_admin = new Smd_Admin( $Smd->get_plugin_name(), $Smd->get_version() );

    $featured_image=$plugin_admin->prevent_featured_image_deletion($image_id,'api');
    $term_image =$plugin_admin->prevent_term_image_deletion($image_id,'api');
    $post_content_image=$plugin_admin->prevent_post_content_image_deletion($image_id,'api');

    if(empty($featured_image) && empty($term_image) && empty($post_content_image)){//Checking if the image is attached to any post or term
        // delete attachment if not attached to any post or term
        wp_delete_attachment( $image_id, true );
        return array(
            'success' => true,
            'message' => 'Image deleted successfully.'
        );
    }else{
        return new WP_Error( 'attachment_in_use', 'Cannot delete image as it is being used in a post or term.', array( 'status' => 403 ) );
    }
}
