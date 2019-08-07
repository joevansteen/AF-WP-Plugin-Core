<?php
/*
 * af_document script Copyright (c) 2005, 2008 Architected Futures, LLC
 *
 * V1.0 2005-MAY-25 JVS Original code.
 * V1.1 2005-MAY-27 JVS Extension of base function to include support for a
 *                  base class of general DOM functions, which the
 *                  C_AF_AirDocument class can inherit. The C_DomDocument base class
 *                  will also provide support for routine DOM functions and
 *                  data manipulation without the C_AF_AirDocument overhead.
 * V1.1 2005-AUG-04 JVS Migrate AirDocument persistance logic to be a feature of
 *                  the C_AF_AirDocument class, rather than an independent facility
 *                  within the Anchor. (Documents still use anchor methods as
 *                  means to achieve database access.
 * V1.2 2005-SEP-08 JVS Code reshaping to utilize data (table) driven logic
 *                      to define data elements managed as part of
 *                      individual element type processing.
 * V1.3 2005-OCT-25 JVS Integration with new standalone PHP environment scripts
 * V1.7 2008-FEB-29 JVS Begin conversion to PHP5 with new DOM. Initial coding is not
 *                      intended to be 'optimized' but simply to be functional.
 * V1.8 2008-MAR-19 JVS Continuation of clean-up of DOM code for PHP5
 * V1.8 2008-APR-13 JVS C_AF_AirDocument refactored from af_document *
 *
 * This file defines the C_AF_AirDocument class which serves as the base
 * for all C_AF_AirElementDoc. It is a bridge between the XML document structure
 * of the AIR elements and the underlying XML DOM implementation.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirDocument';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_AirDocument
	 *
	 * Defines an DOM_DOCUMENT in an AIR DOM tree structure. This is an extension of
	 * an C_DomDocument and is specifically tailored to the representation of an AIR
	 * object, with its unique extensions, as implemented over the DOM model. It
	 * is a specific tailoring of W3C documents to have particular types of behavior
	 * as fundamental characteristics. The DOM_DOCUMENT starts with a DOM DOCUMENT
	 * as a base, and extends it.
	 *
	 * The constructor for C_AF_AirDocument (and its descendents) is minimalistic,
	 * and construction is accomplished by creation of a 'new' object shell (as
	 * created by the constructor) followed by either:
	 *
	 *		1) the execution of an 'initialize' function to complete the process
	 *			of building a new object, or
	 *		2) the execution of a 'load' function to recreate the object from a
	 *			serialized copy of a prior instance.
	 *
	 * New objects create new identities. Restored objects must recreate the old
	 * identity and must be able to be 'created' as corresponding objects
	 * at the correct level of the class hierarchy.
	 ***************************************************************************/
class C_AF_AirDocument extends C_DomDocument {
	var $collectionElementId	= NULL;
	var $collectionNodeList		= NULL;

	/***************************************************************************
	 * Constructor
	 *******/

	function __construct()    			// reference
		{
		$baseNode = new DOMDocument('1.0', 'UTF-8');

		// Propogate the construction process
		parent::__construct($baseNode);

		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		}

	/***************************************************************************
	 * initializeAirDoc()
	 *
	 * Initializes an AirDocument from specifications supplied by the caller.
	 *
	 * This procedure does not initialize 'content' into the document. It creates
	 * the structural frame for an AirDocument, initializes the header segments,
	 * initializes an empty manifest in the control segment, and creates an empty
	 * document body segment.
	 *******/

