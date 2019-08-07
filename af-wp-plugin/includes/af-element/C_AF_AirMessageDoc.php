<?php
/*
 * C_AF_AirMessageDoc script Copyright (c) 2005, 2008 Architected Futures, LLC
 *
 * V1.0 2005-JUL-27 JVS Original code. Bootstrapped from air-document.php
 * V1.2 2005-SEP-08 JVS Code reshaping to utilize data (table) driven logic
 *                      to define data elements managed as part of
 *                      individual element type processing.
 * V1.3 2005-OCT-25 JVS Integration with new standalone PHP environment scripts
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V1.8 2008-APR-06 JVS C_AF_AirMessageDoc refactored from af_message
 *
 * This file defines the C_AF_AirMessageDoc class extension of the common base
 * C_AF_AirDocument. C_AF_AirMessageDoc is the class used to instantiate standard
 * AirMessages which are used to support communications between discretely
 * managed AirComponents.
 *
 * AirMessages adhere to the common base rules for an AirDocument, but are
 * hybridized for the communication function.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirMessageDoc';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_AirMessageDoc
	 *
	 * Defines an AIR Message Document. This is a specific information structure
	 * that is used within the AIR framework to track and control Messages between
	 * clients and servers. It is derived directly from C_AF_AirDocument as a first
	 * order derived data structure.
	 ***************************************************************************/

class C_AF_AirMessageDoc extends C_AF_AirElementDoc {
	var $diagInfoDirty	= true;
	var $diagNodeList		= NULL;

	/***************************************************************************
	 * Constructor
	 *******/

	function __construct()
		{
		// Propogate the construction process
		parent::__construct();

		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$diagInfoDirty			= true;
		$diagNodeList			= NULL;
		}

	/***************************************************************************
	 * initAirMessageDoc()
	 *
	 * Initializes a new AirMessageDoc from specifications supplied by the caller.
	 *
	 * This procedure does not initialize 'content' into the document. It creates
	 * the structural frame for an AirMessageDoc over (or within) the frame (or
	 * envelope) of an AirDocument. The AirDocument creates standard header and
	 * body framing for all AirDocument objects. This initialization further fills
	 * in the framing of message control information as standard for AirMessageDoc
	 * objects.
	 *
	 * After this initialization has been completed, content may be added to the
	 * document using a variety of methods.
	 *******/

	function initAirMessageDoc($messageType)
		{

		$messageId = $this->anchor->create_UUID();
		$messageName = 'Message '.$messageId;

		$this->initAirElementDoc($messageType,						// Type UUID
										 $messageName,						// Document name
										 AIR_UUID_Air,						// Author (s/b the 'local' system - see notes in initializeRepository function)
										 'Automatic Entry',				// Annotation
										 $messageId);						// Document UUID = Message ID

		// $this->anchor->putTraceData(__LINE__, __FILE__, ' Creating MessageControl node ... ');
		$this->putElementControlData('MessageId',		$messageId);
		$this->putElementControlData('GlobalUowId',	'*** GLOBAL UOW *** ');
		$this->putElementControlData('ParentUowId',	' *** PARENT UOW *** ');
		$this->putElementControlData('DestObject',		'');
		$this->putElementControlData('DestAction',		'');
		$this->putElementControlData('DestVers',		'');
		$this->putElementControlData('SourceObject',	'');
		$this->putElementControlData('SourceAction',	'');
		$this->putElementControlData('SourceVers',		'');
		$this->putElementControlData('ProcOptions',	'***PROC OPTIONS***');
		$this->putElementControlData('ModeId',			AIR_ProcMode_Production);
		$this->putElementControlData('Routing',			'***ROUTING***');
		$this->putElementControlData('AuthId',			'***AUTH***');
		}

	/***************************************************************************
	 * getMessageControlData()
	 *******/
	function getMessageControlData($itemId)
		{
		return($this->getElementControlData($itemId));
		} // end of getMessageControlData()

	/***************************************************************************
	 * putMessageControlData()
	 *******/
	function putMessageControlData($itemId, $newContent)
		{
		return($this->putElementControlData($itemId, $newContent));
		} // end of putMessageControlData()

	/***************************************************************************
	 * getMessageData()
	 *******/
	function getMessageData($itemId)
		{
		return($this->getElementData($itemId));
		} // end of getMessageData()

	/***************************************************************************
	 * putMessageData()
	 *******/
	function putMessageData($itemId, $newContent)
		{
		return($this->putElementData($itemId, $newContent));
		} // end of putMessageData()

	/***************************************************************************
	 * appendMessageDataCollection()
	 *******/
	function appendMessageDataCollection($elementId, $newContent)
		{
		return($this->appendElementDataCollection($elementId, $newContent));
		} // end of appendMessageDataCollection()

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

	} // End of class C_AF_AirMessageDoc

?>