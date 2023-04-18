<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\UniversalExport\ExportDialogPluginFactory;
use BlueSpice\UniversalExport\ExportSpecificationFactory;
use BlueSpice\UniversalExport\ModuleFactory;
use MediaWiki\MediaWikiServices;

// PHP unit does not understand code coverage for this file
// as the @covers annotation cannot cover a specific file
// This is fully tested in ServiceWiringTest.php
// @codeCoverageIgnoreStart

return [

	'BSUniversalExportModuleFactory' => static function ( MediaWikiServices $services ) {
		$moduleRegistry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceUniversalExportModuleRegistry'
		);
		return new ModuleFactory(
			$moduleRegistry,
			$services,
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$services->getHookContainer(),
			$services->getSpecialPageFactory(),
			RequestContext::getMain()->getRequest()
		);
	},

	'BSUniversalExportSpecificationFactory' => static function ( MediaWikiServices $services ) {
		return new ExportSpecificationFactory(
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$services->getPageProps()
		);
	},

	'BSUniversalExportDialogPluginFactory' => static function ( MediaWikiServices $services ) {
		return new ExportDialogPluginFactory(
			$services->getService( 'BSUniversalExportModuleFactory' ),
			$services->getService( 'ObjectFactory' ),
			$services->getService( 'PermissionManager' ),
			RequestContext::getMain()
		);
	}
];

// @codeCoverageIgnoreEnd
