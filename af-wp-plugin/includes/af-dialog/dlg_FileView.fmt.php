<?php
/*
 * dlg_FileView.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-15 JVS Begin test of new standalone PHP environment script
 * V1.3 2005-NOV-14 JVS Integration with old Tikiwiki AIR base
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirFileViewPanel';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirFileViewPanel extends C_HtmlPanel {
	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing. (Be careful with code here
	 * to ensure that we are really talking to the right object.)
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
	 * initialize
	 *
	 * Initialize at this level consists of 'building' the result panel as a
	 * series of object specifications in the panel shell.
	 *******/
	function initialize($panelClass = '')
	 	{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		parent::initialize($panelClass);

		/*
		 * Get out data variables for use in configuring the detail portions
		 * of the result panel
		 */
		$actionRefPrefix	= $this->anchor->getActionUrlBase().'dialog='.$this->getPanelName();
		$textareaWidth 	= $this->stdTextareaWidth;
		$textareaHeight 	= $this->stdTextareaHeight;

		$air_showDiagnostics		= $this->anchor->getDlgVar('air_showDiagnostics');
		$resultDiag					= $this->anchor->getDlgVar('resultDiag');
		$air_listItemArray		= $this->anchor->getDlgVar('air_listItemArray');
		$panelItemTitle			= $this->anchor->getDlgVar('panelItemTitle');
//		$panelItemSubtitle		= $this->anchor->getDlgVar('panelItemSubtitle');
//		$air_ItemHeader			= $this->anchor->getDlgVar('air_ItemHeader');
		$air_ItemFooter			= $this->anchor->getDlgVar('air_ItemFooter');

		$fileContent				= $this->anchor->getDlgVar('FileContent');
		$fileName					= $this->anchor->getDlgVar('Filename');
		$fileType					= $this->anchor->getDlgVar('Filetype');
		$fileCoding					= $this->anchor->getDlgVar('Filecoding');
		$fileMime					= $this->anchor->getDlgVar('Filemime');
		$fileSize					= $this->anchor->getDlgVar('Filesize');
		$fileMTime					= $this->anchor->getDlgVar('Filemtime');
		$fileATime					= $this->anchor->getDlgVar('Fileatime');
		$filePerms					= $this->anchor->getDlgVar('Fileperms');
		$fileReadable				= $this->anchor->getDlgVar('Readable');
		$fileWritable				= $this->anchor->getDlgVar('Writable');

		if ($fileSize)
			{
			$textareaHeight = $fileSize / $textareaWidth;
			if ($textareaHeight > $this->maxTextareaHeight)
				{
				$textareaHeight = $this->maxTextareaHeight;
				}
			else
			if ($textareaHeight < $this->stdTextareaHeight)
				{
				$textareaHeight = $this->stdTextareaHeight;
				}
			}

		switch ($fileType)
			{
			case 'xml':
			case 'xsd':
				$displayType = 'tagged-text';
				break;
			case 'htm':
			case 'html':
			case 'txt':
			case 'c':
			case 'cpp':
			case 'h':
			case 'hpp':
			case 'php':
			case 'sql':
			case 'bat':
			case 'cmd':
				$displayType = 'text';
				break;
			case 'gif':
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'tif':
				$displayType = 'image';
				break;
			default:
				$displayType = 'binary';
				break;
			}

		/*
		 * Construct and file our personality objects into the panel outline
		 */
		$this->setHiddenElement('dlgContext',		$this->anchor->sessionDoc->getContextId());
//		$this->setHiddenElement('dlgPanelType',	$dlgPanelType);
		$this->setHiddenElement('dlgCorrID',		$this->anchor->sessionDoc->getCorrId());
		$this->setHiddenElement('dlgDlgID',			$this->anchor->sessionDoc->getDialogId());
		$this->setHiddenElement('dlgMode',			$this->anchor->sessionDoc->getModeId());
		$this->setHiddenElement('dlgAuth',			$this->anchor->sessionDoc->getAuthId());
		$this->setHiddenElement('target',			$fileName);

		$this->pageTitle = 'Element View';

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleH1, null, 'Element View');
		$this->panelTitle = & $myTitleData;

		$itemArray	= array();
		$pageItem	= array();

		if ($air_showDiagnostics)
			{
			$itemMessages	= array();
			foreach ($resultDiag as $diagItem)
				{
				$itemMessage	=	array();
				$itemMessage['msgItem']	= 	$diagItem['msgItem'];
				$itemMessage['msgText']	=	$diagItem['msgText'];
				if (array_key_exists('msgType', $diagItem))
					{
					$itemMessage['msgType']	= 	$diagItem['msgType'];
					}
				$itemMessages[]	= $itemMessage;
				}
			$pageItem['itemMessages']	= $itemMessages;
			}

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleSpan, null, $panelItemTitle);
		$myTitleData->setAttribute(htmlAttrClass, 'ItemTitle');
		$myTitleData->setAttribute(htmlAttrAlign, 'left');
		if (! empty($panelItemSubtitle))
			{
			$work		= & $myTitleData->createChild(htmlEleSpan);
			$work->setAttribute(htmlAttrClass, 'ItemSubtitle');
			$work		= & $work->createChild(htmlEleMini, '<br/>'.$panelItemSubtitle);
			}
		$pageItem['itemTitle']	= & $myTitleData;

		$pageItem['itemHeader']	= & $this->formatStdDialogArray($air_ItemHeader);

		$pageBody = new C_AirHtmlElement($this);
		$pageBody->initialize(htmlEleTable);

		$myTable		= & $pageBody->createChild(htmlEleTable);
		$myTabRow		= & $myTable->createChild(htmlEleTblRow);
		$myTabData		= & $myTabRow->createChild(htmlEleTblHeadCell, 'Attribute');
								 $myTabData->setAttribute(htmlAttrAlign, 'center');
		$myTabData		= & $myTabRow->createChild(htmlEleTblHeadCell, 'Value');
								 $myTabData->setAttribute(htmlAttrAlign, 'center');

		$myTabRow		= & $myTable->createChild(htmlEleTblRow);
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, 'Name');
								 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, $fileName);
								 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabRow		= & $myTable->createChild(htmlEleTblRow);
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, 'Type');
								 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, $fileType);
								 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabRow		= & $myTable->createChild(htmlEleTblRow);
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, 'Coding');
								 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, $fileCoding);
								 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabRow		= & $myTable->createChild(htmlEleTblRow);
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, 'MIME Type');
								 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, $fileMime);
								 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabRow		= & $myTable->createChild(htmlEleTblRow);
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, 'Size (Bytes)');
								 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, $fileSize);
								 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabRow		= & $myTable->createChild(htmlEleTblRow);
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, 'Last Modified');
								 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, date('j F Y H:i', $fileMTime));
								 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabRow		= & $myTable->createChild(htmlEleTblRow);
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, 'Last Accessed');
								 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, date('j F Y H:i', $fileATime));
								 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabRow		= & $myTable->createChild(htmlEleTblRow);
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, 'Permissions');
								 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, decoct($filePerms));
								 $myTabData->setAttribute(htmlAttrAlign, 'left');

