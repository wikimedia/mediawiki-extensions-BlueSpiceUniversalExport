<?php

namespace BlueSpice\UniversalExport\ConfigDefinition;

class MetadataDefaults extends \BlueSpice\ConfigDefinition\StringSetting {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_EXPORT . '/BlueSpiceUniversalExport',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceUniversalExport/' . static::FEATURE_EXPORT,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceUniversalExport',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-universalexport-pref-metadatadefaults';
	}

	/**
	 *
	 * @return array
	 */
	public function makeFormFieldParams() {
		return array_merge(
			parent::makeFormFieldParams(),
			[ 'rows' => 5 ]
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-universalexport-pref-metadatadefaults-help';
	}
}
