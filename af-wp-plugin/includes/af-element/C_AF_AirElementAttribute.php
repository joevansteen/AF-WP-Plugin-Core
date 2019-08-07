<?php
/*
 * C_AF_AirElementAttribute script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-07 JVS Modelled after AirAttributeMetaModel (Java v1.06 implementation)
 *                      with corrections for the rule subject cardinality bug
 *
 * Provides an individual core specification for a C_AF_AirElement that is intended
 * to be largely independent of the operating environment and user interface details.
 * C_AF_AirElementAttribute items are the specification details for a C_AF_AirElementModel.
 * Each C_AF_AirElementAttribute item defines a property, relationship, behavior or other
 * attribute of the C_AF_AirElement that is being described.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirElementAttribute';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

class C_AF_AirElementAttribute extends C_AirObjectBase
{
   // Constants
//   const AIR_ELEMENTS		= 'air_elements';
//   const AIR_INDEX			= 'air_eleindex';
//   const AIR_PROPERTIES		= 'air_eleproperties';
//   const AIR_ASSOCIATIONS	= 'air_eleassociations';
//   const AIR_RELATIONSHIPS	= 'air_elerelationships';
//   const AIR_RULES			= 'air_relrules';
//   const AIR_USERS			= 'user';
//
//	const MIN_AIR_ID_SIZE	= 40;

	private $repository	= NULL;	// The repository manager for the collection
	private $myRelClass	= '';		// The RelClass for this attribute
	private $rule			= NULL;	// The rule database object defining the attribute specification
	private $predicate	 = NULL;	// The predicate of the rule defining the attribute specification
	private $predDocument = NULL;	// The C_AF_AirElementDoc for the predicate of the rule defining the attribute specification
	private $metaData		= array(); // The metaData model describing this attribute

	private $ruleGuid;
	private $ruleName;
	private $predName;

	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing.
	 *******/
	function __construct(& $repository, $ruleGuid)
	{
		// Propogate the construction process
		parent::__construct();

		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

	 	if (($repository == NULL)
	 	 || (!($repository instanceof C_AF_AirRepository)))
		 	{
		 	throw new Exception('Invalid instantiation');
		 	}

	 	if (($ruleGuid == NULL)
	 	 || (empty($ruleGuid)))
		 	{
		 	throw new Exception('Invalid instantiation');
		 	}

		$this->repository = $repository;
		$this->rule = $this->repository->getElementRule($ruleGuid);
		$this->predicate = $this->rule->getPredicate();
		$this->predDocument = & $this->repository->getElementRef($this->predicate);

		$this->buildMetaData();

		$ruleName = $this->repository->getElementName($ruleGuid);
		$predName = $this->predDocument->getPreferredName();
		$this->predName = $predName;
		$this->ruleGuid = $ruleGuid;
		$this->ruleName = $ruleName;
//		$this->anchor->putTraceData(__LINE__, __FILE__, 'Attribute is '.$ruleName.' [Predicate: '.$predName.']');
	}

    /**
     * Retrieves the internal AIR GUID string for the relationship rule.
     * @return the internal AIR GUID string for the relationship rule
     * @see #AirRelRule
     */
    public function getIdent() {
        return $this->rule->getIdent();
    }

    /**
     * Retrieves the collection of AirRelRule subject GUIDs.
     * @return the internal AIR GUID for the relationship rule subject
     */
    public function getSubject() {
        return $this->rule->getSubject();
    }

    /**
     * Retrieves the AirRelRule predicate GUID.
     * @return the internal AIR GUID for the relationship rule predicate
     */
    public function getPredicate() {
        return $this->rule->getPredicate();
    }

    /**
     * Retrieves the internal AIR GUID for the ordinality specification for the rule predicate.
     * This specification defines whether or not the predicate relationship specified on
     * this rule is mandatory or optional for the subject element. It defines the minimum number
     * of occurences for the predicate relationship relative to the subject element. The minimum
     * is either 0 or 1. If the ordinality is 'optional' then the minimum is 0. If the ordinality
     * is ' mandatory' then the minimum is 1. If a predicate is defined as mandatory, then it is
     * an error if the relationship is not specified or does not exist for a given element. If the
     * ordinality is a 'fuzzy' statement (e.g., should, or should not) then the 'error' is also fuzzy.
     * @return the predicate ordinality GUID.
     */
    public function getPredicateOrdinality() {
        return $this->rule->getPredicateOrdinality();
    }

    /**
     * Retrieves the internal AIR GUID for the cardinality specification for the rule predicate.
     * This specification defines whether or not multiple occurences of the predicate
     * relationship are allowed for the given subject element.
     * @return the predicate cardinality GUID.
     */
    public function getPredicateCardinality() {
        return $this->rule->getPredicateCardinality();
    }

    /**
     * Retrieves the value for the maximum occurences of the rule predicate.
     * This specification defines the maximum number of predicate relationships of this
     * type that may exist for this subject element. This value is only meaningful if the
     * cardinality specification indicates that multiple predicates are valid. A zero value as the
     * maximum when multiple relationships are valid is considered to signify no constraint on the
     * maximum predicates.
     * @return the predicate maximum occurence value.
     */
    public function getPredicateMaxOccurences() {
        return $this->rule->getPredicateMaxOccurences();
    }

    /**
     * Retrieves the AirRelRule object GUID.
     * @return the internal AIR GUID for the relationship rule object
     */
    public function getObject() {
        return $this->rule->getObject();
    }

    /**
     * Retrieves the AirRelRule indirect object GUID.
     * @return the internal AIR GUID for the relationship rule indirect object. May be empty.
     */
    public function getIObject() {
        return $this->rule->getIObject();
    }

    /**
     * Retrieves the AirRelRule diagnostic GUID.
     * @return the internal AIR GUID of the diagnostic element for this rule. May be empty.
     */
    public function getDiagnostic() {
        return $this->rule->getDiagnostic();
    }

	/***************************************************************************
	 * getMetadataArray()
	 *
	 * Returns the array of metadata for the attribute.
	 *******/
	public function getMetadataArray()
	{
		return $this->metaData;
	}

	/***************************************************************************
	 * conflictsWith()
	 *
    * Used to determine if this item conflicts with a peer item. A conflict exists if when both
    * Attributes are associated with the same element they would contradict one another. Conflicts
    * exist when property attributes define the same predicate, when associations define the same
    * predicate and object, or when coordination models define the same predicate, object and
    * indirect object. Conflicts such as these can be coded, mostly by mistake, when the same
    * subject predicate pair is defined with different ordinality or cardinality specifications.
    * This will normally happen when different ordinality or cardinality is meant for different
    * subject types, but overlapping sets of subjects are defined on different rules which implement
    * the different ordinality or cardinality specifications. These cases can be identified by use of
    * this function.
    * @param peerAttribute the attribute to be compared
    * @return true if a conflict exists, otherwise false
	 *******/
	public function conflictsWith($peerAttribute)
	{
		$conflicts = false;

      // Conflicts can only exist if both attributes are of the same class
      if ($this->myRelClass == $peerAttribute->myRelClass)
      {
      	switch ($this->myRelClass)
      	{
				case AIR_RelClass_Properties:
					// Properties are equivalent if the predicate (the property type)
					// is the same.
            	if ($this->getPredicate()  == $peerAttribute->getPredicate())
            	{
                  $conflicts = true;
               }
               break;

            case AIR_RelClass_Associations:
					// Associations are equivalent if the predicate (the association type)
					// is the same and the related object type is the same.
               if (($this->getPredicate()  == $peerAttribute->getPredicate())
                && ($this->getObject()  == $peerAttribute->getObject()))
               {
                  $conflicts = true;
               }
               break;

            case AIR_RelClass_CoordModels:
					// CoordModels are equivalent if the predicate (the coordination type)
					// is the same, the related object type is the same, and the indirect
					// object type (the coordination vehicle) is the same.
               if (($this->getPredicate()  == $peerAttribute->getPredicate())
                && ($this->getObject()  == $peerAttribute->getObject())
                && ($this->getIObject()  == $peerAttribute->getIObject()))
               {
               	$conflicts = true;
               }
               break;

            default:
            	throw new Exception('Invalid RelClass');
      	}
      }
		return $conflicts;
	}

	/***************************************************************************
	 * buildMetaData()
	 *
	 * This function builds the metadata information for this attribute specification.
	 * The attribute (this object) defines a single property or characteristic of an
	 * AIR Element. The metadata ($this->metaData variable in this object) defines the
	 * properties of the attribute, such as its name, data type, visibility, capture
	 * strategy, standard sort position, etc. The metadata items are identified by
	 * standard tag names.
	 *******/
	private function buildMetaData()
	{
   	$this->metaData['specType'] = 'basic';
      $this->metaData['elementSpec'] = true;
      $this->buildRuleMetaData();
      $this->buildPredicateMetaData();
	}

	/***************************************************************************
	 * buildRuleMetaData()
	 *
    * Constructs the metadata attribute items that are derived from the rule data.
	 *******/
	private function buildRuleMetaData()
	{
		$this->metaData['RuleIdent']		= $this->getIdent();
		if (! $this->anchor->isValidGuid($this->metaData['RuleIdent']))
		{
			$this->anchor->putTraceData(__LINE__, __FILE__, '*** Invalid Rule RuleIdent specification: '
														.$this->ruleName.' ['.$this->ruleGuid.']');
		}
      $this->metaData['PredType']		= $this->getPredicate();
		if (! $this->anchor->isValidGuid($this->metaData['PredType']))
		{
			$this->anchor->putTraceData(__LINE__, __FILE__, '*** Invalid Rule PredType specification: '
														.$this->ruleName.' ['.$this->ruleGuid.']');
		}
      $this->metaData['SubjType']		= $this->getSubject();
/*		if (! $this->anchor->isValidGuid($this->metaData['SubjType']))
		{
			$this->anchor->putTraceData(__LINE__, __FILE__, '*** Invalid Rule SubjType specification: '
														.$this->ruleName.' ['.$this->ruleGuid.']');
		}
*/
      $this->metaData['PredOrdSpec']	= $this->getPredicateOrdinality();
		if (! $this->anchor->isValidGuid($this->metaData['PredOrdSpec']))
		{
			$this->anchor->putTraceData(__LINE__, __FILE__, '*** Invalid Rule PredOrdSpec specification: '
														.$this->ruleName.' ['.$this->ruleGuid.']');
		}
      $this->metaData['PredCardSpec']	= $this->getPredicateCardinality();
		if (! $this->anchor->isValidGuid($this->metaData['PredCardSpec']))
		{
			$this->anchor->putTraceData(__LINE__, __FILE__, '*** Invalid Rule PredCardSpec specification: '
														.$this->ruleName.' ['.$this->ruleGuid.']');
		}
      $this->metaData['PredCardLimit']	= $this->getPredicateMaxOccurences();
		if (! $this->anchor->isValidGuid($this->metaData['PredCardLimit']))
		{
			$this->anchor->putTraceData(__LINE__, __FILE__, '*** Invalid Rule PredCardLimit specification: '
														.$this->ruleName.' ['.$this->ruleGuid.']');
		}
      $this->metaData['ObjType']			= $this->getObject();
		if (! $this->anchor->isValidGuid($this->metaData['ObjType']))
		{
			$this->anchor->putTraceData(__LINE__, __FILE__, '*** Invalid Rule ObjType specification: '
														.$this->ruleName.' ['.$this->ruleGuid.']');
		}
      $this->metaData['IObjType']		= $this->getIObject();
		if (! $this->anchor->isValidGuid($this->metaData['IObjType']))
		{
			$this->anchor->putTraceData(__LINE__, __FILE__, '*** Invalid Rule IObjType specification: '
														.$this->ruleName.' ['.$this->ruleGuid.']');
		}
      $this->metaData['RuleDiag']		= $this->getDiagnostic();
		if (! $this->anchor->isValidGuid($this->metaData['RuleDiag']))
		{
			$this->anchor->putTraceData(__LINE__, __FILE__, '*** Invalid Rule RuleDiag specification: '
														.$this->ruleName.' ['.$this->ruleGuid.']');
		}
	}

	/***************************************************************************
	 * buildPredicateMetaData()
	 *
    * Constructs the metadata attribute items that are derived from the predicate data.
	 *******/
	private function buildPredicateMetaData()
	{
		$propertyValue = '';
		$element = '';
		$value = '';

      // Create first pass specification
      $propertyValue = $this->getPropertyAttributeContent('Label');
      if (empty($propertyValue))
      {
	 		$this->anchor->putTraceData(__LINE__, __FILE__, 'Predicate short name used as Label override.');
			$propertyValue = $this->predDocument->getShortName();
      }
      $this->metaData['Label'] = $propertyValue;

      $this->myRelClass = $this->getPropertyAttributeContent('RelClass');
      $this->metaData['RelClass']		= $this->myRelClass;

      $this->metaData['External']		= $this->getPropertyAttributeContent('External');
      $this->metaData['Visible']			= $this->getPropertyAttributeContent('Visible');
      $this->metaData['PlugInMod']		= $this->getPropertyAttributeContent('PlugInMod');
      $this->metaData['FldName']			= $this->getPropertyAttributeContent('FldName');
      $this->metaData['Manual']			= $this->getPropertyAttributeContent('Manual');
      $this->metaData['DataCaptType']	= $this->getPropertyAttributeContent('DataCaptType');
      $this->metaData['RelSortKey']		= $this->getPropertyAttributeContent('RelSortKey');
      $this->metaData['SelectionType']	= $this->getPropertyAttributeContent('SelectionType');
      $this->metaData['Default']			= $this->getPropertyAttributeContent('Default');
      $this->metaData['SelTypeFld']		= $this->getPropertyAttributeContent('SelTypeFld');
		if (! $this->anchor->isValidGuid($this->metaData['SelTypeFld']))
		{
			$this->anchor->putTraceData(__LINE__, __FILE__, '*** Invalid SelTypeFld specification: '
														.$this->predName.' ['.$this->predicate.']');
		}

      $dataTypeValue							= $this->getPropertyAttributeContent('DataType');
      $this->metaData['DataType']		= $dataTypeValue;

      // Adjust based on datatype
      $cardSpecValue							= $this->metaData['PredCardSpec'];

      if (($dataTypeValue == AIR_ContentType_UUID)
       || ($dataTypeValue == AIR_ContentType_UUIDList))
      {
      	$capture = $this->metaData['DataCaptType'];
      	if (($capture == AIR_CaptureType_KeyEntry)
   		 || ($capture == AIR_CaptureType_KeyOrSel))
   		{
		 		$this->anchor->putTraceData(__LINE__, __FILE__, 'Keyed entry of UUID ['.$this->metaData['FldName'].'] on '.$this->predDocument->getPreferredName().'!!!');
   		}
      }

      if ($dataTypeValue == AIR_ContentType_UUIDList)
      {
			$this->metaData['specType'] = 'compound';
         if ($cardSpecValue == AIR_RelIsUnique)
         {
//		 		$this->anchor->putTraceData(__LINE__, __FILE__, 'UUIDList cardinality conflict');
//          throw new Exception('UUIDList cardinality conflict');
         }
      } else {
         if ($cardSpecValue == AIR_RelIsCollection)
         {
//		 		$this->anchor->putTraceData(__LINE__, __FILE__, 'datatype cardinality conflict');
//          throw new Exception('datatype cardinality conflict');
         }
      }

		if ($this->myRelClass == AIR_RelClass_Properties)
		{
			// Nothing special at this point
      }
      else if ($this->myRelClass == AIR_RelClass_Associations)
     	{
         $this->metaData['SelectionType']	= $this->getObject();
         $this->metaData['Default']			= '';
         $this->metaData['SelTypeFld']		= '';
         $hasInverseValue = $this->getPropertyAttributeContent('HasInverse');
         if (empty($hasInverseValue))
         {
         	$hasInverseValue = 'false';
         }
         $hasInverse = $this->anchor->getBoolEvaluation($hasInverseValue);
        	$this->metaData['HasInverse'] = $hasInverse;

         if ($hasInverse)
         {
         	$invPredTypeValue = $this->getPropertyAttributeContent('InvPredType');
            if ((empty($invPredTypeValue))
             || ($invPredTypeValue == AIR_Null_Identifier))
            {
            	$this->metaData['HasInverse'] = false;
               throw new Exception('Predicate with hasInverse missing InvPredType');
            } else {
			      $invPredType = $this->anchor->getBoolEvaluation($invPredTypeValue);
            	$this->metaData['InvPredType'] = $invPredType;
            }
         }
      }
      else if ($this->myRelClass == AIR_RelClass_CoordModels)
      {
			// Nothing special at this point
      } else {
         throw new Exception('Invalid RelClass = ['.$this->myRelClass.'] on Attribute ['.$this->predicate.']');
      }
	}

	/***************************************************************************
	 * getPropertyAttributeContent()
	 *
    * Retrieves the C_AF_AirElementDoc property attribute from the predicate
    * document. Elements that are not found will be returned as empty string values.
    * @param property the property attribute name
    * @return the property attribute value
	 *******/
	private function getPropertyAttributeContent($property)
   {
   	$content = NULL;

      $content = $this->predDocument->getElementData($property);
      if ($content == NULL)
      {
      	$content = '';
      }
      return $content;
    }

} // End of class C_AF_AirElementAttribute

 ?>