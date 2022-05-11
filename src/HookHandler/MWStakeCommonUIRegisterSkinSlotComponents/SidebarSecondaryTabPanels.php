<?php

namespace BlueSpice\Discovery\HookHandler\MWStakeCommonUIRegisterSkinSlotComponents;

use BlueSpice\Discovery\Component\PageTabPanel;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class SidebarSecondaryTabPanels implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			"SidebarSecondaryTabPanels",
			[
				'page' => [
					'factory' => static function () {
						return new PageTabPanel();
					}
				]
			]
		);
	}
}
