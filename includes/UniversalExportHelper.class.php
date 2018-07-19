<?php
/**
 * BsUniversalExportHelper.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>

 * @package    BlueSpiceUniversalExport
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * UniversalExport BsUniversalExportHelper class.
 * @package BlueSpiceUniversalExport
 */
class BsUniversalExportHelper {
	/**
	 * Extracts the parameters from the querystring and merges it wir the
	 * default and overrige settings of the UniversalExport Extension.
	 * @param array $aParams
	 */
	public static function getParamsFromQueryString( &$aParams ) {
		global $wgRequest;
		$config = \BlueSpice\Services::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );
		$aParamsOverrides = $config->get( 'UniversalExportParamsOverrides' );
		$aParams = array_merge( $aParams, $wgRequest->getArray( 'ue', array() ) );
		$aParams = array_merge( $aParams, $aParamsOverrides );
		$aParams['oldid']  = $wgRequest->getVal( 'oldid', 0 );
		$sDirection = $wgRequest->getVal( 'direction', '' );
		if( !empty ( $sDirection ) ){
			$aParams['direction'] = $sDirection;
		}
	}

	public static function checkPermissionForTitle( $oTitle, &$aParams ) {
		global $wgUser;

		$bErrorOccurred = false;
		foreach( $aParams as $sValue ) {
			if ( $oTitle->getNamespace() == NS_SPECIAL ) {
				if( !$wgUser->isAllowed( 'read' ) ) $bErrorOccurred = true;
			}
			else{
				if( !$oTitle->userCan( 'read' ) ) $bErrorOccurred = true;
			}
		}

		if( $bErrorOccurred ) throw new Exception ( 'error-no-permission' );
	}

	public static function getCategoriesForTitle( $oTitle ) {
		/* Title::getParentCategories() returns an array like this:
		 * array (
		 *  'Category:Foo' => 'My Article',
		 *  'Category:Bar' => 'My Article',
		 *  'Category:Baz' => 'My Article',
		 * )
		 */
		$aCategories         = $oTitle->getParentCategories();
		$aSimpleCategoryList = array();
		if( !empty( $aCategories ) ) {
			foreach( $aCategories as $sCategoryPageName => $sCurrentTitle ) {
				$aCategoryPageNameParts = explode( ':', $sCategoryPageName );
				$aSimpleCategoryList[]  = $aCategoryPageNameParts[1];
			}
		}
		return $aSimpleCategoryList;
	}

	/**
	 * Finds suitable headlines in $oPageDOM and creates returns a
	 * <bookmarks /> element with links to them
	 * @param DOMDocument $oPageDOM
	 * @return DOMElement
	 */
	public static function getBookmarkElementForPageDOM( $oPageDOM ) {
		$oBookmarksDOM = new DOMDocument();

		//HINT: http://calibre-ebook.com/user_manual/xpath.html
		$oBodyContentXPath = new DOMXPath( $oPageDOM );
		$oHeadingElements  = $oBodyContentXPath->query(
			"//*[contains(@class, 'firstHeading') "
			."or contains(@class, 'mw-headline') "
			."and not(contains(@class, 'mw-headline-'))]"
		);

		//By convention the first <h1> in the PageDOM is the title of the page
		$oPageTitleBookmarkElement    = $oBookmarksDOM->createElement( 'bookmark' );
		$oPageTitleHeadingElement     = $oHeadingElements->item( 0 );
		$sPageTitleHeadingTextContent = trim( $oPageTitleHeadingElement->textContent );

		//By convention previousSibling is an Anchor-Tag (see BsPageContentProvider)
		//TODO: check for null
		$sPageTitleHeadingJumpmark = self::findPreviousDOMElementSibling( $oPageTitleHeadingElement, 'a' )->getAttribute( 'name' );
		$oPageTitleBookmarkElement->setAttribute( 'name', $sPageTitleHeadingTextContent );
		$oPageTitleBookmarkElement->setAttribute( 'href', '#'.$sPageTitleHeadingJumpmark );

		//Adapt MediaWiki TOC #1
		$oTocTableElement = $oBodyContentXPath->query( "//*[@id='toc']" );
		$oTableOfContentsAnchors = array();
		if ( $oTocTableElement->length > 0 ) { //Is a TOC available?
			// HINT: http://de.selfhtml.org/xml/darstellung/xpathsyntax.htm#position_bedingungen
			// - recursive descent operator = getElementsByTag
			$oTableOfContentsAnchors = $oBodyContentXPath->query( "//*[@id='toc']//a" );
			$oTocTableElement->item( 0 )->setAttribute( 'id', 'toc-'.$sPageTitleHeadingJumpmark ); //make id unique
			$oTocTitleElement = $oBodyContentXPath->query( "//*[contains(@class, 'toctitle')]" )->item(0);
			$oTocTitleElement->setAttribute( 'id', 'toctitle-'.$sPageTitleHeadingJumpmark ); //make id unique;
			$oTocTitleElement->setAttribute( 'class', 'toctitle' );
		}

		//Build up <bookmarks> tree
		$oParentBookmark = $oPageTitleBookmarkElement;
		$iParentLevel = 0;
		$aHeadingLevels = array_flip(
			array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' )
		);
		for ( $i = 1; $i < $oHeadingElements->length; $i++ ) {
			$oHeadingElement     = $oHeadingElements->item( $i );
			$sHeadingTextContent = trim( $oHeadingElement->textContent );
			//In $sPageTitleHeadingJumpmark there is the PageTitle AND the RevisionId incorporated
			$sHeadingJumpmark    = 'bs-ue-jumpmark-'.md5( $sPageTitleHeadingJumpmark.$sHeadingTextContent.$i );

			$oBookmarkElement = $oBookmarksDOM->createElement( 'bookmark' );
			$oBookmarkElement->setAttribute( 'name', $sHeadingTextContent );
			$oBookmarkElement->setAttribute( 'href', '#'.$sHeadingJumpmark );

			$sNodeName = strtolower( $oHeadingElement->parentNode->nodeName );
			$iLevel = $aHeadingLevels[$sNodeName] + 1;
			$iLevelDifference = $iLevel - $iParentLevel;
			if( $iLevelDifference > 0 ) { // e.g H2 -> H3 --> Walk down
				for( $j = 0; $j < $iLevelDifference; $j++ ) {
					if( $oParentBookmark->lastChild !== null ) {
						$oParentBookmark = $oParentBookmark->lastChild;
					}
				}
			}
			elseif( $iLevelDifference < 0 ) { // e.g H6 -> H3 --> Walk up
				for( $j = 0; $j > $iLevelDifference; $j-- ) {
					if( $oParentBookmark->parentNode !== null ) {
						$oParentBookmark = $oParentBookmark->parentNode;
					}
				}
			}
			//else if $iLevelDifference == 0 --> no traversal required
			$iParentLevel = $iLevel;
			$oParentBookmark->appendChild( $oBookmarkElement );

			$oHeadingElementAnchor = self::findPreviousDOMElementSibling( $oHeadingElement, 'a' );
			if( $oHeadingElementAnchor !== null ) {

				$sOrigialNameValue = $oHeadingElementAnchor->getAttribute( 'name' );
				$oHeadingElementAnchor->setAttribute( 'name', $sHeadingJumpmark );

				//Adapt MediaWiki TOC #2
				// TODO RBV (01.02.11 14:58): Make this better
				foreach( $oTableOfContentsAnchors as $oTOCAnchorElement ) {
					if( $oTOCAnchorElement->getAttribute( 'href' ) == '#'.$sOrigialNameValue ) {
						$oTOCAnchorElement->setAttribute( 'href', '#'.$sHeadingJumpmark );
					}
				}
			} else {
				//Inject a new anchor for the PDF bookmarks
				$oNewAnchorTag = $oPageDOM->createElement( 'a' );
				$oNewAnchorTag->setAttribute( 'name' , $sHeadingJumpmark );
				$oHeadingElement->insertBefore( $oNewAnchorTag );
			}
		}

		return $oPageTitleBookmarkElement;
	}

	//Seems not to work...
	//HINT: http://www.php.net/manual/en/domdocument.validate.php#99818
	public static function ensureGetElementByIdAccessibility( DOMNode &$oNode ) {
		if( $oNode->hasChildNodes() ) {
			foreach( $oNode->childNodes as $oChildNode ) {
				if( $oChildNode->hasAttributes() ) {
					$sId = $oChildNode->getAttribute( 'id' );
					if( $sId ) {
						$oChildNode->setAttribute( 'id', $sId );
					}
				}
				self::ensureGetElementByIdAccessibility( $oChildNode );
			}
		}
	}

	/**
	 * Simple DOM traversal helper
	 * @deprecated use BsDOMHelper instead
	 * @param DOMNode $oDOMNode
	 * @param type $sWantedNodeName
	 * @return DOMElement | null
	 */
	public static function findPreviousDOMElementSibling( DOMNode &$oDOMNode, $sWantedNodeName = '' ) {
		$oDOMNodesPrevSibling = $oDOMNode->previousSibling;

		if( $oDOMNodesPrevSibling !== null ) {
			if( $oDOMNodesPrevSibling->nodeType == XML_ELEMENT_NODE ) {
				if( !empty( $sWantedNodeName ) ) {
					if( $oDOMNodesPrevSibling->nodeName == $sWantedNodeName ) {
						return $oDOMNodesPrevSibling;
					}
				}
				else {
					return $oDOMNodesPrevSibling;
				}
			}
			return self::findPreviousDOMElementSibling( $oDOMNodesPrevSibling, $sWantedNodeName );
		}

		return null;
	}
}