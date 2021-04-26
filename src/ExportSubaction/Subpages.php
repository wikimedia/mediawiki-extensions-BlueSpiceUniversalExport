<?php

namespace BlueSpice\UniversalExport\ExportSubaction;

use BlueSpice\UniversalExport\ExportSpecification;
use BlueSpice\UniversalExport\IExportSubaction;
use DOMDocument;
use DOMElement;
use Title;
use WebRequest;

abstract class Subpages implements IExportSubaction {
	/** @var array */
	protected $titleMap = [];
	/** @var array */
	protected $includedTitles = [];

	/**
	 * @inheritDoc
	 */
	public function applies( ExportSpecification $specification ) {
		return (bool)$specification->getParam( 'subpages', false );
	}

	/**
	 * @inheritDoc
	 */
	public function apply( &$template, &$contents, $specification ) {
		$newDOM = new DOMDocument();
		$pageDOM = $contents['content'][0];
		$pageDOM->setAttribute(
			'class',
			$pageDOM->getAttribute( 'class' ) . ' bs-source-page'
		);
		$node = $newDOM->importNode( $pageDOM, true );

		$includedTitleMap = [];
		$rootTitle = Title::newFromText( $template['title-element']->nodeValue );
		if ( $pageDOM->getElementsByTagName( 'a' )->item( 0 )->getAttribute( 'id' ) === '' ) {
			$pageDOM->getElementsByTagName( 'a' )->item( 0 )->setAttribute(
				'id',
				md5( $rootTitle->getPrefixedText() )
			);
		}

		$includedTitleMap[$template['title-element']->nodeValue]
			= $pageDOM->getElementsByTagName( 'a' )->item( 0 )->getAttribute( 'id' );

		$newDOM->appendChild( $node );

		$this->includedTitles = $this->findIncludedTitles( $specification );
		if ( count( $this->includedTitles ) < 1 ) {
			return true;
		}

		$this->titleMap = array_merge(
			$includedTitleMap,
			$this->generateIncludedTitlesMap( $this->includedTitles )
		);

		$this->setIncludedTitlesId( $this->includedTitles, $this->titleMap );
		$this->addIncludedTitlesContent( $this->includedTitles, $this->titleMap, $contents['content'] );

		return true;
	}

	/**
	 * @return mixed
	 */
	abstract protected function getPageProvider();

	/**
	 * @inheritDoc
	 */
	public function getExportLink( WebRequest $request, $additional = [] ) {
		return $this->getMainModule()->getExportLink( $request, array_merge( $additional, [
			'ue[subpages]' => 1
		] ) );
	}

	/**
	 *
	 * @param array $includedTitles
	 * @param array $includedTitleMap
	 * @param array &$contents
	 */
	protected function addIncludedTitlesContent(
		$includedTitles, $includedTitleMap, &$contents
	) {
		foreach ( $includedTitles as $name => $content ) {
			$contents[] = $content['dom']->documentElement;
		}
	}

	/**
	 *
	 * @param array $includedTitles
	 * @return array
	 */
	protected function generateIncludedTitlesMap( $includedTitles ) {
		$includedTitleMap = [];

		foreach ( $includedTitles as $name => $content ) {
			$includedTitleMap = array_merge(
				$includedTitleMap,
				[ $name => md5( $name ) ]
			);
		}

		return $includedTitleMap;
	}

	/**
	 *
	 * @param array &$includedTitles
	 * @param array $includedTitleMap
	 */
	protected function setIncludedTitlesId( &$includedTitles, $includedTitleMap ) {
		foreach ( $includedTitles as $name => $content ) {
			// set array index from $includedTitleMap
			$documentLinks = $content['dom']->getElementsByTagName( 'a' );
			if ( $documentLinks->item( 0 ) instanceof DOMElement ) {
				$documentLinks->item( 0 )->setAttribute(
					'id',
					$includedTitleMap[$name]
				);
			}
		}
	}

	/**
	 *
	 * @param ExportSpecification $specs
	 * @return array
	 */
	protected function findIncludedTitles( $specs ) {
		$linkdedTitles = [];

		$subpages = $specs->getTitle()->getSubpages();

		foreach ( $subpages as $title ) {
			$pageProvider = $this->getPageProvider();
			$pageContent = $pageProvider->getPage( [
				'article-id' => $title->getArticleID(),
				'title' => $title->getFullText()
			] );

			if ( !isset( $pageContent['dom'] ) ) {
				continue;
			}

			$linkdedTitles = array_merge(
				$linkdedTitles,
				[
					$title->getPrefixedText() => $pageContent
				]
			);
		}

		ksort( $linkdedTitles );

		return $linkdedTitles;
	}
}
