<?php
/*
 * af_upload2 script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-14 JVS Begin test of new standalone PHP environment script
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'af_upload2';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	foreach($filelist as $fileKey => $fileEntry)
		{
	  	if (array_key_exists($fileKey, $_FILES))
	  		{
			$fileEntry['fileError']		= $_FILES[$fileKey]['error'];
			$fileEntry['fileTmpName']	= $_FILES[$fileKey]['tmp_name'];
			$fileEntry['fileName']		= $_FILES[$fileKey]['name'];
			$fileEntry['fileSize']		= $_FILES[$fileKey]['size'];
			$fileEntry['fileType']		= $_FILES[$fileKey]['type'];
			switch ($fileEntry['fileError'])
				{
				case 0: //UPLOAD_ERROR_OK:
					$uploadStatus	= 'Successful upload';
					$uploadSuccess = true;
					break;
				case 1: //UPLOAD_ERROR_INI_SIZE:
					$uploadStatus	= 'File exceeded php.ini upload_max_filesize';
					$uploadSuccess = false;
					break;
				case 2: //UPLOAD_ERROR_FORM_SIZE:
					$uploadStatus	= 'File exceeded form max_file_size';
					$uploadSuccess = false;
					break;
				case 3: //UPLOAD_ERROR_PARTIAL:
					$uploadStatus	= 'File only partially uploaded';
					$uploadSuccess = false;
					break;
				case 4: //UPLOAD_ERROR_NO_FILE:
					$uploadStatus	= 'No file uploaded';
					$uploadSuccess = false;
					break;
				default:
					$uploadStatus	= 'File had undefined upload problem, error=['.$fileEntry['fileError'].']';
					$uploadSuccess = false;
					break;
				}
			$fileEntry['fileExists']	= $uploadSuccess;
			$fileEntry['fileStatus']	= $uploadStatus;

			/*
			 * The following precaution was advised in
			 *		PHP and MySQL Web Development, Third Edition
			 *		by Luke Welling & Laura Thomson; Pg 406
			 *		Copyright 2005 by Sams Publishing
			 *
			 * It references a PHP security risk documented at
			 *	http://lists.insecure.org/bugtraq/2000/Sep/0237.html
			 *
			 * Weeling and Thompson advise making the check. The resolution
			 * strategy is JVS.
			 *
			 * Note. This should be included in any production server.
			 * However, it causes problem when the server is on the local machine
			 * and we are doing test uploads... which is the reason for the
			 * strange implementation.
			 */
			$localServer = true;
			if ((! $localServer)
			&&  (! is_uploaded_file($fileEntry['fileTmpName'])))
				{
				$fileEntry['fileExists']	= false;
				$fileEntry['fileStatus']	= 'Problem: Possible file upload attack. See BUGTRAQ Sept 2000, 0237';
				}

			if ($fileEntry['fileExists'])
				{
				/*
				 * NOTE: The following directory is relative to the root of the drive.
				 * This process moves the file out of range of casual browsing.
				 */
				$fileEntry['fileAirName'] = '/temp/'.$fileEntry['fileName'];
				if (! move_uploaded_file($fileEntry['fileTmpName'], $fileEntry['fileAirName']))
					{
					$fileEntry['fileExists']	= false;
					$fileEntry['fileStatus']	= 'Problem: Could not move file to staging directory';
					}

				/*
				 * The following removes HTML and PHP tags from the file and
				 * rewrites the file. This code assumes we are knowledable about the
				 * occurence of such tags in real php and html files. It also assumes
				 * that other file types are "as advertised" or that we will take
				 * appropriate precautions in using them.
				 */
				if (($fileEntry['fileExists'])
				 && ($fileEntry['fileType'] == 'text/plain'))
					{
					$fileContent = $anchor->getFileContent($fileEntry['fileAirName']);
					$fileContent = strip_tags($fileContent);
					$anchor->putFileContent($fileEntry['fileAirName'], $fileContent);
					}
				}
			$filelist[$fileKey]			= $fileEntry;
			}
		}

	$anchor->dlgStepResponse->title = 'Element Upload';
	$formColumns = 0;
	$fileCount	 = 0;

	$content = '';

	$content .= '<form ';
	$content .= 'action="'.$anchor->getActionUrlBase().'dialog=';
	$content .= $anchor->dlgStepResponse->pageDialog;
	$content .= '" method=post>';

	foreach($filelist as $fileKey => $fileEntry)
		{
		if ($fileEntry['fileError'] != 4)
			{
			$fileCount++;
			}
		}

	$content .= 	'<center><table>';
	if ($fileCount)
		{
		$content .= 		'<tr>';
		$content .= 			'<td align="center">Description</td>';	$formColumns++;
		$content .= 			'<td align="center">Status</td>';		$formColumns++;
		if ($sysDiag)
			{
			$content .= 			'<td align="center">Ident</td>';		$formColumns++;
			$content .= 			'<td align="center">TmpName</td>';	$formColumns++;
			}
		$content .= 			'<td align="center">Name</td>';			$formColumns++;
		$content .= 			'<td align="center">Size</td>';			$formColumns++;
		$content .= 			'<td align="center">Type</td>';			$formColumns++;
		$content .= 		'</tr>';
		}

	foreach($filelist as $fileKey => $fileEntry)
		{
		if ($fileEntry['fileError'] != 4)
			{
			$content .= 		'<tr>';
			$content .= 			'<td align="right">' . $fileEntry['fileDescr'] . '</td>';
			$content .= 			'<td align="left">' . $fileEntry['fileStatus'] . '</td>';
			if ($fileEntry['fileExists'])
				{
				if ($sysDiag)
					{
					$content .= 			'<td align="left">' . $fileEntry['fileIdent'] . '</td>';
					$content .= 			'<td align="left">' . $fileEntry['fileAirName'] . '</td>';
					}
				$content .= 			'<td align="left">' . $fileEntry['fileName'] . '</td>';
				$content .= 			'<td align="right">' . $fileEntry['fileSize'] . '</td>';
				$content .= 			'<td align="left">' . $fileEntry['fileType'] . '</td>';
				}
			else
				{
				if ($sysDiag)
					{
					$content .= 			'<td align="center"> n/a </td>';
					$content .= 			'<td align="center"> n/a </td>';
					}
				$content .= 			'<td align="center"> n/a </td>';
				$content .= 			'<td align="center"> n/a </td>';
				$content .= 			'<td align="center"> n/a </td>';
				}
			$content .= 		'</tr>';
			}
		}
	$content .= '<tr>';
	if ($fileCount)
		{
		$content .= '<td colspan="'.$formColumns.'" align="center">';
		}
	else
		{
		$content .= '<td align="center"> <em>No files uploaded!</em> </td>';
		$content .= '</tr><tr>';
		$content .= '<td align="center">';
		}
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Back.'" />';
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Okay.'" />';
	$content .= '</td>';
	$content .= 	'</table></center>';
	$content .= '</form>';
	/*
	 * Upload content review
	 */
	$firstPreview = true;
	foreach($filelist as $fileKey => $fileEntry)
		{
		if (($fileEntry['fileExists'])
		 && (($fileEntry['fileType'] == 'text/plain')
		 	|| ($fileEntry['fileType'] == 'text/xml') // Could be dangerous in production, echo causes interpreted replay of html script
		 	|| ($fileEntry['fileType'] == 'text/html'))) // Could be dangerous in production, echo causes interpreted replay of html script
			{
			if ($firstPreview)
				{
				$content .= 	'<hr/><center>Review of uploaded text data:</center><br/>';
				$content .= 	'<dl>';
				$firstPreview = false;
				}
			$content .= 	'<dt>' . $fileEntry['fileName'] . '</dt>';
			$fileContent = $anchor->getFileContent($fileEntry['fileAirName']);
		 	if (($fileEntry['fileType'] == 'text/xml')
		 	 || ($fileEntry['fileType'] == 'text/html')) // Could be dangerous in production, echo causes interpreted replay of html script
		 		{
				$fileContent = htmlspecialchars($fileContent, ENT_QUOTES);
				}
			$content .= 			'<dd>' . $fileContent . '</dd>';
			}
		}
	if (! $firstPreview)
		{
		$content .= 	'</dl>';
		}

	$anchor->dlgStepResponse->setContent($content);
?>