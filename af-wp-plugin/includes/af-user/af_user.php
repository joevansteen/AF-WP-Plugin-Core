<?php
/*
 * af_user script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-17 JVS Begin test of new standalone PHP environment script
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirUser';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');


class C_AirUser extends C_AirObjectBase {
	var $userLoginName	= '';
	var $userIdent			= '';
	var $priorUserIdent	= '';
	var $securityActionAttempts	= array();
	var $securityErrorThreshold	= 20;		// Total consecutive security errors before turning off access
	var $chgPswdErrorThreshold		= 20;		// Total consecutive invalid password changes before turning off access
	var $loginErrorThreshold		= 20;		// Total consecutive invalid logins before turning off access

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
		if (isset($_SESSION['userIdent']))
			{
			$this->userIdent = $_SESSION['userIdent'];
			}
		if (isset($_SESSION['userLoginName']))
			{
			$this->userLoginName = $_SESSION['userLoginName'];
			}
		if (isset($_SESSION['securityActionAttempts']))
			{
			$this->securityActionAttempts = $_SESSION['securityActionAttempts'];
			}
		if (isset($_SESSION['priorUserIdent']))
			{
			$this->priorUserIdent = $_SESSION['priorUserIdent'];
			}
		}

	/***************************************************************************
	 * terminate
	 *******/
	function terminate()
	 	{
 		/*
 		 * Passphrase history debug code ...
 		 *
			echo __FILE__.'['.__LINE__.'] '.'. __CLASS__.'::'.__FUNCTION__;
			echo 'attempts='.count($this->securityActionAttempts).'<br/>';
			$i = 0;
			foreach ($this->securityActionAttempts as $value)
				{
				foreach ($value as $key => $content)
					{
					echo 'Attempt['.$i.']['.$key.'] = ['.$content.']';
					echo '<br/>';
					}
				$i++;
				}
		 *
		 *
		 */
		$_SESSION['userIdent']					= $this->userIdent;
		$_SESSION['userLoginName']				= $this->userLoginName;
		$_SESSION['securityActionAttempts']	= $this->securityActionAttempts;
		$_SESSION['priorUserIdent']			= $this->priorUserIdent;

		parent::terminate();
		}

	/***************************************************************************
	 * isLoggedIn
	 *******/
	function isLoggedIn()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$success = false;
		if (! empty($this->userIdent))
			{
			$success = true;
			}

		return($success);
		}

	/***************************************************************************
	 * login
	 *******/
	function login($loginID, $loginPswd)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$success = false;
		if ((empty($this->userIdent))
		 && ((count($this->securityActionAttempts) < $this->securityErrorThreshold)))
			{
			$result = $this->getUserAccessRecord($loginID);
			if (($result)						// must be a match
			 && (count($result) == 1))
				{
				$userDef = $result[0];
				$userPassphrase		= $userDef['userPassword'];
/*****************
 * Password override assistance
 *****************
echo '<secret>';
echo __file__.' ['.__line__.'] ';
echo ' loginID='.$loginID;
echo ' passphrase='.$loginPswd.'<br />';
echo ' encrypted='.sha1($loginPswd).'<br />';
echo ' compareto='.$userPassphrase;
echo '</secret><br />';
/********************
 * end of password override assistance code
 ********************/
				if ($userPassphrase == sha1($loginPswd))
			 		{
					$this->userLoginName			= $userDef['userLoginName'];
					$userPassphrase				= $userDef['userPassword'];
					$this->userIdent				= $userDef['userIdent'];
					$_SESSION['sessionUser']	= $this->userIdent;
					/*
					 * The following two should probably be in the session document
					 * and NOT the PHP session data
					 */
					$_SESSION['userIdent']		= $this->userIdent;
					$_SESSION['userLoginName']	= $this->userLoginName;
					$success = true;
					if ($this->priorUserIdent == $this->userIdent)
						{
						/*
						 * Session resumption after secure suspension
						 */
						$this->resultText		= 'Successful session resumption';
						}
					else
						{
						/*
						 * New user session starting
						 */
						$this->resultText		= 'Successful new logon session creation';
						}
					$this->securityActionAttempts	= array();
					}
				}
			if (! $success)
				{
				/*
				 * Track invalid login attempts
				 */
				$attempt						= array();
				$attempt['Type']			= 'login';
				$attempt['ID']				= $loginID;
				$attempt['PW']				= $loginPswd;
				$this->securityActionAttempts[]	= $attempt;
				$this->resultText		= 'Login denied!';
				}
			}
		else
	 		{
			if (!empty($this->userIdent))
				{
				$this->resultText		= 'Already logged on!';
				}
			else
			if (count($this->securityActionAttempts) >= $this->securityErrorThreshold)
				{
				$this->resultText		= 'Too many login attempts!';
				}
			else
				{
				$this->resultText		= 'Login denied[2]!';
				}
	 		}

		return($success);
		}

	/***************************************************************************
	 * logout
	 *******/
	function logout()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if ($this->isLoggedIn())
			{
			$this->priorUserIdent			= $this->userIdent;
			$this->userIdent					= '';
			$this->securityActionAttempts	= array();
			$this->resultText					= 'Successful logout';
			}

		return;
		}

	/***************************************************************************
	 * register
	 *******/
	function register($loginID, $loginPswd)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$success = false;
		if (($this->validEmailStructure($loginID))
		 && ($this->validPassphraseStructure($loginPswd)))
			{
			$result = $this->getUserAccessRecord($loginID);
			if ((! $result)
			 || (count($result) == 0))
				{
				$userDef = array();
				$userDef['userLoginName']	= $loginID;
				$userDef['userPassword']	= sha1($loginPswd);
				$userDef['userIdent']		= $this->anchor->create_UUID();
				if ($this->putUserAccessRecord($userDef))
					{
					$success = true;
					$this->resultText = "Successful user registration.";
					}
				else
					{
					$this->resultText = "Problem posting user registration to database. Sorry! ";
					}
				}
			else
			if (($result)
			 && (count($result) == 1))
				{
				$this->resultText = "This ID is already registered! ";
				$this->resultText .= "Please use the forgotten password feature to re-activate";
				$this->resultText .= " an ID for which you have forgotten the password.";
				$this->resultText .= " If you made an error please try again.";
				$this->resultText .= " If you think that someone else has registered with your email";
				$this->resultText .= " address, please contact the system administrator.";
				}
			else
				{
				$this->resultText = "Problem validating your selected user ID. Sorry! ";
				}
			}

		return($success);
		}

	/***************************************************************************
	 * changePassphrase
	 *******/
	function changePassphrase($loginID, $oldPswd, $newPswd)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$success 			= false;
		$securityThreat 	= false;
		if (count($this->securityActionAttempts) < $this->securityErrorThreshold)
			{
			$result = $this->getUserAccessRecord($loginID);
			if (($result)						// must be a match
			 && (count($result) == 1))
				{
				$userDef = $result[0];
				$userPassphrase		= $userDef['userPassword'];
				if ($userPassphrase == sha1($oldPswd))
			 		{
					if ($this->validPassphraseStructure($newPswd))
						{
						$userDef['userPassword']	= sha1($newPswd);
						if ($this->updateUserAccessRecord($userDef))
							{
							$success = true;
							$this->resultText = "Successful passphrase change.";
							}
						else
							{
							$this->resultText = "Problem posting passphrase change to database. Sorry! ";
							return($success);
							}
						}
					else
						{
							return($success);
						// Error message already posted?
						}
					}
				else
					{
					/*
					 * Invalid old PW is a potential security crack attempt
					 */
					$securityThreat 	= true;
					}
				}
			else
				{
				/*
				 * Invalid user ID is a potential security crack attempt
				 */
				$securityThreat 	= true;
				}
			if (! $success)
				{
				/*
				 * Track invalid login attempts that are potential security threats
				 */
				if ($securityThreat)
					{
					$attempt						= array();
					$attempt['Type']			= 'PW change';
					$attempt['ID']				= $loginID;
					$attempt['PW']				= $oldPswd;
					$this->securityActionAttempts[]	= $attempt;
					}
				$this->resultText		= 'Change denied!';
				}
			}
		else
	 		{
			if (count($this->securityActionAttempts) >= $this->securityErrorThreshold)
				{
				$this->resultText		= 'Too many security action attempts!';
				}
			else
				{
				$this->resultText		= 'Change denied[2]!';
				}
			/*
			 * If needed, force a logout - keeping the security error history
			 */
			if (! empty($this->userIdent))
				{
				$this->priorUserIdent			= $this->userIdent;
				$this->userIdent					= '';
				}
	 		}

		return($success);
		}

	/***************************************************************************
	 * changeEmail
	 *******/
	function changeEmail($loginPswd, $oldEmail, $newEmail)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$success = false;
	  	if (array_key_exists($varName, $this->varPool))
	  		{
	  		$varValue = $this->varPool[$varName];
	  		}

		return($success);
		}

	/***************************************************************************
	 * validEmailStructure
	 *******/
	function validEmailStructure($loginEmail)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$success = false;
