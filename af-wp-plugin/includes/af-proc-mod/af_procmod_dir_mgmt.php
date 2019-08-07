<?php
/*
 * af_procmod_dir_mgmt Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-NOV-11 JVS Bootstrap from af_procmod_sys_admin
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 *
 * This module is the primary business logic processing module for directory
 * management functions.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ProcModDirMgmt';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ProcModDirMgmt extends C_AirProcModBase {

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
		if ($air_anchor->trace())
			{
			$air_anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		// Propogate the construction process
		parent::__construct($air_anchor);
		}

	/***************************************************************************
	 * ProcMod_Main
	 *
	 *******/
	function ProcMod_Main(& $procContext, & $baseMsg, & $procMsg)
	 	{
		parent::initialize($procContext, $baseMsg, $procMsg);
		$testValue = $this->procMsg->getMessageData('EleType');
		if (! empty($testValue))
			{
			$result = $this->createTxnDataArrayFromProcMsg();
			if ($result < 0)
				{
				trigger_error("Critical data error in ".__FUNCTION__, E_USER_NOTICE);
				}
			}
		$this->initResultMsg();
		$this->myDialog	= Dialog_EleMaint;

		switch ($this->myMsgObject)
			{
			case ProcMod_DirView:
				switch ($this->myMsgAction)
					{
					case AIR_Action_DirView:
						$this->procSelectView();
						break;
					case AIR_Action_DirViewAdm:
					case AIR_Action_DirViewPvt:
					case AIR_Action_DirViewShr:
					case AIR_Action_DirViewPub:
						$this->procViewDirectory();
						break;
					default:
						$this->unknownActionRequest();
						$this->procDefault();
						break;
					}
				break;
			case ProcMod_FileMaint:
				$request = $this->procMsg->getMessageData('request');
//				switch ($this->myMsgAction)
				switch ($request)
					{
					case AIR_Action_View:		// Initiate view
						$this->procViewFile();
						break;
					case AIR_Action_New:			// Initiate new file create
						$this->procCreateFile();
						break;
					case AIR_Action_Create:		// Post new file
						$this->procCreateFileAudit();
						break;
					case AIR_Action_Edit:		// Initiate edit process
						$this->procEditFile();
						break;
					case AIR_Action_Review:		// Review changes made in edit
						$this->procReviewFile();
						break;
					case AIR_Action_Post:		// Post changes made in edit
						$this->procPostFile();
						break;
					case AIR_Action_Print:		// Initiate print
						$this->procPrintFile();
						break;
					case AIR_Action_Delete:
						$this->procDeleteFile();
						break;
					case AIR_Action_Upload:		// Initiate new file upload
						$this->procUploadFile();
						break;

					case AIR_Action_Save:		// Checkpoint changes made during edit or new file creation
					case AIR_Action_ExecSQL:
					default:
		$this->anchor->putTraceData(__LINE__, __FILE__, 'here');
						$this->unknownActionRequest();
						$this->procDefault();
						break;
					}
				break;
			default:
		$this->anchor->putTraceData(__LINE__, __FILE__, 'here');
				$this->unknownActionRequest();
				$this->procDefault();
				break;
			}

		$result = $this->publishTxnDataArrayToResultMsg();
		if ($result < 0)
			{
			trigger_error("Critical data error in ".__FUNCTION__, E_USER_NOTICE);
			}

		$this->postResultMsg();
		}

	/***************************************************************************
	 * unknownActionRequest
	 *
	 *******/
	function unknownActionRequest()
		{
		$this->anchor->putTraceData(__LINE__, __FILE__, '***ERROR*** ' . __CLASS__ . '::' . __FUNCTION__);
		$this->anchor->putTraceData(__LINE__, __FILE__, 'DestObject = [' . $this->myMsgObject . ']');
		$this->anchor->putTraceData(__LINE__, __FILE__, 'DestAction = [' . $this->myMsgAction . ']');
		}

	/***************************************************************************
	 * procSelectView
	 *
	 *******/
	function procSelectView()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$currentDirViewSet = $this->procContext->getSessionData('DirMaintDirType');
		if (empty($currentDirViewSet))
			{
			$currentDirViewSet = AIR_Action_DirViewPub;
			}
		$this->myMsgAction = $currentDirViewSet;

		return($this->procViewDirectory());
		}

	/***************************************************************************
	 * procViewDirectory
	 *
	 *******/
	function procViewDirectory()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$directory = AF_ROOT_DIR.'/data/';
		$errors		= false;

		/* Clear context data for detail operations */
		$this->procClearDirMaintContext();

		switch ($this->myMsgAction)
			{
			case AIR_Action_DirViewAdm:
				$dirExt		= 'admin';
				$dirLabel	= 'Admin Directory';
				break;
			case AIR_Action_DirViewPub:
				$dirExt		= 'public';
				$dirLabel	= 'Public Directory';
				break;
			case AIR_Action_DirViewPvt:
				$dirExt		= 'private';
				$dirLabel	= 'Private Directory';
				break;
			case AIR_Action_DirViewShr:
				$dirExt		= 'shared';
				$dirLabel	= 'Member Shared Directory';
				break;
			default:
				break;
			}

		if (! empty($dirExt))
			{
			$directory .= $dirExt.'/';
			}
		$errFlag = $this->anchor->getSuppressErrorMsgs();
		$this->anchor->setSuppressErrMsgs(true);
		$dir = @opendir($directory);
		$this->anchor->setSuppressErrMsgs($errFlag);

		if ($dir)
			{
			$this->resultMsg->putMessageData('DirLabel', $dirLabel);
			$fileCount	= 0;
			while ($file = readdir($dir))
				{
				$pathName = $directory.$file;
				$pathInfo = pathinfo($pathName);
				$pathInfo['dirname']		= strtolower($pathInfo['dirname']);
				$pathInfo['basename']	= strtolower($pathInfo['basename']);
				if (array_key_exists('extension', $pathInfo))
					{
					$pathInfo['extension']	= strtolower($pathInfo['extension']);
					}
				else
					{
					$pathInfo['extension']	= '';
					}
				if (is_file($pathName))	// no directories or links
					{
					$newNode = $this->resultMsg->createElement('Item');

					$node = $this->resultMsg->createTextElement('File', $file);
					$newNode->appendChild($node);
					$node = $this->resultMsg->createTextElement('Filetype', $pathInfo['extension']);
					$newNode->appendChild($node);
					$node = $this->resultMsg->createTextElement('Filesize', filesize($pathName));
					$newNode->appendChild($node);
					$node = $this->resultMsg->createTextElement('Filetime', filemtime($pathName));
					$newNode->appendChild($node);
					$node = $this->resultMsg->createTextElement('Readable', is_readable($pathName));
					$newNode->appendChild($node);
					$node = $this->resultMsg->createTextElement('Writable', is_writable($pathName));
					$newNode->appendChild($node);

					$this->resultMsg->createNewDataCollectionItem($newNode);
					$fileCount++;
					}
				}
			closedir($dir);
			}
		else
			{
			$errors = true;
			$diagnostic = 'No such directory.';
			$this->resultMsg->attachDiagnosticTextItem('', $diagnostic, AIR_DiagMsg_Error);
			}

		if (! $errors)
			{
			$successful = true;
			if (! $fileCount)
				{
				$diagnostic = 'Directory is empty.';
				$this->resultMsg->attachDiagnosticTextItem('', $diagnostic, AIR_DiagMsg_Info);
				}
			else
				{
				/* Save context data for detail operations */
				$this->procContext->putSessionData('DirMaintDirType', $this->myMsgAction);
				$this->procContext->putSessionData('DirMaintDirPath', $dirExt);
				}
			if ($successful)
				{
				$encodeObject	= Dialog_DirView;
				$encodeAction	= AIR_Action_Encode;
				$encodeVers		= '1.0';
				}
			else
				{
				$errors		= true;
				}
			}

		if ($errors)
			{
			$encodeObject	= Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procSetResultTarget
	 *******/
	function procSetResultTarget($successful, $dialog)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if ($successful)
			{
			/*
			 * Result message will trigger detail dialog display
			 */
			$encodeObject	= $dialog;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			/*
			 * Abort operations, clear intermediate results
			 */
			$this->procClearDirFileContext();
			/*
			 * Result message will trigger return to appropriate directory view
			 */
			$encodeObject	= ProcMod_DirView;
			$encodeAction	= $this->procContext->getSessionData('DirMaintDirType');
			$encodeVers		= '1.0';
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procCreateFile
	 *******/
	function procCreateFile()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$successful = $this->procContext->getSessionData('DirMaintFileInit');
		if ($successful)
			{
			$diagnostic = 'Internal state error. '.__FUNCTION__.'['.__line__.']';
			$this->resultMsg->attachDiagnosticTextItem('', $diagnostic, AIR_DiagMsg_Error);
			$successful		= false;
			}
		else
			{
			$this->procClearDirFileContext();
			$this->procContext->putSessionData('DirMaintFileInit', true);
			$successful		= true;
			}

		$this->procSetResultTarget($successful, Dialog_FileCreate);
		}

	/***************************************************************************
	 * procCreateFileAudit
	 *******/
	function procCreateFileAudit()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errors		= false;
		$successful = $this->procContext->getSessionData('DirMaintFileInit');
		if (! $successful)
			{
			$diagnostic = 'Internal state error. '.__FUNCTION__.'['.__line__.']';
			$this->resultMsg->attachDiagnosticTextItem('', $diagnostic, AIR_DiagMsg_Error);
			}
		else
			{
			$fileName		= $this->procMsg->getMessageData('fileName');
			if (! is_null($fileName))
				{
				$this->procContext->putSessionData('DirMaintFileName', $fileName);
				}
			$fileType		= $this->procMsg->getMessageData('fileType');
			if (! is_null($fileType))
				{
				$this->procContext->putSessionData('DirMaintFileType', $fileType);
				}
			$fileCoding		= $this->procMsg->getMessageData('fileCoding');
			if (! is_null($fileCoding))
				{
				$this->procContext->putSessionData('DirMaintFileCoding', $fileCoding);
				}
			$fileMime		= $this->procMsg->getMessageData('fileMime');
			if (! is_null($fileMime))
				{
				$this->procContext->putSessionData('DirMaintFileMime', $fileMime);
				}
			$eleNewContent	= $this->procMsg->getMessageData('eleNewContent');
			if (! is_null($eleNewContent))
				{
				$this->procContext->putSessionData('DirMaintEditData', $eleNewContent);
				$contentSize = strlen($eleNewContent);
				$this->procContext->putSessionData('DirMaintFileSize', $contentSize);
				}

			if (empty($fileName))
				{
				$errors		= true;
				$diagnostic = 'Name is required.';
				$this->resultMsg->attachDiagnosticTextItem('Name', $diagnostic, AIR_DiagMsg_Info);
				}

			if (empty($fileType))
				{
				$errors		= true;
				$diagnostic = 'Type specification is required.';
				$this->resultMsg->attachDiagnosticTextItem('Type', $diagnostic, AIR_DiagMsg_Info);
				}

			if (empty($eleNewContent))
				{
				$errors		= true;
				$diagnostic = 'Content is required.';
				$this->resultMsg->attachDiagnosticTextItem('Content', $diagnostic, AIR_DiagMsg_Info);
				}
			}

		if ((! $successful)
		 || ($errors))
		 	{
			$this->procSetResultTarget($successful, Dialog_FileCreate);
			return;
			}

		$successful = $this->procPutFile();

		$encodeObject	= ProcMod_DirView;
		$encodeAction	= $this->procContext->getSessionData('DirMaintDirType');
		$encodeVers		= '1.0';

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procViewFile
	 *******/
	function procViewFile()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$eleNewContent	= $this->procMsg->getMessageData('eleNewContent');
		if (! is_null($eleNewContent))
			{
			$eleNewContent = htmlspecialchars($eleNewContent, ENT_QUOTES);
			$this->procContext->putSessionData('DirMaintEditData', $eleNewContent);
			}
		$successful = $this->procContext->getSessionData('DirMaintFileInit');
		if (! $successful)
			{
			$target		= $this->procMsg->getMessageData('target');
			$this->procContext->putSessionData('DirMaintFileName', $target);

			$successful = $this->procGetFile();
			$this->procContext->putSessionData('DirMaintEditData', $this->procContext->getSessionData('DirMaintFileData'));
			$this->procContext->putSessionData('DirMaintFileInit', true);
			}
		$content = $this->procContext->getSessionData('DirMaintEditData');
		if (empty($content))
			{
			$diagnostic = 'Content is empty.';
			$this->resultMsg->attachDiagnosticTextItem('Content', $diagnostic, AIR_DiagMsg_Info);
			}
		$this->procSetResultTarget($successful, Dialog_FileView);
		}

	/***************************************************************************
	 * procEditFile
	 *******/
	function procEditFile()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$successful = $this->procContext->getSessionData('DirMaintFileInit');
		if (! $successful)
			{
			$target		= $this->procMsg->getMessageData('target');
			$this->procContext->putSessionData('DirMaintFileName', $target);

			$successful = $this->procGetFile();
			$this->procContext->putSessionData('DirMaintEditData', $this->procContext->getSessionData('DirMaintFileData'));
			$this->procContext->putSessionData('DirMaintFileInit', true);
			}
		$content = $this->procContext->getSessionData('DirMaintEditData');
		if (empty($content))
			{
			$diagnostic = 'Content is empty! Posting this result will cause element to be deleted.';
			$this->resultMsg->attachDiagnosticTextItem('Content', $diagnostic, AIR_DiagMsg_Info);
			}
		$this->procSetResultTarget($successful, Dialog_FileEdit);
		}

	/***************************************************************************
	 * procReviewFile
	 *******/
	function procReviewFile()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$eleNewContent	= $this->procMsg->getMessageData('eleNewContent');
		if (! is_null($eleNewContent))
			{
			$eleNewContent = htmlspecialchars($eleNewContent, ENT_QUOTES);
			$this->procContext->putSessionData('DirMaintEditData', $eleNewContent);
			}
		$successful = $this->procContext->getSessionData('DirMaintFileInit');
		if (! $successful)
			{
			$diagnostic = 'No context for review.';
			$this->anchor->setDialogDiagnostic('', $diagnostic, AIR_DiagMsg_Error);
			}
		else
			{
			$content = $this->procContext->getSessionData('DirMaintEditData');
			if (empty($content))
				{
				$diagnostic = 'Content is empty! Posting this result will cause element to be deleted.';
				$this->resultMsg->attachDiagnosticTextItem('New Content', $diagnostic, AIR_DiagMsg_Info);
				}
			}
		$this->procSetResultTarget($successful, Dialog_FileEditReview);
		}

	/***************************************************************************
	 * procPostFile
	 *******/
	function procPostFile()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$eleNewContent	= $this->procMsg->getMessageData('eleNewContent');
		if (! is_null($eleNewContent))
			{
			$eleNewContent = htmlspecialchars($eleNewContent, ENT_QUOTES);
			$this->procContext->putSessionData('DirMaintEditData', $eleNewContent);
			}
		$successful = $this->procContext->getSessionData('DirMaintFileInit');
		if (! $successful)
			{
			$diagnostic = 'No context for posting operation.';
			$this->anchor->setDialogDiagnostic('', $diagnostic, AIR_DiagMsg_Error);
			}
		else
			{
			$content = $this->procContext->getSessionData('DirMaintEditData');
			if (empty($content))
				{
				return($this->procDeleteFile());
				}

			$successful = $this->procPutFile();
			}

		$encodeObject	= ProcMod_DirView;
		$encodeAction	= $this->procContext->getSessionData('DirMaintDirType');
		$encodeVers		= '1.0';

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procPrintFile
	 *******/
	function procPrintFile()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$eleNewContent	= $this->procMsg->getMessageData('eleNewContent');
		if (! is_null($eleNewContent))
			{
			$eleNewContent = htmlspecialchars($eleNewContent, ENT_QUOTES);
			$this->procContext->putSessionData('DirMaintEditData', $eleNewContent);
			}
		$successful = $this->procContext->getSessionData('DirMaintFileInit');
		if (! $successful)
			{
			$target		= $this->procMsg->getMessageData('target');
			$target 		= htmlspecialchars($target, ENT_QUOTES);
			$this->procContext->putSessionData('DirMaintFileName', $target);

			$successful = $this->procGetFile();
			$this->procContext->putSessionData('DirMaintEditData', $this->procContext->getSessionData('DirMaintFileData'));
			$this->procContext->putSessionData('DirMaintFileInit', true);
			}
		$content = $this->procContext->getSessionData('DirMaintEditData');
		if (empty($content))
			{
			$diagnostic = 'Content is empty.';
			$this->resultMsg->attachDiagnosticTextItem('Content', $diagnostic, AIR_DiagMsg_Info);
			}
		$this->procSetResultTarget($successful, Dialog_FilePrint);
		}

	/***************************************************************************
	 * procDeleteFile
	 *******/
	function procDeleteFile()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$target		= $this->procMsg->getMessageData('target');
		$this->procContext->putSessionData('DirMaintFileName', $target);
		$pathName 	= $this->procGetFilePath();

		$errFlag = $this->anchor->getSuppressErrorMsgs();
		$this->anchor->setSuppressErrMsgs(true);
		$successful = @unlink($pathName);
		$this->anchor->setSuppressErrMsgs($errFlag);

		if (! $successful)
			{
			$diagnostic = 'Could not be deleted.';
			$this->anchor->setDialogDiagnostic($target, $diagnostic, AIR_DiagMsg_Error);
			}
		else
			{
			$diagnostic = 'Deleted.';
			$this->anchor->setDialogDiagnostic($target, $diagnostic, AIR_DiagMsg_Info);
			}

		$encodeObject	= ProcMod_DirView;
		$encodeAction	= $this->procContext->getSessionData('DirMaintDirType');
		$encodeVers		= '1.0';

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procGetFile
	 *
	 *******/
	function procGetFile()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$pathName 	= $this->procGetFilePath();
		$mimeTypes	= $this->anchor->getStdVar('contentTypeMap');
		$success		= false;

		if (file_exists($pathName))
			{
			$pathInfo = pathinfo($pathName);
			$pathInfo['dirname']		= strtolower($pathInfo['dirname']);
			$pathInfo['basename']	= strtolower($pathInfo['basename']);
			if (array_key_exists('extension', $pathInfo))
				{
				$pathInfo['extension']	= strtolower($pathInfo['extension']);
				}
			else
				{
				$pathInfo['extension']	= '';
				}

			$errFlag = $this->anchor->getSuppressErrorMsgs();
			$this->anchor->setSuppressErrMsgs(true);
			$fileContent	= $this->anchor->getFileContent($pathName);
			$this->anchor->setSuppressErrMsgs($errFlag);

			$fileExt = $pathInfo['extension'];
			if (array_key_exists($fileExt, $mimeTypes))
				{
				$mimeDef		= $mimeTypes[$fileExt];
				$mimeType	= $mimeDef['mimeType'];
				}
			else
				{
				$mimeType	= '';
				}

			$fileCoding = 'txt';
			if (strpos($fileContent, '<?xml') !== false)
				{
				$fileCoding = 'xml';
				}
			else
				{
				if ((strpos($fileContent, '<') !== false)
				 || (strpos($fileContent, '>') !== false)
				 || (strpos($fileContent, '&') !== false)
				 || (strpos($fileContent, '"') !== false)
				 || (strpos($fileContent, "'") !== false))
					{
					$fileCoding = 'tag';
					}
				}
			switch ($fileCoding)
				{
				case 'xml':
				case 'tag':
					$fileContent = htmlspecialchars($fileContent, ENT_QUOTES);
					break;
				default:
					break;
				}
			$this->procContext->putSessionData('DirMaintFileData', $fileContent);
			$this->procContext->putSessionData('DirMaintFileCoding', $fileCoding);
			$this->procContext->putSessionData('DirMaintFileMime', $mimeType);
			$this->procContext->putSessionData('DirMaintFileType', $fileExt);
			$this->procContext->putSessionData('DirMaintFileSize', filesize($pathName));
			$this->procContext->putSessionData('DirMaintFilePerms', fileperms($pathName));
			$this->procContext->putSessionData('DirMaintFileMtime', filemtime($pathName));
			$this->procContext->putSessionData('DirMaintFileAtime', fileatime($pathName));
			$this->procContext->putSessionData('DirMaintFileRead', is_readable($pathName));
			$this->procContext->putSessionData('DirMaintFileWrite', is_writable($pathName));
			$success		= true;
			}
		else
			{
			$diagnostic = 'Could not be found.';
			$this->anchor->setDialogDiagnostic($target, $diagnostic, AIR_DiagMsg_Error);
			}

		return($success);
		}

	/***************************************************************************
	 * procPutFile
	 *
	 *******/
	function procPutFile()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$pathName 	= $this->procGetFilePath();
		$content		= $this->procContext->getSessionData('DirMaintEditData');

		$errFlag = $this->anchor->getSuppressErrorMsgs();
		$this->anchor->setSuppressErrMsgs(true);
		$success	= $this->anchor->putFileContent($pathName, $content);
		$this->anchor->setSuppressErrMsgs($errFlag);
		if (! $success)
			{
			$target		= $this->procContext->getSessionData('DirMaintFileName');
			$diagnostic = 'Error writing file '.$target;
			$this->anchor->setDialogDiagnostic('', $diagnostic, AIR_DiagMsg_Error);
			}

		return($success);
		}

	/***************************************************************************
	 * procUploadFile
	 *******/
	function procUploadFile()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$target		= $this->procMsg->getMessageData('target');
		$this->procContext->putSessionData('DirMaintFileName', $target);
		$pathName 	= $this->procGetFilePath();

		$encodeObject	= Dialog_FileCreate;
		$encodeAction	= AIR_Action_Encode;
		$encodeVers		= '1.0';

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procClearDirMaintContext
	 *
	 *******/
	function procClearDirMaintContext()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/* Clear context data for detail operations */
		$this->procContext->removeElementNode('DirMaintDirType');		// Detail msg action type code (adm, pvt, pub, etc.)
		$this->procContext->removeElementNode('DirMaintDirPath');		// sub-directory name - not full path
		$this->procClearDirFileContext();
		}

	/***************************************************************************
	 * procClearDirFileContext
	 *
	 *******/
	function procClearDirFileContext()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/* Clear context data for detail operations */
		$this->procContext->putSessionData('DirMaintFileInit', false);	// file context data set for file edit session?
		$this->procContext->removeElementNode('DirMaintFileName');			// file name portion, incl file type extension
		$this->procContext->removeElementNode('DirMaintFileData');			// file data
		$this->procContext->removeElementNode('DirMaintEditData');			// current in-process edit version of file data
		$this->procContext->removeElementNode('DirMaintFileCoding');		// file encoding style
		$this->procContext->removeElementNode('DirMaintFileMime');			// file mime type
		$this->procContext->removeElementNode('DirMaintFileType');			// file type extension
		$this->procContext->removeElementNode('DirMaintFileSize');			// file size
		$this->procContext->removeElementNode('DirMaintFilePerms');		// file permissions
		$this->procContext->removeElementNode('DirMaintFileMtime');		// file M time
		$this->procContext->removeElementNode('DirMaintFileAtime');		// file A time
		$this->procContext->removeElementNode('DirMaintFileRead');			// file readable
		$this->procContext->removeElementNode('DirMaintFileWrite');		// file writable
		}

	/***************************************************************************
	 * procGetFilePath
	 *
	 *******/
	function procGetFilePath()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$target		= $this->procContext->getSessionData('DirMaintFileName');
		$directory = AF_ROOT_DIR.'/data/';
		$dirExt		= $this->procContext->getSessionData('DirMaintDirPath');
		if (! empty($dirExt))
			{
			$directory .= $dirExt.'/';
			}
		$pathName 	= $directory.$target;

		return($pathName);
		}

	} // end of class

/*
 * This code is executed once per 'include' as the module is scanned
 * and all code not inside newly defined "function blocks" is executed.
 */
	if ($this->anchor->debugCoreFcns())
		{
		echo '<debug>'.__FILE__.'['.__LINE__.']'."*** $myDynamClass() include initialization concluded ***".'<br/></debug> ';
		}
 ?>