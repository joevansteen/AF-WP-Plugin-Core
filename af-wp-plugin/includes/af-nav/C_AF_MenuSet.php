<?php
/*
 * C_AF_MenuSet script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-28 JVS Original code
 *
 * Inspired by the menu class in The PHP Anthology, Object Oriented PHP
 * Solutions, Volume 1, by Harry Fuecks, Copyright 2003 Sitepoint Pty Ltd
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_MenuSet';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_MenuSet
	 *
	 * Defines an encapsulation of a menu structure.
	 ***************************************************************************/
class C_AF_MenuSet extends C_AirObjectBase {
	private $menuElements = array();
	private $rootNode		= NULL;

	// --------------------------------------------------------
	// Constructor
	//
	// Initialize the local variable store and creates a local
	// reference to the AIR_anchor object for later use in
	// detail function processing. (Be careful with code here
	// to ensure that we are really talking to the right object.)
	// --------------------------------------------------------
	function __construct()
		{
		// Propogate the construction process
		parent::__construct();

		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->initialize();
		}

	/***************************************************************************
	 * initialize
	 *******/
	private function initialize()
	 	{
		$userRole = '';
		if ($this->anchor->user->isLoggedIn())
			{
			$userRole = 'Registered';
			}
		else
			{
			$userRole = 'Anonymous';
			}

		$xmlFile = AF_ROOT_DIR.'/data/AF_NavigationMenu.xml';
		if (!file_exists($xmlFile))
			{
			throw new Exception('Failed to find XML config file!');
			}

		$simpleXML = simplexml_load_file($xmlFile);
      if (!($simpleXML instanceof SimpleXMLElement))
			{
			throw new Exception('Failed to load config file as SimpleXML!');
			}

		$errors = 0;
		$rootFound = false;

		foreach ($simpleXML->menuStructure as $menuStructure)
			{
			foreach ($menuStructure->element as $element)
				{
				// Note: String cast is necessary, otherwise these elements are
				// passed as SimpleXMLElement objects!
				$role				= (String) $element->role;
				$parent			= (String) $element->parent;
				$item				= (String) $element->itemKey;

				$valid = false;
				$securityOk = false;

				if (($role == '*')
				 || ($role == $userRole))
					{
					$securityOk = true;
					}

				if ($securityOk)
					{
				  	if (empty($parent))
				  		{
			  			if (! $rootFound)
			  				{
							$rootFound = true;
							$valid = true;
							}
						else
							{
							trigger_error('Menu element '.$item.' has no parent specification.', E_USER_NOTICE);
							$errors++;
							}
				  		}
			  		else
				  		{
				  		if (array_key_exists($parent, $this->menuElements))
				  			{
		  					$valid = true;
							}
						else
							{
							trigger_error('Menu element '.$item.' references missing parent '.$parent.'.', E_USER_NOTICE);
							$errors++;
							}
			  			}

				  	if ($valid)
				  		{
						$entry = new C_AF_MenuElement();

						$parentNode = NULL;
						if (!empty($parent))
							{
							$parentNode = $this->menuElements[$parent];
							}

						$entry->initialize($role, $item, $parentNode);
						$entry->ItemType			= (String) $element->itemType;
						$entry->ItemLabel			= (String) $element->label;
						$entry->ItemDescription	= (String) $element->description;

						$this->menuElements[$item] = $entry;

						if (($this->rootNode == NULL)
						 && ($rootFound))
							{
							$this->rootNode = $entry;
							}
				  		}
					}
				}
			}

		if (! $rootFound)
			{
			trigger_error('No root element found.', E_USER_NOTICE);
			$errors++;
			}

		if ($errors > 0)
			{
			throw new Exception('Invalid menu structure');
			}
		ksort($this->menuElements);
		}

	/***************************************************************************
	 * terminate
	 *******/
	function terminate()
	 	{
		parent::terminate();
		}

	/***************************************************************************
	 * getMenuHierarchy
	 *
	 * Given a dialog, returns the menu hierarchy that leads to the dialog. The
	 * hierarchy is an array of keys to MenuElements
	 *******/
	function getMenuHierarchy($dialogReference)
	 	{
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Menu hierarchy for '.$dialogReference);
	 	$element = NULL;

		foreach ($this->menuElements as $itemKey => $menuItem)
			{
			if ($menuItem->ItemKey == $dialogReference)
				{
				$element = $menuItem;
				break;
				}
			}

		if ($element == NULL)
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'No '.$dialogReference.' menu item reference found.');
//				throw new Exception('Menu reference for '.$dialogReference.' not found!');
			return NULL;
			}

		$hierarchy = array();

		while ($element != NULL)
			{
			$hierarchy[] = $element->ItemKey;
			$element = $element->Parent;
			}

		return (array_reverse($hierarchy));
		}

	/***************************************************************************
	 * getElement()
	 *
	 * Given a key value, returns the menuElement.
	 *******/
	function getElement($itemKey)
	 	{
      if (isset($this->menuElements[$itemKey]))
      	{
         $element = $this->menuElements[$itemKey];
         return $element;
			}

		return NULL;
		}

	/***************************************************************************
	 * getRoot()
	 *******/
	function getRoot()
	 	{
		return $this->rootNode;
		}

	} // end of class
?>