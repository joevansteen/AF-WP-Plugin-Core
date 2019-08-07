<?php
/*
 * C_AF_AirElementDoc script Copyright (c) 2005, 2008 Architected Futures, LLC
 *
 * V1.0 2005-AUG-04 JVS Original code. Bootstrapped from air-message.php and
 *                      with extracted logic from air-anchor.php
 * V1.2 2005-SEP-08 JVS Code reshaping to utilize data (table) driven logic
 *                      to define data elements managed as part of
 *                      individual element type processing.
 * V1.3 2005-OCT-25 JVS Integration with new standalone PHP environment scripts
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V1.8 2008-APR-06 JVS C_AF_AirElementDoc refactored from af_element
 *
 * This file defines the C_AF_AirElementDoc class extension of the common base
 * C_AF_AirDocument. C_AF_AirElementDoc is the class used to instantiate standard
 * AirElements which are used as the fundamental repository storage units
 * for persistance as well as the fundamental building blocks for models.
 *
 * AirElements adhere to the common base rules for an AirDocument, but are
 * hybridized for the description of model components.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirElementDoc';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_AirElementDoc
	 *
	 * Defines an AIR Element Document. This is a specific information structure
	 * that is used within the AIR framework to instantiate AirElements, which are
	 * used as the fundamental repository storage units for persistance as well as
	 * the fundamental building blocks for models. C_AF_AirElementDoc is derived
	 * directly from C_AF_AirDocument as a first order derived data structure.
	 ***************************************************************************/

class C_AF_AirElementDoc extends C_AF_AirDocument {

	/***************************************************************************
	 * Constructor
	 *******/

	function __construct()
		{
		// Propogate the construction process
		parent::__construct();

		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		}

	/***************************************************************************
	 * initAirElementDoc()
	 *
	 * Initializes a new AirElementDoc from specifications supplied by the caller.
	 *
	 * This procedure does not initialize 'content' into the document. It creates
	 * the structural frame for an AirElementDoc over (or within) the frame (or
	 * envelope) of an AirDocument. The AirDocument creates standard header and
	 * body framing for all AirDocument objects. This initialization further fills
	 * in the framing of Element control information as standard for AirElementDoc
	 * objects.
	 *
	 * After this initialization has been completed, content may be added to the
	 * document using a variety of methods.
	 *******/

