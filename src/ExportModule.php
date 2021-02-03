<?php

namespace BlueSpice\UniversalExport;

use Config;
use MediaWiki\MediaWikiServices;
use MWException;
use PermissionsError;
use SpecialUniversalExport;
use WebRequest;

abstract class ExportModule implements IExportModule {

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/** @var MediaWikiServices */
	protected $services = null;

	/** @var string */
	protected $name = '';

	/**
	 *
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 */
	protected function __construct( $name, MediaWikiServices $services, Config $config ) {
		$this->name = $name;
		$this->config = $config;
		$this->services = $services;
	}

	/**
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @return IExportModule
	 */
	public static function factory( $name, MediaWikiServices $services, Config $config ) {
		return new static( $name, $services, $config );
	}

	/**
	 * @param SpecialUniversalExport &$caller
	 * @return array
	 * @throws MWException
	 * @throws PermissionsError
	 */
	public function createExportFile( &$caller ) {
		$isAllowed = $this->services->getPermissionManager()
			->userCan(
				$this->getExportPermission(),
				$caller->getUser(),
				$caller->oRequestedTitle
			);
		if ( !$isAllowed ) {
			throw new PermissionsError( $this->getExportPermission() );
		}

		$this->setParams( $caller );

		// If we are in history mode and we are relative to an oldid
		if ( !empty( $caller->aParams['direction'] ) ) {
			$lookup = $this->services->getRevisionLookup();
			$currentRevision = $lookup->getRevisionById( $caller->aParams['oldid'] );
			switch ( $caller->aParams['direction'] ) {
				case 'next':
					$currentRevision = $lookup->getNextRevision(
						$currentRevision
					);
					break;
				case 'prev':
					$currentRevision = $lookup->getPreviousRevision(
						$currentRevision
					);
					break;
				default:
					break;
			}
			if ( $currentRevision !== null ) {
				$caller->aParams['oldid'] = $currentRevision->getId();
			}
		}

		$page = $this->getPage( $caller->aParams );
		$template = $this->getTemplate( $this->getTemplateParams( $caller, $page ) );

		if ( $template === null || $page === null ) {
			// Sanity
			throw new MWException( 'Template or page not set' );
		}

		// Combine Page Contents and Template
		$dom = $template['dom'];

		$contents = [
			'content' => [ $page['dom']->documentElement ]
		];

		$this->decorateTemplate( $template, $contents, $page, $caller );
		$this->callSubactions( $template, $contents, $caller );
		$this->replaceContent( $template, $contents );
		$this->modifyTemplateAfterContents( $template, $page, $caller );

		$caller->aParams['resources'] = $template['resources'];
		$this->setExportConnectionParams( $caller );

		// Prepare response
		$response = $this->getResponseParams();

		if ( $caller->getRequest()->getVal( 'debugformat', '' ) === 'html' ) {
			$response['content'] = $dom->saveXML( $dom->documentElement );
			$response['mime-type'] = 'text/html';
			$response['filename'] = sprintf(
				'%s.html',
				$caller->oRequestedTitle->getPrefixedText()
			);
			$response['disposition'] = 'inline';
			return $response;
		}

		$response['content'] = $this->getExportedContent( $caller, $template );
		if ( $response['content'] === null ) {
			throw new MWException( 'Content not set in response' );
		}

		$response['filename'] = sprintf(
			$response['filename'],
			$caller->oRequestedTitle->getPrefixedText()
		);

		return $response;
	}

