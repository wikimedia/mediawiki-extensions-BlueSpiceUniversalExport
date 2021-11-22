<?php

namespace BlueSpice\UniversalExport\HookHandler;

use BlueSpice\UniversalExport\IExportModule;
use BlueSpice\UniversalExport\IExportSubaction;
use BlueSpice\UniversalExport\ModuleFactory;
use IContextSource;
use MediaWiki\Hook\SidebarBeforeOutputHook;
use MediaWiki\Permissions\PermissionManager;
use Skin;

class SkinHandler implements SidebarBeforeOutputHook {
	/**
	 * @var ModuleFactory
	 */
	private $moduleFactory = null;

	/**
	 * @var PermissionManager
	 */
	private $permissionManager = null;

	/**
	 *
	 * @param ModuleFactory $moduleFactory
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( ModuleFactory $moduleFactory, PermissionManager $permissionManager ) {
		$this->moduleFactory = $moduleFactory;
		$this->permissionManager = $permissionManager;
	}

	/**
	 *
	 * @param Skin $skin
	 * @param array &$sidebar
	 * @return void
	 */
	public function onSidebarBeforeOutput( $skin, &$sidebar ): void {
		/**
		 * Unfortunately the `VectorTemplateTest::testGetMenuProps` from `Skin:Vector` will break
		 * in `REL1_35`, as it does not properly clear out all hook handlers.
		 * See https://github.com/wikimedia/Vector/blob/1b03bafb1267f350ee2b0018da53c31ee0674f92/tests/phpunit/integration/VectorTemplateTest.php#L107-L108
		 * In later versions this test does not exist anymore and we can remove the bail out again.
		 * We do not perform any own UnitTests on this class, so bailing out here should be fine.
		 */
		if ( defined( 'MW_PHPUNIT_TEST' ) ) {
			return;
		}

		if ( $skin->getSkinName() === 'bluespicecalumma' ) {
			// BlueSpiceCalumma has its own integration see:
			// BlueSpice\UniversalExport\Hook\ChameleonSkinTemplateOutputPageBeforeExec\\AddActions
			return;
		}

		foreach ( $this->moduleFactory->getModules() as $name => $module ) {
			$context = $skin->getContext();
			if ( !$this->userCanExport( $module->getExportPermission(), $context ) ) {
				continue;
			}
			$key = 't-' . $module->getName();
			$description = $this->getActionDescription( $key, $module, $skin );
			if ( $description === null ) {
				continue;
			}
			$sidebar['TOOLBOX'][$key] = $description;
			/**
			 * @var string $name
			 * @var IExportSubaction $handler
			 */
			foreach ( $module->getSubactionHandlers() as $subaction => $handler ) {
				if ( !$this->userCanExport( $handler->getPermission(), $context ) ) {
					continue;
				}
				$key = 't-' . $module->getName() . ( $subaction ? "-$subaction" : '' );
				$description = $this->getActionDescription(
					$key,
					$module,
					$skin,
					$subaction,
					$handler
				);
				if ( !$description ) {
					continue;
				}
				$sidebar['TOOLBOX'][$key] = $description;
			}
		}
	}

	/**
	 * @param string $id
	 * @param IExportModule $module
	 * @param Skin $skin
	 * @param string|null $subaction
	 * @param IExportSubaction|null $handler
	 * @return array The ContentAction Array
	 */
	private function getActionDescription( $id, IExportModule $module, Skin $skin,
		$subaction = null, $handler = null ) {
		$authority = $handler !== null ? $handler : $module;

		$actionButtonDetails = $authority->getActionButtonDetails();
		if ( $actionButtonDetails === null ) {
			return null;
		}

		return [
			'id' => $id,
			'href' => $authority->getExportLink( $skin->getRequest() ),
			'title' => $actionButtonDetails['title'] ?? '',
			'text' => $actionButtonDetails['text'] ?? '',
			'class' => 'bs-ue-export-link',
			'iconClass' => $actionButtonDetails['iconClass'] ?? '' . ' bs-ue-export-link'
		];
	}

	/**
	 * @param string $permission
	 * @param IContextSource $context
	 * @return bool
	 */
	private function userCanExport( $permission, IContextSource $context ) {
		return $this->permissionManager->userCan(
			$permission, $context->getUser(), $context->getTitle()
		);
	}

}
