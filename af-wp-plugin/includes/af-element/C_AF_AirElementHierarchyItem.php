<?php
/*
 * C_AF_AirElementHierarchyItem script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-17 JVS Original code
 *
 * Provides an individual item in a hierarchical collection model of AIR repository
 * elements. The collection is composed as a set where each member of the set is mapped
 * to other elements in a parent-child relationship according to a defined association.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirElementHierarchyItem';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

class C_AF_AirElementHierarchyItem extends C_AirObjectBase
{
	private $repository = NULL;    // The repository manager for the elements
	private $hierarchy = NULL; // The C_AF_AirElementHierarchy collection of which this is an element
	private $guid			= NULL;	// The GUID for this element
	private $parentGuid	= NULL;	// The GUID for this element's parent
	private $children = array();		// Array list of child nodes

	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing. (Be careful with code here
	 * to ensure that we are really talking to the right object.)
	 *
	 * repository is the repository where the elements are located
	 * hierarchy is the C_AF_AirElementHierarchy collection of which this is an element
	 *******/
	function __construct(& $repository, $hierarchy, $guid)
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

		$this->repository = $repository;

	 	if (($hierarchy == NULL)
	 	 || (!($hierarchy instanceof C_AF_AirElementHierarchy)))
		 	{
		 	throw new Exception('Invalid instantiation');
		 	}

		$this->hierarchy = $hierarchy;

	 	if (($guid == NULL)
	 	 || ($guid == AIR_Null_Identifier)
	 	 || ($guid == AIR_Any_Identifier)
	 	 || ($guid == AIR_All_Identifier))
		 	{
		 	throw new Exception('Invalid instantiation');
		 	}

		$this->guid 	= $guid;
		$this->ItemKey	= $guid;

		// Need to establish our ItemKey before adding to the hierarchy!
		$this->hierarchy->add($this);
	}

	/***************************************************************************
	 * setParent()
	 *******/
	public function setParent($parent)
		{
	 	if (($parent == NULL)
	 	 || (!($parent instanceof C_AF_AirElementHierarchyItem)))
		 	{
		 	throw new Exception('Invalid parent type.');
		 	}

	 	if (! $this->parentGuid == NULL)
		 	{
		 	throw new Exception('Multiple parent setting.');
		 	}

		$this->parentGuid			= $parent->guid;
		$parent->addChild($this);
		$this->Parent				= $parent;
		}

	/***************************************************************************
	 * hasParent()
	 *
	 * Used to determine if this node in the menu structure has a parent.
	 *******/
	public function hasParent()
	 	{
		return ($this->parentGuid != NULL);
		}

	/***************************************************************************
	 * terminate
	 *******/
	public function terminate()
	 	{
		parent::terminate();
		}

	/***************************************************************************
	 * getName
	 *******/
	public function getName()
	 	{
		return ($this->repository->getElementName($this->guid));
		}

	/***************************************************************************
	 * getType
	 *
	 * Needs to be part of an expanded set of features to provide short-hand access
	 * to element features, potentially using short-hand methods (e.g., read the
	 * index file rather than the element document).
	 *******/
	public function getType()
	 	{
		return NULL;
		}

	/***************************************************************************
	 * addChild()
	 *
	 * Adds a child menu element to this element.
	 *******/
	private function addChild(& $childNode)
	 	{
	 	if ($childNode	instanceof C_AF_AirElementHierarchyItem)
		 	{
		 	if (array_key_exists($childNode->guid, $this->children))
			 	{
			 	throw new Exception('Child node ['.$childNode->guid.'] has duplicate key');
			 	}
		 	$this->children[$childNode->guid] = $childNode;
		 	}
	 	else
		 	{
		 	throw new Exception('Invalid child type.');
		 	}
		}

	/***************************************************************************
	 * hasChildren()
	 *
	 * Used to determine if this node in the menu structure has children.
	 *******/
	public function hasChildren()
	 	{
	 	$result = false;

	 	if (count($this->children) > 0)
		 	{
		 	$result = true;
		 	}

		 return $result;
		}

	/***************************************************************************
	 * childCount()
	 *
	 * Returns the number of children for this node.
	 *******/
	public function childCount()
	 	{
	 	return (count($this->children));
		}

	/***************************************************************************
	 * firstChild()
	 *
	 * Retrieves the first child of the element, or NULL if there are no children.
	 *******/
	public function firstChild()
	 	{
	 	reset($this->children);
		$item = current($this->children);
		if ($item)
			return $item;
		else
			return NULL;
		}

	/***************************************************************************
	 * nextChild()
	 *
	 * Retrieves the next child of the element, or NULL if there are no more children.
	 *******/
	public function nextChild()
	 	{
		$item = next($this->children);
		if ($item)
			return $item;
		else
			return NULL;
		}

}

 ?>