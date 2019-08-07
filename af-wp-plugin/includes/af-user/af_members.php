<?php
/*
 * af_members script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-19 JVS Begin test of new standalone PHP environment script
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'af_members';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');
	
		$navSecondary	= array();
		
		$navItem				= array();
		$navItem['name']		= 'Element Directory';
		$navItem['procIdent']	= Dialog_DirViewMenu;
		$navSecondary[] = $navItem;
		$navItem				= array();
		$navItem['name']		= 'Repository Admin';
		$navItem['procIdent']	= Dialog_AirAdmin;
		$navSecondary[] = $navItem;
		$navItem				= array();
		$navItem['name']		= 'System Admin';
		$navItem['procIdent']	= Dialog_SysAdmin;
		$navSecondary[] = $navItem;
//		$navItem				= array();
//		$navItem['name']		= 'Members';
//		if ($this->anchor->user->isLoggedIn())
//			{
//			$navItem['procIdent']	= Dialog_Members;
//			}
//		else
//			{
//			$navItem['procIdent']	= Dialog_Login;
//			}
//		$navSecondary[] = $navItem;
//		$navItem				= array();
//		$navItem['name']		= 'Help';
//		$navItem['procIdent']	= Dialog_Help;
//		$navItem['name']		= 'Contact';
//		$navItem['procIdent']	= Dialog_Contact;
//		$navSecondary[] = $navItem;
//		$navItem				= array();
//		$navItem['name']		= 'Site Map';
//		$navItem['procIdent']	= Dialog_SiteMap;
//		$navSecondary[] = $navItem;

	$anchor->setStdVar('navSecondary', $navSecondary);
	$anchor->dlgStepResponse->title = 'Members';
	
	if ($anchor->trace())
	 	{
		$anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
		}
	$myPageContent = & new C_AirHtmlElement($anchor);
	$myPageContent->initialize(htmlEleCenter);
	
	$table1			= & $myPageContent->createChild(htmlEleTable);
	$tab1row1		= & $table1->createChild(htmlEleTblRow);
	$tab1col1_1		= & $tab1row1->createChild(htmlEleTblData);
							 $tab1col1_1->createChild(htmlEleH1, 'Members');
	$tab1row2		= & $table1->createChild(htmlEleTblRow);
	$tag1b			= & $tab1row2->createChild(htmlEleCenter);
	$table2			= & $tag1b->createChild(htmlEleTable);
	$tab2row1		= & $table2->createChild(htmlEleTblRow);
	$tab2col1_1		= & $tab2row1->createChild(htmlEleTblData);
							 $tab2col1_1->setAttribute(htmlAttrAlign, 'left');
							 $tab2col1_1->createChild(htmlEleParagraph, 'Please take some time to explore our site and get to know us.');
							 $tab2col1_1->createChild(htmlEleParagraph, 'We would enjoy hearing any feedback you may have to offer.');
	
	$anchor->dlgStepResponse->setContent($myPageContent->paintElement());
?>