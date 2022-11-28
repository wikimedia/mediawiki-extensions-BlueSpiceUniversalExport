bs.ue.ui.dialog.ExportDialog = function( config ) {
	this.config = config || {};
	this.config.title = config.title || {};
	this.config.callback = config.callback || {};
	this.config.callback.scope = config.callback.scope || {};

	this.plugins = [];
	this.activePlugin = {};
	this.modulePanels = [];

	for ( var pluginId in bs.ue.registry.Plugin.registry ) {
		if ( !bs.ue.registry.Plugin.registry.hasOwnProperty( pluginId ) ) {
			continue;
		}

		this.plugins.push( new bs.ue.registry.Plugin.registry[ pluginId ]( config ) );
	}

	this.plugins.sort( function( plugin1, plugin2 ) {
		var pos1 = plugin1.getFavoritePosition();
		var pos2 = plugin2.getFavoritePosition();
		if ( pos1 < pos2 ) {
			return -1;
		} else if ( pos1 > pos2 ) {
			return 1;
		}
		return 0;
	} );

	bs.ue.ui.dialog.ExportDialog.super.call( this, this.config );
};

OO.inheritClass( bs.ue.ui.dialog.ExportDialog, OO.ui.ProcessDialog );

bs.ue.ui.dialog.ExportDialog.static.name = 'UniversalExport.dialog.ExportDialog';

bs.ue.ui.dialog.ExportDialog.static.title = mw.message(
	'bs-ue-export-dialog-title'
).text();

bs.ue.ui.dialog.ExportDialog.static.size = 'large';

bs.ue.ui.dialog.ExportDialog.static.actions = [
	{
		action: 'submit',
		label: mw.message( 'bs-ue-export-dialog-action-submit' ).text(),
		flags: [ 'primary', 'progressive' ]
	},
	{
		action: 'cancel',
		label: mw.message( 'bs-ue-export-dialog-action-cancel' ).text(),
		flags: [ 'safe', 'close' ]
	}
];

bs.ue.ui.dialog.ExportDialog.prototype.initialize = function() {
	bs.ue.ui.dialog.ExportDialog.super.prototype.initialize.call( this );

	var moduleSelectPanel = new OO.ui.PanelLayout( {
		expanded: false,
		framed: false,
		padded: true,
		$content: ''
	} );

	/** Build selection and panels */
	var moduleSelectItems = [];
	for ( var index = 0; index < this.plugins.length; index++ ) {
		var curPlugin = this.plugins[index];

		moduleSelectItems.push(
			{
				label: curPlugin.getLabel(),
				data: index
			}
		);

		this.modulePanels.push( curPlugin.getPanel() );
		this.modulePanels[index].toggle();

		if ( index === 0 ) {
			this.modulePanels[index].toggle();
			this.activePlugin = index;
			this.activePanel = this.modulePanels[index];
		}

	}

	/** Add modules to dropdown menu */
	var disabledState = true;
	if ( moduleSelectItems.length > 1 ) {
		disabledState = false;
	}

	this.moduleSelect = new OO.ui.DropdownInputWidget( {
		options: moduleSelectItems,
		$overlay: true,
		disabled: disabledState
	} );

	if ( moduleSelectItems.length > 0 ) {
		this.moduleSelect.setValue( moduleSelectItems[0].data );
	}

	this.moduleSelect.on( 'change', this.onChangeModule.bind( this ) );

	/** Build fieldset */
	fieldset = new OO.ui.FieldsetLayout();
	fieldset.addItems( [
		new OO.ui.FieldLayout(
			this.moduleSelect,
			{
				align: 'left',
				label:  mw.message( 'bs-ue-export-dialog-label-select-module' ).text()
			}
		)
	] );

	/** Append fieldset */
	moduleSelectPanel.$element.append( fieldset.$element );
	moduleSelectPanel.$element.append( $( '<hr />' ) );

	/** Append panels */
	for( var index = 0; index < this.modulePanels.length; index++ ) {
		moduleSelectPanel.$element.append( this.modulePanels[index].$element );
	}

	this.$body.append( moduleSelectPanel.$element );
};

bs.ue.ui.dialog.ExportDialog.prototype.onChangeModule = function () {
	/* hide current panel */
	this.modulePanels[this.activePlugin].toggle();

	/* set and show new panel */
	this.activePlugin = this.moduleSelect.getValue();
	this.modulePanels[this.activePlugin].toggle();
	this.updateSize();
}

bs.ue.ui.dialog.ExportDialog.prototype.getActionProcess = function ( action ) {
	var dialog = this;
	if ( action === 'cancel' ) {
		return new OO.ui.Process( function () {
			dialog.close( { action: action } );
		} );
	}

	if ( ( action === 'submit' ) && this.config.callback.hasOwnProperty( 'submit' ) ) {
		var params = this.plugins[this.activePlugin].getParams();
		var queryParams = {};
		for ( var name in params ) {
			if ( name === 'title' ) {
				this.config.title = params[name];
				continue;
			}
			queryParams['ue[' + name + ']'] = params[name];
		}

		let additionalParams = this.getAdditonalParams();
		for ( let index = 0; index < additionalParams.length; index++ ) {
			let additionalParam = additionalParams[index];
			let key = additionalParam[0];
			let value = additionalParam[1];
			if ( key === 'title' ) {
				continue;
			}
			queryParams[key] = value;
		}

		this.config.callback.submit.call(
			this.config.callback.scope,
			this.config.title,
			queryParams
		);

		return new OO.ui.Process( function () {
			dialog.close( { action: action } );
		} );
	}

	return bs.ue.ui.dialog.ExportDialog.super.prototype.getActionProcess.call( this, action );
}

bs.ue.ui.dialog.ExportDialog.prototype.getAdditonalParams = function () {
	let search = window.location.search;
	search = search.substr( 1 );
	let searchParams = search.split( '&' );
	let locationParams = [];
	for ( let index = 0; index < searchParams.length; index++ ){
		let part = searchParams[index];
		let partValues = part.split( '=' );
		locationParams.push( partValues );
	}
	return locationParams;
}