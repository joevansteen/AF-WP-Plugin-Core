<?php
/*
 * C_AF_AirElementCollection script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-04 JVS Modelled after AirElementCollection (Java implementation)
 *
 * Provides a collection model abstraction of AIR repository elements that have
 * been assembled as a set. Element contents are stored as an array of GUIDs
 * defining elements in a particular repository.
 * <p>The AirElement Collection provides a factory for generating RepositoryElement
 * objects when elements within the collection are requested. (RepositoryElements
 * are light-weight, abstract proxies for the actual repository elements, which all
 * take the form of an AirElement objects.) </p>
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirElementCollection';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

class C_AF_AirElementCollection extends C_AirObjectBase
{
	private $collection = array(); // Array of GUID strings, keyed by index
	private $repository = NULL;    // The repository manager for the collection

	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing. (Be careful with code here
	 * to ensure that we are really talking to the right object.)
	 *
	 * repository is the repository where the elements are located
	 * elements is an array of GUIDs used to initialize the collection
	 *******/
	function __construct(& $repository, $elements = NULL)
	{
		// Propogate the construction process
		parent::__construct();

		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

	 	if (($repository == NULL)
	 	 || (!($repository instanceof C_AF_AirRepository)))
		 	{
		 	throw new Exception('Invalid instantiation');
		 	}

		$this->repository = $repository;

		if (($elements != NULL)
		 && (!empty($elements)))
		{
			foreach ($elements as $element)
			{
				$this->collection[$element] = $element;
			}
		}
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
	 * getElementAt()
	 * Retrieve the collection element at the indicated position. NOTE: This is
	 * a HIGHLY inefficient method to access the collection and should only be
	 * used for random access to a select few elements. To iterate through the
	 * collection in either forward or backward order, use either getFirst() or
	 * getLast() followed by either getNext() or getPrev(). These functions will
	 * return a NULL value when the iteration is complete.
	 *
	 * Returns the C_AF_AirRepositoryElement identified by the GUID at the
	 * indicated position.
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
			$result = new C_AF_AirRepositoryElement($this->repository, $element);
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

			while ($element && $element != $guid)
			{
		    	$element = next($this->collection);
		    	$index++;
			}

			if ($element && $element == $guid)
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
			$result = new C_AF_AirRepositoryElement($this->repository, $keyValue);
		}

		return $result;
	}

	/***************************************************************************
    * Adds a C_AF_AirRepositoryElement to the end of the collection.
    * @param element the C_AF_AirRepositoryElement to be added
	 *******/
   function add($element)
   {
	 	if (($element == NULL)
	 	 || (!($element instanceof C_AF_AirRepositoryElement)))
		 	{
		 	throw new Exception('Invalid parameter type');
		 	}

   	$guid = $element->getGuid();
      if (!array_key_exists($guid, $this->collection))
      {
      	$this->collection[$guid] = $guid;
      }
   }

	/***************************************************************************
    * Retrieves a C_AF_AirRepositoryElement identified by the GUID at the
    * beginning of the collection.
    * Returns the first element in the collection
	 *******/
	function getFirst()
   {
    	reset($this->collection);
    	return $this->getCurrent();
   }

	/***************************************************************************
    * Retrieves a C_AF_AirRepositoryElement identified by the GUID at the
    * end of the collection.
    * Returns the last element in the collection
	 *******/
   function getLast()
   {
    	end($this->collection);
    	return $this->getCurrent();
   }

	/***************************************************************************
    * Retrieves a C_AF_AirRepositoryElement identified by the GUID at the
    * current cursor position of the collection.
    * Returns the current element in the collection
	 *******/
   function getCurrent()
   {
    	$element = current($this->collection);
    	if ($element)
			return new C_AF_AirRepositoryElement($this->repository, $element);
    	else
      	return NULL;
   }

	/***************************************************************************
    * Retrieves a C_AF_AirRepositoryElement identified by the GUID at the
    * next cursor position of the collection and advances the cursor.
    * Returns the next element in the collection
	 *******/
   function getNext()
   {
    	$element = next($this->collection);
    	if ($element)
			return new C_AF_AirRepositoryElement($this->repository, $element);
    	else
      	return NULL;
   }

	/***************************************************************************
    * Retrieves a C_AF_AirRepositoryElement identified by the GUID at the
    * previous cursor position of the collection and decrements the cursor.
    * Returns the previous element in the collection
	 *******/
   function getPrev()
   {
   	$element = prev($this->collection);
    	if ($element)
			return new C_AF_AirRepositoryElement($this->repository, $element);
    	else
      	return NULL;
   }

	/***************************************************************************
    * Merges another C_AF_AirElementCollection into this collection.
    * @param mergedCollection the C_AF_AirElementCollection to be added
	 *******/
   function merge($mergedCollection)
   {
	 	if (($mergedCollection == NULL)
	 	 || (!($mergedCollection instanceof C_AF_AirElementCollection)))
		 	{
		 	throw new Exception('Invalid parameter type');
		 	}

      $mergeSize = count($mergedCollection->collection);
      for ($i = 0; $i < $mergeSize; $i++)
      {
			$item = $mergedCollection->collection[$i];
   	   if (!array_key_exists($item, $this->collection))
      	{
	      	$this->collection[$item] = $item;
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