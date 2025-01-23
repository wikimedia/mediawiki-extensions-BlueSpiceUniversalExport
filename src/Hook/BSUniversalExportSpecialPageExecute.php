<?php

namespace BlueSpice\UniversalExport\Hook;

use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;

/**
 * DEPRECATED
 * @deprecated since version 3.3 - use ExtensionAttributeBasedRegistry
 * "BlueSpiceUniversalExportModuleRegistry" instead
 */
abstract class BSUniversalExportSpecialPageExecute extends \BlueSpice\Hook {

	/**
	 * @deprecated since version 3.3 - mabe null!
	 * @var \SpecialPage|null
	 */
	protected $special = null;

	/**
	 * @deprecated since version 3.3 - always empty!
	 * @var string
	 */
	protected $parameter = null;

	/**
	 *
	 * @var \BsUniversalExportModule[]
	 */
	protected $modules = null;

	/**
	 * DEPRECATED
	 * @deprecated since version 3.3 - use ExtensionAttributeBasedRegistry
	 * "BlueSpiceUniversalExportModuleRegistry" instead
	 * @param \SpecialPage $special
	 * @param string $parameter
	 * @param \BsUniversalExportModule[] &$modules
	 * @return bool
	 */
	public static function callback( $special, $parameter, &$modules ) {
		wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$special,
			$parameter,
			$modules
		);
		return $hookHandler->process();
	}

	/**
	 * @param IContextSource $context
	 * @param Config $config
	 * @param \SpecialPage $special
	 * @param string $parameter
	 * @param \BsUniversalExportModule[] &$modules
	 */
	public function __construct( $context, $config, $special, $parameter, &$modules ) {
		parent::__construct( $context, $config );

		$this->special = $special;
		$this->parameter = $parameter;
		$this->modules =& $modules;
	}
}
