<?php
/*
 * C_AF_AirElementModel script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-07 JVS Modelled after AirElementModel (Java implementation)
 *
 * Provides a core specification model of an AirElement that is intended to be largely
 * independent of the operating environment and user interface details. The model
 * extends the information in an AirDocument encoding of the element by defining the
 * structural context for the content, including  allowed and disallowed properties,
 * relationships and behaviors that may apply to the element through inheritance or
 * other relationships with its context.
 *
 * These models are dynamically created by the C_AF_AirRepository to describe the
 * characteristics of an C_AF_AirElement.
 *
	 * The original version of this function was hard coded as a series of
	 * procedure calls to build the array dynamically, and controlled by a switch
	 * statement, based on element type. That version was used as scaffolding to
	 * build the database entries for driving version 2. Both version 1 and 2 were
	 * coded as anchor() functions. The following notes are from anchor version 2:
	 *
	 * Two sets of database entries exist to control the process:
	 * - Property Defintion entries, that define the form of each property value.
	 * - Property Rules entries, the describe the association between properties
	 *		and the element types; ie, which elements contain which properties.
	 *
	 * Given a request for a property list for an element type, the construction
	 * strategy is to search the rules enties for the element type in question
	 * and to build a list of property values that are needed in the construction
	 * of that type of element.
	 *
	 * Given a list of property types that are needed, the property entries are then
	 * retrieved to complete the construction of the property list.
	 *
	 * Property lists are considered to be reasonably static artifacts. Once
	 * constructed property lists are cached, and subsequent requests for a property
	 * list for the same element will be retrieved from cache. Caching strategy has
	 * two parts:
	 * - initially the cache entry is constructed on first demand for the list
	 *   within a dialog step process stream. The lists are required at various points
	 *   in the processing, so a chached list can save over 66% of the DB access calls
	 *   that might be required.
	 * - The next step is to cache the dynamically created structure definition in a
	 *   data base table, so that the structure itself can be access with a single
	 *   DB access, rather than needing the multiple accesses and analysis to create
	 *   the structure each time. Since the structures are assumed to have long term
	 *   stability, this can have significant savings, especially as the scale of
	 *   system operations goes up.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirElementModel';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * sortAttributesBySortKey()
	 *
	 * Places attributes into ascending order by sort key. If Sort key values
	 * are equal, elements are placed into ascending order alphabetically.
	 *******/
function sortAttributesBySortKey($attrib1, $attrib2)
	{
	$sortResult = 0;

	$ele1 = $attrib1->getMetadataArray();
	$ele2 = $attrib2->getMetadataArray();

	$key1 = strtolower($ele1['RelSortKey']);
	$key2 = strtolower($ele2['RelSortKey']);

	if ($key1 == $key2)
		{
		$key1a = strtolower($ele1['Label']);
		$key2a = strtolower($ele2['Label']);

		if ($key1a == $key2a)
			{
			$sortResult = 0;
			}
		else
		if ($key1a < $key2a)
			{
			$sortResult = -1;
			}
		else
			{
			$sortResult = 1;
			}
		}
	else
	if ($key1 < $key2)
		{
		$sortResult = -1;
		}
	else
		{
		$sortResult = 1;
		}

	return ($sortResult);
	}

class C_AF_AirElementModel extends C_AirObjectBase
{
	private $repository	= NULL;	// The repository manager for the collection
	private $guid			= NULL;	// The GUID for the element that this object models
	private $hierarchy	= NULL;	// The contextual hierarchy of the element being modelled
	private $attributes	= NULL;	// The attribute list for the element being modelled

	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing. (Be careful with code here
	 * to ensure that we are really talking to the right object.)
	 *******/
	function __construct(& $repository, $guid)
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

	 	if (($guid == NULL)
	 	 || (empty($guid)))
		 	{
		 	throw new Exception('Invalid instantiation');
		 	}

		$this->repository = $repository;
		$this->guid = $guid;

