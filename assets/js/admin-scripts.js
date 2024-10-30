jQuery(document).ready(function($) {
	$( 'body' ).on( 'click', '.ci-image-widget-upload-button', function( e ) {
		e.preventDefault();

		var uploadButton = $( this );

		var storeID      = uploadButton.siblings( '.ci-image-widget-media-id' );
		var uploadPreview = uploadButton.siblings( '.ci-image-widget-thumb-container' );

		var ci_image_widget_media_frame = wp.media( {
			className: 'media-frame ci-image-widget-media-frame',
			frame: 'select', //Allow Select Only
			multiple: false, //Disallow Mulitple selections
			library: {
				type: 'image' //Only allow images
			}
		} ).on( 'select', function(){
			// grab the selected images object
			var selection = ci_image_widget_media_frame.state().get( 'selection' );

			// grab object properties for each image
			selection.map( function( attachment ){
				var attachment = attachment.toJSON();

				if( storeID.length > 0 ) {
					storeID.val( attachment.id ).trigger( 'change' );
				}
				if( uploadPreview.length > 0 ) {
					// For some reason, attachment.sizes doesn't include additional image sizes.
					// Only 'thumbnail', 'medium' and 'full' are exposed, so we use 'thumbnail' instead of 'ci_featgal_small_thumb'
					var html = '<img src="' + attachment.sizes.thumbnail.url + '" class="ci-image-widget-thumb" />';
					uploadPreview.html( html );
				}
			});
		} ).open();
	}); // on click
});
