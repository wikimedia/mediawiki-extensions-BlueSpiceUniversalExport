<?php

namespace BlueSpice\UniversalExport\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;
use BlueSpice\UniversalExport\IExportModule;
use BlueSpice\UniversalExport\IExportSubaction;
use BlueSpice\UniversalExport\ModuleFactory;

class AddActions extends SkinTemplateOutputPageBeforeExec {
	protected function skipProcessing() {
		return $this->skin->getTitle()->isSpecialPage();
	}

	protected function doProcess() {
		/** @var ModuleFactory $moduleFactory */
		$moduleFactory = $this->getServices()->getService(
			'BSUniversalExportModuleFactory'
		);

		$actions = [];
		foreach ( $moduleFactory->getModules() as $name => $module ) {
			if ( !$this->userCanExport( $module->getExportPermission() ) ) {
				continue;
			}
			$description = $this->getActionDescription( $module );
			if ( $description === null ) {
				continue;
			}
			$actions[md5( $name )] = $description;
			/**
			 * @var string $name
			 * @var IExportSubaction $handler
			 */
			foreach ( $module->getSubactionHandlers() as $subaction => $handler ) {
				if ( !$this->userCanExport( $handler->getPermission() ) ) {
					continue;
				}
				$description = $this->getActionDescription( $module, $subaction, $handler );
				if ( $description === null ) {
					continue;
				}
				$key = md5( $name . '/' . $subaction );
				$actions[$key] = $description;
			}
		}

		$this->mergeSkinDataArray(
				SkinData::EXPORT_MENU,
				array_values( $actions )
		);

		return true;
	}

	/**
	 * @param IExportModule $module
	 * @param string|null $subaction
	 * @param IExportSubaction|null $handler
	 * @return array The ContentAction Array
	 */
	private function getActionDescription(
		IExportModule $module, $subaction = null, $handler = null
	) {
		$authority = $handler !== null ? $handler : $module;

		$actionButtonDetails = $authority->getActionButtonDetails();
		if ( $actionButtonDetails === null ) {
			return null;
		}

		return [
			'id' => $module->getName() . ( $subaction ? "-$subaction" : '' ),
			'href' => $authority->getExportLink( $this->skin->getRequest() ),
			'title' => $actionButtonDetails['title'] ?? '',
			'text' => $actionButtonDetails['text'] ?? '',
			'class' => 'bs-ue-export-link',
			'iconClass' => $actionButtonDetails['iconClass'] ?? '' . ' bs-ue-export-link'
		];
	}

	/**
	 * @param string $permission
	 * @return bool
	 */
	private function userCanExport( $permission ) {
		return $this->getContext()->getTitle()->userCan(
			$permission, $this->getContext()->getUser()
		);
	}
}
