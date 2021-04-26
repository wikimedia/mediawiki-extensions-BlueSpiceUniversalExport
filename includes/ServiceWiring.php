<?php

use BlueSpice\UniversalExport\ExportSpecificationFactory;
use BlueSpice\UniversalExport\Util;
use MediaWiki\MediaWikiServices;
use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\UniversalExport\ModuleFactory;

return [
	'BSUniversalExportUtils' => function ( MediaWikiServices $services ) {
		return new Util();
	},

	'BSUniversalExportModuleFactory' => function ( MediaWikiServices $services ) {
		$moduleRegistry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceUniversalExportModuleRegistry'
		);
		return new ModuleFactory(
			$moduleRegistry,
			$services,
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSUniversalExportSpecificationFactory' => function ( MediaWikiServices $services ) {
		return new ExportSpecificationFactory(
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$services->getService( 'BSUtilityFactory' )
		);
	}
];
