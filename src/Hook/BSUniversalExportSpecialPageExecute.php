<?php

namespace BlueSpice\UniversalExport\Hook;

abstract class BSUniversalExportSpecialPageExecute extends \BlueSpice\Hook {

	/**
	 *
	 * @var \SpecialPage
	 */
	protected $special = null;

	/**
	 *
	 * @var string
	 */
	protected $parameter = null;

	/**
	 *
	 * @var \BsUniversalExportModule[]
	 */
	protected $modules = null;

	/**
	 *
	 * @param \SpecialPage $special
	 * @param string $parameter
	 * @param \BsUniversalExportModule[] $modules
	 * @return boolean
	 */
	public static function callback( $special, $parameter, &$modules ) {
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
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param \SpecialPage $special
	 * @param string $parameter
	 * @param \BsUniversalExportModule[] $modules
	 */
	public function __construct( $context, $config, $special, $parameter, &$modules) {
		parent::__construct( $context, $config );

		$this->special = $special;
		$this->parameter = $parameter;
		$this->modules =& $modules;
	}
}
