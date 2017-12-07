<?php
/**
 * Renders the UniversalExport special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>

 * @package    BlueSpiceUniversalExport
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * UniversalExport special page class.
 * @package BlueSpiceUniversalExport
 */
class SpecialUniversalExport extends BsSpecialPage {

	//MW Globals
	/**
	 *
	 * @var OutputPage
	 */
	public $oOutputPage = null;

	//UniversalExport
	/**
	 * array( 'ModuleKey' => $oModuleObjectImplementsBsUniversalExportModule, ... )
	 * @var array(
	 */
	public $aModules = array();

	/**
	 *
	 * @var array
	 */
	public $aParams = array();

	/**
	 *
	 * @var array
	 */
	public $aMetadata = array();

	/**
	 *
	 * @var Title
	 */
	public $oRequestedTitle = null;

	/**
	 *
	 * @var array
	 */
	public $aCategoryWhitelist = array();

	/**
	 *
	 * @var array
	 */
	public $aCategoryBlacklist = array();

	/**
	 * The default contructor of the SpecialUniversalExport class
	 */
	function  __construct() {
		parent::__construct( 'UniversalExport', 'universalexport-export', true );

		$this->oOutputPage = $this->getOutput();

		//Set up default parameters and metadata
		$this->aParams = BsConfig::get( 'MW::UniversalExport::ParamsDefaults' );
		$this->aParams['webroot-filesystempath'] = BsCore::getMediaWikiWebrootPath();
		$this->aMetadata = FormatJson::decode( BsConfig::get( 'MW::UniversalExport::MetadataDefaults' ), true );

		//Set up Black- and Whitelists
		$this->aCategoryWhitelist = BsConfig::get( 'MW::UniversalExport::CategoryWhitelist' );
		$this->aCategoryBlacklist = BsConfig::get( 'MW::UniversalExport::CategoryBlacklist' );
	}

	/**
	 * This method gets called by the MediaWiki framework on page display.
	 * @param string $sParameter
	 */
	function execute( $sParameter ) {
		parent::execute( $sParameter );
		Hooks::run( 'BSUniversalExportSpecialPageExecute', array( $this, $sParameter, &$this->aModules ) );

		if( !empty( $sParameter ) ) {
			$this->processParameter( $sParameter );
		}
		else {
			$this->outputInformation();
		}

		return;
	}

	/**
	 * Dispatched from execute();
	 */
	private function processParameter( $sParameter ) {
		try {
			$this->oRequestedTitle = Title::newFromText( $sParameter );
			/*if( !$this->oRequestedTitle->exists() && $this->oRequestedTitle->getNamespace() != NS_SPECIAL ) { //!$this->mRequestedTitle->isSpecialPage() does not work in MW 1.13
				throw new Exception( 'error-requested-title-does-not-exist' );
			}*/

			//Get relevant page props
			$dbr = wfGetDB( DB_REPLICA );
			$res = $dbr->selectField(
				'page_props',
				'pp_value',
				array(
					'pp_propname' => 'bs-universalexport-params',
					'pp_page'     => $this->oRequestedTitle->getArticleID()
				)
			);
			if( $res != false ) {
				$res = FormatJson::decode( $res, true );
				if( is_array( $res ) ) {
					$this->aParams = array_merge(
						$this->aParams,
						$res
					);
				}
			}

			BsUniversalExportHelper::getParamsFromQueryString( $this->aParams );

			//Title::userCan always returns false on special pages (exept for createaccount action)
			if( $this->oRequestedTitle->getNamespace() === NS_SPECIAL ) {
				if( $this->getUser()->isAllowed('universalexport-export') !== true ) {
					throw new Exception( 'bs-universalexport-error-permission');
				}
			} elseif( $this->oRequestedTitle->userCan( 'universalexport-export' ) === false ) {
				throw new Exception( 'bs-universalexport-error-permission');
			}

			// TODO RBV (24.01.11 17:37): array_intersect(), may be better?
			$aCategoryNames = BsUniversalExportHelper::getCategoriesForTitle( $this->oRequestedTitle );
			foreach( $aCategoryNames as $sCategoryName ) {
				if ( in_array( $sCategoryName, $this->aCategoryBlacklist ) ) {
					throw new Exception( 'bs-universalexport-error-requested-title-in-category-blacklist');
				}
			}

			BsUniversalExportHelper::checkPermissionForTitle( $this->oRequestedTitle, $this->aParams ); //Throws Exception

			$sModuleKey = $this->aParams['module'];
			if( !isset( $this->aModules[ $sModuleKey ] )
				|| !($this->aModules[ $sModuleKey ] instanceof BsUniversalExportModule) ) {
				throw new Exception( 'bs-universalexport-error-requested-export-module-not-found' );
			}

			$oExportModule = $this->aModules[ $sModuleKey ];
			$aFile = $oExportModule->createExportFile( $this );

			$this->returnFile( $aFile );
		}
		catch( Exception $oException ) {
			//Display Exception-Message and Stacktrace
			$this->oOutputPage->setPageTitle( wfMessage( 'bs-universalexport-page-title-on-error' )->text() );
			$oExceptionView = new ViewException( $oException );
			$this->oOutputPage->addHtml( $oExceptionView->execute() );
		}
	}

	/**
	 * Dispatched from execute();
	 */
	private function outputInformation() {
		// TODO RBV (14.12.10 09:59): Display information about WebService availability, configuration settings, etc... Could also be used to monitor Webservice and manually empty cache.
		$this->oOutputPage->setPageTitle( wfMessage( 'bs-universalexport-page-title-without-param' )->text() );
		$this->oOutputPage->addHtml( wfMessage( 'bs-universalexport-page-text-without-param' )->text() );
		$this->oOutputPage->addHtml( '<hr />' );

		if( empty( $this->aModules ) ){
			$this->oOutputPage->addHtml( wfMessage( 'bs-universalexport-page-text-without-param-no-modules-registered' )->text() );
			return;
		}

		foreach( $this->aModules as $sKey => $oModule ) {
			if( $oModule instanceof BsUniversalExportModule ){
				$oModuleOverview = $oModule->getOverview();
				$this->oOutputPage->addHtml( $oModuleOverview->execute() );
			}
			else {
				wfDebugLog( 'BS::UniversalExport', 'SpecialUniversalExport::outputInformation: Invalid view.' );
			}
		}
	}

	// TODO RBV (03.02.11 10:54): Promote to Adapter interface?
	private function returnFile( &$aFile ) {
		$this->oOutputPage->disable();
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Cache-Control: public' );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: '.$aFile['mime-type'] );
		if ( isset( $aFile['disposition'] ) ) { //Maybe use array_merge for default values?
			header( 'Content-Disposition: '.$aFile['disposition'].'; filename="'.$aFile['filename'].'"' );
		} else {
			header( 'Content-Disposition: attachment; filename="'.$aFile['filename'].'"' );
		}
		header( 'Content-Transfer-Encoding: binary' );

		echo $aFile['content'];
	}

	protected function getGroupName() {
		return 'bluespice';
	}
}