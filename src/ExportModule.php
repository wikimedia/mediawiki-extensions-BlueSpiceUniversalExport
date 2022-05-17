<?php

namespace BlueSpice\UniversalExport;

use Config;
use Exception;
use ExtensionRegistry;
use Hooks;
use MediaWiki\MediaWikiServices;
use MWException;
use PermissionsError;
use RequestContext;
use SpecialPageFactory;
use WebRequest;

abstract class ExportModule implements IExportModule {

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/** @var MediaWikiServices */
	protected $services = null;
	/** @var WebRequest */
	protected $request = null;

	/** @var string */
	protected $name = '';

	/**
	 *
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @param WebRequest $request
	 */
	protected function __construct(
		$name, MediaWikiServices $services, Config $config, WebRequest $request
	) {
		$this->name = $name;
		$this->config = $config;
		$this->services = $services;
		$this->request = $request;
	}

	/**
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @param WebRequest|null $request
	 * @return IExportModule
	 */
	public static function factory(
		$name, MediaWikiServices $services, Config $config, $request = null
	) {
		if ( !$request ) {
			$request = RequestContext::getMain()->getRequest();
		}
		return new static( $name, $services, $config, $request );
	}

	/**
	 * @param ExportSpecification &$specification
	 * @return array
	 * @throws MWException
	 * @throws PermissionsError
	 */
	public function createExportFile( ExportSpecification &$specification ) {
		$isAllowed = $specification->getTitle()->userCan(
			$this->getExportPermission(),
			$specification->getUser()
		);
		if ( !$isAllowed ) {
			throw new PermissionsError( $this->getExportPermission() );
		}
		$this->setParams( $specification );
		$oldId = $this->request->getInt( 'oldid', -1 );
		if ( $oldId ) {
			$specification->setParam( 'oldid', $oldId );
		}

		// If we are in history mode and we are relative to an oldid
		if ( !empty( $specification->getParam( 'direction' ) ) ) {
			$lookup = $this->services->getRevisionLookup();
			$currentRevision = $lookup->getRevisionById( $specification->getParam( 'oldid' ) );
			switch ( $specification->getParam( 'direction' ) ) {
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
				$specification->setParam( 'oldid', $currentRevision->getId() );
			}
		}

		$page = $this->getPage( $specification );
		$template = $this->getTemplate( $this->getTemplateParams( $specification, $page ) );

		if ( $template === null || $page === null ) {
			// Sanity
			throw new MWException( 'Template or page not set' );
		}

		// Combine Page Contents and Template
		$dom = $template['dom'];

		$contents = [
			'content' => [ $page['dom']->documentElement ]
		];

		$this->decorateTemplate( $template, $contents, $page, $specification );
		$this->callSubactions( $template, $contents, $specification );
		$this->replaceContent( $template, $contents );
		$this->modifyTemplateAfterContents( $template, $page, $specification );

		$specification->setParam( 'resources', $template['resources'] );
		$this->setExportConnectionParams( $specification );

		// Prepare response
		$response = $this->getResponseParams();

		if ( $specification->getParam( 'debugformat' ) === 'html' ) {
			$response['content'] = $dom->saveXML( $dom->documentElement );
			$response['mime-type'] = 'text/html';
			$response['filename'] = sprintf(
				'%s.html',
				$specification->getTitle()->getPrefixedText()
			);
			$response['disposition'] = 'inline';
			return $response;
		}

		$response['content'] = $this->getExportedContent( $specification, $template );
		if ( $response['content'] === null ) {
			throw new MWException( 'Content not set in response' );
		}

		$response['filename'] = sprintf(
			$response['filename'],
			$specification->getTitle()->getPrefixedText()
		);

		return $response;
	}

	/**
	 * @param ExportSpecification &$specification
	 */
	protected function setParams( &$specification ) {
		// NOOP
	}

