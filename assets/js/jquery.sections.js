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
			// this.googleMapFix();

			//Resize event
			$( window ).resize( function() {
			
				$this.parallax();

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