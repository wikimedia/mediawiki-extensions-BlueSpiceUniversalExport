<?php

namespace BlueSpice\UniversalExport;

use MWException;
use SpecialPage;
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
}