	/**
	 * @param WebRequest $request
	 * @param array|null $additional Additional query params to append
	 * @return string
	 * @throws MWException
	 */
	public function getExportLink( WebRequest $request, array $additional = [] ) {
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
		$pageNameForSpecial = trim( $pageNameForSpecial, '_ ' );
		$special = SpecialPageFactory::getPage(
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
	 * @param ExportSpecification &$specs
	 */
	protected function setExportConnectionParams( ExportSpecification &$specs ) {
		$token = md5( $specs->getTitle()->getPrefixedText() )
			. '-'
			. intval( $specs->getParam( 'oldid' ) );
		$specs->setParam( 'document-token', $token );
	}

	/**
	 * @param ExportSpecification $specification
	 * @param array $page
	 * @return array
	 */
	protected function getTemplateParams( $specification, $page ) {
		$templateParams = [
			'language' => $specification->getUser()->getOption( 'language', 'en' ),
			'meta'     => $page['meta']
		];

		// Override template param if needed. The override may come
		// from GET (&ue[template]=...) or from a tag (<bs:ueparams template="..." />)
		// TODO: Make more generic
		if ( !empty( $specification->getParam( 'template' ) ) ) {
			$templateParams['template'] = $specification->getParam( 'template' );
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
	 * @param ExportSpecification $specs
	 */
	protected function decorateTemplate( &$template, &$contents, &$page, $specs ) {
		$template['title-element']->nodeValue = $specs->getTitle()->getPrefixedText();

		Hooks::run(
			'UniversalExportBeforeTemplateSetContent',
			[
				&$template,
				&$contents,
				$specs,
				&$page
			]
		);
	}

	/**
	 * @param array &$template
	 * @param array &$contents
	 * @param ExportSpecification $specs
	 */
	protected function callSubactions( &$template, &$contents, $specs ) {
		/**
		 * @var string $name
		 * @var IExportSubaction $handler
		 */
		foreach ( $this->getSubactionHandlers() as $name => $handler ) {
			$permission = $handler->getPermission();
			if ( $permission ) {
				$isAllowed = $specs->getTitle()->userCan(
					$permission, $specs->getUser()
				);
				if ( !$isAllowed ) {
					throw new PermissionsError( $permission );
				}
			}

			if ( $handler->applies( $specs ) ) {
				$handler->apply( $template, $contents, $specs );
			}
		}
	}

	/**
	 * @param ExportSpecification $specification
	 * @return null
	 */
	protected function getPage( ExportSpecification $specification ) {
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
	 * @param ExportSpecification $specs
	 * @param array &$template
	 * @return mixed
	 */
	protected function getExportedContent( $specs, &$template ) {
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
	 * @param ExportSpecification $specs
	 */
	protected function modifyTemplateAfterContents( &$template, $page, $specs ) {
		// NOOP
	}

	/**
	 * @param array $file
	 * @param ExportSpecification $specs
	 * @throws Exception
	 */
	public function invokeExportTarget( $file, $specs ) {
		$descriptor = new LegacyArrayDescriptor( $file );

		$targetKey = 'download';
		if ( $specs->getParam( 'target' ) ) {
			$targetKey = $specs->getParam( 'target' );
		}

		$registryAttribute =
			ExtensionRegistry::getInstance()->getAttribute(
				'BlueSpiceUniversalExportExportTargetRegistry'
			);

		if ( !isset( $registryAttribute[$targetKey] ) ) {
			throw new Exception( 'bs-universalexport-error-target-invalid' );
		}

		if ( !is_callable( $registryAttribute[$targetKey] ) ) {
			throw new Exception( 'bs-universalexport-error-target-factory-not-callable' );
		}

		$target = call_user_func_array(
			$registryAttribute[$targetKey],
			[
				$specs->getParams(),
				$this->config
			]
		);

		if ( $target instanceof IExportTarget === false ) {
			throw new Exception( 'bs-universalexport-error-target-invalid' );
		}

		$status = $target->execute( $descriptor );

		if ( !$status->isOK() ) {
			throw new Exception( 'bs-universalexport-error-target-failed' );
		}
	}

}
