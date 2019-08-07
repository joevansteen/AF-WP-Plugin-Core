<?php
/*
 * af_menuSet script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-28 JVS Original code
 *
 * Inspired by the menu class in The PHP Anthology, Object Oriented PHP
 * Solutions, Volume 1, by Harry Fuecks, Copyright 2003 Sitepoint Pty Ltd
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_MenuSet';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_MenuSet
	 *
	 * Defines an encapsulation of a menu structure.
	 ***************************************************************************/
class C_MenuSet extends C_AirObjectBase {
	private $itemArray	= array();
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
	function __construct(&$air_anchor)
		{
		// Propogate the construction process
		parent::__construct($air_anchor);

		if ($air_anchor->trace())
			{
			$air_anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$stdScriptDir	= AF_ROOT_DIR.'/scripts/';
		require_once($stdScriptDir.'af_menuItem.php');
		require_once($stdScriptDir.'af_menuElement.php');

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

		foreach ($simpleXML->menuItems as $menuItems)
			{
			foreach ($menuItems->menuItem as $menuItem)
				{
				// Note: String cast is necessary, otherwise these elements are
				// passed as SimpleXMLElement objects!
				$item = new C_MenuItem($this->anchor);

				$item->ItemKey				= (String) $menuItem->itemKey;
				$item->ItemLabel			= (String) $menuItem->label;
				$item->ItemDescription	= (String) $menuItem->description;

				$this->itemArray[$item->ItemKey] = $item;
				}
			}

		ksort($this->itemArray);

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
						$entry = new C_MenuElement($this->anchor);

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
	 * Given a dilog, returns the menu hierarchy that leads to the dialog. The
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