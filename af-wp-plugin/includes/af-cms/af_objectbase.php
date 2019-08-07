<?php
/*
 * af_objectbase script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-NOV-07 JVS PHP restructuring
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 */

// Insure a correct execution context ...
$myDynamClass = 'C_AirObjectBase';	
require(AF_CORE_SCRIPTS.'/af_script_header.php');

class C_AirObjectBase {
	private $anchor;						// The AIR anchor object
	public	$wpdb;
	private $propertyArray	= array();
	private $instantiated	= false;
	private $initialized		= false;
	var $resultText		= '';
	var $resultArray		= array();

	// --------------------------------------------------------
	// Constructor
	//
	// Initialize the local variable store and creates a local
	// reference to the AIR_anchor object for later use in
	// detail function processing. (Be careful with code here
	// to ensure that we are really talking to the right object.)
	// --------------------------------------------------------
	function __construct(& $air_anchor)
		{
		if (!is_object($air_anchor))
			{
			global $anchor;
			echo $anchor->whereAmI();
			die ('Invalid air_anchor object passed to '.__CLASS__.' constructor');
			}

		$this->anchor			= &$air_anchor;
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$this->attachDiagnosticItem('&#160;', AIR_DiagMsg_Success, 'Successful instantiation');
		$this->instantiated	= true;
		$this->clearDiagnostics();
		$this->initialized = true;
		}

	/***************************************************************************
	 * terminate
	 *******/
	function terminate()
	 	{
		$this->resultText		= 'Successful termination';
		$this->initialized = false;
		}

	/***************************************************************************
	 * __get
	 * accessor
	 *******/
	function __get($prop_name)
	   {
      if (isset($this->propertyArray[$prop_name]))
      	{
         $prop_value = $this->propertyArray[$prop_name];
         return $prop_value;
			}
		}

	/***************************************************************************
	 * __set
	 * accessor
	 *******/
   function __set($prop_name, $prop_value) {
	   if ($prop_name == 'pageDialog')
	   	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Changing pageDialog to '.$prop_value.' in ' . __CLASS__ . '::' . __FUNCTION__);
	   	}
      $this->propertyArray[$prop_name] = $prop_value;
      return true;
  }
  
  /***************************************************************************
   * Indirect call function
   * from: https://www.php.net/manual/en/reserved.classes.php
   *
   * this will accept both arrays, strings and Closures:
	 *    $dynamic->myMethod = "thatFunction";
   *    $dynamic->hisMethod = array($instance,"aMethod");
   * 		$dynamic->newMethod = array(SomeClass,"staticMethod");
   * 		$dynamic->anotherMethod = function(){
   *     		echo "Hey there";
   * 		};
   */ 	
	public function __call($key,$params){
		if(!isset($this->{$key})) {
			throw new Exception("Call to undefined method ".get_class($this)."::".$key."()");
		}
		$subject = $this->{$key};
    call_user_func_array($subject,$params);
  }
  
	/***************************************************************************
	 * getDiagnosticCount()
	 *
	 * returns the count of the number of diagnostics
	 *******/
	function getDiagnosticCount()
		{
		$diagCount = 0;

		$diagCount = count($this->resultArray);

		return($diagCount);
		} // end of getDiagnosticCount()

	/***************************************************************************
	 * getDiagnosticItemData()
	 *
	 * gets a specific diagnostic item
	 *******/
	function getDiagnosticItemData($itemIndex)
		{
		$diagArray = array();

		$elements = count($this->resultArray);

		if ($elements > $itemIndex)
			{
			$diagArray	= $this->resultArray[$itemIndex];
			}

		return($diagArray);
		} // end of getDiagnosticItemData()

	/***************************************************************************
	 * attachDiagnosticTextItem()
	 *
	 * adds a new diagnostic item to the message
	 *******/
	function attachDiagnosticItem($diagRef, $diagLevel, $diagText)
		{
		$diagArray = array();

		$diagArray['msgItem']	= $diagRef;
		$diagArray['msgType']	= $diagLevel;
		$diagArray['msgText']	= $diagText;

		$this->resultArray[] 	= $diagArray;
		$this->resultText			= $diagText;

		return;
		} // end of attachDiagnosticTextItem()

	/***************************************************************************
	 * clearDiagnostics()
	 *
	 * clears the diagnostic information
	 *******/
	function clearDiagnostics()
		{
		foreach($this->resultArray as $varKey => $varValue)
			{
				unset($this->resultArray[$varKey]);
			}
		$this->resultText		= '';

		return;
		} // end of clearDiagnostics()

	/***************************************************************************
	 * getLastResultText
	 *******/
	function getLastResultText()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		return($this->resultText);
		}
	} // end of class

?>