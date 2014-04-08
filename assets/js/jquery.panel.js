var WolfSectionsAdminEditorPanel = WolfSectionsAdminEditorPanel || {},
	WolfSectionsAjax = WolfSectionsAjax || {};

/* jshint -W062 */
WolfSectionsAdminEditorPanel = function ( $ ) {

	'use strict';

	return {

		/**
		 * Init UI
		 */
		init : function () {

			this.createPanel();
			this.getSectionList();
			this.addSectionButton();
			this.switchTab();
			this.toolTip();
			this.insertDialogContent();
			this.openDialog();
			this.sortable();
			this.removeSection();

			//Resize event
			// $( window ).resize( function() {

			// } ).resize();

		},

		/**
		 * Create Section managment panel
		 */
		createPanel : function () {

			var html = '<div id="wolf-sections-panel"><div id="wolf-sections-toolbar"></div><div id="wolf-sections-list"></div></div>';

			$( html ).insertAfter( '#wp-content-editor-container' )
				.addClass( 'wp-editor-container' )
				.hide();
		},

		/**
		 * Get saved section list in post editor
		 */
		getSectionList : function () {

			var data = {

				post_id : WolfSectionsAjax.currentPostId,
				action : 'wolf_get_section_list'

			};

			$.post( WolfSectionsAjax.adminUrl, data, function( response ){
				
				if ( response !== 'none' ) {

					$( '#wolf-sections-list' ).append( response );

				}
				
			} );

		},

		/**
		 * Insert button to open dialog box
		 */
		addSectionButton : function () {

			$( '#wolf-sections-toolbar' )
			.append( '<a id="wolf-add-section" class="wolf-section-help" title="' + WolfSectionsAjax.addSectionMessage + '"></a>' );

		},

		/**
		 * Add a "Sections" tab to the text editor and handle tab switch
		 */
		switchTab : function () {

			// $.cookie('sections-tab-active',true,{path:'/',expires:1});
			// $.cookie('sections-tab-active', null);

			if ( $.cookie( 'sections-tab-active' ) && $.cookie( 'sections-tab-active' ) === 'true' ) {
				$( '#wp-content-wrap' ).removeClass( 'tmce-active html-active' );

				$( '#wp-content-editor-container, #post-status-info' ).hide();

				// Show sections and the inside div
				$( '#wolf-sections-panel' ).show().find( '> .inside' ).show();
				$( '#wp-content-wrap' ).addClass( 'sections-active' );


				// Triggers full refresh
				$( window ).trigger( 'resize' );
				$( '#content-resize-handle' ).hide();
			}

			$( '#wp-content-editor-tools' ).find( '.wp-editor-tabs' ).click(function () {
				
				// var $$ = $(this);

				$( '#wp-content-editor-container, #post-status-info' ).show();
				$( '#wolf-sections-panel' ).hide();
				$( '#wp-content-wrap' ).removeClass( 'sections-active' );
				$.cookie( 'sections-tab-active', false, { path : '/', expires:1 } );
			
				$('#content-resize-handle' ).show();
				
				} ).prepend(
					
					$( '<a id="wolf-sections-tab" class="wp-switch-editor switch-sections">' + WolfSectionsAjax.SectionTabTitle + '</a>' )
				
					.click( function () {
						// var $$ = $( this );
						// This is so the inactive tabs don't show as active
						$( '#wp-content-wrap' ).removeClass( 'tmce-active html-active' );

						// Hide all the standard content editor stuff
						$( '#wp-content-editor-container, #post-status-info' ).hide();

						// Show sections and the inside div
						$( '#wolf-sections-panel' ).show().find( '> .inside' ).show();
						$( '#wp-content-wrap' ).addClass( 'sections-active' );
						$.cookie( 'sections-tab-active', true, { path : '/', expires:1 } );

						// Triggers full refresh
						$( window ).trigger( 'resize' );
						$( '#content-resize-handle' ).hide();

						return false;
					}
				)
			);

			$( '#wp-content-editor-tools .wp-switch-editor' ).click( function() {
				// This fixes an occasional tab switching glitch
				var $$ = $( this ),
					p = $$.attr( 'id' ).split( '-' );
				$( '#wp-content-wrap' ).addClass( p[1] + '-active' );
			} );

			// When the content sections button is clicked, trigger a window resize to set up the columns
			$( '#wolf-sections-tab' ).click( function() {
				$( window ).resize();
			});
		},

		/**
		 * Tooltip, just for cosmetic
		 */
		toolTip : function () {


			$( '.wolf-section-help' ).tipsy( {
				fade: true,
				live: true,
				gravity: 's'
			} );


		},

		/**
		 * Insert dialog div with section list dropdown
		 */
		insertDialogContent : function () {

			var dialogContainer = '<div id="wolf-dialog-sections" />';

			$( '#wpwrap' ).append( dialogContainer );

			$.post( WolfSectionsAjax.pluginUrl + '/dialog/sections.php' , '', function( response ) {
				
				$( '#wolf-dialog-sections' ).html( response );
				
			} );

		},

		/**
		 * Open dialog with sections list select box
		 */
		openDialog : function () {

			var $this = this,
				$info = $( '#wolf-dialog-sections' );
			
			$info.dialog( {
				'dialogClass' : 'wp-dialog',
				'modal' : true,
				'autoOpen' : false,
				'closeOnEscape' : true,
				'minWidth' : 350,
				'minHeight' : 250,
				'buttons' : {
					
					'Add': function() {
						$this.insertSection();
						$( this ).dialog('close');
					},

					'Cancel': function() {
						$( this ).dialog('close');
					}
				}
			} );

			$( '#wolf-add-section' ).on( 'click', function( event ) {
				event.preventDefault();
				$info.dialog( 'open' );
			} );
		},

		/**
		 * Insert section block in editor
		 */
		insertSection : function () {

			var listContainer = $( '#wolf-sections-list' ),
				select = $( '#wolf-section-select' ),
				sectionId = select.val(),
				sectionTitle = select.find( ':selected' ).data( 'title' );

			// create block div
			$( listContainer ).append( '<div class="wolf-section-block">' + sectionTitle + '<span title="' + WolfSectionsAjax.removeSectionMessage + '" class="wolf-section-remove"><input type="hidden" name="wolf-sections[]" value="' + sectionId + '"></div>' );


		},


		/**
		 * Remove section block from editor
		 */
		removeSection : function () {

			$( '#wolf-sections-list' ).on( 'click', '.wolf-section-remove', function() {

				var sectionBlock = $( this ).parent();

				sectionBlock.slideUp( 'normal', function() {

					sectionBlock.remove();

				} );

			} );

		},


		/**
		 * Re-order section block
		 */
		sortable : function () {

			$( '#wolf-sections-list' ).sortable( {
				
				helper: function( e, ui ) {
					ui.children().each( function() {
						$( this ).width( $( this ).width() );
						$( this ).height( $( this ).height() );
					} );
					return ui;
				},
				
				placeholder: 'state-highlight',
				opacity : 0.6,
				accept : 'state-default',
				update: function() {}
			} );

			$( '#wolf-sections-list' ).disableSelection();
		}
	};

}( jQuery );


;( function( $ ) {

	'use strict';
	WolfSectionsAdminEditorPanel.init();

	$( window ).load( function() {


	} );
	
} )( jQuery );