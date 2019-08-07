<?php
/*
 * af_xmlparser script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.4 2005-DEC-02 JVS Bootstrap from af_queue with some strategy and
 *								concepts adapted from The PHP Anthology by Harry Fuecks
 *								Copyright 2003 Sitepoint Pty Ltd, and additional
 *								concepts adapted from ibjoel at hotmail dot com
 *								contribution to the PHP manual materials at
 *								http://us3.php.net/manual/en/function.xml-set-element-handler.php
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirXmlParser';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AirXmlParser
	 *
	 * This class is the initial prototype for the queue. As we need more
	 * sophistication, or if we need more forms of collections and we develop
	 * a hierarchy of classes, this will provide an abstraction layer to make
	 * the users of the queue mechanism unconcerned about that evolution.
	 *******/

class C_AirXmlParser extends C_AirObjectBase {
	var $sax;								// The PHP SAX XML parser
	var $rootNode;							// The document root node
	var $items;
	var $level;

	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local reference to the
	 * AIR_anchor object for later use in detail function processing. (Be careful
	 * with code here to ensure that we are really talking to the right object.)
	 *******/
	function __construct(&$air_anchor)
		{
		// Propogate the construction process
		parent::__construct($air_anchor);

		if ($air_anchor->trace())
			{
			$air_anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		}

	/***************************************************************************
	 * initialize
	 *******/
	function initialize()
	 	{
	 	$attrs				= array();
	 	$attrs['version'] = '1.0';

 		$rootNode			= new C_AirXmlNode($this->anchor);
 		$rootNode->initialize($this, '', $attrs);
		$this->items		= array();
		$this->sax			= xml_parser_create();
		$this->level		= 0;
		}

	/***************************************************************************
	 * terminate
	 *******/
	function terminate()
	 	{
		parent::terminate();
		}

	/***************************************************************************
	 * parse()
	 *
	 * Parse a chunck of an XML document.
	 *******/
	function parse($data)
		{
		$success	= true;

		xml_set_object($this->sax, $this);
		xml_parser_set_option($this->sax, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($this->sax, "startElement", "endElement");
		xml_set_character_data_handler($this->sax, "characterData");

		if (! xml_parse($this->sax, $data))
		 	{
			$errorCode			= xml_get_error_code($this->sax);
			$errorString		= xml_error_string($errorCode);
			$errorLine			= xml_get_current_line_number($this->sax);
			$errorCol			= xml_get_current_column_number($this->sax);
			$errorByteIndex	= xml_get_current_byte_index($this->sax);
			$diagText			= $errorString.' at line '.$errorLine.' col '.$errorCol.' [byte index '.$errorByteIndex.']';
			$this->attachDiagnosticItem('XML Parse Error', AIR_DiagMsg_Error, $diagText);
			$success				= false;
			}

		xml_parser_free($this->sax);

		return($success);
		}

	/***************************************************************************
	 * startElement()
	 *******/
	function startElement($sax, $element, $attribArray)
	 	{
//    xml_set_character_data_handler($sax, "characterData");
		/*
		 * Start the current tag processing
		 */
	 	$parentNode 					=& $this->items[$this->level];
      $this->level++;
 		$this->items[$this->level]	=& $parentNode->createChildNode($element, $attribArray);
		}

	/***************************************************************************
	 * endElement()
	 *******/
	function endElement($sax, $element)
	 	{
	 	if ($this->level > 0)
	 		{
	      $this->level--;
	 		}
		}

	/***************************************************************************
	 * characterData()
	 *******/
	function characterData($sax, $data)
	 	{
	 	$nodeRef =& $this->items[$this->level];
	 	$nodeRef->content = $data;
		}

	/***************************************************************************
	 * getNextItem()
	 *******/
	function getNextItem()
	 	{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__.'::'.__FUNCTION__.' executed here!');
			}

		$item = each($this->items);
		if ($item)
			{
			return($item['value']);
			}
		else
			{
			reset($this->items);
			return(false);
			}
		}
	}

class C_AirXmlNode extends C_AirObjectBase {
	var $nodeUuid;					// Unique ID for the node

	var $nodeName;					// Name of the node
	var $nodeValue;				// not implemented
	var $parentNode;	 			// Parent of the node
	var $childNodes;	 			// Array of child nodes
	var $firstChild;				// not implemented
	var $lastChild;				// not implemented
	var $previousSibling;		// not implemented
	var $nextSibling;				// not implemented
	var $attributes;				// Attribute array for the node
	var $ownerDocument;			// not implemented
	var $namespaceURI;			// not implemented
	var $prefix;					// not implemented
	var $localName;				// not implemented
	var $baseURI;					// not implemented
	var $textContent;				// Text content for the node


	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local reference to the
	 * AIR_anchor object for later use in detail function processing. (Be careful
	 * with code here to ensure that we are really talking to the right object.)
	 *******/
	function __construct(&$air_anchor)
		{
		// Propogate the construction process
		parent::__construct($air_anchor);

		if ($air_anchor->trace())
			{
			$air_anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		}

	/***************************************************************************
	 * initialize
	 *******/
	function initialize(&$parent, $name, $attrs=array(), $content='')
	 	{
		$this->nodeUuid		= $this->anchor->create_UUID();
		$this->parentNode		=& $parent;
		$this->nodeName		= $name;
		$this->attributes		= $attrs;
		$this->textContent	= $content;
		$this->childNodes		= array();
		}

	/***************************************************************************
	 * createChildNode
	 *
	 * Creates a new node and adds it as a child to this node.
	 *******/
	function & createChildNode($name, $attrs=array(), $content='')
		{
		$newChild	=new C_AirXmlNode($this->anchor, $this, $name, $attrs, $content);
		$this->childNodes[$newChild->nodeUuid] = $newChild;
		return ($newChild);
		}
	} // end of class C_AirXmlNode
	/***************************************************************************
	 ***************************************************************************
	 ***************************************************************************
	 ***************************************************************************/
?>