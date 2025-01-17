<?php
/**
 * Renders the UniversalExport special page.
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

use BlueSpice\UniversalExport\ExportSpecification;
use BlueSpice\UniversalExport\IExportModule;
use BlueSpice\UniversalExport\ModuleFactory;
use MediaWiki\Title\Title;

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
	 * @var array
	 */
	public $aModules = [];

	/**
	 * The default contructor of the SpecialUniversalExport class
	 */
	public function __construct() {
		parent::__construct( 'UniversalExport', 'read', true );

		$this->oOutputPage = $this->getOutput();
	}

	/**
	 * This method gets called by the MediaWiki framework on page display.
	 * @param string $sParameter
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		if ( !empty( $sParameter ) ) {
			$this->processParameter( $sParameter );
		} else {
			$this->outputInformation();
		}
	}

	/**
	 *
	 * @return ModuleFactory
	 */
	private function getModuleFactory() {
		return $this->services->getService( 'BSUniversalExportModuleFactory' );
	}

	/**
	 * Dispatched from execute();
	 * @param string $sParameter
	 */
	private function processParameter( $sParameter ) {
		try {
			$requestedTitle = Title::newFromText( $sParameter );

			BsUniversalExportHelper::assertPermissionsForTitle( $requestedTitle, $this->getUser() );

			$params = [];
			BsUniversalExportHelper::getParamsFromQueryString( $params );
			/** @var ExportSpecification $specs */
			$specs = $this->services->getService( 'BSUniversalExportSpecificationFactory' )
				->newSpecification( $requestedTitle, $this->getUser(), $params );

			$categories = BsUniversalExportHelper::getCategoriesForTitle( $requestedTitle );
			if ( !empty( array_intersect( $specs->getCategoryBlacklist(), $categories ) ) ) {
				throw new Exception(
					'bs-universalexport-error-requested-title-in-category-blacklist'
				);
			}

			$module = $this->getModuleFactory()->newFromName( $specs->getParam( 'module', null ) );
			if ( $module === null ) {
				throw new Exception(
					'bs-universalexport-error-requested-export-module-not-found'
				);
			}

			$aFile = $module->createExportFile( $specs );
			$module->invokeExportTarget( $aFile, $specs );
		} catch ( Exception $oException ) {
			// Display Exception-Message
			$this->oOutputPage->setPageTitle(
				wfMessage( 'bs-universalexport-page-title-on-error' )->text()
			);
			if ( $oException instanceof ErrorPageError ) {
				// Somehow, when message object is parsed in the Error class itself, it does not respect overrides
				$messageObj = $oException->getMessageObject();
			} else {
				$messageObj = Message::newFromKey( $oException->getMessage() );
				if ( !$messageObj->exists() ) {
					$messageObj = new RawMessage( $oException->getMessage() );
				}
			}
			$this->oOutputPage->addHTML( $messageObj->parseAsBlock() );
		}
	}

	/**
	 * Dispatched from execute();
	 */
	private function outputInformation() {
		// TODO RBV (14.12.10 09:59): Display information about WebService availability,
		// configuration settings, etc... Could also be used to monitor Webservice and
		// manually empty cache.
		$this->oOutputPage->setPageTitle(
			wfMessage( 'bs-universalexport-page-title-without-param' )->text()
		);
		$this->oOutputPage->addHtml( wfMessage( 'bs-universalexport-page-text-without-param' )->text() );
		$this->oOutputPage->addHtml( '<hr />' );

		if ( empty( $this->aModules ) ) {
			$this->oOutputPage->addHtml(
				wfMessage( 'bs-universalexport-page-text-without-param-no-modules-registered' )->text()
			);
			return;
		}

		foreach ( $this->aModules as $sKey => $oModule ) {
			if ( $oModule instanceof IExportModule ) {
				$oModuleOverview = $oModule->getOverview();
				$this->oOutputPage->addHtml( $oModuleOverview->execute() );
			} else {
				wfDebugLog( 'BS::UniversalExport', 'SpecialUniversalExport::outputInformation: Invalid view.' );
			}
		}
	}
}
