<?php
/*
 * C_AF_AirElementHierarchy script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-17 JVS Bootstrap from C_AF_AirElementCollection
 *
 * Provides a hierarchical collection model of AIR repository elements that have
 * been assembled as a set where each member of the set is mapped to other elements
 * in a parent-child relationship according to a defined association.
 *
 * Collection contents are stored as an array of GUIDs
 * defining elements in a particular repository.
 * <p>The AirElement Collection provides a factory for generating RepositoryElement
 * objects when elements within the collection are requested. (RepositoryElements
 * are light-weight, abstract proxies for the actual repository elements, which all
 * take the form of an AirElement objects.) </p>
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirElementHierarchy';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

class C_AF_AirElementHierarchy extends C_AirObjectBase
{
	private $collection = array(); // GUID Keyed Array of Collection Elements
	private $repository = NULL;    // The repository manager for the elements
	private $association = NULL;	// The association type of the elements
	private $assocDocument = NULL;	// The C_AF_AirElementDoc for the association type
	private $roots = NULL;	// The root elements of the hierarchy (elements with no parent)

	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing. (Be careful with code here
	 * to ensure that we are really talking to the right object.)
	 *
	 * repository is the repository where the elements are located
	 * association is the GUID of the association type defining the relationship
	 *             between the elements in the collection
	 *******/
	function __construct(& $repository, $association = NULL)
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

	 	if (($association == NULL)
	 	 || ($association == AIR_Null_Identifier)
	 	 || ($association == AIR_Any_Identifier)
	 	 || ($association == AIR_All_Identifier))
		 	{
		 	throw new Exception('Invalid instantiation');
		 	}

		$this->assocDocument = & $this->repository->getElementRef($association);
	 	if (($this->assocDocument == NULL)
	 	 || (!($this->assocDocument instanceof C_AF_AirElementDoc)))
		 	{
		 	throw new Exception('Invalid instantiation');
		 	}

		$this->association = $association;
	}

	/***************************************************************************
	 * getSize()
	 * Returns the number of elements in the collection.
	 *******/
	function getSize()
	{
		return count($this->collection);
	}

	/***************************************************************************
	 * getRoots()
	 * Returns a collection of elements that are the root elements of the hierarchy.
	 * Roots are elements with no parent. There may be one or more roots in the
	 * hierarchy, or no roots if the collection is circular.
	 *******/
	function & getRoots()
	{
		if ($this->roots == NULL)
		{
			$keys = array();
			foreach ($this->collection as $key => $value)
			{
				if (! $value->hasParent())
					$keys[] = $value->ItemKey;
			}

			$this->roots = new C_AF_AirElementCollection($this->repository, $keys);
		}

		return $this->roots;
	}

	/***************************************************************************
	 * getElementAt()
	 * Retrieve the collection element at the indicated position. NOTE: This is
	 * a HIGHLY inefficient method to access the collection and should only be
	 * used for random access to a select few elements. To iterate through the
	 * collection in either forward or backward order, use either getFirst() or
	 * getLast() followed by either getNext() or getPrev(). These functions will
	 * return a NULL value when the iteration is complete.
	 *
	 * Returns the C_AF_AirElementHierarchyItem at the indicated position.
	 *******/
	function getElementAt($index)
	{
		$result = NULL;

		if ((count($this->collection) > $index)
		 && ($index >= 0))
		{
			reset($this->collection);
    		$element = $this->getCurrent();
			for ($i = 0; $i < $index; $i++)
			{
		    	$element = next($this->collection);
			}
			$result = $element;
		}

		return $result;
	}

	/***************************************************************************
	 * getPositionOf()
	 * Used to determine the relative position of the identified element in the
	 * collection. The element is identified by its GUID.
	 *
	 * Returns the index value for the element, or -1 if the element is not
	 * found in the collection.
	 *******/
	function getPositionOf($guid)
	{
		$result = -1;

		if (count($this->collection) > 0)
		{
			reset($this->collection);
			$index = 0;
    		$element = $this->getCurrent();

			while ($element && $element->ItemKey != $guid)
			{
		    	$element = next($this->collection);
		    	$index++;
			}

			if ($element && $element->ItemKey == $guid)
				$result = $index;
		}

		return $result;
	}

	/***************************************************************************
	 * getItem()
	 * Retrieve the collection element with the indicated key value.
	 *
	 * Returns the C_AF_AirElementHierarchyItem identified by the GUID at the
	 * indicated position.
	 *******/
	function getItem($keyValue)
	{
		$result = NULL;

      if (array_key_exists($keyValue, $this->collection))
		{
			$result = $this->collection[$keyValue];
		}

		return $result;
	}

	/***************************************************************************
    * Adds a C_AF_AirElementHierarchyItem to the collection.
    * @param element the C_AF_AirElementHierarchyItem to be added
	 *******/
   function add($element)
   {
	 	if (($element == NULL)
	 	 || (!($element instanceof C_AF_AirElementHierarchyItem)))
		 	{
		 	throw new Exception('Invalid parameter type');
		 	}

   	$guid = $element->ItemKey;
      if (!array_key_exists($guid, $this->collection))
      {
      	$this->collection[$guid] = $element;
      }
   }

	/***************************************************************************
    * Retrieves the C_AF_AirElementHierarchyItem identified at the beginning
    * of the collection.
    * Returns the first element in the collection
	 *******/
	function getFirst()
   {
    	reset($this->collection);
    	return $this->getCurrent();
   }

	/***************************************************************************
    * Retrieves the C_AF_AirElementHierarchyItem at the end of the collection.
    * Returns the last element in the collection
	 *******/
   function getLast()
   {
    	end($this->collection);
    	return $this->getCurrent();
   }

	/***************************************************************************
    * Retrieves the current C_AF_AirElementHierarchyItem in the collection.
    * Returns the current element in the collection
	 *******/
   function getCurrent()
   {
    	$element = current($this->collection);
    	if ($element)
			return $element;
    	else
      	return NULL;
   }

	/***************************************************************************
    * Retrieves the next C_AF_AirElementHierarchyItem in the collection.
    * Returns the next element in the collection
	 *******/
   function getNext()
   {
    	$element = next($this->collection);
    	if ($element)
			return $element;
    	else
      	return NULL;
   }

	/***************************************************************************
    * Retrieves the prior C_AF_AirElementHierarchyItem in the collection.
    * Returns the previous element in the collection
	 *******/
   function getPrev()
   {
   	$element = prev($this->collection);
    	if ($element)
			return $element;
    	else
      	return NULL;
   }

	/***************************************************************************
    * Merges another C_AF_AirElementHierarchy into this collection.
    * @param mergedCollection the C_AF_AirElementHierarchy to be added
	 *******/
   function merge($mergedCollection)
   {
	 	if (($mergedCollection == NULL)
	 	 || (!($mergedCollection instanceof C_AF_AirElementHierarchy)))
		 	{
		 	throw new Exception('Invalid parameter type');
		 	}

      $mergeSize = count($mergedCollection->collection);
      for ($i = 0; $i < $mergeSize; $i++)
      {
			$item = $mergedCollection->collection[$i];
	   	$guid = $item->ItemKey;
   	   if (!array_key_exists($guid, $this->collection))
      	{
	      	$this->collection[$guid] = $item;
   	   }
      }
   }

	/***************************************************************************
    * Removes an element from the collection.
    * @param index the relative index of the element to be removed
	 *******/
   function remove($index)
   {
	  	if ($index < count($this->collection))
	  	{
  			array_splice($this->collection, $index, 1);
  		}
   }

}

 ?>