	/**
	 * @param WebRequest $request
	 * @param array|null $additional Additional query params to append
	 * @return string
	 * @throws MWException
	 */
	public function getExportLink( WebRequest $request, $additional = [] ) {
		$queryParams = $request->getValues();
		$title = '';

		if ( isset( $additional['title'] ) ) {
			$title = $additional['title'];
			unset( $additional['title'] );
		}

		if ( $title === '' && isset( $queryParams['title'] ) ) {
			$title = $queryParams['title'];
		}

		// TODO: To be replaced with ParamProcessor
		$pageNameForSpecial = \BsCore::sanitize( $title, '', \BsPARAMTYPE::STRING );
		$pageNameForSpecial = trim( $pageNameForSpecial, '_' );
		$special = $this->getServices()->getSpecialPageFactory()->getPage(
			'UniversalExport'
		);

		if ( isset( $queryParams['title'] ) ) {
			unset( $queryParams['title'] );
		}
		$queryParams['ue[module]'] = $this->getName();

		return $special->getPageTitle( $pageNameForSpecial )
			->getLinkURL( array_merge( $queryParams, $additional ) );
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @inheritDoc
	 */
	public function getServices() {
		return $this->services;
	}

	/**
	 * Set additional parameters on the caller
	 *
	 * @param SpecialUniversalExport &$caller
	 */
	protected function setParams( &$caller ) {
		$request = $caller->getRequest();

		$caller->aParams['title'] = $caller->oRequestedTitle->getPrefixedText();
		$caller->aParams['display-title'] = $caller->oRequestedTitle->getPrefixedText();
		$caller->aParams['article-id'] = $caller->oRequestedTitle->getArticleID();
		$caller->aParams['oldid'] = $request->getInt( 'oldid', 0 );
		$caller->aParams['direction'] = $request->getVal( 'direction', '' );
	}

	/**
	 * @param SpecialUniversalExport &$caller
	 */
	protected function setExportConnectionParams( &$caller ) {
		$caller->aParams['document-token'] =
			md5( $caller->oRequestedTitle->getPrefixedText() )
			. '-'
			. $caller->aParams['oldid'];
	}

	/**
	 * @param SpecialUniversalExport $caller
	 * @param array $page
	 * @return array
	 */
	protected function getTemplateParams( $caller, $page ) {
		$templateParams = [
			'language' => $caller->getUser()->getOption( 'language', 'en' ),
			'meta'     => $page['meta']
		];

		// Override template param if needed. The override may come
		// from GET (&ue[template]=...) or from a tag (<bs:ueparams template="..." />)
		// TODO: Make more generic
		if ( !empty( $caller->aParams['template'] ) ) {
			$templateParams['template'] = $caller->aParams['template'];
		}

		return $templateParams;
	}

	/**
	 * @param array $params
	 * @return array|null
	 */
	protected function getTemplate( $params ) {
		return null;
	}

	/**
	 * @param array &$template
	 * @param array &$contents
	 * @param array &$page
	 * @param SpecialUniversalExport $caller
	 */
	protected function decorateTemplate( &$template, &$contents, &$page, $caller ) {
		$template['title-element']->nodeValue = $caller->oRequestedTitle->getPrefixedText();

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'UniversalExportBeforeTemplateSetContent',
			[
				&$template,
				&$contents,
				$caller,
				&$page
			]
		);
	}

	/**
	 * @param array &$template
	 * @param array &$contents
	 * @param SpecialUniversalExport $caller
	 */
	protected function callSubactions( &$template, &$contents, $caller ) {
		/**
		 * @var string $name
		 * @var IExportSubaction $handler
		 */
		foreach ( $this->getSubactionHandlers() as $name => $handler ) {
			$permission = $handler->getPermission();
			if ( $permission ) {
				$isAllowed = $this->getServices()->getPermissionManager()
					->userCan( $permission, $caller->getUser(), $caller->oRequestedTitle );
				if ( !$isAllowed ) {
					throw new PermissionsError( $permission );
				}
			}

			if ( $handler->applies( $caller->getRequest() ) ) {
				$handler->apply( $template, $contents, $caller );
			}
		}
	}

	/**
	 * @param array $params
	 * @return null
	 */
	protected function getPage( $params ) {
		return null;
	}

	/**
	 * @return array
	 */
	protected function getResponseParams() {
		return [
			'content' => ''
		];
	}

	/**
	 * @param SpecialUniversalExport $caller
	 * @param array &$template
	 * @return mixed
	 */
	protected function getExportedContent( $caller, &$template ) {
		return null;
	}

	/**
	 * @param array &$template
	 * @param array &$contents
	 */
	protected function replaceContent( &$template, &$contents ) {
		$contentTags = $template['dom']->getElementsByTagName( 'content' );
		$i = $contentTags->length - 1;
		while ( $i > -1 ) {
			$contentTag = $contentTags->item( $i );
			$sKey = $contentTag->getAttribute( 'key' );
			if ( isset( $contents[$sKey] ) ) {
				foreach ( $contents[$sKey] as $node ) {
					$node = $template['dom']->importNode( $node, true );
					$contentTag->parentNode->insertBefore( $node, $contentTag );
				}
			}
			$contentTag->parentNode->removeChild( $contentTag );
			$i--;
		}
	}

	/**
	 * @param array &$template
	 * @param array $page
	 * @param SpecialUniversalExport $caller
	 */
	protected function modifyTemplateAfterContents( &$template, $page, $caller ) {
		// NOOP
	}
}
