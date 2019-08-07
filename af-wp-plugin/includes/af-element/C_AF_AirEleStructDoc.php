<?php
/*
 * C_AF_AirEleStructDoc script Copyright (c) 2005, 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-06 JVS C_AF_AirEleStructDoc refactored from af_element
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirEleStructDoc';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_AirEleStructDoc
	 *
	 * Defines an AIR Element Struct Document. This is a base class (document
	 * type) used to implement structured content within AIR models. This class
	 * is typically used as an abstract base class for concrete classes that define
	 * specific structured documents to support specific element types.
	 ***************************************************************************/

class C_AF_AirEleStructDoc extends C_AF_AirEleContentDoc {

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
		} // end of [Constructor] C_AirEleStructDoc()

	/***************************************************************************
	 * initAirEleStructDoc()
	 *
	 * Initializes a new AirEleStructDoc from specifications supplied by the caller.
	 *******/
	function initAirEleStructDoc($eleType, $eleName = NULL, $eleAuthor = NULL,
															$eleComment = NULL, $eleId = NULL)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (empty($eleId))
			{
			$thisElementId = $this->anchor->create_UUID();
			}
		else
			{
			$thisElementId = $eleId;
			}
		if (empty($eleName))
			{
			$thisElementName = 'Model Element '.$eleId;
			}
		else
			{
			$thisElementName = $eleName;
			}
		if (empty($eleAuthor))
			{
			$thisAuthorId = $this->anchor->sessionDoc->getLoggedUserId();
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

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " eleType = $eleType");
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " thisElementName = $thisElementName");
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " thisElementId = $thisElementId");
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " thisAuthorId = $thisAuthorId");
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " thisEleAnnotation = $thisEleAnnotation");
			}

		$this->initAirEleContentDoc($eleType,					// Type UUID
										  $thisElementName,		// Document name
										  $thisAuthorId,			// Author
										  $thisEleAnnotation,		// Annotation
										  $thisElementId);			// Document UUID = Element ID
		$this->putContentType(AIR_EleContent_Struct);
		}	// End of function initAirEleStructDoc()

	/***************************************************************************
	 * getStructType()
	 *******/
	function getStructType()
		{
		return($this->getElementData('StructType'));
		} // end of getStructType()

	/***************************************************************************
	 * putStructType()
	 *******/
	function putStructType($newContent)
		{
		return($this->putElementData('StructType', $newContent));
		} // end of putStructType()

	/***************************************************************************
	 * getStructSchemaId()
	 *******/
	function getStructSchemaId()
		{
		return($this->getElementData('StructSchemaId'));
		} // end of getStructSchemaId()

	/***************************************************************************
	 * putStructSchemaId()
	 *******/
	function putStructSchemaId($newContent)
		{
		return($this->putElementData('StructSchemaId', $newContent));
		} // end of putStructSchemaId()

	} // End of class C_AF_AirEleStructDoc

?>