	function initAirElementDoc($eleType, $eleName = NULL, $eleAuthor = NULL,
															$eleComment = NULL, $eleId = NULL)
		{

		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (empty($eleId))
			{
			$thisElementId = $GLOBALS['AF_INSTANCE']->create_UUID();
			}
		else
			{
			$thisElementId = $eleId;
			}
		if (empty($eleName))
			{
			$thisElementName = 'Element '.$eleId;
			}
		else
			{
			$thisElementName = $eleName;
			}
		if (empty($eleAuthor))
			{
			$thisAuthorId = $GLOBALS['AF_INSTANCE']->sessionDoc->getLoggedUserId();
			}
		else
			{
			$thisAuthorId = $eleAuthor;
			}
		if (empty($eleComment))
			{
			$thisEleAnnotation = 'Original Entry';
			}
		else
			{
			$thisEleAnnotation = $eleComment;
			}

		if ($GLOBALS['AF_INSTANCE']->eleInsertDebug())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " eleType = $eleType");
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " thisElementName = $thisElementName");
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " thisElementId = $thisElementId");
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " thisAuthorId = $thisAuthorId");
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " thisEleAnnotation = $thisEleAnnotation");
			}

		$this->initializeAirDoc($eleType,				// Type UUID
										$thisElementName,		// Document name
										$thisElementId,		// Document UUID = Element ID
										$thisAuthorId,			// Author
										$thisEleAnnotation);	// Annotation

		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, ' Creating Element Structure ... ');
			}
		$this->createEleDictSegment();

		$rootNode 			= $this->documentElement();
		$bodyNode			= $rootNode->getChildByName('DocBody');

		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, ' Creating ElementControl node ... ');
			}
		$controlNode = $this->createElement('ElementControl');

		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, ' attaching ElementControl to base doc ... ');
			}
		$bodyNode->appendChild($controlNode);

		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, ' Creating ElementData node ... ');
			}
		$dataNode = $this->createElement('ElementData');

		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, ' attaching ElementData to base doc ... ');
			}
		$bodyNode->appendChild($dataNode);
		}	// End of function initAirElementDoc()

	/***************************************************************************
	 * getElementControlData()
	 *******/
	function getElementControlData($itemId)
		{
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
		$searchId 		= $this->getDocBodyData('ElementControl', $itemId);

		return($searchId);
		} // end of getElementControlData()

	/***************************************************************************
	 * putElementControlData()
	 *******/
	function putElementControlData($itemId, $newContent)
		{
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
		$contentNode	= $this->putDocBodyData('ElementControl', $itemId, $newContent);

		return($contentNode);
		} // end of putElementControlData()

	/***************************************************************************
	 * getElementData()
	 *******/
	function getElementData($itemId)
		{
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
		$searchId 		= $this->getDocBodyData('ElementData', $itemId);

		return($searchId);
		} // end of getElementData()

	/***************************************************************************
	 * putElementData()
	 *******/
	function putElementData($itemId, $newContent)
		{
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
		$contentNode	= $this->putDocBodyData('ElementData', $itemId, $newContent);

		return($contentNode);
		} // end of putElementData()

	/***************************************************************************
	 * removeElementNode()
	 *******/
	function removeElementNode($itemId)
		{
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " [$itemId]");
		$contentNode	= $this->removeDocBodyNode('ElementData', $itemId);

		return($contentNode);
		} // end of removeElementNode()

	/***************************************************************************
	 * getPreferredName()
	 *******/
	function getPreferredName($substitute=true)
		{
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
		$nameData = $this->getElementData('PrefLabel');
		if ((empty($nameData))
		 && ($substitute))
			{
			$nameData = $this->getDocName();
			}

		return($nameData);
		} // end of getPreferredName()

	/***************************************************************************
	 * getShortName()
	 *******/
	function getShortName($substitute=true)
		{
//		$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
		$nameData = $this->getElementData('ShortName');
		if ((empty($nameData))
		 && ($substitute))
			{
			$nameData = $this->getElementData('PrefLabel');
			if (empty($nameData))
				{
				$nameData = $this->getDocName();
				}
			}

		return($nameData);
		} // end of getShortName()

	/***************************************************************************
	 * appendElementDataCollection()
	 *******/
	function appendElementDataCollection($elementId, $newContent)
		{
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
		$contentNode	= $this->appendDocBodyDataCollection('ElementData', $elementId, $newContent);

		return($contentNode);
		} // end of appendElementDataCollection()

	/***************************************************************************
	 * createNewDataCollectionItem()
	 *******/
	function createNewDataCollectionItem(& $newNode)
		{
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
		$contentNode	= $this->putDocBodyNode('ElementData', $newNode);

		return($contentNode);
		} // end of createNewDataCollectionItem()

	/***************************************************************************
	 * getDataCollectionItemCount()
	 *
	 * returns the count of the number of diagnostics attached to this message
	 *******/
	function getDataCollectionItemCount($elementId)
		{
		return(parent::getSectionDataCollectionItemCount('ElementData', $elementId));
		} // end of getDataCollectionItemCount()

	/***************************************************************************
	 * getDataCollectionItem()
	 *
	 * gets a specific collection item
	 *******/
	function getDataCollectionItem($elementId, $itemIndex)
		{
		$collectionItemNode = NULL;

		$elements = $this->getDataCollectionItemCount($elementId);

		if ($elements > $itemIndex)
			{
			$collectionItemNode = $this->collectionNodeList->item($itemIndex);
			}

		return($collectionItemNode);
		} // end of getDataCollectionItem()

	/***************************************************************************
	 * getDataCollectionItemContent()
	 *
	 * gets a specific collection item's content
	 *******/
	function getDataCollectionItemContent($elementId, $itemIndex)
		{
		$content = NULL;

		$elements = $this->getDataCollectionItemCount($elementId);

		if ($elements > $itemIndex)
			{
			$collectionItemNode = $this->collectionNodeList->item($itemIndex);
			$content	= $collectionItemNode->getContent();
			}

		return($content);
		} // end of getDataCollectionItemContent()

	/***************************************************************************
	 * removeDataCollectionItemByIndex()
	 *
	 * removes a specific item from a collection
	 *******/
	function removeDataCollectionItemByIndex($elementId, $itemIndex)
		{
		return(parent::removeSectionDataCollectionItemByIndex('ElementData', $elementId, $itemIndex));
		} // end of removeDataCollectionItemByIndex()

	/***************************************************************************
	 * removeDataCollectionItemByRef()
	 *
	 * removes a specific item from a collection
	 *******/
	function removeDataCollectionItemByRef($elementId, & $itemRef)
		{
		return(parent::removeSectionDataCollectionItemByRef('ElementData', $elementId, $itemRef));
		} // end of removeDataCollectionItemByRef()

	/***************************************************************************
	 * purgeDataCollectionItems()
	 *
	 * removes an entire collection
	 *******/
	function purgeDataCollectionItems($elementId)
		{
		return(parent::purgeSectionDataCollectionItems('ElementData', $elementId));
		} // end of purgeDataCollectionItems()

	/***************************************************************************
	 * getDiagnosticCount()
	 *
	 * returns the count of the number of diagnostics attached to this message
	 *******/
	function getDiagnosticCount()
		{
		$diagCount = 0;

		$diagNode = $this->getDocBodyNode('Diagnostics');
		if (is_null($diagNode))
			{
			$this->diagInfoDirty = false;
			$this->diagNodeList	= NULL;
			}
		else
			{
			$this->diagNodeList = $diagNode->getElementsByTagName('Item');
			$this->diagInfoDirty = false;
			}

		if (! is_null($this->diagNodeList))
			{
			$diagCount = $this->diagNodeList->length;
			}

		return($diagCount);
		} // end of getDiagnosticCount()

	/***************************************************************************
	 * getDiagnosticItemData()
	 *
	 * gets a specific diagnostic item
	 *******/
	function getDiagnosticItemData($itemIndex)
		{
		$diagArray = NULL;

		$elements = $this->getDiagnosticCount();

		if ($elements > $itemIndex)
			{
			$diagItemNode = $this->diagNodeList->item($itemIndex);
			$diagArray = array();
			$diagArray['DiagRef']	= $diagItemNode->getChildContentByName('DiagRef');
			$diagArray['DiagType']	= $diagItemNode->getChildContentByName('DiagType');
			$diagArray['DiagIdent'] = $diagItemNode->getChildContentByName('DiagIdent');
			}

		return($diagArray);
		} // end of getDiagnosticItemData()

	/***************************************************************************
	 * attachDiagnosticTextItem()
	 *
	 * adds a new diagnostic item to the message
	 *******/
	function attachDiagnosticTextItem($diagRef, $diagText, $diagLevel = AIR_DiagMsg_Error)
		{
		$this->diagInfoDirty	= true;
		$this->diagNodeList	= NULL;

		$rootNode 		= $this->documentElement();
		$bodyNode		= $rootNode->getChildByName('DocBody');
		$sectionNode	= $bodyNode->getChildByName('ElementControl');
		$areaNode		= $sectionNode->getChildByName('Diagnostics');
		if (is_null($areaNode))
			{
			$node = $this->createElement('Diagnostics');
			$areaNode = $sectionNode->appendChild($node);
			}

		$diagNode = $this->createElement('Item');
		$node = parent::createTextElement('DiagRef', $diagRef);
		$diagNode->appendChild($node);

		$node = parent::createTextElement('DiagType', 'Text');
		$diagNode->appendChild($node);

		$node = parent::createTextElement('DiagLevel', $diagLevel);
		$diagNode->appendChild($node);

		$node = parent::createTextElement('DiagIdent', $diagText);
		$diagNode->appendChild($node);

		$areaNode->appendChild($diagNode);

		return;
		} // end of attachDiagnosticTextItem()

	/***************************************************************************
	 * attachDiagnosticRefItem()
	 *
	 * adds a new diagnostic item to the message
	 *******/
	function attachDiagnosticRefItem($diagRef, $diagIdent)
		{
		$this->diagInfoDirty	= true;
		$this->diagNodeList	= NULL;

		$rootNode 		= $this->documentElement();
		$bodyNode		= $rootNode->getChildByName('DocBody');
		$sectionNode	= $bodyNode->getChildByName('ElementControl');
		$areaNode		= $sectionNode->getChildByName('Diagnostics');
		if (is_null($areaNode))
			{
			$node = $this->createElement('Diagnostics');
			$areaNode = $sectionNode->appendChild($node);
			}

		$diagNode = $this->createElement('Item');
		$node = parent::createTextElement('DiagRef', $diagRef);
		$diagNode->appendChild($node);

		$node = parent::createTextElement('DiagType', 'Ref');
		$diagNode->appendChild($node);

		$node = parent::createTextElement('DiagIdent', $diagIdent);
		$diagNode->appendChild($node);

		$areaNode->appendChild($diagNode);

		return;
		} // end of attachDiagnosticRefItem()

	} // End of class C_AF_AirElementDoc

?>