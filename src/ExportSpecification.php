<?php

namespace BlueSpice\UniversalExport;

use BlueSpice\UtilityFactory;
use Config;
use FormatJson;
use PageProps;
use Title;
use User;

class ExportSpecification {
	/** @var Config */
	private $config;
	/** @var UtilityFactory */
	private $utilFactory;
	/** @var Title */
	private $title;
	/** @var User */
	private $user = null;
	/** @var array */
	private $params;
	/** @var array */
	private $metadata = [];
	/** @var array */
	private $categoryWhitelist = [];
	/** @var array */
	private $categoryBlacklist = [];

	/**
	 * @param Config $config
	 * @param UtilityFactory $utilFactory
	 * @param Title $title
	 * @param User $user
	 * @param array $params
	 */
	public function __construct(
		Config $config, UtilityFactory $utilFactory, Title $title, User $user, $params = []
	) {
		$this->config = $config;
		$this->utilFactory = $utilFactory;
		$this->title = $title;
		$this->user = $user;
		$this->params = $params;

		$this->setDefaults();
	}

	/**
	 * @return Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return User|null
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * @param string $param
	 * @param null $default
	 * @return mixed
	 */
	public function getParam( $param, $default = null ) {
		if ( isset( $this->params[$param] ) ) {
			return $this->params[$param];
		}

		return $default;
	}

	/**
	 * @param string $param
	 * @param mixed $value
	 */
	public function setParam( $param, $value ) {
		$this->params[$param] = $value;
	}

	/**
	 * @param string $item
	 * @param mixed $value
	 */
	public function setMetadataItem( $item, $value ) {
		$this->metadata[$item] = $value;
	}

	/**
	 * @return array
	 */
	public function getMetadata() {
		return $this->metadata;
	}

	/**
	 * @return array
	 */
	public function getCategoryWhitelist() {
		return $this->categoryWhitelist;
	}

	/**
	 * @return array
	 */
	public function getCategoryBlacklist() {
		return $this->categoryBlacklist;
	}

	private function setDefaults() {
		// Set up default parameters and metadata
		$this->params = array_merge( $this->config->get(
			'UniversalExportParamsDefaults'
		), $this->params );

		$webrootPath = str_replace( '\\', '/', $GLOBALS['IP'] );
		if ( !empty( $this->config->get( 'ScriptPath' ) ) ) {
			$parts = explode( '/', $webrootPath );
			if ( "/" . array_pop( $parts ) === $this->config->get( 'ScriptPath' ) ) {
				$webrootPath = implode( '/', $parts );
			}
		}

		$this->setParam( 'webroot-filesystempath', $webrootPath );
		$this->setParam( 'title', $this->title->getPrefixedText() );
		$this->setParam( 'display-title', $this->getDisplayTitle() );
		$this->setParam( 'article-id', $this->title->getArticleID() );
		$this->setParam( 'oldid', 0 );
		$this->setParam( 'direction', '' );
		$this->addPageParams();

		$this->metadata = FormatJson::decode(
			$this->config->get( 'UniversalExportMetadataDefaults' ),
			true
		);

		// Set up Black- and Whitelists
		$this->categoryWhitelist = $this->config->get(
			'UniversalExportCategoryWhitelist'
		);
		$this->categoryBlacklist = $this->config->get(
			'UniversalExportCategoryBlacklist'
		);
	}

	private function addPageParams() {
		$propHelper = $this->utilFactory->getPagePropHelper( $this->title );

		// Get relevant page props
		if ( $propHelper->getPageProp( 'bs-universalexport-params' ) ) {
			$prop = FormatJson::decode(
				$propHelper->getPageProp( 'bs-universalexport-params' ),
				true
			);
			if ( is_array( $prop ) ) {
				foreach ( $prop as $key => $value ) {
				$this->setParam( $key, $value );
				}
			}
		}
	}

	/**
	 * @return string
	 */
	private function getDisplayTitle(): string {
		$pageProperties = [];
		$pageProps = PageProps::getInstance()->getAllProperties( $this->title );

		$id = $this->title->getArticleID();

		if ( isset( $pageProps[$id] ) ) {
			$pageProperties = $pageProps[$id];
		}

		if ( isset( $pageProperties['displaytitle'] ) ) {
			return $pageProperties['displaytitle'];
		}

		return $this->title->getPrefixedText();
	}
}
