<?php

namespace BlueSpice\Discovery\HookHandler\MWStakeCommonUIRegisterSkinSlotComponents;

use BlueSpice\Discovery\Component\DefaultBreadcrumbNav;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;
use RequestContext;

class Breadcrumb implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'Breadcrumb',
			[
				'default-breadcrumb-nav' => [
					'factory' => static function () {
						$context = RequestContext::getMain();
						$title = $context->getTitle();
						$user = $context->getUser();
						$messageLocalizer = $context;
						$specialPageFactory = MediaWikiServices::getInstance()->getSpecialPageFactory();
						$namespaceInfo = MediaWikiServices::getInstance()->getNamespaceInfo();
						$breadcrumbFactory = MediaWikiServices::getInstance()
							->getService( 'BlueSpiceDiscoveryBreadcrumbDataProviderFactory' );
						return new DefaultBreadcrumbNav( $title, $user, $messageLocalizer, $specialPageFactory,
							$namespaceInfo, $breadcrumbFactory );
					}
				]
			]
		);
	}
}
