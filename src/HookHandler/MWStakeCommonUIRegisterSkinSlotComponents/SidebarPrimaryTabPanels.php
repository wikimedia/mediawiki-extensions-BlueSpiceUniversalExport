<?php

namespace BlueSpice\Discovery\HookHandler\MWStakeCommonUIRegisterSkinSlotComponents;

use BlueSpice\Discovery\Component\MainTabPanel;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class SidebarPrimaryTabPanels implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			"SidebarPrimaryTabPanels",
			[
				'main' => [
					'factory' => static function () {
						return new MainTabPanel();
					}
				]
			]
		);
	}
}
