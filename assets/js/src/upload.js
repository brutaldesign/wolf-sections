;( function( $ ) {

	"use strict";

/*-----------------------------------------------------------------------------------*/
/*	Uploader
/*-----------------------------------------------------------------------------------*/
	$( '.wolf-sections-set-img, .wolf-sections-set-bg' ).click( function( e ) {
		e.preventDefault();
		var $el = $( this ).parent();
		var uploader = wp.media({
			title : 'Choose an image',
			multiple : false
		})
		.on( 'select', function(){
			var selection = uploader.state().get('selection');
			var attachment = selection.first().toJSON();
			$('input', $el).val(attachment.url);
			$('img', $el).attr('src', attachment.url).show();
		})
		.open();
	});

	$( '.wolf-sections-set-file' ).click(function(e){
		e.preventDefault();
		var $el = $( this ).parent();
		var uploader = wp.media({
			title : 'Choose a file',
			multiple : false
		})
		.on( 'select', function(){
			var selection = uploader.state().get('selection');
			var attachment = selection.first().toJSON();
			$('input', $el).val(attachment.url);
			$('span', $el).html(attachment.url).show();
		})
		.open();
	});


/*-----------------------------------------------------------------------------------*/
/*	Reset Image preview
/*-----------------------------------------------------------------------------------*/

	$('.wolf-sections-reset-img, .wolf-sections-reset-bg').click(function(){
		
		$( this ).parent().find('input').val('');
		$( this ).parent().find('.wolf-sections-img-preview').hide();
		return false;

	});

	$('.wolf-sections-reset-file').click(function(){
		
		$( this ).parent().find('input').val('');
		$( this ).parent().find('span').empty();
		return false;

	});

/*-----------------------------------------------------------------------------------*/
/*	Tipsy
/*-----------------------------------------------------------------------------------*/      
	
	$( '.hastip' ).tipsy( { fade: true, gravity: 's' } );

} )( jQuery );
