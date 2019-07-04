bs.util.registerNamespace( 'bs.unvrslxprt.util.tag' );

bs.unvrslxprt.util.tag.PdfPageBreakDefinition = function BsVecUtilTagPdfPageBreakDefinition() {
	bs.unvrslxprt.util.tag.PdfPageBreakDefinition.super.call( this );
};

OO.inheritClass( bs.unvrslxprt.util.tag.PdfPageBreakDefinition, bs.vec.util.tag.Definition );

bs.unvrslxprt.util.tag.PdfPageBreakDefinition.prototype.getCfg = function() {
	var cfg = bs.unvrslxprt.util.tag.PdfPageBreakDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'PdfPageBreak',
		name: 'pdfPageBreak',
		tagname: 'bs:uepagebreak',
		menuItemMsg: 'bs-universalexport-tag-pagebreak-title',
		descriptionMsg: 'bs-universalexport-tag-pagebreak-desc'
	});
};

bs.vec.registerTagDefinition(
	new bs.unvrslxprt.util.tag.PdfPageBreakDefinition()
);
