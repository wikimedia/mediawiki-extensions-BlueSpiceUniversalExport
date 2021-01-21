<?php

use BlueSpice\UniversalExport\Util;
use MediaWiki\MediaWikiServices;

return [
	'BSUniversalExportUtils' => function ( MediaWikiServices $services ) {
		return new Util();
	},
];
