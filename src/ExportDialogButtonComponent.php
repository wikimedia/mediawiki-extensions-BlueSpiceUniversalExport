<?php

namespace BlueSpice\UniversalExport;

use ExtensionRegistry;
use IContextSource;
use MediaWiki\MediaWikiServices;
use Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLink;

class ExportDialogButtonComponent extends SimpleLink {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct( [
			'id' => 'bs-ue-exprt-dlg-open',
			'role' => 'button',
			'classes' => [ 'ico-btn', 'bi-file-earmark', 'bs-ue-export-dialog-open' ],
			'href' => '',
			'title' => Message::newFromKey( 'bs-ue-export-dialog-button-title' ),
			'aria-label' => Message::newFromKey( 'bs-ue-export-dialog-button-text' ),
			'rel' => 'nofollow'
		] );
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		if ( !$context->getTitle() ) {
			return false;
		}
		$services = MediaWikiServices::getInstance();
		$objectFactory = $services->getObjectFactory();
		$permissionManager = $services->getService( 'PermissionManager' );
		$moduleFactory = $services->getService( 'BSUniversalExportModuleFactory' );

		$extensionRegistry = ExtensionRegistry::getInstance();
		$exportDialogPluginRegistry = $extensionRegistry->getAttribute(
			'BlueSpiceUniversalExportExportDialogPluginRegistry'
		);

		$plugins = [];
		foreach ( $moduleFactory->getModules() as $name => $module ) {
			$requiredPermission = $module->getExportPermission();
			if ( $requiredPermission !== null && !$permissionManager->userCan(
				$requiredPermission, $context->getUser(), $context->getTitle() )
				) {
				continue;
			}

			if ( !isset( $exportDialogPluginRegistry[$name] ) ) {
				continue;
			}

			$specs = $exportDialogPluginRegistry[$name];
			$plugin = $objectFactory->createObject( $specs );
			if ( $plugin instanceof IExportDialogPlugin === false ) {
				continue;
			}

			if ( $plugin->skip( $context ) ) {
				continue;
			}

			$plugins[] = $name;
		}

		if ( !empty( $plugins ) ) {
			return true;
		}

		return false;
	}

}
