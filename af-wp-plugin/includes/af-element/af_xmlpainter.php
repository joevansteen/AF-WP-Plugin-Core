<?php
/*
 * af_xmlpainter script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.4 2005-DEC-14 JVS Bootstrap from af_xmlparser to create a unique
 *                      subclass for painting XML for display.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirXmlPainter';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AirXmlPainter
	 *******/

class C_AirXmlPainter extends C_AirObjectBase {
	var $sax;								// The PHP SAX XML parser
	var $items;
	var $level;
	var $stream;
	var $lastEvent;
	var $hasContent;
	var $tagIncomplete;
	var $startPos;
	var $debugColor;
	var $punctClass;
	var $eleNameClass;
	var $eleDataClass;
	var $attrNameClass;
	var $attrDataClass;

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
		$this->items			= array();
		$this->sax				= xml_parser_create();
		$this->stream			= '';
		$this->tagIncomplete	= false;
		$this->startPos		= 0;
		$this->level			= 0;
		$this->debugColor		= 'red';
		$this->punctClass		= 'XmlPunct';
		$this->eleNameClass	= 'XmlEleName';
		$this->eleDataClass	= 'XmlEleData';
		$this->attrNameClass	= 'XmlAttrName';
		$this->attrDataClass	= 'XmlAttrData';
		$this->lastEvent		= '';
		$this->hasContent		= false;

		xml_set_object($this->sax, $this);
		xml_parser_set_option($this->sax, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($this->sax, "startElement", "endElement");
		xml_set_character_data_handler($this->sax, "characterData");
		}

	/***************************************************************************
	 * terminate
	 *******/
	function terminate()
	 	{
		xml_parser_free($this->sax);

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

		return($success);
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

	/***************************************************************************
	 * getStream()
	 *******/
	function & getStream()
	 	{
	 	$streamRef = & $this->stream;
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__.'::'.__FUNCTION__.' executed here!');
			}

		return($streamRef);
		}

	/***************************************************************************
	 * appendStream()
	 *******/
	function appendStream($data, $class)
	 	{
		$this->stream	.= '<span class="'.$class.'">'.$data.'</span>';
		}

	/***************************************************************************
	 * startElement()
	 *******/
	function startElement($sax, $element, $attribArray)
	 	{
//    xml_set_character_data_handler($sax, "characterData");
		if ($this->tagIncomplete)
			{
			/*
			 * Wrap up prior tag that is still hanging
			 */
			$this->appendStream('&gt;', $this->punctClass);
	      $this->tagIncomplete = false;
			}
		/*
		 * Start the current tag processing
		 */
		$this->stream	.= '<br />';

		$this->startPos = xml_get_current_byte_index($sax);
		for ($i = 0; $i < $this->level; $i++)
			{
			$this->stream	.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
		$this->appendStream('&lt;', $this->punctClass);
		$this->appendStream($element, $this->eleNameClass);
      if (count($attribArray) >= 1)
       	{
         foreach ($attribArray as $attribName => $attribValue)
         	{
 				$this->stream	.= ' ';
				$this->appendStream($attribName, $this->attrNameClass);
				if (! empty($attribValue))
					{
					$this->appendStream(' = &quot;', $this->punctClass);
					$this->appendStream($attribValue, $this->attrDataClass);
					$this->appendStream('&quot;', $this->punctClass);
					}
            }
	      }
      $this->level++;
	   $this->tagIncomplete	= true;
		$this->hasContent 	= false;
	   $this->lastEvent 		= 'start';
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
		if ($this->lastEvent != 'start')
       	{
       	//if end tag is more than 2 bytes from start tag
			if ($this->tagIncomplete)
				{
				/*
				 * Wrap up prior tag that is still hanging
				 */
				$this->appendStream('&gt;', $this->punctClass);
//				$this->stream	.= '<br />';
		      $this->tagIncomplete = false;
				}
			/*
			 * Start the current tag processing
			 */
//			switch ($this->lastEvent)
//				{
//				case 'start':
//					$this->appendStream('S', $this->debugColor);
//					break;
//				case 'data':
//					$this->appendStream('D', $this->debugColor);
//					break;
//				case 'end':
//					$this->appendStream('E', $this->debugColor);
//					break;
//				default:
//					$this->appendStream('X', $this->debugColor);
//					break;
//				}
			if (($this->lastEvent == 'end')
			 || (($this->lastEvent == 'data')
			  && ($this->hasContent == false)))
				{
				$this->stream	.= '<br />';
				$this->startPos = xml_get_current_byte_index($sax);
				for ($i = 0; $i < $this->level; $i++)
					{
					$this->stream	.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					}
				}

			/*
			 * Create standalone end tag
			 */
			$this->appendStream('&lt;/', $this->punctClass);
			$this->appendStream($element, $this->eleNameClass);
			$this->appendStream('&gt;', $this->punctClass);
//			$this->stream	.= '<br />';
       	}
      else
			{
			/*
			 * Create self closing end tag
			 */
			$this->appendStream(' /&gt;', $this->punctClass);
//			$this->stream	.= '<br />';
			}

      $this->tagIncomplete = false;
	   $this->lastEvent = 'end';
		}

	/***************************************************************************
	 * characterData()
	 *******/
	function characterData($sax, $data)
	 	{
		if ($this->tagIncomplete)
			{
			/*
			 * Wrap up prior tag that is still hanging
			 */
			$this->appendStream('&gt;', $this->punctClass);
//			$this->stream	.= '<br />';
	      $this->tagIncomplete = false;
			}

      $data = trim($data);
      if (strlen($data) > 0)
      	{
			$this->appendStream($data, $this->eleDataClass);
			$this->hasContent = true;
			}
		else
			{
			$this->hasContent = false;
			}
	   $this->lastEvent = 'data';
		}
	}

	/***************************************************************************
	 * C_AirXmlPainter2
	 *******/