//		if (strlen($loginEmail) < 3)
//			{
//			$this->resultText = 'Email address must be at least 3 characters';
//			}
//		else
		if (! (ereg('^[a-zA-Z0-9 \._\-]+@([a-zA-Z0-9][a-zA-Z0-9\-]*\.)+[a-zA-Z]+$', $loginEmail)))
			{
			$this->resultText = 'Email address has invalid structure';
			}
		else
			{
			$success = true;
			}
		return($success);
		}

	/***************************************************************************
	 * validPassphraseStructure
	 *
	 * minumum of 7 characters
	 * 3 out of 4 "at least one" rules must be satisfied.
	 *		At least one capital letter
	 * 	at least one lowercase letter
	 * 	at least one number
	 * 	at least one special symbol
	 * No blanks (nix, blanks are probably good!)
	 *******/
	function validPassphraseStructure($loginPswd)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$success = false;
		if (strlen($loginPswd) < 7)
			{
			$this->resultText = 'Pass-phrases must be at least 7 characters. Long pass-phrases are desired. You may use up to 250 character strings to create your security phrase.';
			}
		else
			{
			/*
			 */
			$tests = 0;
			if (! (ereg('[a-z]', $loginPswd)))
				{
				$this->resultText = 'Pass-phrases must contain lower case letters';
				}
			else
				{
				$tests++;
				}
			if (! (ereg('[A-Z]', $loginPswd)))
				{
				$this->resultText = 'Pass-phrases must contain upper case letters';
				}
			else
				{
				$tests++;
				}
			if (! (ereg('[0-9]', $loginPswd)))
				{
				$this->resultText = 'Pass-phrases must contain numbers';
				}
			else
				{
				$tests++;
				}
			$specials = '~!@#$%^&*_-+=:;,.?';
			$eregCount = ereg('[~!@#\$%\^&\*_\-\+=:;,\.\?]+', $loginPswd);
			if (! $eregCount)
				{
				$this->resultText = 'Pass-phrases must contain one or more of: "'.$specials.'"';
				}
			else
				{
				$tests++;
				}
			if ($tests < 4)
				{
				}
			else
				{
				$success = true;
				}
			}
		return($success);
		}

	/***************************************************************************
	 * getRandomPassphrase
	 *
	 * Generates a random passphrase that meets our edit criteria. Not all of
	 * our allowed special characters are used. The ones that are likely to have
	 * issues on certain systems, or that are harder to recognize and re-type
	 * are not used for the generated pass phrases.
	 *******/
	function getRandomPassphrase()
		{
		$specials = '!@#$%&*_-+=?';
		$specialLimit = strlen($specials) - 1;
		srand ((double) microtime() * 1000000);
		$myNumber = rand(1, 999);
		$myPunct = rand(0, $specialLimit);
		$list = $this->getRandomWords(2);
		$list[] = $myNumber;
		$list[] = $specials[$myPunct];

		$randString	= '';
		shuffle($list);
		foreach ($list as $entry)
			{
			$randString	.= ucfirst($entry);
			}

		return($randString);
		}

	/***************************************************************************
	 * getRandomWords
	 *
	 * Adapted from PHP andMySQL Web Development by Luke Welling and
	 * Laura Thomson, 3rd Edition, Copyright 2005 by Sam's Publishing
	 *
	 * Retrieves a specified number of random words from the word dictionary
	 *******/
	function getRandomWords($wordCount, $min=3, $max=7)
		{
		$words = array();

		/*
		 * Open the dictionary and determine it's size
		 */
		$dictionary = AF_ROOT_DIR.'/data/words.txt';
		$errFlag = $this->anchor->getSuppressErrorMsgs();
		$this->anchor->setSuppressErrMsgs(true);
		$wordFile	= @fopen($dictionary, 'r');
		$this->anchor->setSuppressErrMsgs($errFlag);
		if (! $wordFile)
			{
			return($words);
			}

		$fileSize	= filesize($dictionary);

		/*
		 * Get the specified number of words
		 */
		for ($i = 0; $i < $wordCount; $i++)
			{
			/*
			 * Position to a random point
			 */
			srand ((double) microtime() * 1000000);
			$location	= rand(0, $fileSize);
			fseek($wordFile, $location);
			$word = fgets($wordFile, 80); // bypass first probably 'partial' word

			/*
			 * Get the next word that satisfies our criteria
			 */
			$found	= false;
			while (! $found)
				{
				$word = fgets($wordFile, 80);
				if (feof($wordFile))
					{
					fseek($wordFile, 0);
					}
				else
					{
					$word = trim($word);
					$wordSize = strlen($word);
					$badPunct = (strpos($word, "'") !== false);

					if (($wordSize >= $min)
					 && ($wordSize <= $max)
					 && (! $badPunct))
						{
						$found = true;
						}
					}
				}
			$words[] = $word;
			}

		fclose($wordFile);
		return($words);
		}

	/***************************************************************************
	 * getUserAccessRecord
	 *******/
	function getUserAccessRecord($loginID)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$userLoginName = strtolower($loginID);
		$query = 'select * from user '
					." where userLoginName='$userLoginName'";
		$result = $this->anchor->db->query1($query);
		return($result);
		}

	/***************************************************************************
	 * putUserAccessRecord
	 *******/
	function putUserAccessRecord($userDef)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Extra cautious?
		 */
		$userLoginName = strtolower($userDef['userLoginName']);
		$userLoginName = $this->anchor->prepTextForSql($userLoginName);
		$query = 'insert into user '
					."	set	userLoginName	= '".$userLoginName."',"
					."			userPassword	= '".$userDef['userPassword']."',"
					."			userIdent		= '".$userDef['userIdent']."'";
		$result = $this->anchor->db->query2($query);
		$success = $this->anchor->db->successful();
		return($success);
		}

	/***************************************************************************
	 * updateUserAccessRecord
	 *******/
	function updateUserAccessRecord($userDef)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Extra cautious?
		 */
		$userLoginName = strtolower($userDef['userLoginName']);
		$userLoginName = $this->anchor->prepTextForSql($userLoginName);
		$query = 'update user '
					."	set	userPassword	= '".$userDef['userPassword']."',"
					."			userIdent		= '".$userDef['userIdent']."'"
					."	where	userLoginName	= '".$userLoginName."'";
		$result = $this->anchor->db->query2($query);
		$success = $this->anchor->db->successful();
		return($success);
		}

	} // end of class

?>