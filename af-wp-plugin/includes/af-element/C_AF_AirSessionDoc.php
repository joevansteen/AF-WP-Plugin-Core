<?php
/*
 * C_AF_AirSessionDoc script Copyright (c) 2005, 2008 Architected Futures, LLC
 *
 * V1.2 2005-SEP-08 JVS Code reshaping to utilize data (table) driven logic
 *                      to define data elements managed as part of
 *                      individual element type processing.
 * V1.2 2005-SEP-13 JVS Remove session document from base document file and
 *                      revise to become a subclass of C_AIR_Element
 * V1.3 2005-OCT-25 JVS Integration with new standalone PHP environment scripts
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V1.8 2008-APR-06 JVS C_AF_AirSessionDoc refactored from af_session
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirSessionDoc';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_AirSessionDoc
	 *
	 * Defines an AIR Session Document. This is a specific information structure
	 * that is used within the AIR framework to track and control sessions between
	 * clients and servers. It is derived directly from C_AF_AirDocument as a first
	 * order derived data structure.
	 ***************************************************************************/

class C_AF_AirSessionDoc extends C_AF_AirElementDoc {

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
	 * initAirSessionDoc()
	 *
	 * Initializes a new AirSessionDoc from specifications supplied by the caller.
	 *
	 * This procedure does not initialize 'content' into the document. It creates
	 * the structural frame for an AirSessionDoc over (or within) the frame (or
	 * envelope) of an AirDocument. The AirDocument creates standard header and
	 * body framing for all AirDocument objects. This initialization further fills
	 * in the framing of session control information as standard for AirSessionDoc
	 * objects.
	 *
	 * After this initialization has been completed, content may be added to the
	 * document using a variety of methods.
	 *******/

	function initAirSessionDoc($sessionType) {
		$sessionId = '*undefined*';
		if ( $GLOBALS['AF_INSTANCE'] == NULL) {
			$sessionId = $AF_RUN_INSTANCE;
		} else {
			$sessionId = $GLOBALS['AF_INSTANCE']->create_UUID();	
		}
		$sessionName = "Session ".$sessionId;

		$this->initAirElementDoc($sessionType,						// Type UUID
										 $sessionName,						// Document name
										 AIR_UUID_Air,						// Author (s/b the 'local' system - see notes in initializeRepository function)
										 'Automatic Entry',				// Annotation
										 $sessionId);						// Document UUID = Message ID

		// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, " Creating SessionControl node ... ");
		$this->putElementControlData('SessionId',		$sessionId);
		$this->putElementControlData('ContextId',		AIR_Context_Global);
		$this->putElementControlData('EleClass',		"***CLASS***");
		$this->putElementControlData('ClientDlgId',	"***CLIENT-DIALOG***");
		$this->putElementControlData('CorrId',			sha1(uniqid(rand(), true)));
		$this->putElementControlData('DialogId',		$GLOBALS['AF_INSTANCE']->create_UUID());
		$this->putElementControlData('ModeId',			AIR_ProcMode_Production);
		$this->putElementControlData('ServiceId',		"***ACTION***");
		$this->putElementControlData('ObjectId',		"***OBJECT***");
		$this->putElementControlData('AuthId',			$GLOBALS['AF_INSTANCE']->create_UUID());

		// temporary
		$this->putElementData('UseKeyedLists', false);
		$this->putElementData('UseNumberLists', true);
		}

	/***************************************************************************
	 * getSessionControlData()
	 *******/
	function getSessionControlData($itemId)
		{
		return($this->getElementControlData($itemId));
		} // end of getSessionControlData()

	/***************************************************************************
	 * putSessionControlData()
	 *******/
	function putSessionControlData($itemId, $newContent)
		{
		return($this->putElementControlData($itemId, $newContent));
		} // end of putSessionControlData()

	/***************************************************************************
	 * getSessionData()
	 *******/
	function getSessionData($itemId)
		{
		return($this->getElementData($itemId));
		} // end of getSessionData()

	/***************************************************************************
	 * putSessionData()
	 *******/
	function putSessionData($itemId, $newContent)
		{
		return($this->putElementData($itemId, $newContent));
		} // end of putSessionData()

	/***************************************************************************
	 * appendSessionDataCollection()
	 *******/
	function appendSessionDataCollection($elementId, $newContent)
		{
		return($this->appendElementDataCollection($elementId, $newContent));
		} // end of appendSessionDataCollection()

	/***************************************************************************
	 * getAuthId()
	 *******/
	function getAuthId()
		{
		return($this->getElementControlData('AuthId'));
		} // end of getAuthId()

	/***************************************************************************
	 * putAuthId()
	 *******/
	function putAuthId($newContent)
		{
		return($this->putElementControlData('AuthId', $newContent));
		} // end of putAuthId()

	/***************************************************************************
	 * getContextId()
	 *******/
	function getContextId()
		{
		return($this->getElementControlData('ContextId'));
		} // end of getContextId()

	/***************************************************************************
	 * putContextId()
	 *******/
	function putContextId($newContent)
		{
		return($this->putElementControlData('ContextId', $newContent));
		} // end of putContextId()

	/***************************************************************************
	 * getCorrId()
	 *******/
	function getCorrId()
		{
		return($this->getElementControlData('CorrId'));
		} // end of getCorrId()

	/***************************************************************************
	 * putCorrId()
	 *******/
	function putCorrId($newContent)
		{
		return($this->putElementControlData('CorrId', $newContent));
		} // end of putCorrId()

	/***************************************************************************
	 * getDialogId()
	 *******/
	function getDialogId()
		{
		return($this->getElementControlData('DialogId'));
		} // end of getDialogId()

	/***************************************************************************
	 * putDialogId()
	 *******/
	function putDialogId($newContent)
		{
		return($this->putElementControlData('DialogId', $newContent));
		} // end of putDialogId()

	/***************************************************************************
	 * getClientDlgId()
	 *******/
	function getClientDlgId()
		{
		return($this->getElementControlData('ClientDlgId'));
		} // end of getClientDlgId()

	/***************************************************************************
	 * putClientDlgId()
	 *******/
	function putClientDlgId($newContent)
		{
		return($this->putElementControlData('ClientDlgId', $newContent));
		} // end of putClientDlgId()

	/***************************************************************************
	 * getModeId()
	 *******/
	function getModeId()
		{
		return($this->getElementControlData('ModeId'));
		} // end of getModeId()

	/***************************************************************************
	 * putModeId()
	 *******/
	function putModeId($newContent)
		{
		return($this->putElementControlData('ModeId', $newContent));
		} // end of putModeId()

	/***************************************************************************
	 * getLoggedUserId()
	 *******/
	function getLoggedUserId()
		{
		return($this->getElementControlData('LoggedUserId'));
		} // end of getLoggedUserId()

	/***************************************************************************
	 * putLoggedUserId()
	 *******/
	function putLoggedUserId($newContent)
		{
		return($this->putElementControlData('LoggedUserId', $newContent));
		} // end of putLoggedUserId()

	/***************************************************************************
	 * putDialogVarTracker()
	 *******/
	function putDialogVarTracker($varName, $varType)
		{
		$newNode = $this->createElement('dlgVar');

		$node = $this->createTextElement('Name', $varName);
		$newNode->appendChild($node);
		$node = $this->createTextElement('Type', $varType);
		$newNode->appendChild($node);

		$this->createNewDataCollectionItem($newNode);
		}

	} // End of class C_AF_AirSessionDoc

?>