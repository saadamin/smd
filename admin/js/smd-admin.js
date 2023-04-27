(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	  

document.addEventListener('DOMContentLoaded', function() {
	// Hook into jQuery's ajaxComplete event
	jQuery(document).ajaxComplete(function(event, xhr, settings) {
	  if (settings.url.indexOf('admin-ajax.php') !== -1) {
		// Default admin-ajax.php ajax call is completed
		smdReplaceDeleteButton();
		console.log('Default admin-ajax.php ajax call is completed');
	  }
	});
	smdReplaceDeleteButton();
  });
  
  //  default behavior of delete attachment has been changed here by replacing button class
  function smdReplaceDeleteButton() {
		// Get all the td elements with class "field"
		const tdElements = document.querySelectorAll("td.field") ? document.querySelectorAll("td.field") : document.querySelectorAll("td.column-image_linked_object");

		// Loop through each td element
		tdElements.forEach(function(tdElement) {
			// Check if the td element has a child element with an anchor tag
			if (tdElement.querySelector("a")) {
			// Get the delete button element
			const deleteButton1 = document.querySelector(".actions .delete-attachment");
			// Check if the delete button element exists
			if (deleteButton1) {
				// Change the class of the delete button element to "smd-prevent-delete" to prevent it from being clicked
				deleteButton1.classList.replace("delete-attachment", "smd-prevent-delete");
			}
			const deleteButton2 = document.querySelector("#delete-action a.submitdelete");
			// Check if the delete button element exists
			if (deleteButton2) {
				// Change the class of the delete button element to "smd-prevent-delete" to prevent it from being clicked
				deleteButton2.classList.replace("submitdelete", "smd-prevent-delete");
				deleteButton2.removeAttribute('onClick');
				deleteButton2.removeAttribute('href');
			}
			}
		});

		// Get all <tr> elements with class 'author-self' , this codes are applicable for media library (wp-admin/upload.php?mode=list)
		const rows = document.querySelectorAll('tr.author-self');

		// Loop through all the rows and check if it has a child element with class 'image_linked_object'
		rows.forEach(row => {
		const imgLinkedObj = row.querySelector('td.image_linked_object');
		
		if (imgLinkedObj) {
			// Check if the child element has an anchor tag
			const anchorTag = imgLinkedObj.querySelector('a');
			
			if (anchorTag) {
			// Replace the class of the anchor tag inside the <span class="delete"> element with 'smd-prevent-delete'
			const deleteAnchor = row.querySelector('span.delete a.submitdelete');
			
			if (deleteAnchor) {
				deleteAnchor.classList.replace('submitdelete', 'smd-prevent-delete');
				deleteAnchor.removeAttribute('onClick');
				deleteAnchor.removeAttribute('href');
			}
			}
		}
		});

  }

  // Add click event listener to dynamically created buttons
document.addEventListener('click', function(event) {
  if (event.target.classList.contains('smd-prevent-delete')) {

    // Get all elements in the td with class 'field'
    const elements = smdSelectAnchorElements(event);

    // Group elements by 'type' attribute
    const groups = {};
    elements.forEach(function(element) {
      const type = element.getAttribute('type');
      if (!groups[type]) {
        groups[type] = [];
      }
      groups[type].push(element.outerHTML);
    });

    // Construct alert message
    let message = '';
    if (groups.post_content) {
      message += 'This image is being used by these post contents: ' + groups.post_content.join(', ') + ' <br>';
    }
    if (groups.term) {
      message += 'This image is being used by these terms : ' + groups.term.join(', ') + ' <br>';
    }
    if (groups.featured_image) {
      message += 'This image is being used by these posts as a featured image: ' + groups.featured_image.join(', ') + ' ';
    }

    // Show alert message
	Swal.fire({
		icon: 'error',
		title: 'Oops...',
		html: message,
		footer: 'You have to delete image references first before deleting this image.<br>Note: default behavior of this button has been changed in smd-admin.js file line number 46.'
	  })
    // alert(message);
  }
});

function smdSelectAnchorElements(event){
	if(document.querySelectorAll('tr.author-self')){
		return event.target.parentNode.parentNode.parentNode.nextElementSibling.nextElementSibling.nextElementSibling.nextElementSibling.nextElementSibling.querySelectorAll('td.image_linked_object.column-image_linked_object a');
	}else{
		return document.querySelectorAll('td.field a');
	}

}

})( jQuery );
