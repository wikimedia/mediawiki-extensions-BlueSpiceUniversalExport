<?php
/**
 * UniversalExport extension for BlueSpice
 *
 * Enables MediaWiki to export pages into different formats.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @package    BlueSpiceUniversalExport
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * Base class for UniversalExport extension
 * @package BlueSpiceUniversalExport
 */
class UniversalExport extends BsExtensionMW {

	protected $aCategoryWhitelist = array( 'Exportable',   'Public' );
	protected $aCategoryBlacklist = array( 'Confidential', 'Internal' );

	//These values may be overridden by application logic. I.e. by query string parameter or tag
	protected $aParamsDefaults = array(
		'format'      => 'pdf',
		'pdf-merging' => '0',
		'attachments' => '0',
		'recursive'   => false,
		'filter'      => 'use-all'
	);

	//These values override values found by the application logic.
	protected $aParamsOverrides = array(
		'use-serverside-caching'  => false,
		'pdf-attachment-formats'  => array( 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'odg', 'svg' ) //Not yet supported
	);
	/**
	 *  Initialization of UniversalExport extension
	 */
	protected function initExt() {
		//Hooks
		$this->setHook( 'ParserFirstCallInit', 'onParserFirstCallInit' );
		$this->setHook( 'BSWidgetListHelperInitKeyWords' );
		$this->setHook( 'BSStateBarAddSortBodyVars', 'onStatebarAddSortBodyVars' );
		$this->setHook( 'BSStateBarBeforeBodyViewAdd' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSUsageTrackerRegisterCollectors' );

		global $bsgBlueSpiceExtInfo;
		//Configuration variables
		$aMetadataDefaults = array(
			'creator' => 'Hallo Welt! GmbH',
		);
		$aMetadataOverrides = array(
			'producer' => 'UniversalExport '.$bsgBlueSpiceExtInfo['version'].' (BlueSpice MediaWiki)'
		);

		BsConfig::registerVar( 'MW::UniversalExport::CategoryWhitelist', $this->aCategoryWhitelist,  BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_STRING );
		BsConfig::registerVar( 'MW::UniversalExport::CategoryBlacklist', $this->aCategoryBlacklist,  BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_STRING );
		BsConfig::registerVar( 'MW::UniversalExport::MetadataDefaults',  json_encode( $aMetadataDefaults ),  BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-universalexport-pref-metadatadefaults', 'textarea' );
		BsConfig::registerVar( 'MW::UniversalExport::MetadataOverrides', json_encode( $aMetadataOverrides ), BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-universalexport-pref-metadataoverrides', 'textarea' );
		BsConfig::registerVar( 'MW::UniversalExport::ParamsDefaults',    $this->aParamsDefaults,     BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_MIXED );
		BsConfig::registerVar( 'MW::UniversalExport::ParamsOverrides',   $this->aParamsOverrides,    BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_MIXED );
	}

	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		//MetadataDefaults and MetadataOverrides
		$aPrefs = array( 'rows' => 5 );

		return $aPrefs;
	}

	public function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$out->addModuleStyles( 'ext.bluespice.universalExport.css' );
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortBodyVars'
	 * @param array $aSortBodyVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortBodyVars( &$aSortBodyVars ) {
		$aSortBodyVars['statebarbodyuniversalexportmeta']   = wfMessage( 'bs-universalexport-statebarbodyuniversalexportmeta' )->plain();
		$aSortBodyVars['statebarbodyuniversalexportparams'] = wfMessage( 'bs-universalexport-statebarbodyuniversalexportparams' )->plain();
		return true;
	}

	/**
	 * Hook handler that adds information to the StateBar body
	 * @param StateBar $oStatebar
	 * @param array $aBodyViews
	 * @param User $oUser
	 * @param Title $oTitle
	 * @return boolean Always true to keep the hook running
	 */
	public function onBSStateBarBeforeBodyViewAdd( $oStatebar, &$aBodyViews, $oUser, $oTitle ) {
		$oMeta   = BsArticleHelper::getInstance($oTitle)->getJSONPageProp( 'bs-universalexport-meta' );
		$oParams = BsArticleHelper::getInstance($oTitle)->getJSONPageProp( 'bs-universalexport-params' );

		if( $oMeta instanceof stdClass ) {
			$aBodyViews['statebarbodyuniversalexportmeta'] =
				BsUniversalExportTagLibrary::makeStateBarBodyElementKeyValueTable(
					array(
						'rows'    => (array)$oMeta,
						'heading' => wfMessage('bs-universalexport-statebarbodyuniversalexportmeta')->plain(),
						'key'     => 'statebarbodyuniversalexportmeta'
					)
				);
		}

		if( $oParams instanceof stdClass ) {
			$aBodyViews['statebarbodyuniversalexportparams'] =
				BsUniversalExportTagLibrary::makeStateBarBodyElementKeyValueTable(
					array(
						'rows'    => (array)$oParams,
						'heading' => wfMessage('bs-universalexport-statebarbodyuniversalexportparams')->plain(),
						'key'     => 'statebarbodyuniversalexportparams'
					)
				);
		}

		return true;
	}

	/**
	 * Renders widget view of InfoBox. Called by MW::WidgetBar::DefaultWidgets.
	 * @param BsEvent $oEvent The Event object
	 * @param array $aWidgets An array of widgets. Add your Widget to this array.
	 * @return bool allow other hooked methods to be executed. always true
	 * @deprecated in 1.1.1
	 */
	public function onDefaultWidgets( $oEvent, $aWidgets ) {
		$oWidget = $this->getWidget();
		if( $oWidget !== null ) {
			$aWidgets['UNIVERSALEXPORT'] = $oWidget;
		}
		return $aWidgets;
	}

	/**
	 * Hook-Handler for 'MW::Utility::WidgetListHelper::InitKeywords'. Registers a callback for the UNIVERSALEXPORT Keyword.
	 * @param array $aKeywords
	 * @param Title $oTitle
	 * @return boolean Always true to keep the hook running
	 */
	public function onBSWidgetListHelperInitKeyWords( &$aKeywords, $oTitle ) {
		$aKeywords['UNIVERSALEXPORT'] = array( $this, 'onWidgetListKeyword' );
		return true;
	}

	/**
	 * Creates a Widget for the UNIVERSALEXPORT Keyword.
	 * @return ViewWidget
	 */
	public function onWidgetListKeyword() {
		return $this->getWidget();
	}

	/**
	 * Hook-Handler for the MediaWiki 'ParserFirstCallInit' hook. Dispatches registration og TagExtensions to the TagLibrary.
	 * @param Parser $oParser The MediaWiki Parser object
	 * @return bool Always true to keep the hook runnning.
	 */
	public function onParserFirstCallInit( &$oParser ) {
		return BsUniversalExportTagLibrary::onParserFirstCallInit( $oParser );
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array $aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		return BsUniversalExportTagLibrary::onBSUsageTrackerRegisterCollectors( $aCollectorsConfig );
	}

	/**
	 * Creates a Widget object
	 * @return ViewWidget
	 */
	public function getWidget() {
		$sAction = $this->getRequest()->getVal( 'action', 'view' );
		if( !in_array( $sAction, array( 'view', 'historysubmit' ) ) ) return null;

		$oCurrentTitle = $this->getTitle();
		if( $oCurrentTitle->quickUserCan( 'read' ) === false ) return null;

		$aCurrentQueryParams = $this->getRequest()->getValues();
		$sTitle = isset($aCurrentQueryParams['title']) ? $aCurrentQueryParams['title'] : "";
		$sSpecialPageParameter = BsCore::sanitize( $sTitle, '', BsPARAMTYPE::STRING );
		$oSpecialPage = SpecialPage::getTitleFor( 'UniversalExport',$sSpecialPageParameter );
		if( isset( $aCurrentQueryParams['title'] ) ) unset( $aCurrentQueryParams['title'] );

		$aModules = array();
		Hooks::run(
			'BSUniversalExportGetWidget',
			array( $this, &$aModules, $oSpecialPage, $oCurrentTitle, $aCurrentQueryParams )
		);

		if( empty( $aModules ) ) return null;

		$sList = '';
		foreach( $aModules as $oModuleView ) {
			if( $oModuleView instanceof ViewBaseElement ) {
				$sList .= $oModuleView->execute();
			}
			else {
				wfDebugLog( 'BS::UniversalExport', 'getWidget: Invalid view.' );
			}
		}

		$oWidgetView = new ViewWidget();
		$oWidgetView->setId( 'universalexport' )
					->setTitle( wfMessage( 'bs-universalexport-widget-title' )->plain() )
					->setBody( $sList )
					->setTooltip( wfMessage( 'bs-universalexport-widget-tooltip' )->plain() );

		return $oWidgetView;
	}

	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'tags' ) return true;

		$oResponse->result[] = array(
			'id' => 'bs:uemeta',
			'type' => 'tag',
			'name' => 'uemeta',
			'desc' => wfMessage( 'bs-universalexport-tag-meta-desc' )->plain(),
			'code' => '<bs:uemeta someMeta="Some Value" />',
			'examples' => array(
				array(
					'code' => '<bs:uemeta department="IT" security="high" />'
				)
			),
			'helplink' => 'https://help.bluespice.com/index.php/UniversalExport'
		);

		$oResponse->result[] = array(
			'id' => 'bs:ueparams',
			'type' => 'tag',
			'name' => 'ueparams',
			'desc' => wfMessage( 'bs-universalexport-tag-params-desc' )->plain(),
			'code' => '<bs:ueparams someParam="Some Value" />',
			'examples' => array(
				array(
					'code' => '<bs:ueparams template="BlueSpice Landscape" />'
				)
			),
			'helplink' => 'https://help.bluespice.com/index.php/UniversalExport'
		);

		$oResponse->result[] = array(
			'id' => 'bs:uepagebreak',
			'type' => 'tag',
			'name' => 'uepagebreak',
			'desc' => wfMessage( 'bs-universalexport-tag-pagebreak-desc' )->plain(),
			'code' => '<bs:uepagebreak />',
			'helplink' => 'https://help.bluespice.com/index.php/UniversalExport'
		);

		$oResponse->result[] = array(
			'id' => 'bs:uenoexport',
			'type' => 'tag',
			'name' => 'uenoexport',
			'desc' => wfMessage( 'bs-universalexport-tag-noexport-desc' )->plain(),
			'code' => '<bs:uenoexport>Not included in export</bs:uenoexport>',
			'examples' => array(
				array(
					'code' => '<bs:uenoexport>Not included in export</bs:uenoexport>'
				)
			),
			'helplink' => 'https://help.bluespice.com/index.php/UniversalExport'
		);

		return true;
	}
}
