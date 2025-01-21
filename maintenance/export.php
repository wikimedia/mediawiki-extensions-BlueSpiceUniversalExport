<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

$IP = dirname( dirname( dirname( __DIR__ ) ) );

require_once "$IP/maintenance/Maintenance.php";

/**
 * Perform a single page export
 *
 * @ingroup Maintenance
 */
class BSUniversalExportPageExport extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( "Perform an export of selected pages" );

		$this->requireExtension( "BlueSpiceUniversalExport" );
		$this->addOption(
			'specification-file', 'Json file containing the specification for the export',
			true, true, 'spec'
		);
	}

	public function execute() {
		try {
			$this->doExport();
		} catch ( Exception $ex ) {
			$this->error( $ex->getMessage() . PHP_EOL );
		}
	}

	/**
	 * @throws Exception
	 */
	public function doExport() {
		$file = realpath( trim( $this->getOption( 'specification-file' ) ) );
		if ( !file_exists( $file ) ) {
			throw new Exception( "Cannot open $file" );
		}

		$content = file_get_contents( $file );
		if ( $content === false ) {
			throw new Exception( "Source file $file cannot be read!" );
		}
		$parsed = FormatJson::decode( $content, 1 );
		if ( !is_array( $parsed ) ) {
			throw new Exception( "Specification file contents are not a valid JSON!" );
		}

		$specs = $this->makeSpecsFromArray( $parsed );
		/** @var \BlueSpice\UniversalExport\ExportModule $module */
		$module = $this->getModuleFactory()->newFromName( $specs->getParam( 'module', null ) );
		if ( $module === null ) {
			throw new Exception(
				'bs-universalexport-error-requested-export-module-not-found'
			);
		}

		$aFile = $module->createExportFile( $specs );
		$module->invokeExportTarget( $aFile, $specs );
	}

	/**
	 * @param array $specData
	 * @return \BlueSpice\UniversalExport\ExportSpecification
	 * @throws MWException
	 */
	private function makeSpecsFromArray( array $specData ) {
		$title = Title::newFromText( $specData['title'] );
		if ( !$title instanceof Title ) {
			throw new Exception( "Cannot create title " . $specData['title'] );
		}
		unset( $specData['title'] );
		$user = $this->setUser();

		return MediaWikiServices::getInstance()->getService(
			'BSUniversalExportSpecificationFactory'
		)->newSpecification( $title, $user, $specData );
	}

	/**
	 * @return \BlueSpice\UniversalExport\ModuleFactory
	 */
	private function getModuleFactory() {
		return MediaWikiServices::getInstance()->getService( 'BSUniversalExportModuleFactory' );
	}

	/**
	 * @return User
	 * @throws MWException
	 */
	private function setUser() {
		/** @var \BlueSpice\UtilityFactory $utilFactory */
		$utilFactory = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' );
		$user = $utilFactory->getMaintenanceUser()->getUser();
		// Apparently needed for some API call
		RequestContext::getMain()->setUser( $user );
		$GLOBALS['wgUser'] = $user;

		return $user;
	}

}

$maintClass = BSUniversalExportPageExport::class;
require_once RUN_MAINTENANCE_IF_MAIN;
