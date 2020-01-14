<?php
/**
 * The TagLibrary of the UniversalExport Extension.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 *
 * @package    BlueSpiceUniversalExport
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

/**
 * UniversalExport TagLibrary class.
 * @package BlueSpiceUniversalExport
 */
class BsUniversalExportTagLibrary {
	/**
	 * Hook-Handler for the MediaWiki 'ParserFirstCallInit' hook. Registers
	 * TagExtensions within the Parser.
	 * @param Parser &$oParser The MediaWiki Parser object
	 * @return bool Always true to keep the hook runnning.
	 */
	public static function onParserFirstCallInit( &$oParser ) {
		$oParser->setHook(
			'pdfpagebreak',
			'BsUniversalExportTagLibrary::onPagebreakTag'
		);
		$oParser->setHook(
			'universalexport:pagebreak',
			'BsUniversalExportTagLibrary::onPagebreakTag'
		);
		$oParser->setHook(
			'bs:universalexport:pagebreak',
			'BsUniversalExportTagLibrary::onPagebreakTag'
		);
		$oParser->setHook(
			'bs:uepagebreak',
			'BsUniversalExportTagLibrary::onPagebreakTag'
		);

		$oParser->setHook(
			'nopdf',
			'BsUniversalExportTagLibrary::onExcludeTag'
		);
		$oParser->setHook(
			'universalexport:exclude',
			'BsUniversalExportTagLibrary::onExcludeTag'
		);
		$oParser->setHook(
			'bs:universalexport:exclude',
			'BsUniversalExportTagLibrary::onExcludeTag'
		);
		$oParser->setHook(
			'universalexport:noexport',
			'BsUniversalExportTagLibrary::onExcludeTag'
		);
		$oParser->setHook(
			'bs:universalexport:noexport',
			'BsUniversalExportTagLibrary::onExcludeTag'
		);
		$oParser->setHook(
			'bs:uenoexport',
			'BsUniversalExportTagLibrary::onExcludeTag'
		);

		$oParser->setHook(
			'pdfhidetitle',
			'BsUniversalExportTagLibrary::onHideTitleTag'
		);
		$oParser->setHook(
			'universalexport:hidetitle',
			'BsUniversalExportTagLibrary::onHideTitleTag'
		);
		$oParser->setHook(
			'bs:universalexport:hidetitle',
			'BsUniversalExportTagLibrary::onHideTitleTag'
		);
		$oParser->setHook(
			'bs:uehidetitle',
			'BsUniversalExportTagLibrary::onHideTitleTag'
		);

		$oParser->setHook(
			'pdfexcludepage',
			'BsUniversalExportTagLibrary::onExcludeArticleTag'
		);
		$oParser->setHook(
			'universalexport:excludearticle',
			'BsUniversalExportTagLibrary::onExcludeArticleTag'
		);
		$oParser->setHook(
			'bs:universalexport:excludearticle',
			'BsUniversalExportTagLibrary::onExcludeArticleTag'
		);
		$oParser->setHook(
			'bs:ueexcludearticle',
			'BsUniversalExportTagLibrary::onExcludeArticleTag'
		);

		$oParser->setHook(
			'universalexport:meta',
			'BsUniversalExportTagLibrary::onMetaTag' );
		$oParser->setHook(
			'bs:universalexport:meta',
			'BsUniversalExportTagLibrary::onMetaTag'
		);
		$oParser->setHook(
			'bs:uemeta',
			'BsUniversalExportTagLibrary::onMetaTag'
		);

		$oParser->setHook(
			'universalexport:params',
			'BsUniversalExportTagLibrary::onParamsTag'
		);
		$oParser->setHook(
			'bs:universalexport:params',
			'BsUniversalExportTagLibrary::onParamsTag'
		);
		$oParser->setHook(
			'bs:ueparams',
			'BsUniversalExportTagLibrary::onParamsTag'
		);
		return true;
	}

