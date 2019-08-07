<?php
/*
 * C_AF_AirEleModelDoc script Copyright (c) 2005, 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-06 JVS C_AF_AirEleModelDoc refactored from af_element
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirEleModelDoc';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_AirEleModelDoc
	 *
	 * Defines an AIR Element Model Document. This is a specific information
	 * used as an abstract base for model (relationship definition and management)
	 * elements, as opposed to content elements (defined by C_AF_AirEleContentDoc).
	 ***************************************************************************/

class C_AF_AirEleModelDoc extends C_AF_AirElementDoc {

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
		} // end of [Constructor] C_AirEleModelDoc()

	/***************************************************************************
	 * initAirEleModelDoc()
	 *
	 * Initializes a new AirEleModelDoc from specifications supplied by the caller.
	 *******/
	function initAirEleModelDoc($eleType, $eleName = NULL, $eleAuthor = NULL,
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

		$this->initAirElementDoc($eleType,					// Type UUID
										 $thisElementName,		// Document name
										 $thisAuthorId,			// Author
										 $thisEleAnnotation,		// Annotation
										 $thisElementId);			// Document UUID = Element ID
		}	// End of function initAirEleModelDoc()

	/***************************************************************************
	 * getRdfSubject()
	 *******/
	function getRdfSubject()
		{
		return($this->getElementData('RuleSubjType'));
//		return($this->getElementControlData('RdfSubject'));
		} // end of getRdfSubject()

	/***************************************************************************
	 * putRdfSubject()
	 *******/
	function putRdfSubject($newContent)
		{
		return($this->putElementData('RulePredType', $newContent));
//		return($this->putElementControlData('RdfSubject', $newContent));
		} // end of putRdfSubject()

	/***************************************************************************
	 * getRdfPredicate()
	 *******/
	function getRdfPredicate()
		{
		return($this->getElementData('RulePredType'));
//		return($this->getElementControlData('RdfPredicate'));
		} // end of getRdfPredicate()

	/***************************************************************************
	 * putRdfPredicate()
	 *******/
	function putRdfPredicate($newContent)
		{
		return($this->putElementData('RulePredType', $newContent));
//		return($this->putElementControlData('RdfPredicate', $newContent));
		} // end of putRdfPredicate()

	/***************************************************************************
	 * getRdfObject()
	 *******/
	function getRdfObject()
		{
		return($this->getElementData('RuleObjType'));
//		return($this->getElementControlData('RdfObject'));
		} // end of getRdfObject()

	/***************************************************************************
	 * putRdfObject()
	 *******/
	function putRdfObject($newContent)
		{
		return($this->putElementData('RuleObjType', $newContent));
//		return($this->putElementControlData('RdfObject', $newContent));
		} // end of putRdfObject()

	} // End of class C_AF_AirEleModelDoc

?>