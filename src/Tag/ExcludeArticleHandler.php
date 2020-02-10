<?php

namespace BlueSpice\UniversalExport\Tag;

use BlueSpice\Tag\Handler;

class ExcludeArticleHandler extends Handler {

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setProperty( 'bs-tag-universalexport-excludearticle', 1 );

		return '';
	}
}
