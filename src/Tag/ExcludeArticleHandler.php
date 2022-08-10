<?php

namespace BlueSpice\UniversalExport\Tag;

use BlueSpice\Tag\Handler;

class ExcludeArticleHandler extends Handler {

	/**
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setPageProperty( 'bs-tag-universalexport-excludearticle', 1 );

		return '';
	}
}
