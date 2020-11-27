ve.ui.BSInsertPdfNoExportTool = function VeUiBSInsertNoExportTool() {
	ve.ui.BSInsertPdfNoExportTool.super.apply( this, arguments );
};

OO.inheritClass( ve.ui.BSInsertPdfNoExportTool, ve.ui.FragmentInspectorTool );

ve.ui.BSInsertPdfNoExportTool.static.name = 'bsInsertPdfNoExportTool';
ve.ui.BSInsertPdfNoExportTool.static.group = 'meta';
ve.ui.BSInsertPdfNoExportTool.static.icon = 'pdfnoexport';
ve.ui.BSInsertPdfNoExportTool.static.deactivateOnSelect = true;
ve.ui.BSInsertPdfNoExportTool.static.title = OO.ui.deferMsg(
	'bs-universalexport-insert-noexport-title'
);

ve.ui.BSInsertPdfNoExportTool.static.commandName = 'pdfNoExportCommand';
ve.ui.toolFactory.register( ve.ui.BSInsertPdfNoExportTool );