	function initializeAirDoc($docType,								// Type UUID
								$docName,									// Document name
								$docId,										// Document UUID
								$docAuthor,									// Author UUID
								$docComment = NULL,						// Annotation
								$infoState	= AIR_EleState_Current,	// row status
								$keyExt		= '0',						// key discriminator
								$keySerType	= AIR_EleSerial_Only,	// Serial flag
								$keySerial	= 0 )							// Serial number
		{
		$myTimestamp	= date("YmdHisO"); // YYYYMMDDHHMMSS+nnnn

		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Syntax note for documentation and debug comments:
		 *
		 * Inadvertent use of a syntax of a->b where both a and b were
		 * specified as PHP variable (with a dollar sign) caused
		 * the interpreter to wander and stop processing during
		 * the file load process. Thus the debug documentation
		 * syntax change to class::function, which seems okay.
		 */
		if (isset($docAuthor) && (!is_string($docAuthor)))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid docAuthor string to " . __CLASS__ . '::' . __FUNCTION__);
			}
		elseif (isset($docType) && (!is_string($docType)))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid docType string to " . __CLASS__ . '::' . __FUNCTION__);
			}
		elseif (isset($docName) && (!is_string($docName)))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid docName string to " . __CLASS__ . '::' . __FUNCTION__);
			}
		elseif (isset($docId) && (!is_string($docId)))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid docId string to " . __CLASS__ . '::' . __FUNCTION__);
			}
		elseif (isset($infoState) && (!is_string($infoState)))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid infoState string to " . __CLASS__ . '::' . __FUNCTION__);
			}
		elseif (isset($keyExt) && (!is_string($keyExt)))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid keyExt string to " . __CLASS__ . '::' . __FUNCTION__);
			}
		elseif (isset($keySerType) && (!is_string($keySerType)))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid keySerType string to " . __CLASS__ . '::' . __FUNCTION__);
			}
		elseif (isset($keySerial) && (!is_integer($keySerial)))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid keySerial string to " . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode = $this->documentElement();
		if (!isset($rootNode))
			{
			// create the frame
			// trigger_error('< ' . __CLASS__ . '::' . __FUNCTION__ . ' >' , E_USER_NOTICE);
			$node = parent::createElement('AirDocument');
			// trigger_error('< ' . __CLASS__ . '::' . __FUNCTION__ . ' >' , E_USER_NOTICE);
			$node->setAttribute("version", "1.0.0");		// our document model version
			// trigger_error('< ' . __CLASS__ . '::' . __FUNCTION__ . ' >' , E_USER_NOTICE);
			$docRoot = parent::appendChild($node);

			// trigger_error('< ' . __CLASS__ . '::' . __FUNCTION__ . ' >' , E_USER_NOTICE);
			// create the header
			$node = parent::createElement('DocHeader');
			$docHdr = $docRoot->appendChild($node);

			// DocIdent section
			$node0 = parent::createElement('DocIdent');

			$node = parent::createTextElement('UUID', $docId);
			$node0->appendChild($node);

			$node = parent::createTextElement('Status', $infoState);
			$node0->appendChild($node);

			$node = parent::createTextElement('KeyDiscriminator', $keyExt);
			$node0->appendChild($node);

			$node = parent::createTextElement('SerialNo', $keySerial);
			$node0->appendChild($node);

			$node = parent::createTextElement('SerialType', $keySerType);
			$node0->appendChild($node);

			$docHdr->appendChild($node0);

			// DocControl section
			$node0 = parent::createElement('DocControl');

				// DocType
			$node = parent::createTextElement('DocType', $docType);
			$node0->appendChild($node);

				// DocName
			$node = parent::createTextElement('DocName', $docName);
			$node0->appendChild($node);

				// Origin
			$node1 = parent::createElement('Origin');
					// Creation
			$node2 = parent::createElement('Creation');

			$node = parent::createTextElement('EntityId', $docAuthor);
			$node2->appendChild($node);

			$node = parent::createTextElement('Timestamp', $myTimestamp);
			$node2->appendChild($node);

			$node1->appendChild($node2);
					// LastChange
			$node2 = parent::createElement('LastChange');

			$node = parent::createTextElement('Type', AIR_EleChgType_Insert);
			$node2->appendChild($node);

			$node = parent::createTextElement('EntityId', $docAuthor);
			$node2->appendChild($node);

			$node = parent::createTextElement('Timestamp', $myTimestamp);
			$node2->appendChild($node);

			$node = parent::createTextElement('Comment', $docComment);
			$node2->appendChild($node);

			$node1->appendChild($node2);

			$node0->appendChild($node1);

				// ChangePending
			$node = parent::createTextElement('ChangePending', AIR_EleChgPending_N);
			$node0->appendChild($node);

				// EffectiveFrom
			$node = parent::createTextElement('EffectiveFrom', $myTimestamp);
			$node0->appendChild($node);

				// EffectiveTo
			$node = parent::createTextElement('EffectiveTo', $myTimestamp);
			$node0->appendChild($node);

				// Manifest
			$node1 = parent::createElement('Manifest');
					// ItemCount
			$node = parent::createTextElement('ItemCount', 0);
			$node1->appendChild($node);

			$node0->appendChild($node1);

			$docHdr->appendChild($node0);

			// create the body
			$node = parent::createElement('DocBody');
			$docBody = $docRoot->appendChild($node);
			}
		} // end of initializeAirDoc()

	/***************************************************************************
	 * createEleDictSegment()
	 *
	 * Appends an element reference dictionary segment into the body of an AirDocument.
	 *
	 * Updates the DocControl segment in the header to reflect the addition
	 * of a new content segment in the body of the document.
	 *******/

	function createEleDictSegment()
		{
		$myTimestamp	= date("YmdHisO"); // YYYYMMDDHHMMSS+nnnn

		/*
		 * Syntax note for documentation and debug comments:
		 *
		 * Inadvertent use of a syntax of a->b where both a and b were
		 * specified as PHP variable (with a dollar sign) caused
		 * the interpreter to wander and stop processing during
		 * the file load process. Thus the debug documentation
		 * syntax change to class::function, which seems okay.
		 */

		if (!isset($this->node))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("No base node in " . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		if (!isset($rootNode))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("No root node in " . __CLASS__ . '::' . __FUNCTION__);
			}
		$bodyNode		= $rootNode->getChildByName('DocBody');
		if (!isset($bodyNode))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();

			$value = $this->serialize(true);
			echo htmlspecialchars($value, ENT_QUOTES);

			die ("No DocBody in " . __CLASS__ . '::' . __FUNCTION__);
			}
		$docDictNode	= $bodyNode->getChildByName('EleDict');
		if (isset($docDictNode))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Previous EleDict in " . __CLASS__ . '::' . __FUNCTION__);
			}

		// $this->putTraceData(__LINE__, __FILE__, " Creating EleDict node ... ");
		$dictNode = $this->createElement('EleDict');

		$node = $this->createTextElement('ItemCount', '0');
		$dictNode->appendChild($node);

		// $this->putTraceData(__LINE__, __FILE__, " attaching EleDict to base doc ... ");
		$bodyNode->appendChild($dictNode);

		} // end of createEleDictSegment()

	/***************************************************************************
	 * load()
	 *
	 * Initializes an AirDocument from an xml stream that contains a
	 * previously serialized AirDocument.
	 *******/
	function load(&$xmlString)
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (!isset($xmlString) || (!is_string($xmlString)))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			trigger_error('< ' . __CLASS__ . '::' . __FUNCTION__ . ' >' , E_USER_NOTICE);
			die ("Missing or non-string xmlString");
			}

		if (!parent::initalizeFromString($xmlString))
			{
			echo 'Document['.__LINE__.'] problem initializing doucment<br/>';
			return(FALSE);
			}

		$root = $this->node->documentElement;
		$nodeName = $root->nodeName;
		if (($nodeName != 'AirDocument')
		 || (!$root->hasAttributes())
		 || (!$root->hasChildNodes()))
			{
			trigger_error(" *** " . __CLASS__ . '::' . __FUNCTION__ . " xml stream not an AirDocument" , E_USER_NOTICE);
			parent::clearDoc();
			return(FALSE);
			}

		// could do more:
		// - validate the version (possibly doing version uplevel dynamically as needed)
		// - validate the superstructure of the tree

		return(TRUE);
		} // end of load()

	/***************************************************************************
	 * serialize()
	 *******/
	function serialize($pretty = FALSE)
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
//		echo "<CHKPT>C_AF_AirDocument serialize()</CHKPT>";
      $this->node->formatOutput = $pretty;
