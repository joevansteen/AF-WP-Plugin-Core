<?php
/*
 * C_AF_AirRelRule script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-07 JVS Modelled after AirRelRule (Java v1.06 implementation)
 *
 * Provides an encapsulation and data mapping for a single row of the AIR
 * relationship rules table. The relationship rules table defines the rules
 * constraints for relationships that elements may have with one another
 * within the repository.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirRelRule';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

class C_AF_AirRelRule extends C_AirObjectBase implements I_AF_AirTableItem
{
	private $database			= NULL;	// The database assiated with this item
	private $table				= NULL;	// The table to which the item is bound
	private $isDatabaseItem	= false; // Flag to indicate if item was retrieved from the database
	private $isDirty			= false; // Flag to indicate if item was modified since last database store or refresh

    /**
     * Defines the AIR GUID for this relationship rule.
     */
    private $eleID = '';
    /**
     * Defines the array of AIR GUIDs of the relationship rule's
     * subject elements. The rule is a constraint for relationships
     * that are defined where these elements are the subject of the
     * relationships.
     */
    private $subject = array();
    /**
     * Defines the AIR GUID of the relationship rule's predicate
     * element. The rule is a constraint for relationships that
     * are defining where this element ID is the predicate of the
     * relationship.
     */
    private $predicate = '';
    /**
     * Defines the whether or not the predicate relationship
     * specified on this rule is mandatory or optional for the
     * subject element. It defines the minimum number of occurences
     * for the predicate relationship relative to the subject element.
     * The minimum is either 0 or 1. If the ordinality is 'optional'
     * then the minimum is 0. If the ordinality is ' mandatory' then
     * the minimum is 1. If a predicate is defined as mandatory,
     * then it is an error if the relationship is not specified
     * or does not exist for a given element. If the ordinality is
     * a 'fuzzy' statement (e.g., should, or should not) then the
     * 'error' is also fuzzy.
     */
    private $predOrdinality = '';
    /**
     * Defines whether or not multiple occurences of the predicate
     * relationship are allowed for the given subject element.
     */
    private $predCardinality = '';
    /**
     * Defines the maximum number of predicate relationships of this
     * type that may exist for this subject element. This value is
     * only meaningful if the cardinality specification indicates
     * that multiple predicates are valid. A zero value as the
     * maximum when multiple relationships are valid is considered to
     * signify no constraint on the maximum predicates.
     */
    private $predMaximum = 0;
    /**
     * Defines the AIR GUID of the relationship rule's object
     * element. The rule is a constraint for relationships that
     * are defined where this element ID is the object of the
     * relationship.
     */
    private $object = '';
    /**
     * Defines the AIR GUID of the relationship rule's indirect
     * object element. The rule is a constraint for relationships that
     * are defined where this element ID is the indirect object of the
     * relationship.
     */
    private $iObject = '';
    /**
     ** Defines the AIR GUID of the diagnostic element for this rule.
     */
    private $diagnostic = '';

	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing.
	 *******/
	function __construct(& $database, $table, $eleID = NULL)
	{
		// Propogate the construction process
		parent::__construct();

		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

	 	if (($database == NULL)
	 	 || (!($database instanceof C_AF_AirDatabase)))
		 	{
		 	throw new Exception('Invalid instantiation');
		 	}

	 	if (($table == NULL)
	 	 || (empty($table)))
		 	{
		 	throw new Exception('Invalid instantiation');
		 	}

		$this->database = $database;
		$this->table = $table;
		$this->eleID = $eleID;

	 	if (($eleID == NULL)
	 	 || (empty($eleID)))
	 	{
		 	throw new Exception('Invalid instantiation');
	 	}
	 	else
	 	{
	 		$this->loadItem();
	 	}
	}

    /**
     * Reports whether the item has been initialized from, or stored to,
     * the database.
     * @return true if the item represents a database entry
     */
    public function isDatabaseItem() {
        return $this->isDatabaseItem;
    }

    /**
     * Reports whether item has been modified since it was retrieved
     * from the database.
     * @return true if item has been modified
     */
    public function isDirty() {
        return $this->isDirty;
    }

    /**
     * Retrieves the internal AIR GUID string for the relationship rule.
     * @return the internal AIR GUID string for the relationship rule
     * @see #AirRelRule
     */
    public function getIdent() {
        return $this->eleID;
    }

    /**
     * Retrieves the collection of AirRelRule subject GUIDs.
     * @return the internal AIR GUID for the relationship rule subject
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * Retrieves the AirRelRule predicate GUID.
     * @return the internal AIR GUID for the relationship rule predicate
     */
    public function getPredicate() {
        return $this->predicate;
    }

    /**
     * Retrieves the internal AIR GUID for the ordinality specification for the rule predicate.
     * This specification defines whether or not the predicate relationship specified on
     * this rule is mandatory or optional for the subject element. It defines the minimum number
     * of occurences for the predicate relationship relative to the subject element. The minimum
     * is either 0 or 1. If the ordinality is 'optional' then the minimum is 0. If the ordinality
     * is ' mandatory' then the minimum is 1. If a predicate is defined as mandatory, then it is
     * an error if the relationship is not specified or does not exist for a given element. If the
     * ordinality is a 'fuzzy' statement (e.g., should, or should not) then the 'error' is also fuzzy.
     * @return the predicate ordinality GUID.
     */
    public function getPredicateOrdinality() {
        return $this->predOrdinality;
    }

    /**
     * Retrieves the internal AIR GUID for the cardinality specification for the rule predicate.
     * This specification defines whether or not multiple occurences of the predicate
     * relationship are allowed for the given subject element.
     * @return the predicate cardinality GUID.
     */
    public function getPredicateCardinality() {
        return $this->predCardinality;
    }

    /**
     * Retrieves the value for the maximum occurences of the rule predicate.
     * This specification defines the maximum number of predicate relationships of this
     * type that may exist for this subject element. This value is only meaningful if the
     * cardinality specification indicates that multiple predicates are valid. A zero value as the
     * maximum when multiple relationships are valid is considered to signify no constraint on the
     * maximum predicates.
     * @return the predicate maximum occurence value.
     */
    public function getPredicateMaxOccurences() {
        return $this->predMaximum;
    }

    /**
     * Retrieves the AirRelRule object GUID.
     * @return the internal AIR GUID for the relationship rule object
     */
    public function getObject() {
        return $this->object;
    }

    /**
     * Retrieves the AirRelRule indirect object GUID.
     * @return the internal AIR GUID for the relationship rule indirect object. May be empty.
     */
    public function getIObject() {
        return $this->iObject;
    }

    /**
     * Retrieves the AirRelRule predicate GUID.
     * @return the internal AIR GUID of the diagnostic element for this rule. May be empty.
     */
    public function getDiagnostic() {
        return $this->diagnostic;
    }

    /**
     * Persist an item to the database. Insert a new item into the database,
     * or update an existing item.
     * @return the result code of the store operation.
     */
    public function storeItem() {
		$retCode = false;
		$count = 0;

		/***
        if (($this->subject.length() > 0)
         && ($this->predicate.length() > 0)
         && ($this->object.length() > 0)) {

            if ($this->isDatabaseItem) {
                sqlQuery = "UPDATE " + $this->tableName + " SET"
                        + " Air_RelRule_Subject = '" + $this->subject + "',"
                        + " Air_RelRule_Predicate = '" + $this->predicate + "',"
                        + " Air_RelRule_PredOrd = '" + $this->predOrdinality + "',"
                        + " Air_RelRule_PredCard = '" + $this->predCardinality + "',"
                        + " Air_RelRule_PredMax = '" + $this->predMaximum + "',"
                        + " Air_RelRule_Object = '" + $this->object + "',"
                        + " Air_RelRule_IObject = '" + $this->iObject + "',"
                        + " Air_RelRule_Diag = '" + $this->diagnostic + "'"
                        + " WHERE "
                        + " Air_Ele_Id = '" + $this->eleID + "'";
            } else {
                sqlQuery = "INSERT INTO " + $this->tableName + " SET"
                        + " Air_Ele_Id = '" + $this->eleID + "',"
                        + " Air_RelRule_Subject = '" + $this->subject + "',"
                        + " Air_RelRule_Predicate = '" + $this->predicate + "',"
                        + " Air_RelRule_PredOrd = '" + $this->predOrdinality + "',"
                        + " Air_RelRule_PredCard = '" + $this->predCardinality + "',"
                        + " Air_RelRule_PredMax = '" + $this->predMaximum + "',"
                        + " Air_RelRule_Object = '" + $this->object + "',"
                        + " Air_RelRule_IObject = '" + $this->iObject + "',"
                        + " Air_RelRule_Diag = '" + $this->diagnostic + "'";
            }

            count = airDB.postUpdate(sqlQuery);
            if (count == 1) {
                $this->isDatabaseItem = true;
                $this->isDirty = false;
                retCode = IAirTableItem.SUCCESS;
            }
        }
        ***/

        return $retCode;
    }

    /**
     * Retrieve an item from the database.
     * @return the result code of the operation.
     */
	public function loadItem()
	{
		$retCode = false;
		$count = 0;
		$dbData = $this->database->get_AirTableItem($this->table, $this->eleID);

		$count = count($dbData);
		if ($count >= 1)
		{
			for ($i = 0; $i < $count; $i++)
			{
		      $dbRow = $dbData[$i];
		      if ($dbRow['Air_Ele_Id'] == $this->eleID)
      		{
 					$this->subject[]			= $dbRow['Air_RelRule_Subject'];
   	  			if ($i == 0)
      			{
	      		   $this->predicate			= $dbRow['Air_RelRule_Predicate'];
			         $this->predOrdinality	= $dbRow['Air_RelRule_PredOrd'];
	      		   $this->predCardinality	= $dbRow['Air_RelRule_PredCard'];
			         $this->predMaximum		= $dbRow['Air_RelRule_PredMax'];
	      		   $this->object				= $dbRow['Air_RelRule_Object'];
			         $this->iObject				= $dbRow['Air_RelRule_IObject'];
	      		   $this->diagnostic			= $dbRow['Air_RelRule_Diag'];

						$retCode = true;
						$this->isDatabaseItem = true;
	      		   $this->isDirty = false;
      			}
		      }
		      else
				{
					$errText = 'Datbase Key ID error';
					$this->isDatabaseItem = false;
					throw new Exception($errText);
				}
			}
		}
		else
		{
			$errText = 'Missing AirRelRule in database';
			$this->isDatabaseItem = false;
			throw new Exception($errText);
		}

   	return $retCode;
	}

    /**
     * Delete an item from the database.
     * @return the result code of the purge operation.
     */
	public function deleteItem()
	{
		$retCode = false;
		$count = 0;

      if ($this->isDatabaseItem)
      {
      	$retCode = $this->database->purge_AirTableItem($this->table, $this->eleID);
         $count	= $this->database->lastOperRows;
      }

      if (($retCode) && ($count == 1))
      {
			$this->isDatabaseItem = false;
         $this->isDirty = false;
      }

      return $retCode;
    }

} // End of class C_AF_AirRelRule

 ?>