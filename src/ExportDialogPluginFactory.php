<?php

namespace BlueSpice\UniversalExport;

use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class ExportDialogPluginFactory {

	/**
	 * @var ModuleFactory
	 */
	private $moduleFactory = null;

	/**
	 * @var ObjectFactory
	 */
	private $objectFactory = null;

	/**
	 * @var PermissionManager
	 */
	private $permissionManager = null;

	/**
	 * @var IContextSource
	 */
	private $context = null;

	/**
	 * @var array
	 */
	private $pluginRegistry = null;

	/**
	 *
	 * @param ModuleFactory $moduleFactory
	 * @param ObjectFactory $objectFactory
	 * @param PermissionManager $permissionManager
	 * @param IContextSource $context
	 */
	public function __construct( ModuleFactory $moduleFactory, ObjectFactory $objectFactory,
		PermissionManager $permissionManager, IContextSource $context ) {
		$this->moduleFactory = $moduleFactory;
		$this->objectFactory = $objectFactory;
		$this->permissionManager = $permissionManager;
		$this->context = $context;

		$extensionRegistry = ExtensionRegistry::getInstance();
		$this->pluginRegistry = $extensionRegistry->getAttribute(
			'BlueSpiceUniversalExportExportDialogPluginRegistry'
		);
	}

	/**
	 * @return array
	 */
	public function getPlugins(): array {
		$plugins = [];

		foreach ( $this->moduleFactory->getModules() as $name => $module ) {
			$requiredPermission = $module->getExportPermission();
			if ( $requiredPermission !== null ) {
				$userCanExport = $this->userCanExport(
					$requiredPermission,
					$this->context
				);
				if ( !$userCanExport ) {
					continue;
				}
			}

			if ( !isset( $this->pluginRegistry[$name] ) ) {
				continue;
			}

			$specs = $this->pluginRegistry[$name];

			if ( isset( $specs['factory'] ) && is_array( $specs['factory'] ) ) {
				$lastFactory = array_pop( $specs['factory'] );
				$specs['factory'] = $lastFactory;
			}
			if ( isset( $specs['class'] ) && is_array( $specs['class'] ) ) {
				$lastClass = array_pop( $specs['class'] );
				$specs['class'] = $lastClass;
			}
			if ( isset( $specs['class'] ) && isset( $specs['factory'] ) ) {
				unset( $specs['factory'] );
			}

			/**
			 * TODO: Inject HookContainer
			 */
			$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
			$hookContainer->run(
				'BlueSpiceUniversalExportExportDialogPluginFactoryBeforeCreatePlugin',
				[ $name, &$specs ]
			);

			$plugin = $this->objectFactory->createObject( $specs );
			if ( $plugin instanceof IExportDialogPlugin === false ) {
				continue;
			}

			if ( $plugin->skip( $this->context ) ) {
				continue;
			}

			$plugins[] = $plugin;
		}

		return $plugins;
	}

	/**
	 * @param string $permission
	 * @param IContextSource $context
	 * @return bool
	 */
	private function userCanExport( $permission, IContextSource $context ) {
		$title = $context->getTitle();
		return $title && $this->permissionManager->userCan(
			$permission, $context->getUser(), $title
		);
	}
}