//		echo $this->node->saveXML(true);
		return($this->node->saveXML());
		} // end of serialize()

	/***************************************************************************
	 * logContextInfo()
	 *
	 * Creates a log entry showing standard charcateristics of the doucment and
	 * providing a readable content dump. Note, the format of the content dump is
	 * accomplished by the 'putTraceData' function where the extra 'true' parameter
	 * calls for "pretty" XML formatting.
	 *******/

	function logContextInfo($pretty = FALSE)
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$serialized = $this->serialize(FALSE);
		$serialSize1 = strlen($serialized);

		$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, ' serialized DOM = '.$serialSize1.' bytes');
		$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, $serialized, true);

		} // end of logContextInfo()

	/***************************************************************************
	 * persist()
	 *
	 * Persist the C_AF_AirDocument to the database.
	 *

	 * If the Element already exists in the database, it is replaced with the
	 * updated data in the document. If the Element does not yet exist in
	 * the database, a new database element is created.
	 *
	 * This procedure does not modify 'content' in the document. It uses the
	 * content in the document to set the attributes for the database record.
	 *******/
	function persist()
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Inspect the database for an element which
		 * already exists using this UUID
		 */
		$dbData = $GLOBALS['AF_INSTANCE']->Repository->airDB->get_currAirElement($this->getDocumentId(), 1);

		if ((!isset($dbData))
		 || (is_null($dbData))
		 || (!is_array($dbData)))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ('Invalid DB query return to ' . __FUNCTION__);
			}

		if (count($dbData) > 0)
			{
			$GLOBALS['AF_INSTANCE']->replaceAirElement($this);
			}
		else
			{
			$GLOBALS['AF_INSTANCE']->insertAirElement($this);
			}
		}

	/***************************************************************************
	 * getDocumentId()
	 *******/

	function getDocumentId()
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Retrieve the UUID of the document using
		 * XPATH-ish retrieval of UUID
		 * including adjustment for XML_DOM error in recognition
		 * of TEXT node at tail of search
		 */

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$identNode		= $headerNode->getChildByName('DocIdent');
		$docId 			= $identNode->getChildContentByName('UUID');
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, " UUID node value = ".$docId." ");
			}

		return($docId);
		} // end of getDocumentId()

	/***************************************************************************
	 * getDocType()
	 *******/
	function getDocType()
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$docType			= $controlNode->getChildContentByName('DocType');

		return($docType);
		} // end of getDocType()

	/***************************************************************************
	 * putDocType()
	 *******/
	function putDocType($newContent)
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$sectionNode	= $headerNode->getChildByName('DocControl');
		$contentNode	= $sectionNode->putChildContentByName('DocType', $newContent);
		if (is_null($contentNode))
			{
			$node = $this->createTextElement('DocType', $newContent);
			$contentNode = $sectionNode->appendChild($node);
			}

		return($contentNode);
		} // end of putDocType()

	/***************************************************************************
	 * getDocName()
	 *******/
	function getDocName()
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$docName			= $controlNode->getChildContentByName('DocName');

		return($docName);
		} // end of getDocName()

	/***************************************************************************
	 * putDocName()
	 *******/
	function putDocName($newContent)
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$sectionNode	= $headerNode->getChildByName('DocControl');
		$contentNode	= $sectionNode->putChildContentByName('DocName', $newContent);
		if (is_null($contentNode))
			{
			$node = $this->createTextElement('DocName', $newContent);
			$contentNode = $sectionNode->appendChild($node);
			}

		return($contentNode);
		} // end of putDocName()

	/***************************************************************************
	 * getDocCreateParty()
	 *******/
	function getDocCreateParty()
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$originNode		= $controlNode->getChildByName('Origin');
		$creationNode	= $originNode->getChildByName('Creation');
		$docData			= $creationNode->getChildContentByName('EntityId');
		//	$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, " EntityId node value = ".$docData." ");

		return($docData);
		} // end of getDocCreateParty()

	/***************************************************************************
	 * getDocCreateTime()
	 *******/
	function getDocCreateTime()
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$originNode		= $controlNode->getChildByName('Origin');
		$creationNode	= $originNode->getChildByName('Creation');
		$docData			= $creationNode->getChildContentByName('Timestamp');
		//	$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, " Timestamp node value = ".$docData." ");

		return($docData);
		} // end of getDocCreateTime()

	/***************************************************************************
	 * getDocUpdateParty()
	 *******/
	function getDocUpdateParty()
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$originNode		= $controlNode->getChildByName('Origin');
		$updateNode		= $originNode->getChildByName('LastChange');
		$docData			= $updateNode->getChildContentByName('EntityId');
		//	$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, " EntityId node value = ".$docData." ");

		return($docData);
		} // end of getDocUpdateParty()

	/***************************************************************************
	 * putDocUpdateParty()
	 *******/
	function putDocUpdateParty($newContent)
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$originNode		= $controlNode->getChildByName('Origin');
		$updateNode		= $originNode->getChildByName('LastChange');
		$contentNode	= $updateNode->putChildContentByName('EntityId', $newContent);
		if (is_null($contentNode))
			{
			$node = $this->createTextElement('EntityId', $newContent);
			$contentNode = $sectionNode->appendChild($node);
			}

		return($contentNode);
		} // end of putDocUpdateParty()

	/***************************************************************************
	 * getDocUpdateTime()
	 *******/
	function getDocUpdateTime()
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$originNode		= $controlNode->getChildByName('Origin');
		$updateNode		= $originNode->getChildByName('LastChange');
		$docData			= $updateNode->getChildContentByName('Timestamp');
		//	$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, " Timestamp node value = ".$docData." ");

		return($docData);
		} // end of getDocUpdateTime()

	/***************************************************************************
	 * putDocUpdateTime()
	 *******/
	function putDocUpdateTime($newContent)
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$originNode		= $controlNode->getChildByName('Origin');
		$updateNode		= $originNode->getChildByName('LastChange');
		$contentNode	= $updateNode->putChildContentByName('Timestamp', $newContent);
		if (is_null($contentNode))
			{
			$node = $this->createTextElement('Timestamp', $newContent);
			$contentNode = $sectionNode->appendChild($node);
			}

		return($contentNode);
		} // end of putDocUpdateTime()

	/***************************************************************************
	 * getDocUpdateType()
	 *******/
	function getDocUpdateType()
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$originNode		= $controlNode->getChildByName('Origin');
		$updateNode		= $originNode->getChildByName('LastChange');
		$docData			= $updateNode->getChildContentByName('Type');
		//	$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, " Timestamp node value = ".$docData." ");

		return($docData);
		} // end of getDocUpdateType()

	/***************************************************************************
	 * putDocUpdateType()
	 *******/
	function putDocUpdateType($newContent)
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$originNode		= $controlNode->getChildByName('Origin');
		$updateNode		= $originNode->getChildByName('LastChange');
		$contentNode	= $updateNode->putChildContentByName('Type', $newContent);
		if (is_null($contentNode))
			{
			$node = $this->createTextElement('Type', $newContent);
			$contentNode = $sectionNode->appendChild($node);
			}

		return($contentNode);
		} // end of putDocUpdateType()

	/***************************************************************************
	 * getDocUpdateComment()
	 *******/
	function getDocUpdateComment()
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$originNode		= $controlNode->getChildByName('Origin');
		$updateNode		= $originNode->getChildByName('LastChange');
		$docData			= $updateNode->getChildContentByName('Comment');
		//	$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, " Timestamp node value = ".$docData." ");

		return($docData);
		} // end of getDocUpdateComment()

	/***************************************************************************
	 * putDocUpdateComment()
	 *******/
	function putDocUpdateComment($newContent)
		{
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$rootNode 		= $this->documentElement();
		$headerNode		= $rootNode->getChildByName('DocHeader');
		$controlNode	= $headerNode->getChildByName('DocControl');
		$originNode		= $controlNode->getChildByName('Origin');
		$updateNode		= $originNode->getChildByName('LastChange');
		$contentNode	= $updateNode->putChildContentByName('Comment', $newContent);
		if (is_null($contentNode))
			{
			$node = $this->createTextElement('Comment', $newContent);
			$contentNode = $sectionNode->appendChild($node);
			}

		return($contentNode);
		} // end of putDocUpdateComment()

	/***************************************************************************
	 * getDocBodyNode()
	 *******/
	function getDocBodyNode($sectionId)
		{
		$searchId 		= null;
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);

		/*
		 * Standard processing pattern
		 */
		$rootNode 		= $this->documentElement();
		$bodyNode		= $rootNode->getChildByName('DocBody');
		$sectionNode	= $bodyNode->getChildByName($sectionId);

		return($sectionNode);
		} // end of getDocBodyNode()

	/***************************************************************************
	 * putDocBodyNode()
	 *******/
	function putDocBodyNode($sectionId, & $newNode)
		{
		$searchId 		= null;
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);

		/*
		 * Standard processing pattern
		 */
		$rootNode 		= $this->documentElement();
		$bodyNode		= $rootNode->getChildByName('DocBody');
		$sectionNode	= $bodyNode->getChildByName($sectionId);

		$contentNode 	= $sectionNode->appendChild($newNode);

		return($contentNode);
		} // end of putDocBodyNode()

	/***************************************************************************
	 * removeDocBodyNode()
	 *
	 * Removes a named node from the designated section in the doc body if the
	 * node exists.
	 *
	 * returns:
	 *		the old node if the value was removed
	 *		NULL if the node was not found
	 *******/
	function removeDocBodyNode($sectionId, $elementId)
		{
//		$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " [$sectionId - $elementId]");

		/*
		 * Standard processing pattern
		 */
		$rootNode 		= $this->documentElement();
		$bodyNode		= $rootNode->getChildByName('DocBody');
		$sectionNode	= $bodyNode->getChildByName($sectionId);
		if (is_null($sectionNode))
			{
			// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " abort 1");
			$contentNode = null;
			}
		else
			{
			$elementNode = $sectionNode->getChildByName($elementId);
			if (is_null($elementNode))
				{
				// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " abort 2");
				$contentNode = null;
				}
			else
				{
				$contentNode = $sectionNode->removeChild($elementNode);
				// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " success?");
				}
			}

		return($contentNode);
		} // end of removeDocBodyNode()

	/***************************************************************************
	 * getDocBodyData()
	 *******/
	function getDocBodyData($sectionId, $elementId)
		{
		$searchId 		= null;
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);

		/*
		 * Standard processing pattern
		 */
		$sectionNode	= $this->getDocBodyNode($sectionId);
		if (!is_null($sectionNode))
			{
			$searchId 		= $sectionNode->getChildContentByName($elementId);
			}
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, ' found '.$sectionId.':'.$elementId.' value = '.$searchId);

		return($searchId);
		} // end of getDocBodyData()

	/***************************************************************************
	 * putDocBodyData()
	 *
	 * Adds a new named node to the designated section in the doc body if the
	 * node does not exist, or updates the contents if the node already exists.
	 *
	 * returns:
	 *		the old node if the value was replaced
	 *		the new node if the value was inserted
	 *		NULL if 'insert' was false or insert failed
	 *******/

	function putDocBodyData($sectionId, $elementId, $newContent, $insert = true)
		{
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);

		/*
		 * Standard processing pattern
		 */
		$rootNode 		= $this->documentElement();
		$bodyNode		= $rootNode->getChildByName('DocBody');
		$sectionNode	= $bodyNode->getChildByName($sectionId);
		if ((is_null($sectionNode))
		 && ($insert))
			{
			$node = $this->createElement($sectionId);
			$sectionNode = $bodyNode->appendChild($node);
			}
		if (is_null($sectionNode))
			{
			$contentNode = null;
			}
		else
			{
			$contentNode	= $sectionNode->putChildContentByName($elementId, $newContent);
			if ((is_null($contentNode))
			 && ($insert))
				{
				$node = $this->createTextElement($elementId, $newContent);
				$contentNode = $sectionNode->appendChild($node);
				}
			}

		return($contentNode);
		} // end of putDocBodyData()

	/***************************************************************************
	 * appendDocBodyDataCollection()
	 *
	 * Adds a new named node to the designated section in the doc body. If the
	 * node already exists, adds a new sibling with the same name.
	 *
	 * returns:
	 *		the old node if the value was replaced
	 *		the new node if the value was inserted
	 *		NULL if 'insert' was false or insert failed
	 *******/

	function appendDocBodyDataCollection($sectionId, $elementId, $newContent, $insert = true)
		{
		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);

		/*
		 * Standard processing pattern
		 */
		$rootNode 		= $this->documentElement();
		$bodyNode		= $rootNode->getChildByName('DocBody');
		$sectionNode	= $bodyNode->getChildByName($sectionId);
		if ((is_null($sectionNode))
		 && ($insert))
			{
			$node = $this->createElement($sectionId);
			$sectionNode = $bodyNode->appendChild($node);
			}
		if (is_null($sectionNode))
			{
			$contentNode = null;
			}
		else
			{
			$node = $this->createTextElement($elementId, $newContent);
			$contentNode = $sectionNode->appendChild($node);
			}

		return($contentNode);
		} // end of appendDocBodyDataCollection()

	/***************************************************************************
	 * getSectionDataCollectionItemCount()
	 *
	 * returns the count of the number of items in a collection of same named
	 * elements.
	 *******/
	function getSectionDataCollectionItemCount($sectionId, $elementId)
		{
		$collectionCount = 0;

		$this->collectionElementId = $elementId;
		$this->collectionNodeList	= NULL;

		$hostNode = $this->getDocBodyNode($sectionId);
		if (! is_null($hostNode))
			{
			$this->collectionNodeList = $hostNode->getElementsByTagName($elementId);
			if (! is_null($this->collectionNodeList))
				{
				$collectionCount = $this->collectionNodeList->length;
				}
			}

		return($collectionCount);
		} // end of getSectionDataCollectionItemCount()

	/***************************************************************************
	 * removeSectionDataCollectionItemByIndex()
	 *
	 * removes a specific item from a collection
	 *******/
	function removeSectionDataCollectionItemByIndex($sectionId, $elementId, $itemIndex)
		{
		$collectionCount = 0;

		$this->collectionElementId = $elementId;
		$this->collectionNodeList	= NULL;

		$hostNode = $this->getDocBodyNode($sectionId);
		if (! is_null($hostNode))
			{
			$this->collectionNodeList = $hostNode->getElementsByTagName($elementId);
			if (! is_null($this->collectionNodeList))
				{
				$collectionCount = $this->collectionNodeList->length;
				if ($collectionCount > $itemIndex)
					{
					$collectionItemNode = $this->collectionNodeList->item($itemIndex);
					$hostNode->removeChild($collectionItemNode);
					$collectionCount -= 1;
					}
				}
			}

		return($collectionCount);
		} // end of removeSectionDataCollectionItemByIndex()

	/***************************************************************************
	 * removeSectionDataCollectionItemByRef()
	 *
	 * removes a specific item from a collection
	 *******/
	function removeSectionDataCollectionItemByRef($sectionId, $elementId, & $itemRef)
		{
		$collectionCount = 0;

		$this->collectionElementId = $elementId;
		$this->collectionNodeList	= NULL;

		$hostNode = $this->getDocBodyNode($sectionId);
		if (! is_null($hostNode))
			{
			$result = $hostNode->removeChild($itemRef);
			}

		return($result);
		} // end of removeSectionDataCollectionItemByRef()

	/***************************************************************************
	 * purgeSectionDataCollectionItems()
	 *
	 * removes an entire collection
	 *******/
	function purgeSectionDataCollectionItems($sectionId, $elementId)
		{
		$collectionCount = 0;

		$this->collectionElementId = $elementId;
		$this->collectionNodeList	= NULL;

		$hostNode = $this->getDocBodyNode($sectionId);
		if (! is_null($hostNode))
			{
			$this->collectionNodeList = $hostNode->getElementsByTagName($elementId);
			if (! is_null($this->collectionNodeList))
				{
				$collectionCount = $this->collectionNodeList->length;
				while ($collectionCount)
					{
					$node = $this->collectionNodeList->item($collectionCount - 1);
					$hostNode->removeChild($node);
					$collectionCount -= 1;
					}
				}
			}

		return($collectionCount);
		} // end of purgeSectionDataCollectionItems()

	} // End of class C_AF_AirDocument

?>