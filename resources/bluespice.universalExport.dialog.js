( function( mw, $, d, undefined ){

	$( d ).on( 'click', '#bs-ue-export-dialog-open, .bs-ue-export-dialog-open', function( e ) {
		e.preventDefault();

		me = this;
		this.config = {};
		this.config.title = mw.config.get( 'wgPageName' );
		this.config.callback = {};
		this.config.callback.scope = this;
		this.config.callback.submit = function ( title, params ) {
			var exportUrl = mw.util.getUrl(
				'Special:UniversalExport/' + title,
				params
			);
			window.open( exportUrl );
		}

		var rlModules = mw.config.get( 'bsgUEExportDialogPluginRLModules' );

		mw.loader.using( rlModules ).done( function() {
			var windowManager = OO.ui.getWindowManager();
			var dialog = new bs.ue.ui.dialog.ExportDialog( me.config );

			windowManager.addWindows( [ dialog ] );
			windowManager.openWindow( dialog );
		} );

		return false;
	} );

} )( mediaWiki, jQuery, document );