/***************************************
		$myTable		= & $pageBody->createChild(htmlEleTable);
		$myTabRow		= & $myTable->createChild(htmlEleTblRow);
		$myTabData		= & $myTabRow->createChild(htmlEleTblData, $fileContent);
								 $myTabData->setAttribute(htmlAttrAlign, 'left');
		***********************************/

		$frame			= & $pageBody->createChild(htmlEleDefList);
		$def				= & $frame->createChild(htmlEleDefTerm, 'Content');
		$data				= & $frame->createChild(htmlEleDefDescr);
		switch ($displayType)
			{
			case 'text':
				$work		= & $data->createChild(htmlEleTextarea, $fileContent);
//								$work->setAttribute(htmlAttrName, "eleNewContent");
								$work->setAttribute(htmlAttrRows, $textareaHeight);
								$work->setAttribute(htmlAttrCols, $textareaWidth);
								$work->setAttribute(htmlAttrReadonly);
//				$this->anchor->sessionDoc->putDialogVarTracker('eleNewContent', 'Item'); // Catalog the dialog form variables
				break;
			case 'tagged-text':
				$parser = new C_AirXmlPainter();
				$parser->initialize();
				if (! $parser->parse($fileContent))
					{
					$newData	 = '<font color="RED">';
					$newData	.= 'Error in dlg_FileView.fmt['.__LINE__.'] xml parser: ';
					$newData	.= '</font><b>';
					$newData .= $parser->getLastResultText();
					$newData .= '</b><br />';
//					$fileContent = htmlspecialchars($fileContent, ENT_QUOTES);
					$newData .= $this->htmlLinewrap($fileContent);
					}
				else
					{
					$newData = $parser->getStream();
					}
				$work		= & $data->createChild(htmlEleDiv);
				$work->setContent($newData);
				$work->setAttribute(htmlAttrAlign, 'left');
	//			$work		= & $data->createChild(htmlEleTextarea, $newData);
//								$work->setAttribute(htmlAttrName, "eleNewContent");
	//							$work->setAttribute(htmlAttrRows, $textareaHeight);
	//							$work->setAttribute(htmlAttrCols, $textareaWidth);
	//							$work->setAttribute(htmlAttrReadonly);
				$parser->terminate();
				unset($parser);
				break;
			case 'image':
				break;
			case 'binary':
				break;
			}

		$pageItem['itemBody']	= & $pageBody;

		$pageItem['itemFooter']	= & $this->formatStdDialogArray($air_ItemFooter);

		$itemArray[]				= $pageItem;
		$this->panelItems 		= $itemArray;
		}

	} // end of class

?>