<?php

use PHPUnit\Framework\TestCase;
use Faker\Factory as Faker;
define('DOING_PHPUNIT', 'true');
require_once dirname(__FILE__) . '/../../../../wp-load.php';
require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once( ABSPATH . 'wp-admin/includes/file.php' );// it allows us to use download_url() and wp_handle_sideload() functions
require_once( ABSPATH . 'wp-admin/includes/image.php' );

class MediaLibraryTest extends TestCase {
    /**
     * A single example test.
     */
    
    public function test_delete_Image() {
        
        $faker = Faker::create();

        /* Now we are going to test the case when the image is in a post content */
        $attachment_id = $this->createTestMediaImage($faker);
        $attachment_url = wp_get_attachment_url( $attachment_id );
        $this->assertFalse(empty($attachment_url));

        $post_content = '<p>'.$faker->paragraph().' and an image: <img src="' . $attachment_url . '" alt="test image"></p>';
        //Create test post
        $post_id = wp_insert_post(array(
            'post_title' => $faker->sentence(10),
            'post_content' => $post_content,
            'post_status' => 'publish'
        ));

        //Try to delete the image
        $this->deleteImageAttemptShouldFail($attachment_id);

        // Delete the post
        $this->deleteThePost($post_id);
        
        //Trying to delete the image when it is NOT in a post content
        $this->deleteImageAttemptShouldSuccess($attachment_id);


        
        /* Now we are going to test the case when the image is a page featured image */
        
        //Create anothertest post
        $post_id = wp_insert_post(array(
            'post_title' => $faker->sentence(10),
            'post_content' => $faker->paragraph(),
            'post_type' => 'page',
            'post_status' => 'publish'
        ));

        // Create another test media image
        $attachment_id = $this->createTestMediaImage($faker);
        
        // Set the featured image of a post to the downloaded image
        $this->assertTrue(is_int(set_post_thumbnail($post_id, $attachment_id)));

        //Try to delete the image
        $this->deleteImageAttemptShouldFail($attachment_id);

        // Delete the post
        $this->deleteThePost($post_id);

        //Trying to delete the image when it is NOT in a post featured image
        $this->deleteImageAttemptShouldSuccess($attachment_id);




        /* Now we are going to test the case when the image is a tearm meta
            This time we will try to delete the image by rest API
        */

        // Create another test media image
        $attachment_id = $this->createTestMediaImage($faker);

        // Create a test term
        $term = wp_insert_term($faker->text(20), 'category');

        $attachment_url = wp_get_attachment_url( $attachment_id );

        // Add the image to the term meta
        add_term_meta($term['term_id'], 'test_image', $attachment_url);
        add_term_meta($term['term_id'], 'test_image_id', $attachment_id);

        $term_meta = get_term_meta( $term['term_id'], 'test_image' ,true);

        //Check if the image is in the term meta
        $this->assertFalse(empty($term_meta));

        // //Delete the image by rest API
        $response = $this->testdeleteImageByRest($attachment_id);

        // //Delete the image by rest API should return false
        $this->assertFalse($response->success);
        
        // Delete the term
        wp_delete_term($term['term_id'], 'category');

        //Get the image details by rest API
        $this->TestGetImageByRest($attachment_id,$this);

        //Delete the image by rest API
        $response = $this->testdeleteImageByRest($attachment_id);

        //Delete the image by rest API should return true
        $this->assertTrue($response->success);



      }

