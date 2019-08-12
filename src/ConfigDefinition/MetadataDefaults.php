<?php

namespace BlueSpice\UniversalExport\ConfigDefinition;

class MetadataDefaults extends \BlueSpice\ConfigDefinition\StringSetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_EXPORT . '/BlueSpiceUniversalExport',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceUniversalExport/' . static::FEATURE_EXPORT,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceUniversalExport',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-universalexport-pref-metadatadefaults';
	}

	public function makeFormFieldParams() {
		return array_merge(
			parent::makeFormFieldParams(),
			[ 'rows' => 5 ]
		);
	}
}
