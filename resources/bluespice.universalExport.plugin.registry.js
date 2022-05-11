bs = bs || {};
bs.ue = bs.ue || {};
bs.ue.ui = bs.ue.ui || {};
bs.ue.ui.dialog = bs.ue.ui.dialog || {};
bs.ue.ui.plugin = bs.ue.ui.plugin || {};
bs.ue.registry = bs.ue.registry || {};

bs.ue.ui.plugin.Registry = function () {
	bs.ue.ui.plugin.Registry.parent.call( this );
};

OO.inheritClass( bs.ue.ui.plugin.Registry, OO.Registry );

bs.ue.registry.Plugin = new bs.ue.ui.plugin.Registry();