		$this->hierarchy = & $this->buildElementHierarchy();
		$this->attributes = array();
		$this->buildAttributeModel();
	}

	/***************************************************************************
	 * getRuleArray()
	 *
	 * Returns the old style composition map for the element.
	 *******/
	public function & getRuleArray()
	{
		$result = array();
		foreach ($this->attributes as $attribute)
		{
			$result[] = $attribute->getMetadataArray();
		}

		return $result;
	}

	/***************************************************************************
	 * buildElementHierarchy()
	 *
	 * Returns an array of GUIDs defining the element hierarchy of the underlying
	 * element. The hierarchy starts with the base element. If the element is
	 * not an EleType, the next item in the hierarchy is the EleType for the
	 * element. The remaining items are the class hierarchy for the EleType item.
	 * The final item in the hierarchy is the "All" GUID, which is an implied
	 * superclass for all classes.
	 *******/
	private function & buildElementHierarchy()
	{
		$hierarchy = array();
		$finished = false;
		$step = 0;
		$element = NULL;
		$eleType = NULL;
		$functionDebug = true;

		$eleIdent = $this->guid;
		$hierarchy[] = $eleIdent;

		while (! $finished)
		{
			switch ($step)
			{
				case 0:
					if ((empty($this->guid))
					 || ($this->guid == AIR_Null_Identifier)
					 || ($this->guid == AIR_Any_Identifier)
					 || ($this->guid == AIR_All_Identifier))
					{
						$finished = true;
					}
					else
					{
						$element = $this->repository->getElementRef($eleIdent);
						$eleType = $element->getDocType();
						$step++;
					}
					break;

				case 1:
					if ((empty($eleType))
					 || ($eleType == AIR_Null_Identifier))
					{
						$finished = true;
					}
					else if ($eleType != AIR_EleType_EleType)
					{
						$eleIdent = $eleType;
						$element = $this->repository->getElementRef($eleIdent);
						$eleType = $element->getDocType();
					}
					$step++;
					break;

				case 2:
					// eleIdent should now be an EleType element
					if ((empty($eleType))
					 || ($eleType != AIR_EleType_EleType))
					{
						$finished = true;
					}
					else
					{
						if ($eleIdent != $this->guid)
						{
							// If the 'type' was discovered and not input,
							// Add it to the hierarchy
							$hierarchy[] = $eleIdent;
						}
						$eleIdent = $element->getElementData('EleClass');
						if ((empty($eleIdent))
						 || ($eleIdent == AIR_Null_Identifier)
						 || ($eleIdent == AIR_All_Identifier))
						{
							$this->anchor->putTraceData(__LINE__, __FILE__, 'Element Class ['.$eleIdent.'] not found for element '
												.$element->getDocName().'['
												.$element->getDocumentId().']');
							$finished = true;
						}
					}
					$step++;
					break;

				case 3:
					// eleIdent should now be an EleClass element
					$element = $this->repository->getElementRef($eleIdent);
					$eleType = $element->getDocType();
					if ((empty($eleType))
					 || ($eleType != AIR_EleType_EleClass))
					{
						$this->anchor->putTraceData(__LINE__, __FILE__, 'Parent Class not found!');
						$finished = true;
					}
					else
					{
						$hierarchy[] = $eleIdent;
						$eleIdent = $element->getElementData('ParentClass');
						if ((empty($eleIdent))
						 || ($eleIdent == AIR_Null_Identifier)
						 || ($eleIdent == AIR_All_Identifier))
						{
							$finished = true;
						}
					}
					break;

				default:
					$finished = true;
					break;
			}
		}

		$hierarchy[] = AIR_All_Identifier;

		if ($functionDebug)
		{
//			$this->anchor->putTraceData(__LINE__, __FILE__, 'Composition map for: '.$this->repository->getElementName($this->guid));
			$i = 1;
			foreach ($hierarchy as $hElement)
			{
				$eleName = $this->repository->getElementName($hElement);
				if (($hElement == AIR_Null_Identifier)
				 || ($hElement == AIR_Any_Identifier)
				 || ($hElement == AIR_All_Identifier))
				{
					$typeName = 'CONSTANT';
				}
				else
				{
					$element = $this->repository->getElementRef($hElement);
					$eleType = $element->getDocType();
					$typeName = $this->repository->getElementName($eleType);
				}
//				$this->anchor->putTraceData(__LINE__, __FILE__, 'Hierarchy item '.$i.' is '.$eleName.' [Type: '.$typeName.']');
				$i++;
			}
		}

		return $hierarchy;
	}

	/***************************************************************************
	 * buildAttributeModel()
	 *
	 * Uses the information in the hierarchy specification to create the attribute
	 * model.
	 *******/
	private function buildAttributeModel()
	{
//		$this->anchor->putTraceData(__LINE__, __FILE__, 'Attribute Model for: '.$this->repository->getElementName($this->guid));
		foreach ($this->hierarchy as $hElement)
		{
			$collection = $this->repository->getElementRulesList($hElement);
			$colSize = $collection->getSize();
//			$this->anchor->putTraceData(__LINE__, __FILE__, $colSize.' attributes of: '.$this->repository->getElementName($hElement));
			$proxy = $collection->getFirst();
			while ($proxy != NULL)
			{
				$attributeID = $proxy->getGuid();
				// We only keep one (the first) instance of any rule specification
				// Secondary instances (from higher level classes) are deemed to be
				// overridden. Althought it might make sense in some cases to eliminate
				// the multiple duplicate specs and allow the higher level classes to
				// provide the rule. (In most cases, this occurs due to properties assigned
				// to 'all' object types.)

				if (! array_key_exists($attributeID, $this->attributes))
				{
					$attribute = new C_AF_AirElementAttribute($this->repository, $attributeID);
					// Given an attribute, we need to check for specificational conflict
					// with the existing attributes. This can occur when different rules
					// specifiy overlapping or conflicting specifications. In this case,
					// the first (lower level) rule is considered an 'override' and the
					// more recent rule is not applied.
					$conflict = false;
					foreach ($this->attributes as $key => $storedAttibute)
					{
						if (! $conflict)
						{
							$conflict = $storedAttibute->conflictsWith($attribute);
						}
					}

					if (! $conflict)
					{
						$this->attributes[$attributeID] = $attribute;
						$ruleName = $this->repository->getElementName($attributeID);
//						$this->anchor->putTraceData(__LINE__, __FILE__, 'Rule '.$i.' is '.$ruleName.' [Applied]');
					}
					else
					{
						$ruleName = $this->repository->getElementName($attributeID);
						$this->anchor->putTraceData(__LINE__, __FILE__, 'Rule '.$i.' is '.$ruleName.' [Overridden]');
					}
				}
				else
				{
					$ruleName = $this->repository->getElementName($attributeID);
//					$this->anchor->putTraceData(__LINE__, __FILE__, 'Rule '.$i.' is '.$ruleName.' [Redundant]');
				}

				$proxy = $collection->getNext();

			}
		}


		/*
		 * Sort the resulting metadata array by sort key value to be able to arrange
		 * the order of items on display as per the ordering desired by the element
		 * specifications.
		 */
		usort($this->attributes, 'sortAttributesBySortKey');
	}

} // End of class C_AF_AirElementModel

 ?>