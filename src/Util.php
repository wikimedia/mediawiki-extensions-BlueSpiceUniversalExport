<?php

namespace BlueSpice\UniversalExport;

use Exception;
use MWException;
use SpecialPage;
use Title;
use User;
use WebRequest;

class Util {

	/**
	 * @param WebRequest $request
	 * @param string $module Export module name
	 * @param array|null $additional Additional query params to append
	 * @return string
	 * @throws MWException
	 */
	public function getExportLink( WebRequest $request, $module, $additional = [] ) {
		$queryParams = $request->getValues();
		$title = '';
		if ( isset( $queryParams['title'] ) ) {
			$title = $queryParams['title'];
		}

		// TODO: To be replaced with ParamProcessor
		$pageNameForSpecial = \BsCore::sanitize( $title, '', \BsPARAMTYPE::STRING );
		$pageNameForSpecial = trim( $pageNameForSpecial, '_' );

		$special = SpecialPage::getTitleFor( 'UniversalExport', $pageNameForSpecial );
		if ( isset( $queryParams['title'] ) ) {
			unset( $queryParams['title'] );
		}
		$queryParams['ue[module]'] = $module;

		return $special->getLinkUrl( array_merge( $queryParams, $additional ) );
	}

	/**
	 * @param Title $title
	 * @param User|null $user
	 * @throws Exception
	 */
	public function assertPermissionsForTitle( Title $title, $user = null ) {
		if ( $user === null ) {
			$user = \RequestContext::getMain()->getUser();
		}
		if ( $title->getNamespace() === NS_SPECIAL && !$user->isAllowed( 'read' ) ) {
			throw new Exception( 'error-no-permission' );
		}
		if ( $title->getNamespace() !== NS_SPECIAL && !$title->userCan( 'read', $user ) ) {
			throw new Exception( 'error-no-permission' );
		}
	}
}