class C_AirXmlPainter2 extends C_AirXmlParser {
	var $stream;
	var $lastEvent;
	var $hasContent;
	var $tagIncomplete;
	var $startPos;
	var $debugColor;
	var $punctClass;
	var $eleNameClass;
	var $eleDataClass;
	var $attrNameClass;
	var $attrDataClass;

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
		$this->stream			= '';
		$this->tagIncomplete	= false;
		$this->startPos		= 0;
		$this->level			= 0;
		$this->debugColor		= 'red';
		$this->punctClass		= 'XmlPunct';
		$this->eleNameClass	= 'XmlEleName';
		$this->eleDataClass	= 'XmlEleData';
		$this->attrNameClass	= 'XmlAttrName';
		$this->attrDataClass	= 'XmlAttrData';
		$this->lastEvent		= '';
		$this->hasContent		= false;

		xml_set_object($this->sax, $this);
		xml_parser_set_option($this->sax, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($this->sax, "startElement", "endElement");
		xml_set_character_data_handler($this->sax, "characterData");

		parent::initialize();
		}

	/***************************************************************************
	 * terminate
	 *******/
	function terminate()
	 	{
		xml_parser_free($this->sax);

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

		return($success);
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

	/***************************************************************************
	 * getStream()
	 *******/
	function & getStream()
	 	{
	 	$streamRef = & $this->stream;
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__.'::'.__FUNCTION__.' executed here!');
			}

		return($streamRef);
		}

	/***************************************************************************
	 * appendStream()
	 *******/
	function appendStream($data, $class)
	 	{
		$this->stream	.= '<span class="'.$class.'">'.$data.'</span>';
		}

	/***************************************************************************
	 * startElement()
	 *******/
	function startElement($sax, $element, $attribArray)
	 	{
//    xml_set_character_data_handler($sax, "characterData");
		if ($this->tagIncomplete)
			{
			/*
			 * Wrap up prior tag that is still hanging
			 */
			$this->appendStream('&gt;', $this->punctClass);
	      $this->tagIncomplete = false;
			}
		/*
		 * Start the current tag processing
		 */
		$this->stream	.= '<br />';

		$this->startPos = xml_get_current_byte_index($sax);
		for ($i = 0; $i < $this->level; $i++)
			{
			$this->stream	.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
		$this->appendStream('&lt;', $this->punctClass);
		$this->appendStream($element, $this->eleNameClass);
      if (count($attribArray) >= 1)
       	{
         foreach ($attribArray as $attribName => $attribValue)
         	{
 				$this->stream	.= ' ';
				$this->appendStream($attribName, $this->attrNameClass);
				if (! empty($attribValue))
					{
					$this->appendStream(' = &quot;', $this->punctClass);
					$this->appendStream($attribValue, $this->attrDataClass);
					$this->appendStream('&quot;', $this->punctClass);
					}
            }
	      }
      $this->level++;
	   $this->tagIncomplete	= true;
		$this->hasContent 	= false;
	   $this->lastEvent 		= 'start';
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
		if ($this->lastEvent != 'start')
       	{
       	//if end tag is more than 2 bytes from start tag
			if ($this->tagIncomplete)
				{
				/*
				 * Wrap up prior tag that is still hanging
				 */
				$this->appendStream('&gt;', $this->punctClass);
//				$this->stream	.= '<br />';
		      $this->tagIncomplete = false;
				}
			/*
			 * Start the current tag processing
			 */
//			switch ($this->lastEvent)
//				{
//				case 'start':
//					$this->appendStream('S', $this->debugColor);
//					break;
//				case 'data':
//					$this->appendStream('D', $this->debugColor);
//					break;
//				case 'end':
//					$this->appendStream('E', $this->debugColor);
//					break;
//				default:
//					$this->appendStream('X', $this->debugColor);
//					break;
//				}
			if (($this->lastEvent == 'end')
			 || (($this->lastEvent == 'data')
			  && ($this->hasContent == false)))
				{
				$this->stream	.= '<br />';
				$this->startPos = xml_get_current_byte_index($sax);
				for ($i = 0; $i < $this->level; $i++)
					{
					$this->stream	.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					}
				}

			/*
			 * Create standalone end tag
			 */
			$this->appendStream('&lt;/', $this->punctClass);
			$this->appendStream($element, $this->eleNameClass);
			$this->appendStream('&gt;', $this->punctClass);
//			$this->stream	.= '<br />';
       	}
      else
			{
			/*
			 * Create self closing end tag
			 */
			$this->appendStream(' /&gt;', $this->punctClass);
//			$this->stream	.= '<br />';
			}

      $this->tagIncomplete = false;
	   $this->lastEvent = 'end';
		}

	/***************************************************************************
	 * characterData()
	 *******/
	function characterData($sax, $data)
	 	{
		if ($this->tagIncomplete)
			{
			/*
			 * Wrap up prior tag that is still hanging
			 */
			$this->appendStream('&gt;', $this->punctClass);
//			$this->stream	.= '<br />';
	      $this->tagIncomplete = false;
			}

      $data = trim($data);
      if (strlen($data) > 0)
      	{
			$this->appendStream($data, $this->eleDataClass);
			$this->hasContent = true;
			}
		else
			{
			$this->hasContent = false;
			}
	   $this->lastEvent = 'data';
		}
	}
?>