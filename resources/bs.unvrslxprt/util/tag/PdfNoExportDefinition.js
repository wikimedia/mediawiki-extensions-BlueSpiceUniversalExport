bs.util.registerNamespace( 'bs.unvrslxprt.util.tag' );

bs.unvrslxprt.util.tag.PdfNoExportDefinition = function BsVecUtilTagPdfNoExportDefinition() {
	bs.unvrslxprt.util.tag.PdfNoExportDefinition.super.call( this );
};

OO.inheritClass( bs.unvrslxprt.util.tag.PdfNoExportDefinition, bs.vec.util.tag.Definition );

bs.unvrslxprt.util.tag.PdfNoExportDefinition.prototype.getCfg = function() {
	var cfg = bs.unvrslxprt.util.tag.PdfNoExportDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'PdfNoExport',
		name: 'pdfNoExport',
		tagname: 'bs:uenoexport',
		hideMainInput: false,
		menuItemMsg: 'bs-universalexport-tag-noexport-title',
		descriptionMsg: 'bs-universalexport-tag-noexport-desc'
	});
};

bs.vec.registerTagDefinition(
	new bs.unvrslxprt.util.tag.PdfNoExportDefinition()
);