	/**
	 *
	 * @param string $sContent
	 * @param array $aAttributes
	 * @param Parser $oParser
	 * @return string
	 */
	public static function onPagebreakTag( $sContent, $aAttributes, $oParser ) {
		$oParser->getOutput()->setProperty( 'bs-tag-universalexport-pagebreak', 1 );

		$aOut = [];
		// TODO RBV (08.02.11 11:34): Use CSS class for styling
		$style = "border-top: 2px dotted #999; background-color: #F5F5F5;"
				. "color: #BBB; font-style: italic; text-align: center;";
		$aOut[] = "<div class='bs-universalexport-pagebreak' style='$style'>";
		$aOut[] = wfMessage( 'bs-universalexport-tag-pagebreak-text' )->plain();
		$aOut[] = '</div>';

		return implode( '', $aOut );
	}

	/**
	 *
	 * @param string $sContent
	 * @param array $aAttributes
	 * @param Parser $oParser
	 * @return string
	 */
	public static function onExcludeTag( $sContent, $aAttributes, $oParser ) {
		$oParser->getOutput()->setProperty( 'bs-tag-universalexport-exclude', 1 );

		$aOut = [];

		// TODO RBV (08.02.11 11:34): Use CSS class for styling
		$msg = wfMessage( 'bs-universalexport-tag-exclude-text' )->plain();
		$aOut[] = "<div class='bs-universalexport-exportexclude' title='$msg'>";
		$aOut[] = $oParser->recursiveTagParse( $sContent );
		$aOut[] = '</div>';

		return implode( '', $aOut );
	}

	/**
	 *
	 * @param string $sContent
	 * @param array $aAttributes
	 * @param Parser $oParser
	 * @return string
	 */
	public static function onHideTitleTag( $sContent, $aAttributes, $oParser ) {
		$oParser->getOutput()->setProperty( 'bs-tag-universalexport-hidetitle', 1 );
		$oParser->getOutput()->setProperty(
			'bs-universalexport-hidetitle',
			true
		);

		return '';
	}

	/**
	 *
	 * @param string $sContent
	 * @param array $aAttributes
	 * @param Parser $oParser
	 * @return string
	 */
	public static function onExcludeArticleTag( $sContent, $aAttributes, $oParser ) {
		$oParser->getOutput()->setProperty( 'bs-tag-universalexport-excludearticle', 1 );

		return '';
	}

	/**
	 *
	 * @param string $sContent
	 * @param array $aAttributes
	 * @param Parser $oParser
	 * @return string
	 */
	public static function onMetaTag( $sContent, $aAttributes, $oParser ) {
		$oParser->getOutput()->setProperty( 'bs-tag-universalexport-meta', 1 );
		$oParser->getOutput()->setProperty(
			'bs-universalexport-meta',
			json_encode( $aAttributes )
		);

		$aOut = [];
		$aOut[] = '<div class="bs-universalexport-meta"';
		foreach ( $aAttributes as $sKey => $sValue ) {
			$aOut[] = ' ' . $sKey . '="' . $sValue . '"';
		}
		$aOut[] = '></div>';

		return implode( '', $aOut );
	}

	/**
	 *
	 * @param string $sContent
	 * @param array $aAttributes
	 * @param Parser $oParser
	 * @return string
	 */
	public static function onParamsTag( $sContent, $aAttributes, $oParser ) {
			$oParser->getOutput()->setProperty( 'bs-tag-universalexport-params', 1 );
			$oParser->getOutput()->setProperty(
			'bs-universalexport-params',
			json_encode( $aAttributes )
		);

		$aOut = [];
		$aOut[] = '<div class="bs-universalexport-params"';
		foreach ( $aAttributes as $sKey => $sValue ) {
			$aOut[] = ' ' . $sKey . '="' . $sValue . '"';
		}
		$aOut[] = '></div>';

		return implode( '', $aOut );
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array &$aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public static function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:universalexport:pagebreak'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-pagebreak'
			]
		];
		$aCollectorsConfig['bs:universalexport:exclude'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-exclude'
			]
		];
		$aCollectorsConfig['bs:universalexport:hidetitle'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-hidetitle'
			]
		];
		$aCollectorsConfig['bs:universalexport:excludearticle'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-excludearticle'
			]
		];
		$aCollectorsConfig['bs:universalexport:meta'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-meta'
			]
		];
		$aCollectorsConfig['bs:universalexport:params'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-params'
			]
		];
		return true;
	}
}
