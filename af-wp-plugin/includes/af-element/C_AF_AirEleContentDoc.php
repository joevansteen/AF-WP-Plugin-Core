<?php
/*
 * C_AF_AirEleContentDoc script Copyright (c) 2005, 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-06 JVS C_AF_AirEleContentDoc refactored from af_element
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirEleContentDoc';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_AirEleContentDoc
	 *
	 * Defines an AIR Element Content Document. This is a specific information
	 * used as an abstract base for content elements, as opposed to relationship
	 * definition and management elements (defined by C_AirEleModelDoc).
	 ***************************************************************************/

class C_AF_AirEleContentDoc extends C_AF_AirElementDoc {

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
		} // end of [Constructor] C_AirEleContentDoc()

	/***************************************************************************
	 * initAirEleContentDoc()
	 *
	 * Initializes a new AirEleContentDoc from specifications supplied by the caller.
	 *******/
	function initAirEleContentDoc($eleType, $eleName = NULL, $eleAuthor = NULL,
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
			$thisElementName = 'Content Element '.$eleId;
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

		$this->initAirElementDoc($eleType,					// Type UUID
										 $thisElementName,		// Document name
										 $thisAuthorId,			// Author
										 $thisEleAnnotation,		// Annotation
										 $thisElementId);			// Document UUID = Element ID
		}	// End of function initAirEleContentDoc()

	/***************************************************************************
	 * getContentType()
	 *******/
	function getContentType()
		{
		return($this->getElementControlData('ContentType'));
		} // end of getContentType()

	/***************************************************************************
	 * putContentType()
	 *******/
	function putContentType($newContent)
		{
		return($this->putElementControlData('ContentType', $newContent));
		} // end of putContentType()

	/***************************************************************************
	 * getContentSize()
	 *******/
	function getContentSize()
		{
		return($this->getElementControlData('ContentSize'));
		} // end of getContentSize()

	/***************************************************************************
	 * putContentSize()
	 *
	 * n/a - this value is accessible, but is maintain automatically as the
	 * string size of the content.

	function putContentSize($newContent)
		{
		return($this->putElementControlData('ContentSize', $newContent));
		} // end of putContentSize()

	 *******/

	/***************************************************************************
	 * getContent()
	 *******/
	function getContent()
		{
		return($this->getElementData('Content'));
		} // end of getContent()

	/***************************************************************************
	 * putContent()
	 *******/
	function putContent($newContent)
		{
		$sizeValue = strlen($newContent);
		$this->putElementControlData('ContentSize', $sizeValue);
		return($this->putElementData('Content', $newContent));
		} // end of putContent()

	} // End of class C_AF_AirEleContentDoc

?>