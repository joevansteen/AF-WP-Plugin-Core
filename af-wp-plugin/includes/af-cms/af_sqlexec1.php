<?php
/*
 * af_sqlexec1 script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-21 JVS Begin test of new standalone PHP environment script
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'af_sqlexec1';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	$anchor->dlgStepResponse->title = 'SQL Exec Preview';
	$currentDir 	= '/temp/';
	$pathName 		= $currentDir.$target;
	$_SESSION['sqlTarget']	= $pathName;
	$_SESSION['sqlStart']	= 0;
	$dataStream		= new C_AirTextStream($anchor);
	$dataStream->initialize($pathName);
//	$fileContent	= $anchor->getFileContent($pathName);

	$myPageContent = new C_AirHtmlElement($anchor);
	$myPageContent->initialize(htmlEleForm);
							 $myPageContent->setAttribute(htmlAttrAction, $anchor->getActionUrlBase().'dialog='.$anchor->dlgStepResponse->pageDialog);
							 $myPageContent->setAttribute(htmlAttrMethod, 'post');
	$work				= & $myPageContent->createChild(htmlEleCenter);
	$pageTable		= & $work->createChild(htmlEleTable);
	$pageTabRow		= & $pageTable->createChild(htmlEleTblRow);

	$work				= & $pageTabRow->createChild(htmlEleCenter);
	$smryTable		= & $work->createChild(htmlEleTable);

	$smryTabRow		= & $smryTable->createChild(htmlEleTblRow);
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData);
							 $smryTabData->setAttribute(htmlAttrColSpan, '2');
							 $smryTabData->setAttribute(htmlAttrAlign, 'center');
	$work				= & $smryTabData->createChild(htmlEleBold);
	$work				= & $work->createChild(htmlEleEmphasis);
							 $work->createChild(htmlEleParagraph, $target);

	$smryTabRow		= & $smryTable->createChild(htmlEleTblRow);
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData, 'Type: ');
							 $smryTabData->setAttribute(htmlAttrAlign, 'right');
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData, filetype($pathName));
							 $smryTabData->setAttribute(htmlAttrAlign, 'left');

	$smryTabRow		= & $smryTable->createChild(htmlEleTblRow);
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData, 'Permissions: ');
							 $smryTabData->setAttribute(htmlAttrAlign, 'right');
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData, decoct(fileperms($pathName)));
							 $smryTabData->setAttribute(htmlAttrAlign, 'left');

	$smryTabRow		= & $smryTable->createChild(htmlEleTblRow);
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData, 'Size (Bytes): ');
							 $smryTabData->setAttribute(htmlAttrAlign, 'right');
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData, filesize($pathName));
							 $smryTabData->setAttribute(htmlAttrAlign, 'left');

	$smryTabRow		= & $smryTable->createChild(htmlEleTblRow);
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData, 'Last Modified: ');
							 $smryTabData->setAttribute(htmlAttrAlign, 'right');
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData, date('j F Y H:i', filemtime($pathName)));
							 $smryTabData->setAttribute(htmlAttrAlign, 'left');

	$smryTabRow		= & $smryTable->createChild(htmlEleTblRow);
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData, 'Last Accessed: ');
							 $smryTabData->setAttribute(htmlAttrAlign, 'right');
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData, date('j F Y H:i', fileatime($pathName)));
							 $smryTabData->setAttribute(htmlAttrAlign, 'left');

	$smryTabRow		= & $smryTable->createChild(htmlEleTblRow);
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData);
							 $smryTabData->setAttribute(htmlAttrColSpan, '2');
							 $smryTabData->setAttribute(htmlAttrAlign, 'center');

	$smryTabBtn		= & $smryTabData->createChild(htmlEleInput);
							 $smryTabBtn->setAttribute(htmlAttrType, 'submit');
							 $smryTabBtn->setAttribute(htmlAttrName, 'request');
							 $smryTabBtn->setAttribute(htmlAttrValue, AIR_Action_Okay);

	$smryTabBtn		= & $smryTabData->createChild(htmlEleInput);
							 $smryTabBtn->setAttribute(htmlAttrType, 'submit');
							 $smryTabBtn->setAttribute(htmlAttrName, 'request');
							 $smryTabBtn->setAttribute(htmlAttrValue, AIR_Action_Edit);

	$smryTabBtn		= & $smryTabData->createChild(htmlEleInput);
							 $smryTabBtn->setAttribute(htmlAttrType, 'submit');
							 $smryTabBtn->setAttribute(htmlAttrName, 'request');
							 $smryTabBtn->setAttribute(htmlAttrValue, AIR_Action_Quit);

	$smryTabRow		= & $smryTable->createChild(htmlEleTblRow);
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData, '&#160;'); // Blank table row for visual spacing
							 $smryTabData->setAttribute(htmlAttrColSpan, '2');

	$smryTabRow		= & $smryTable->createChild(htmlEleTblRow);
	$smryTabData	= & $smryTabRow->createChild(htmlEleTblData);
							 $smryTabData->setAttribute(htmlAttrColSpan, '2');
							 $smryTabData->setAttribute(htmlAttrAlign, 'center');
	$work				= & $smryTabData->createChild(htmlEleBold);
	$work				= & $work->createChild(htmlEleEmphasis);
							 $work->createChild(htmlEleParagraph, 'Please review the following carefully and ensure that it is what you want to do.');

	$pageTabRow		= & $pageTable->createChild(htmlEleTblRow);
	$pageTabData	= & $pageTabRow->createChild(htmlEleTblData);

	$mainTable		= & $pageTabData->createChild(htmlEleTable);

	$mainTabRow		= & $mainTable->createChild(htmlEleTblRow);
	$mainTabData	= & $mainTabRow->createChild(htmlEleTblData);
							 $mainTabData->setAttribute(htmlAttrAlign, 'left');


	$contentList	= & $mainTabData->createChild(htmlEleDefList);
							 $contentList->createChild(htmlEleDefTerm, 'Content');
//							 $contentList->createChild(htmlEleDefDescr, htmlspecialchars($fileContent, ENT_QUOTES));
	$contentData	= & $contentList->createChild(htmlEleDefDescr);

	$listTable		= & $contentData->createChild(htmlEleTable);
	$fileContent 	= $dataStream->getNextFragment();
	while ($fileContent != null)
		{
		$listTabRow		= & $listTable->createChild(htmlEleTblRow);
		$listTabData	= & $listTabRow->createChild(htmlEleTblData, $fileContent);
								 $listTabData->setAttribute(htmlAttrAlign, 'left');
		if ($anchor->trace())
		 	{
			if ($dataStream->lastChar($fileContent, ';'))
				{
				$listTabRow		= & $listTable->createChild(htmlEleTblRow);
				$listTabData	= & $listTabRow->createChild(htmlEleTblData, '<hr />');
				}
			}

		$fileContent 	= $dataStream->getNextFragment();
		}

	$pageTabRow		= & $pageTable->createChild(htmlEleTblRow);
	$pageTabData	= & $pageTabRow->createChild(htmlEleTblData);
							 $pageTabData->setAttribute(htmlAttrAlign, 'center');

	$smryTabBtn		= & $pageTabData->createChild(htmlEleInput);
							 $smryTabBtn->setAttribute(htmlAttrType, 'submit');
							 $smryTabBtn->setAttribute(htmlAttrName, 'request');
							 $smryTabBtn->setAttribute(htmlAttrValue, AIR_Action_Okay);

	$smryTabBtn		= & $pageTabData->createChild(htmlEleInput);
							 $smryTabBtn->setAttribute(htmlAttrType, 'submit');
							 $smryTabBtn->setAttribute(htmlAttrName, 'request');
							 $smryTabBtn->setAttribute(htmlAttrValue, AIR_Action_Edit);

	$smryTabBtn		= & $pageTabData->createChild(htmlEleInput);
							 $smryTabBtn->setAttribute(htmlAttrType, 'submit');
							 $smryTabBtn->setAttribute(htmlAttrName, 'request');
							 $smryTabBtn->setAttribute(htmlAttrValue, AIR_Action_Quit);

	$anchor->dlgStepResponse->setContent($myPageContent->paintElement());
?>