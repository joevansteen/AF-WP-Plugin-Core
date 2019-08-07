<?php
/*
 * af_menuElement script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-28 JVS Original code
 *
 * Inspired by the menu class in The PHP Anthology, Object Oriented PHP
 * Solutions, Volume 1, by Harry Fuecks, Copyright 2003 Sitepoint Pty Ltd
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_MenuElement';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_MenuElement
	 *
	 * Defines an encapsulation of a menu element. Menu elements define the detail
	 * structural parts of a menu set. They define the hierarchical nature of the
	 * menu set. They contain associative pointers to menu items which define the
	 * action strategy and text content of the menu item.
	 ***************************************************************************/
class C_AF_MenuElement extends C_AirObjectBase {
	private $role;				// Defines security constraint
	private $children;		// Array list of child nodes

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
		}

	/***************************************************************************
	 * initialize()
	 *******/
	public function initialize($role, $itemId, $parentElement)
		{
	 	if (($parentElement != NULL)
	 	 && (!($parentElement instanceof C_AF_MenuElement)))
		 	{
		 	throw new Exception('Invalid parent type.');
		 	}

		$this->role					= $role;
		$this->Role					= $role;
		$this->ItemKey				= $itemId;
		$this->Parent				= $parentElement;
		$this->children			= array();

	 	if ($parentElement != NULL)
		 	{
			$this->Parent->addChild($this);
		 	}
		}

	/***************************************************************************
	 * terminate
	 *******/
	public function terminate()
	 	{
		parent::terminate();
		}

	/***************************************************************************
	 * addChild()
	 *
	 * Adds a child menu element to this element.
	 *******/
	private function addChild(& $childNode)
	 	{
	 	if ($childNode	instanceof C_AF_MenuElement)
		 	{
		 	if (array_key_exists($childNode->ItemKey, $this->children))
			 	{
			 	throw new Exception('Child node ['.$childNode->ItemKey.'] has duplicate key');
			 	}
		 	$this->children[$childNode->ItemKey] = $childNode;
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

	} // end of class
?>