<?php
/*
 * C_AF_AirEleCoordDoc script Copyright (c) 2005, 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-06 JVS C_AF_AirEleCoordDoc refactored from af_element
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirEleCoordDoc';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_AirEleCoordDoc
	 *
	 * Defines an AIR Element Coordination Model Document. This is a concrete class
	 * (document type) used to implement simple models which define structured
	 * arranmgements of three elements as a unit. Coordination documents declare 4
	 * elements to define a series of binary associations between three objects. All
	 * associations imply the existance of the 'contained' 3 association models
	 * that are implied by the single flow model. Association models are declared
	 * in the form of RDF data models with a subject, an object and a predicate.
	 * Coordination models add a fourth element, an indirect object, to the specification.
	 * The predicate defines a particular relationship between the remaining three
	 * elements and implies 3 internal detail predicates that form the basis for
	 * the 3 'contained' associations. In AIR terms we define Coordination models as
	 * ternary relationships even though 4 elements are used to define
	 * the model. These models define 'compounds' that are also considered
	 * fundametal to the complex models described within BEAMS.
	 ***************************************************************************/

class C_AF_AirEleCoordDoc extends C_AF_AirEleModelDoc {

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
		} // end of [Constructor] C_AirEleCoordDoc()

	/***************************************************************************
	 * initAirEleCoordDoc()
	 *
	 * Initializes a new AirEleCoordDoc from specifications supplied by the caller.
	 *******/
	function initAirEleCoordDoc($eleType, $eleName = NULL, $eleAuthor = NULL,
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

		$this->initAirEleModelDoc($eleType,					// Type UUID
										  $thisElementName,		// Document name
										  $thisAuthorId,			// Author
										  $thisEleAnnotation,		// Annotation
										  $thisElementId);			// Document UUID = Element ID
		}	// End of function initAirEleCoordDoc()

	/***************************************************************************
	 * getIndirectObject()
	 *******/
	function getIndirectObject()
		{
		return($this->getElementData('RuleIobjType'));
//		return($this->getElementControlData('IndirectObject'));
		} // end of getIndirectObject()

	/***************************************************************************
	 * putIndirectObject()
	 *******/
	function putIndirectObject($newContent)
		{
		return($this->putElementData('RuleIobjType', $newContent));
//		return($this->putElementControlData('IndirectObject', $newContent));
		} // end of putIndirectObject()

	} // End of class C_AF_AirEleCoordDoc

?>