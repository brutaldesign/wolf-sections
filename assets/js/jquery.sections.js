var WolfSections = WolfSections || {};

/* jshint -W062 */
WolfSections = function ( $ ) {

	'use strict';

	return {

		isMobile : navigator.userAgent.match( /(iPad)|(iPhone)|(iPod)|(Android)|(PlayBook)|(BB10)|(BlackBerry)|(Opera Mini)|(IEMobile)|(webOS)|(MeeGo)/i ),

		/**
		 * Init UI
		 */
		init : function () {

			var $this = this;

			this.parallax();
			this.customVideoBackground();
			// this.googleMapFix();

			//Resize event
			$( window ).resize( function() {
			
				$this.parallax();
				$this.customVideoBackground();

			} ).resize();

		},

		/**
		 *  Parallax Background
		 */
		parallax : function () {

			if ( ! this.isMobile ) {
				$( '.wolf-section-parallax' ).each( function() {
					$( this ).parallax( '50%', 0.1 );
				} );
			}
		},

		/**
		 * Video Background
		 */
		customVideoBackground : function () {

			var videoContainer = $( '.wolf-section-video-container' );

			if ( ! this.isMobile ) {

				videoContainer.each( function() {

					var containerWidth = $( this ).width(),
						containerHeight = $( this ).height(),
						ratioWidth = 640,
						ratioHeight = 360,
						ratio = ratioWidth/ratioHeight,
						video = $( this ).find( '.wolf-section-video' ),
						newHeight,
						newWidth,
						newMarginLeft,
						newmarginTop,
						newCss;

					if ( ( containerWidth / containerHeight ) >= ratio ) {

						newWidth = containerWidth;
						newHeight = Math.floor( ( containerWidth/ratioWidth ) * ratioHeight ) + 2;
						newmarginTop =  Math.floor( ( containerHeight - newHeight ) / 2 );

						newCss = {
							width : newWidth,
							height : newHeight,
							marginTop :  newmarginTop,
							marginLeft : 0
						};

						video.css( newCss );

					} else if ( ( containerWidth / containerHeight ) < ratio ) {
						// console.log( ratio );
						newHeight = containerHeight;
						newWidth = Math.floor( ( containerHeight/ratioHeight )*ratioWidth );
						newMarginLeft =  Math.floor( ( containerWidth - newWidth ) / 2 );
						

						newCss = {
							width : newWidth,
							height : newHeight,
							marginLeft :  newMarginLeft,
							marginTop : 0
						};

						video.css( newCss );
					}

				} );

			} else {
				videoContainer.hide();
			}
		},

		/**
		 * If a google map is displayed, make it full width
		 */
		googleMapFix : function () {

			if ( $( '.wolf-section .wolf-google-map' ).length || $( '.wolf-section iframe[src^="https://maps.google"]' ).length ){
				
				$( '.wolf-section' ).css( { padding : 0 } );
				$( '.wolf-section .wolf-section-wrap' ).css( { 'overflow' : 'hidden', 'max-width' : '100%', width : '100%', height : $( '.wolf-section iframe[src^="https://maps.google"]' ).height() } );
				$( '.wolf-section .wolf-section-wrap p' ).remove();
			
			}

		}
	};

}( jQuery );

;( function( $ ) {

	'use strict';

	$( document ).ready( function() {
		WolfSections.init();
	} );

	$( window ).load( function() {
		WolfSections.parallax();
	} );
	
} )( jQuery );