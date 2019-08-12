<?php
/**
 * Renders the UniversalExport special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>

 * @package    BlueSpiceUniversalExport
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use BlueSpice\Services;
use BlueSpice\UniversalExport\LegacyArrayDescriptor;
use BlueSpice\UniversalExport\IExportTarget;

/**
 * UniversalExport special page class.
 * @package BlueSpiceUniversalExport
 */
class SpecialUniversalExport extends \BlueSpice\SpecialPage {

	// MW Globals
	/**
	 *
	 * @var OutputPage
	 */
	public $oOutputPage = null;

	// UniversalExport
	/**
	 * array( 'ModuleKey' => $oModuleObjectImplementsBsUniversalExportModule, ... )
	 * @var array(
	 */
	public $aModules = [];

	/**
	 *
	 * @var array
	 */
	public $aParams = [];

	/**
	 *
	 * @var array
	 */
	public $aMetadata = [];

	/**
	 *
	 * @var Title
	 */
	public $oRequestedTitle = null;

	/**
	 *
	 * @var array
	 */
	public $aCategoryWhitelist = [];

	/**
	 *
	 * @var array
	 */
	public $aCategoryBlacklist = [];

	/**
	 * The default contructor of the SpecialUniversalExport class
	 */
	function  __construct() {
		parent::__construct( 'UniversalExport', 'read', true );

		$this->oOutputPage = $this->getOutput();

		// Set up default parameters and metadata
		$this->aParams = $this->getConfig()->get(
			'UniversalExportParamsDefaults'
		);

		$webrootPath = str_replace( '\\', '/', $GLOBALS['IP'] );
		if ( !empty( $this->getConfig()->get( 'ScriptPath' ) ) ) {
			$parts = explode( '/', $webrootPath );
			if ( "/" . array_pop( $parts ) === $this->getConfig()->get( 'ScriptPath' ) ) {
				$webrootPath = implode( '/', $parts );
			}
		}
		$this->aParams['webroot-filesystempath'] = $webrootPath;
		$this->aMetadata = FormatJson::decode(
			$this->getConfig()->get( 'UniversalExportMetadataDefaults' ),
			true
		);

		// Set up Black- and Whitelists
		$this->aCategoryWhitelist = $this->getConfig()->get(
			'UniversalExportCategoryWhitelist'
		);
		$this->aCategoryBlacklist = $this->getConfig()->get(
			'UniversalExportCategoryBlacklist'
		);
	}

	/**
	 * This method gets called by the MediaWiki framework on page display.
	 * @param string $sParameter
	 */
	function execute( $sParameter ) {
		parent::execute( $sParameter );
		Hooks::run( 'BSUniversalExportSpecialPageExecute', [ $this, $sParameter, &$this->aModules ] );

		if ( !empty( $sParameter ) ) {
			$this->processParameter( $sParameter );
		} else {
			$this->outputInformation();
		}
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

			$propHelper = Services::getInstance()->getBSUtilityFactory()
			->getPagePropHelper( $this->oRequestedTitle->getArticleID() );

			// Get relevant page props
			if ( $propHelper->getPageProp( 'bs-universalexport-params' ) ) {
				$prop = FormatJson::decode(
					$propHelper->getPageProp( 'bs-universalexport-params' ),
					true
				);
				if ( is_array( $res ) ) {
					$this->aParams = array_merge(
						$this->aParams,
						$prop
					);
				}
			}

			BsUniversalExportHelper::getParamsFromQueryString( $this->aParams );

			// Title::userCan always returns false on special pages (exept for createaccount action)
			if ( $this->oRequestedTitle->getNamespace() === NS_SPECIAL ) {
				if ( $this->getUser()->isAllowed( 'read' ) !== true ) {
					throw new Exception( 'bs-universalexport-error-permission' );
				}
			} elseif ( $this->oRequestedTitle->userCan( 'read' ) === false ) {
				throw new Exception( 'bs-universalexport-error-permission' );
			}

			// TODO RBV (24.01.11 17:37): array_intersect(), may be better?
			$aCategoryNames = BsUniversalExportHelper::getCategoriesForTitle( $this->oRequestedTitle );
			foreach ( $aCategoryNames as $sCategoryName ) {
				if ( in_array( $sCategoryName, $this->aCategoryBlacklist ) ) {
					throw new Exception( 'bs-universalexport-error-requested-title-in-category-blacklist' );
				}
			}

			BsUniversalExportHelper::checkPermissionForTitle( $this->oRequestedTitle, $this->aParams ); // Throws Exception

			$sModuleKey = $this->aParams['module'];
			if ( !isset( $this->aModules[ $sModuleKey ] )
				|| !( $this->aModules[ $sModuleKey ] instanceof BsUniversalExportModule ) ) {
				throw new Exception( 'bs-universalexport-error-requested-export-module-not-found' );
			}

			$oExportModule = $this->aModules[ $sModuleKey ];
			$aFile = $oExportModule->createExportFile( $this );

			$this->invokeExportTarget( $aFile );
		}
		catch ( Exception $oException ) {
			// Display Exception-Message and Stacktrace
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

		if ( empty( $this->aModules ) ) {
			$this->oOutputPage->addHtml( wfMessage( 'bs-universalexport-page-text-without-param-no-modules-registered' )->text() );
			return;
		}

		foreach ( $this->aModules as $sKey => $oModule ) {
			if ( $oModule instanceof BsUniversalExportModule ) {
				$oModuleOverview = $oModule->getOverview();
				$this->oOutputPage->addHtml( $oModuleOverview->execute() );
			} else {
				wfDebugLog( 'BS::UniversalExport', 'SpecialUniversalExport::outputInformation: Invalid view.' );
			}
		}
	}

	private function invokeExportTarget( $aFile ) {
		$descriptor = new LegacyArrayDescriptor( $aFile );

		$targetKey = 'download';
		if ( isset( $this->aParams['target'] ) ) {
			$targetKey = $this->aParams['target'];
		}

		$registryAttribute =
			ExtensionRegistry::getInstance()->getAttribute(
				'BlueSpiceUniversalExportExportTargetRegistry'
			);

		if ( !isset( $registryAttribute[$targetKey] ) ) {
			throw new Exception( 'bs-universalexport-error-target-invalid' );
		}

		if ( !is_callable( $registryAttribute[$targetKey] ) ) {
			throw new Exception( 'bs-universalexport-error-target-factory-not-callable' );
		}

		$target = call_user_func_array(
			$registryAttribute[$targetKey],
			[
				$this->aParams,
				$this->getContext(),
				$this->getConfig()
			]
		);

		if ( $target instanceof IExportTarget === false ) {
			throw new Exception( 'bs-universalexport-error-target-invalid' );
		}

		$status = $target->execute( $descriptor );

		if ( !$status->isOK() ) {
			throw new Exception( 'bs-universalexport-error-target-failed' );
		}
	}

}