    private function deleteThePost($post_id){
        // Delete the post
        $delete_post= wp_delete_post($post_id, true);
       
        // Check if the image is not deleted and a WP_Error object is returned
        $this->assertTrue(is_a( $delete_post, 'WP_Post' ));

        // get the post object by post ID
        $post = get_post($post_id);

        // Check if the post is not deleted
        $this->assertTrue(empty($post));
    }
    private function createTestMediaImage($faker){
        $temp_file=$this->createTestImage();// Please enable php extension gd ; extension=gd in php.ini

        // move the temp file into the uploads directory
        $file = array(
            'name'     => $faker->randomNumber().time().'.jpg',
            'type'     => mime_content_type( $temp_file ),
            'tmp_name' => $temp_file,
            'size'     => filesize( $temp_file ),
        );

        $sideload = wp_handle_sideload(
            $file,
            array(
                'test_form'   => false ,
                'mimes' => array(
                    'jpg' => 'image/jpeg',//Make suere you have this mime type in your mime type list
                    'png' => 'image/png',//Make suere you have this mime type in your mime type list
                ),
            )
        );
        $this->assertTrue(empty( $sideload[ 'error' ] ));


        // it is time to add our uploaded image into WordPress media library
        $attachment_id = wp_insert_attachment(
            array(
                'guid'           => $sideload[ 'url' ],
                'post_mime_type' => $sideload[ 'type' ],
                'post_title'     => basename( $sideload[ 'file' ] ),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ),
            $sideload[ 'file' ]
        );
        $this->assertFalse(is_wp_error( $attachment_id ) );
        $this->assertFalse(!$attachment_id );


        wp_update_attachment_metadata(
            $attachment_id,
            wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] )
        );
        return $attachment_id;
    }
    private function deleteImageAttemptShouldSuccess($attachment_id){
            //Trying to delete the image when it is in post content
            $delete_post= wp_delete_attachment($attachment_id, true);
            // Check if the image is deleted and a WP_Error object is returned
            $this->assertTrue(is_a( $delete_post, 'WP_Post' ));

            $attachment = get_post($attachment_id);

            // Check if the image is deleted and a null object is returned
            $this->assertTrue(is_null( $attachment));
    
            // Check if the file exists in the disk physical path
            $this->assertFalse(file_exists(get_attached_file($attachment_id)));
    }
    private function deleteImageAttemptShouldFail($attachment_id){
            //Trying to delete the image when it is in post content
            $delete_post= wp_delete_attachment($attachment_id, true);
            // Check if the image is deleted and a WP_Error object is returned
            $this->assertFalse($delete_post);

            $attachment = get_post($attachment_id);

            // Check if the image is deleted and a null object is returned
            $this->assertFalse(is_null( $attachment));
    
            // Check if the file exists in the disk physical path
            $this->assertTrue(file_exists(get_attached_file($attachment_id)));
    }
    private function createTestImage() {
       // Generate a random image with width 500 and height 500
        $img = imagecreatetruecolor(500, 500);

        // Set a random background color
        $bgColor = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
        imagefill($img, 0, 0, $bgColor);

        // Add some random lines to the image
        for ($i = 0; $i < 10; $i++) {
            $lineColor = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
            imageline($img, rand(0, 500), rand(0, 500), rand(0, 500), rand(0, 500), $lineColor);
        }

        // Add some random text to the image
        $textColor = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
        imagettftext($img, 20, 0, 50, 250, $textColor, 'C:\Windows\Fonts\arial.ttf', 'Random Image');

        // Save the image to the temp directory
        $tempDir = sys_get_temp_dir();
        $imageFile = tempnam($tempDir, 'random_');
        imagejpeg($img, $imageFile);

        // Free up memory
        imagedestroy($img);

        // Print the path to the generated image
        return $imageFile;
    }
	private static function testGetImageByRest($attachment_id,$test) {
        //Get the image details by rest API
        $url = get_rest_url()."assignment/v1/image/$attachment_id";

		$response = wp_remote_get( $url );

        $test->assertFalse(is_wp_error( $response ));//Check if the response is not a WP_Error object

        if ( !is_wp_error( $response ) && $response['response']['code'] == 200 ) {
            // The request was successful
            $response = json_decode(wp_remote_retrieve_body( $response ));
            $test->assertFalse(empty($response->ID) && empty($response->date) && empty($response->slug) && empty($response->type) && empty($response->link));
            $test->assertTrue(isset($response->alt_text) && property_exists($response->attached_objects, 'posts') && property_exists($response->attached_objects, 'terms') && property_exists($response->attached_objects, 'featured_image'));
        } else {
            // The request failed
            return $response->get_error_message();
        }
	}
	private static function testdeleteImageByRest($attachment_id) {

        $url =get_rest_url()."assignment/v1/delete-image/$attachment_id";

        // Set the arguments for the request
        $args = array(
            'method' => 'DELETE','timeout'     => 99999999,//Set the timeout to a large value for debugging process
        );

        // Send the DELETE request using wp_remote_request()
        $response = wp_remote_request( $url, $args );

        // Check if the request was successful
        if ( !is_wp_error( $response ) && $response['response']['code'] == 200 ) {
            // The request was successful
            return json_decode(wp_remote_retrieve_body( $response ));
        } else {
            return $response->get_error_message();
        }

	}
}
