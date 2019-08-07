<?php
/*
 * C_AF_AirEleAssocDoc script Copyright (c) 2005, 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-06 JVS C_AF_AirEleAssocDoc refactored from af_element
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirEleAssocDoc';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_AirEleAssocDoc
	 *
	 * Defines an AIR Element Association Document. This is a concrete class
	 * (document type) used to implement element property and association
	 * features of models. Association documents declare 3 elements to define
	 * binary associations between two objects. All associations are declared
	 * in the form of RDF data models with a subject, an object and a predicate.
	 * The predicate defines a particular relationship between the subject and
	 * the object. In AIR terms of binary and ternary, we define this RDF 'triple'
	 * as a 'binary' relationship, even though 3 elements are used to define
	 * the model. These models are fundametal to the complex models described
	 * within BEAMS.
	 ***************************************************************************/

class C_AF_AirEleAssocDoc extends C_AF_AirEleModelDoc {

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
		} // end of [Constructor] C_AirEleAssocDoc()

	/***************************************************************************
	 * initAirEleAssocDoc()
	 *
	 * Initializes a new AirEleAssocDoc from specifications supplied by the caller.
	 *******/
	function initAirEleAssocDoc($eleType, $eleName = NULL, $eleAuthor = NULL,
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
		}	// End of function initAirEleAssocDoc()

	} // End of class C_AF_AirEleAssocDoc

?>