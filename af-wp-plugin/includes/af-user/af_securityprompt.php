<?php
/*
 * af_securityprompt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-NOV-07 JVS Begin test of new standalone PHP environment script
 *								Security prompt graphic class in support of logon
 *								security. Adapted from The PHP Anthology by Harry Fuecks
 *								Copyright 2003 Sitepoint Pty Ltd
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirSecurityPrompt';
$myDynamClass = $myProcClass;	

class C_AirSecurityPrompt extends C_AirObjectBase {
	var $image;								// background image
	var $iHeight;							// image height
	var $iWidth;							// image width
	var $fHeight;							// font height
	var $fWidth;							// font width
	var $xPos;								// pixel tracker
	var $fonts;								// font array

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
	function initialize($jpeg, $fHeight = 12, $fWidth = 12)
	 	{
 		/*
      $this->image  = imagecreatetruecolor(500, 50);
      $bgc = imagecolorallocate($this->image, 255, 255, 255);
      imagefilledrectangle($this->image, 0, 0, 500, 50, $bgc);
		*/
//      $tc  = imagecolorallocate($this->image, 0, 0, 0);
//      imagestring($this->image, 1, 5, 5, "This is the image", $tc);
		$this->image	= imagecreatefromjpeg($jpeg);
		if ($this->image === false)
		{
			throw Exception('Failure to create jpeg image resource');
		}
		$this->iHeight	= imagesy($this->image);
		$this->iWidth	= imagesx($this->image);
		$this->fHeight	= $fHeight;
		$this->fWidth	= $fWidth;
		$this->xPos		= 0;
		$this->fonts	= array(2, 3, 4, 5);
		}

	/***************************************************************************
	 * terminate
	 *******/
	function terminate()
	 	{
		parent::terminate();
		}

	/***************************************************************************
	 * addText
	 *******/
	function addText($text, $r=38, $g=38, $b=38)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$length = $this->fWidth * strlen($text);
		if ($length >= ($this->iWidth - ($this->fWidth * 2)))
			{
			return(false);
			}
		$this->xPos = floor(($this->iWidth - $length) / 2);
		$fColor		= imagecolorallocate($this->image, $r, $g, $b);

		srand((float)microtime() * 1000000);
		$fonts	= array(4, 5, 2, 3, 4, 5);
		$yStart	= floor($this->iHeight / 2) - $this->fHeight;
		$yEnd		= $yStart + $this->fHeight;
		$yPos		= range($yStart, $yEnd);

		for ($strPos = 0; $strPos < strlen($text); $strPos++)
			{
			shuffle($fonts);
			shuffle($yPos);
			imagestring($this->image,
							$fonts[0],
							$this->xPos,
							$yPos[0],
							substr($text, $strPos, 1),
							$fColor);
			$this->xPos += $this->fWidth;
			}

		return(true);
		}

	/***************************************************************************
	 * clearFonts
	 *******/
	function clearFonts()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$this->fonts = array();

		return;
		}

	/***************************************************************************
	 * addFont
	 *******/
	function addFont($font)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$this->fonts[] = $font;

		return;
		}

	/***************************************************************************
	 * getImageHeight
	 *******/
	function getImageHeight()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		return($this->iHeight);
		}

	/***************************************************************************
	 * getImageWidth
	 *******/
	function getImageWidth()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		return($this->iWidth);
		}

	/***************************************************************************
	 * getImage
	 *******/
	function getImage()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		return($this->image);
		}

	/***************************************************************************
	 * createRandomString
	 *******/
	function createRandomString($size = 8)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		srand((double) microtime() * 1000000);
		$letters1	= range('A', 'H');
		$letters2	= range('J', 'N');
		$letters3	= range('P', 'W');
		$numbers		= range(2, 9);
		$chars		= array_merge($letters1, $letters2, $letters3, $numbers);
		$randString	= '';
		for ($i = 0; $i < $size; $i++)
			{
			shuffle($chars);
			$randString	.= $chars[0];
			}

		return($randString);
		}

	}

?>