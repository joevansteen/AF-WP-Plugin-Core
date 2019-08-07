<?php
/*
 * C_AF_AirRepositoryElement script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-04 JVS Modelled after RepositoryElement (Java implementation)
 *
 * Describes RepositoryElement items according to a generic definition based
 * on the CollectionModelElement.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirRepositoryElement';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AF_AirRepositoryElement extends C_AirObjectBase
{
	private $repository	= NULL;	// The repository manager for the collection
	private $guid			= NULL;	// The GUID for the element

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
	}

	/***************************************************************************
    * Retrieves the repository for the element.
    * @return the element's repository reference
	 *******/
   function getRepository()
   {
   	return $this->repository;
   }

	/***************************************************************************
    * Retrieves the unique identifier for the element.
    * @return the element's unique identifier
	 *******/
   function getGuid()
   {
   	return $this->guid;
   }

}

 ?>