<?php
/*
 * C_AF_AirRepository script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-06 JVS Modelled after AirRepository (Java implementation)
 *
 * Provides a collection model abstraction of an AIR repository knowledgebase.
 * The AirRepository class provides an object wrapper over an AIR database.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirRepository';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

class C_AF_AirRepository extends C_AirObjectBase {
	public $airDB						= NULL;		// The AIR database access object
	private $localKR						= array();	// Array of air databases
	private $tables					= array();	// Array of table specs
	private $elementDocumentCache	= array();	// array format of cached reference document objects, keyed by element ID
	private $elementModelCache		= array();	// array format of cached element models (C_AF_AirElementModel), keyed by element ID
	private $elementRuleCache		= array();	// array format of cached element rules (attribute specs) for AIR elements, keyed by rule ID
	private $elementRuleListCache	= array();	// array format of cached element collections defining relationship rules for AIR elements, keyed by element ID for which the rule set applies
	private $elementTypeCache		= array();	// array format of cached element collections defining elements by type, keyed by element type
	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing. (Be careful with code here
	 * to ensure that we are really talking to the right object.)
	 *******/
	function __construct( $anchor )
	{
		// Propogate the construction process
		parent::__construct( $anchor );

		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		// Initialize
		/*
		 * Create default NULL reference document in cache
		 */
		$this->elementDocumentCache[AIR_Null_Identifier] = NULL;
		
	   $this->tables[C_AF_AirDatabase::AIR_ELEMENTS]		= 'Elements';
   	$this->tables[C_AF_AirDatabase::AIR_INDEX]			= 'Element Index';
	   $this->tables[C_AF_AirDatabase::AIR_PROPERTIES]		= 'Properties';
	  	$this->tables[C_AF_AirDatabase::AIR_ASSOCIATIONS]	= 'Associations';
  		$this->tables[C_AF_AirDatabase::AIR_RELATIONSHIPS]	= 'Relationships';
	   $this->tables[C_AF_AirDatabase::AIR_RULES]			= 'Relationship Rules';
   	$this->tables[C_AF_AirDatabase::AIR_USERS]			= 'Users';
	$AF_CONSTANTS['C_AF_AirRepository']   = 		$this->tables;
	}

	/***************************************************************************
	 * initialize
	 *******/
	function initialize()
 	{
		$this->airDB 			= new C_AF_AirDatabase( $this );
		$this->localKR['airDB'] = $this->airDB;                         // AIR KR Content
		$this->localKR['afSessions'] = new C_AF_AirDatabase( $this );		// Active Sessions
		$this->localKR['afTxns'] = new C_AF_AirDatabase( $this );				// Active Transactions
		$this->localKR['afChannels'] = new C_AF_AirDatabase( $this );				// Active Message Channels
		$this->localKR['afRepos'] = new C_AF_AirDatabase( $this );				// Active Repositories
	}
	
	function not_used() {
//		$this->initializeRepositoryElement(AIR_EleType_Null,					'AIR_EleType_Null',					AIR_EleType_EleType);
//		$this->initializeRepositoryElement(AIR_EleType_PropClass,			'AIR_EleType_PropClass',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_PropType,				'Model Property Type',				AIR_EleType_EleType);
//		$this->initializeRepositoryElement(AIR_EleType_InversePropType,	'AIR_EleType_InversePropType',	AIR_EleType_EleType);
//		$this->initializeRepositoryElement(AIR_EleType_AssocClass,			'AIR_EleType_AssocClass',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_AssocType,			'Model Association Type',			AIR_EleType_EleType);
//		$this->initializeRepositoryElement(AIR_EleType_InverseAssocType,	'AIR_EleType_InverseAssocType',	AIR_EleType_EleType);
//		$this->initializeRepositoryElement(AIR_EleType_CoordType,			'AIR_EleType_CoordType',			AIR_EleType_EleType);

		$this->initializeRepositoryElement(AIR_EleType_EleManifest,			'AIR_EleType_EleManifest',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_ArchMessage,			'AIR_EleType_ArchMessage',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_Association,			'Business Element Association',	AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_AssocRule,			'Metamodel Association Rule',		AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_AutomationClass,	'AIR_EleType_AutomationClass',	AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_BehaviorModel,		'AIR_EleType_BehaviorModel',		AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_CardinalitySpec,	'AIR_EleType_CardinalitySpec',	AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_Company,				'AIR_EleType_Company',				AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_ConceptLvl,			'AIR_EleType_ConceptLvl',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_ContentType,			'AIR_EleType_ContentType',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_Context,				'AIR_EleType_Context',				AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_Diagnostic,			'AIR_EleType_Diagnostic',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_EleClass,				'AIR_EleType_EleClass',				AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_EleType,				'AIR_EleType_EleType',				AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_CoordModel,			'AIR_EleType_CoordModel',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_CoordRule,			'AIR_EleType_CoordRule',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_DebugOptGroup,		'AIR Admin Debug Option Group',	AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_DebugOptItem,		'AIR Admin Debug Option Item',	AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_InetDomain,			'AIR_EleType_InetDomain',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_InetHost,				'AIR_EleType_InetHost',				AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_LinkContent,			'AIR_EleType_LinkContent',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_OrdinalitySpec,		'AIR_EleType_OrdinalitySpec',		AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_Person	,				'AIR_EleType_Person',				AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_ProcMode,				'AIR_EleType_ProcMode',				AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_ProcOptItem,			'AIR Processing Option',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_Property,				'Business Element External Property',	AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_CaptureType,			'AIR_EleType_CaptureType',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_PropRule,				'Metamodel Property Rule',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_RelClass,				'AIR_EleType_RelClass',				AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_RelType,				'Metamodel Relationship Type',	AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_StructContent,		'AIR_EleType_StructContent',		AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_System,				'AIR_EleType_System',				AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_TextContent,			'AIR_EleType_TextContent',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_WebSession,			'AIR_EleType_WebSession',			AIR_EleType_EleType);
		$this->initializeRepositoryElement(AIR_EleType_ZfDimension,			'AIR_EleType_ZfDimension',			AIR_EleType_EleType);

//			$this->initializeRepositoryElement($eleUUID,							$eleName,							$eleType)

		$this->initializeRepositoryElement(AIR_DfltDebugOptGroup,			'Default Admin Debug Options',	AIR_EleType_DebugOptGroup);
		$this->initializeRepositoryElement(AIR_DebugOpt_GenTrace,			'General Processing Trace',		AIR_EleType_DebugOptItem);
		$this->initializeRepositoryElement(AIR_DebugOpt_ErrDiag,				'Error Diagnostic Logging',		AIR_EleType_DebugOptItem);
		$this->initializeRepositoryElement(AIR_DebugOpt_HttpTrace,			'HTTP Processing Trace',			AIR_EleType_DebugOptItem);
		$this->initializeRepositoryElement(AIR_DebugOpt_MsgDispatch,		'Message Dispatch Logging',		AIR_EleType_DebugOptItem);
		$this->initializeRepositoryElement(AIR_DebugOpt_MsgDataFlow,		'Message Data Flow Trace',			AIR_EleType_DebugOptItem);
		$this->initializeRepositoryElement(AIR_DebugOpt_EleInsert,			'Element Insert Logging',			AIR_EleType_DebugOptItem);
		$this->initializeRepositoryElement(AIR_DebugOpt_CoreFcnTrace,		'Core Functions Debugging',		AIR_EleType_DebugOptItem);

		$this->initializeRepositoryElement(AIR_ProcOpt_NmbrLists,			'Use Numbered Lists',				AIR_EleType_ProcOptItem);
		$this->initializeRepositoryElement(AIR_ProcOpt_ShowEleKeys,			'Show Element UUID Keys',			AIR_EleType_ProcOptItem);

		$this->initializeRepositoryElement(AIR_PropType_Abstract,			'Abstract',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_AltLabel,			'Alternate Label',					AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Calculations,		'Calculations',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_ChangeNote,			'Change Note',							AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Comments,			'Comments',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Constraints,		'Constraints',							AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Definition,			'Definition',							AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Description,		'Description',							AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_EditorialNote,		'Editorial Note',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Edits,				'Edits',									AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Example,				'Example',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Format,				'Format',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Frequency,			'Frequency',							AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_HiddenLabel,		'Hidden Label',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_HistoryNote,		'History Note',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_ImpactAssess,		'Impact Assessment',					AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Issues,				'Issues',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Media,				'Media',									AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Name,					'Element Name',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Objective,			'Objective',							AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_PrefLabel,			'Preferred Label',					AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_PrivateNote,		'Private Note',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_PublicNote,			'Public Note',							AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Purpose,				'Purpose',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Ranking,				'Ranking',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Retention,			'Retention',							AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_ScopeNote,			'Scope',									AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Script,				'Script',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Security,			'Security',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_ShortName,			'Short Name',							AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Strategy,			'Strategy',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Summary,				'Summary',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Verification,		'Verification',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_Volume,				'Volume',								AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_WhenAccessed,		'When Accessed',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_WhenCreated,		'When Created',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_WhenDeleted,		'When Deleted',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_WhenModified,		'When Modified',						AIR_EleType_PropType);

		$this->initializeRepositoryElement(AIR_PropType_MD_AssocType,		'AIR MD Prop: Assoc Type',					AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_AutoType,		'AIR MD Prop: Automation Type',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_BehaveType,		'AIR MD Prop: Behavior Type',				AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_CaptureMethod,	'AIR MD Prop: Capture Method',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_ConceptLvl,		'AIR MD Prop: Concept Level',				AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_ContentFmt,		'AIR MD Prop: Content Format',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_CoordType,		'AIR MD Prop: Coord Type',					AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_DefaultValue,	'AIR MD Prop: Default Value',				AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_DisplayLbl,		'AIR MD Prop: Display Label',				AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_PresSortKey,	'AIR MD Prop: Presentation Sort Key',	AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_EditMod,			'AIR MD Prop: Edit Module',				AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_EleClass,		'AIR MD Prop: Element Class',				AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_EleParentClass,'AIR MD Prop: Element Parent Class',	AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_ExtItem,			'AIR MD Prop: Externalized Item Flag',	AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_FldName,			'AIR MD Prop: Field Name',					AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_HasInverse,		'AIR MD Prop: Has Inverse Flag',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_IObj,				'AIR MD Prop: Indirect Object',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_IAssocType,		'AIR MD Prop: Inverse Assoc Type',		AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_IObjType,		'AIR MD Prop: Indirect Object Type',	AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_Manual,			'AIR MD Prop: Manual Input Data',		AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_O2IAssocType,	'AIR MD Prop: O2I Assoc Type',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_Obj,				'AIR MD Prop: Object',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_ObjType,			'AIR MD Prop: Object Type',				AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_PredCard,		'AIR MD Prop: Predicate Cardinality',	AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_PredOrd,			'AIR MD Prop: Predicate Ordinality',	AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_PredMax,			'AIR MD Prop: Predicate Maximum',		AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_PredType,		'AIR MD Prop: Predicate Type',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_PropType,		'AIR MD Prop: Prop Type',					AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_RelClass,		'AIR MD Prop: Relationship Class',		AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_RelParentClass,'AIR MD Prop: Rel Parent Class',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_RuleDiag,		'AIR MD Prop: Rule Diagnostic',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_S2IAssocType,	'AIR MD Prop: S2I Assoc Type',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_S2OAssocType,	'AIR MD Prop: S2O Assoc Type',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_SelectField,	'AIR MD Prop: Selection Type Field',	AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_SelectType,		'AIR MD Prop: Selection Type',			AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_Subj,				'AIR MD Prop: Subject',						AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_SubjList,		'AIR MD Prop: Subject Type List',		AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_SubjType,		'AIR MD Prop: Subject Type',				AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_Visible,			'AIR MD Prop: Visible Flag',				AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_ZfDim,			'AIR MD Prop: Zachman Dimension',		AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_ZfObjectList,	'AIR MD Prop: Zachman Object List',		AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_MD_ZfSubjectList,	'AIR MD Prop: Zachman Subject List',	AIR_EleType_PropType);

		$this->initializeRepositoryElement(AIR_PropType_DebugOptions,		'AIR Debug Options',					AIR_EleType_PropType);
		$this->initializeRepositoryElement(AIR_PropType_ProcOptions,		'AIR Processing Options',			AIR_EleType_PropType);

		$this->initializeRepositoryElement(AIR_AssocType_Uses,				'Assoc Type: Uses',					AIR_EleType_AssocType);
		$this->initializeRepositoryElement(AIR_AssocType_UsedBy,				'Assoc Type: Used By',				AIR_EleType_AssocType);
		$this->initializeRepositoryElement(AIR_AssocType_HasA,				'Assoc Type: Has Property',		AIR_EleType_AssocType);
		$this->initializeRepositoryElement(AIR_AssocType_PropertyOf,		'Assoc Type: Property Of',			AIR_EleType_AssocType);

		$this->initializeRepositoryElement(AIR_EleClass_AirMetadata,		'AIR_EleClass_AirMetadata',		AIR_EleType_EleClass);
		$this->initializeRepositoryElement(AIR_EleType_ContentBlock,		'AIR_EleType_ContentBlock',		AIR_EleType_EleClass);

		$this->initializeRepositoryElement(AIR_UUID_JVS,						'Van Steen, Joe',						AIR_EleType_Person);
		$this->initializeRepositoryElement(AIR_UUID_AF,							'Architected Futures, LLC',		AIR_EleType_Company);
		$this->initializeRepositoryElement(AIR_Context_Global,				'AIR_Context_Global',				AIR_EleType_Context);

		$this->initializeRepositoryElement(AIR_UUID_Air,						'AIR Software System',				AIR_EleType_System);

//		$this->initializeRepositoryElement($this->lclSystemUUID,				$_SERVER['SERVER_ADDR'],			AIR_EleType_InetHost);

		$this->initializeRepositoryElement(AIR_ProcMode_Production,			'AIR_ProcMode_Production',			AIR_EleType_ProcMode);
		$this->initializeRepositoryElement(AIR_ProcMode_Development,		'AIR_ProcMode_Development',		AIR_EleType_ProcMode);
		$this->initializeRepositoryElement(AIR_ProcMode_SandBox,				'AIR_ProcMode_SandBox',				AIR_EleType_ProcMode);

		$this->initializeRepositoryElement(AIR_EleIsRequired,					'AIR_EleIsRequired',					AIR_EleType_OrdinalitySpec);
		$this->initializeRepositoryElement(AIR_EleIsDesired,					'AIR_EleIsDesired',					AIR_EleType_OrdinalitySpec);
		$this->initializeRepositoryElement(AIR_EleIsOptional,					'AIR_EleIsOptional',					AIR_EleType_OrdinalitySpec);
		$this->initializeRepositoryElement(AIR_EleIsNotPreferred,			'AIR_EleIsNotPreferred',			AIR_EleType_OrdinalitySpec);
		$this->initializeRepositoryElement(AIR_EleIsNotAllowed,				'AIR_EleIsNotAllowed',				AIR_EleType_OrdinalitySpec);

		$this->initializeRepositoryElement(AIR_RelIsNotAllowed,				'AIR_RelIsNotAllowed',				AIR_EleType_CardinalitySpec);
		$this->initializeRepositoryElement(AIR_RelIsUnique,					'AIR_RelIsUnique',					AIR_EleType_CardinalitySpec);
		$this->initializeRepositoryElement(AIR_RelIsCollection,				'AIR_RelIsCollection',				AIR_EleType_CardinalitySpec);

		$this->initializeRepositoryElement(AIR_EleType_ConceptConceptual,	'AIR_EleType_ConceptConceptual',	AIR_EleType_ConceptLvl);
		$this->initializeRepositoryElement(AIR_EleType_ConceptLogical,		'AIR_EleType_ConceptLogical',		AIR_EleType_ConceptLvl);
		$this->initializeRepositoryElement(AIR_EleType_ConceptPhysical,	'AIR_EleType_ConceptPhysical',	AIR_EleType_ConceptLvl);

		$this->initializeRepositoryElement(AIR_EleType_SysClassConcept,	'AIR_EleType_SysClassConcept',	AIR_EleType_AutomationClass);
		$this->initializeRepositoryElement(AIR_EleType_SysClassManual,		'AIR_EleType_SysClassManual',		AIR_EleType_AutomationClass);
		$this->initializeRepositoryElement(AIR_EleType_SysClassAutomated,	'AIR_EleType_SysClassAutomated',	AIR_EleType_AutomationClass);
		$this->initializeRepositoryElement(AIR_EleType_SysClassMan2Auto,	'AIR_EleType_SysClassMan2Auto',	AIR_EleType_AutomationClass);
		$this->initializeRepositoryElement(AIR_EleType_SysClassAuto2Man,	'AIR_EleType_SysClassAuto2Man',	AIR_EleType_AutomationClass);
		$this->initializeRepositoryElement(AIR_EleType_SysClassBiDirect,	'AIR_EleType_SysClassBiDirect',	AIR_EleType_AutomationClass);

//		$this->initializeRepositoryElement(AIR_CaptureType_Integer,			'AIR_CaptureType_Integer',			AIR_EleType_CaptureType);
//		$this->initializeRepositoryElement(AIR_CaptureType_IntTextBlock,	'AIR_CaptureType_IntTextBlock',	AIR_EleType_CaptureType);
//		$this->initializeRepositoryElement(AIR_CaptureType_IntHyperlink,	'AIR_CaptureType_IntHyperlink',	AIR_EleType_CaptureType);
//		$this->initializeRepositoryElement(AIR_CaptureType_StructSpec,		'AIR_CaptureType_StructSpec',		AIR_EleType_CaptureType);

		$this->initializeRepositoryElement(AIR_CaptureType_KeyEntry,		'AIR_CaptureType_KeyEntry',		AIR_EleType_CaptureType);
		$this->initializeRepositoryElement(AIR_CaptureType_KeyOrSel,		'AIR_CaptureType_KeyOrSel',		AIR_EleType_CaptureType);
		$this->initializeRepositoryElement(AIR_CaptureType_EleSelect,		'AIR_CaptureType_EleSelect',		AIR_EleType_CaptureType);
		$this->initializeRepositoryElement(AIR_CaptureType_EleSpec,			'AIR_CaptureType_EleSpec',			AIR_EleType_CaptureType);
		$this->initializeRepositoryElement(AIR_CaptureType_EleRef,			'AIR_CaptureType_EleRef',			AIR_EleType_CaptureType);
		$this->initializeRepositoryElement(AIR_CaptureType_RelEleSel,		'AIR_CaptureType_RelEleSel',		AIR_EleType_CaptureType);
		$this->initializeRepositoryElement(AIR_CaptureType_CheckList,		'AIR_CaptureType_CheckList',		AIR_EleType_CaptureType);

		$this->initializeRepositoryElement(AIR_ContentType_OrdSpec,			'Data Type OrdSpec',					AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_IntText,			'Data Type Internal Text',			AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_IntTextBlock,	'Data Type Internal Text Block',	AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_ExtText,			'Data Type External Text',			AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_ExtTextBlock,	'Data Type External Text Block',	AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_IntHyperlink,	'Data Type Internal Hyperlink',	AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_ExtHyperlink,	'Data Type External Hyperlink',	AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_Binary,			'Data Type Binary',					AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_Boolean,			'Data Type Boolean',					AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_UUID,				'Data Type AIR Element UUID',		AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_UUIDList,		'Data Type AIR Element UUID List',	AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_Float,			'Data Type Float',					AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_Integer,			'Data Type Integer',					AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_Date,				'Data Type Date',						AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_Time,				'Data Type Time',						AIR_EleType_ContentType);
		$this->initializeRepositoryElement(AIR_ContentType_Datetime,		'Data Type Datetime',				AIR_EleType_ContentType);

		$this->initializeRepositoryElement(AIR_EleType_ZfThings,				'AIR_EleType_ZfThings',				AIR_EleType_ZfDimension);
		$this->initializeRepositoryElement(AIR_EleType_ZfProcess,			'AIR_EleType_ZfProcess',			AIR_EleType_ZfDimension);
		$this->initializeRepositoryElement(AIR_EleType_ZfPeople,				'AIR_EleType_ZfPeople',				AIR_EleType_ZfDimension);
		$this->initializeRepositoryElement(AIR_EleType_ZfTiming,				'AIR_EleType_ZfTiming',				AIR_EleType_ZfDimension);
		$this->initializeRepositoryElement(AIR_EleType_ZfLocation,			'AIR_EleType_ZfLocation',			AIR_EleType_ZfDimension);
		$this->initializeRepositoryElement(AIR_EleType_ZfMotivation,		'AIR_EleType_ZfMotivation',		AIR_EleType_ZfDimension);

		$this->initializeRepositoryElement(AIR_EleType_BehaveDiscrete,		'AIR_EleType_BehaveDiscrete',		AIR_EleType_BehaviorModel);
		$this->initializeRepositoryElement(AIR_EleType_BehaveContinuous,	'AIR_EleType_BehaveContinuous',	AIR_EleType_BehaviorModel);
		$this->initializeRepositoryElement(AIR_EleType_BehaveMixed,			'AIR_EleType_BehaveMixed',			AIR_EleType_BehaviorModel);

	}

	/***************************************************************************
	 * initializeRepositoryElement()
	 *
	 * This is a support routine for the initializeRepository function above.
	 * It performs the actual initialization function for a single repository element.
	 *******/
	function initializeRepositoryElement($eleUUID, $eleName, $eleType)
		{
		$eleDefDoc	= NULL;

		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . ": $eleName");
			}

		/*
		 * Check to see if the item already exists
		 */
		$exists = $GLOBALS['AF_INSTANCE']->currElementExists($eleUUID);
		if (! $exists)
			{
			if ($GLOBALS['AF_INSTANCE']->debugCoreFcns())
				{
				$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, "$eleName was not found, adding default value");
				}
			/*
			 * Create a new C_AirElement encoding	for the element
			 */
			$eleDefDoc = $GLOBALS['AF_INSTANCE']->createAirElementDoc($eleType, $eleName,
																AIR_UUID_Air,
																"System Initialization",
																$eleUUID);
			/*
			 * Persist the element definition to the database
			 */
	 		$eleDefDoc->persist();
		 	}
 		return($eleDefDoc);
		}

	/***************************************************************************
	 * terminate
	 *******/
	function terminate()
 	{
		$this->airDB->terminate();
		parent::terminate();
	}

	/***************************************************************************
	 * getSize()
	 * Returns the number of elements in the collection.
	 *******/
	function getSize()
	{
		return count($this->collection);
	}

	/***************************************************************************
	 * getElementAt()
	 * Retrieve the collection element at the indicated position.
	 *******/
	function getElementAt($index)
	{
		$result = NULL;

		if ((count($this->collection) > $index)
		 && ($index >= 0))
		{
			$result = $this->collection[$index];
		}

		return $result;
	}

	/***************************************************************************
	 * getElementRef
	 *
	 * Returns a reference to a typed AirDocument for the specified GUID. The
	 * reference is returned to a previously cached item if one exists. If not,
	 * the item is retrieved from the database, cached, and returned.
	 *
	 * Proper use of this method is: $ele = & $xyz->getElementRef();
	 *******/
	public function & getElementRef($eleIdent)
		{
		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (($eleIdent == NULL)
		 || (empty($eleIdent)))
			{
			trigger_error(__FUNCTION__ . " called with empty element id [$eleIdent]" , E_USER_NOTICE);
			return(NULL);
			}

		if (($eleIdent == AIR_Null_Identifier)
		 || ($eleIdent == AIR_Any_Identifier)
		 || ($eleIdent == AIR_All_Identifier))
			{
			trigger_error(__FUNCTION__ . " called with reserved ID [$eleIdent]" , E_USER_NOTICE);
			return(NULL);
			}

		if (! array_key_exists($eleIdent, $this->elementDocumentCache))
			{
			$eleDoc = & $this->getTypedAirDocument($eleIdent, NULL);
			if ((!isset($eleDoc))
			 || (is_null($eleDoc)))
				{
				trigger_error(__FUNCTION__ . " unable to find [$eleIdent] in DB" , E_USER_NOTICE);
				$eleDoc = NULL;
				}

			$this->elementDocumentCache[$eleIdent] = & $eleDoc;
			}

		$refElement = $this->elementDocumentCache[$eleIdent];

		return($refElement);
		}

	/***************************************************************************
	 * getElementModel
	 *
	 * Returns a reference to an element specification model for the specified GUID.
	 * The reference is returned to a previously cached item if one exists. If not,
	 * the item is built dynamically from the respository specifications, cached,
	 * and returned.
	 *
	 * Proper use of this method is: $ele = & $xyz->getElementModel();
	 *******/
	public function & getElementModel($eleIdent)
		{
		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$refElement = NULL;

		if (($eleIdent == NULL)
		 || (empty($eleIdent)))
			{
			$GLOBALS['AF_INSTANCE']->putLogicCheck(__LINE__, __FILE__, 'Called with empty element id');
			return($refElement);
			}

		if (($eleIdent == AIR_Null_Identifier)
		 || ($eleIdent == AIR_Any_Identifier)
		 || ($eleIdent == AIR_All_Identifier))
			{
			// NOTE: Some variant of this may make sense in the future.
			// - Null should return an empty model
			// - All should the model for 'all' elements, which is the top layer
			//   of the hierarchy for the current elements
			// - Any should return a complete model of all specifications, which is
			//   the superset of all specifications in the repository. This is the
			//   'current' specification potential for a new element type without
			//   defining any new attributes in the metadata. It would make the job
			//   of assigning attributes to new types easier if the operator could use
			//   the 'any' spec as a selection list.
			trigger_error(__FUNCTION__ . " called with reserved ID [$eleIdent]" , E_USER_NOTICE);
			return(NULL);
			}

		if (! array_key_exists($eleIdent, $this->elementModelCache))
			{
			$eleModel = new C_AF_AirElementModel($this, $eleIdent);
			if ((!isset($eleModel))
			 || (is_null($eleModel)))
				{
				trigger_error(__FUNCTION__ . " unable to find [$eleIdent] in DB" , E_USER_NOTICE);
				$eleModel = NULL;
				}

			$this->elementModelCache[$eleIdent] = & $eleModel;
			}

		$refElement = $this->elementModelCache[$eleIdent];
		return($refElement);
		}

    /**
     * Retrieves a collection identifying the AirRelRules that target a specific AirElement
     * as the subject of the rule.
     * @param elementID the GUID for the subject element of the resulting rules collection
     * @return an AirElementCollection identifying the rules where the requested AirElement is
     * the subject of the rule.
     */
	public function & getElementRulesList($eleIdent)
	{
		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (($eleIdent == NULL)
		 || (empty($eleIdent)))
			{
			trigger_error(__FUNCTION__ . " called with empty element id [$eleIdent]" , E_USER_NOTICE);
			return(NULL);
			}

		if (($eleIdent == AIR_Null_Identifier)
		 || ($eleIdent == AIR_Any_Identifier))
			{
			// NOTE: Some variant of this may make sense in the future.
			// - Null should return an empty model
			// - Any should return a complete model of all specifications, which is
			//   the superset of all specifications in the repository. This is the
			//   'current' specification potential for a new element type without
			//   defining any new attributes in the metadata. It would make the job
			//   of assigning attributes to new types easier if the operator could use
			//   the 'any' spec as a selection list.
			trigger_error(__FUNCTION__ . " called with invalid reserved ID [$eleIdent]" , E_USER_NOTICE);
			return(NULL);
			}

		if (! array_key_exists($eleIdent, $this->elementRuleListCache))
		{
			$dbData = $this->airDB->get_RelRulesForSubject($eleIdent, 5000);
			if ((!isset($dbData))
			 || (is_null($dbData))
			 || (!is_array($dbData)))
			{
				trigger_error(__FUNCTION__ . " unable to find [$eleIdent] in DB" , E_USER_NOTICE);
				return(NULL);
			}

			$ruleSet = array();
			$rowCount = count($dbData);
			if ($rowCount > 0)
			{
				foreach ($dbData as $dbRow)
				{
					$ruleSet[] 		= $dbRow['Air_Ele_Id'];
				}
			}

			$collection = new C_AF_AirElementCollection($this, $ruleSet);
			$this->elementRuleListCache[$eleIdent] = & $collection;
		}

		$attributeSet = $this->elementRuleListCache[$eleIdent];
		return($attributeSet);
	}

    /**
     * Retrieves a collection identifying the Repository elements of a given type.
     * @param eleType the GUID for the element type to be retrieved
     * @return an AirElementCollection identifying the elements of the specified type.
     */
	public function & getElementTypeCollection($eleType)
	{
		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (($eleType == NULL)
		 || (empty($eleType)))
			{
			trigger_error(__FUNCTION__ . " called with empty element id [$eleType]" , E_USER_NOTICE);
			return(NULL);
			}

		if (($eleType == AIR_Null_Identifier)
		 || ($eleType == AIR_All_Identifier)
		 || ($eleType == AIR_Any_Identifier))
			{
			trigger_error(__FUNCTION__ . " called with invalid reserved ID [$eleType]" , E_USER_NOTICE);
			return(NULL);
			}

		if (! array_key_exists($eleType, $this->elementTypeCache))
		{
			$dbData = $this->airDB->get_EleTypeIndex($eleType, 5000);
			if ((!isset($dbData))
			 || (is_null($dbData))
			 || (!is_array($dbData)))
			{
				trigger_error(__FUNCTION__ . ' unable to find Element Class items in DB index' , E_USER_NOTICE);
				return(NULL);
			}

			$classKeys = array();
			$rowCount = count($dbData);
			if ($rowCount > 0)
			{
				foreach ($dbData as $dbRow)
				{
					$classKeys[] 		= $dbRow['Air_Ele_Id'];
				}
			}

			$collection = new C_AF_AirElementCollection($this, $classKeys);
			$this->elementTypeCache[$eleType] = & $collection;
		}

		$collection = $this->elementTypeCache[$eleType];
		return($collection);
	}

    /**
     * Retrieves an AirRelRule Object given a GUID.
     * @param elementID the GUID for the AirRelRule to be returned
     * @return the AirRelRule object corresponding to the element GUID
     */
   public function getElementRule($eleIdent)
	{
		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (($eleIdent == NULL)
		 || (empty($eleIdent)))
			{
			trigger_error(__FUNCTION__ . " called with empty element id [$eleIdent]" , E_USER_NOTICE);
			return(NULL);
			}

		if (($eleIdent == AIR_Null_Identifier)
		 || ($eleIdent == AIR_All_Identifier)
		 || ($eleIdent == AIR_Any_Identifier))
			{
			trigger_error(__FUNCTION__ . " called with invalid reserved ID [$eleIdent]" , E_USER_NOTICE);
			return(NULL);
			}

		if (! array_key_exists($eleIdent, $this->elementRuleCache))
		{
			$rule = new C_AF_AirRelRule($this->airDB, C_AF_AirDatabase::AIR_RULES, $eleIdent);
			if ((!isset($rule))
			 || (is_null($rule)))
				{
				trigger_error(__FUNCTION__ . " unable to find [$eleIdent] in DB" , E_USER_NOTICE);
				$rule = NULL;
				}

			$this->elementRuleCache[$eleIdent] = & $rule;
		}

		$ruleItem = $this->elementRuleCache[$eleIdent];
		return($ruleItem);
    }

	/***************************************************************************
	 * getClassHierarchy
	 *
	 * retrieve the class hierarchy for the identified association type
	 *******/
	function & getClassHierarchy($associationType, $expandedList = false)
	{
		// Get a collection of the class elements
		$classCollection = $this->getElementTypeCollection(AIR_EleType_EleClass);
		// Create a new hierarchy object
		$hierarchy = new C_AF_AirElementHierarchy($this, $associationType);

		// Scan the collection and add the items to the hierarchy
		$classItem = $classCollection->getFirst();
		while ($classItem != NULL)
		{
			// Create new hierarchy items. Each item self-registers with the hierarchy.
			new C_AF_AirElementHierarchyItem($this, $hierarchy, $classItem->getGuid());
			$classItem = $classCollection->getNext();
		}
		// rescan and update relationships
		$classItem = $classCollection->getFirst();
		while ($classItem != NULL)
		{
			// eleIdent should now be an EleClass element
			$element = $this->getElementRef($classItem->getGuid());
			if ($element == NULL)
			{
				throw new Exception('ElementDoc for Class not found!');
			}
			$eleType = $element->getDocType();
			if ((empty($eleType))
			 || ($eleType != AIR_EleType_EleClass))
			{
				throw new Exception('Class ElementDoc has invalid type!');
			}

			$parent = $element->getElementData('ParentClass');
			if (!(empty($parent))
			 && ($parent != AIR_Null_Identifier)
			 && ($parent != AIR_Any_Identifier)
			 && ($parent != AIR_All_Identifier))
			{
				$parentItem = $hierarchy->getItem($parent);
				if ($parentItem == NULL)
				{
					throw new Exception('Parent class instance not found!');
				}

				$hierarchyItem = $hierarchy->getItem($classItem->getGuid());
				$hierarchyItem->setParent($parentItem);
			}

			$classItem = $classCollection->getNext();
		}

	return $hierarchy;
   }

	/***************************************************************************
	 * getElementName
	 *
	 * retrieve the preferred label for a reference element
	 *******/
	function getElementName($eleIdent, $getShortName = false)
		{
		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (empty($eleIdent))
			{
			trigger_error(__FUNCTION__ . " called with empty element id [$eleIdent]" , E_USER_NOTICE);
			return(NULL);
			}

		switch ($eleIdent)
			{
			case AIR_Null_Identifier:
				$eleName = '- None -';
				break;
			case AIR_Any_Identifier:
				$eleName = '- Any -';
				break;
			case AIR_All_Identifier:
				$eleName = '- All -';
				break;
			default:
				$eleDoc = & $this->getElementRef($eleIdent);
				if (!is_null($eleDoc))
					{
					if ($getShortName)
						{
						$eleName = $eleDoc->getShortName();
						}
					else
						{
						$eleName = $eleDoc->getPreferredName();
						}
					}
				else
					{
					$eleName = 'AIR Element ' . $eleIdent;
					}
				break;
			}

		return($eleName);
		}

	// --------------------------------------------------------
	// getTypedAirDocument()
	//
	// This function retrieves an AIR Element from the database and
	// returns an appropriately typed DOM AirElementDoc.
	// --------------------------------------------------------
	public function & getTypedAirDocument($docIdent, $docType)
		{
		$myResult		= NULL;

		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns)
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __FUNCTION__ . " getting context for ".$docIdent." ");
			}

		if (empty($docIdent))
			{
			trigger_error(__FUNCTION__ . " called with empty element id [$docIdent] type [$docType]" , E_USER_NOTICE);
			return(NULL);
			}

		// Get the database entry
		$dbData = $this->airDB->get_currAirElement($docIdent, 1);
		if ((!isset($dbData))
		 || (is_null($dbData))
		 || (!is_array($dbData)))
			{
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ('Invalid DB query return to ' . __FUNCTION__);
			}

		if (count($dbData) > 0)
			{
			if ($GLOBALS['AF_INSTANCE']->debugCoreFcns)
				{
				$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __FUNCTION__ . " got it! ");
				}
			}
		else
			{
			trigger_error(__FUNCTION__ . " cannot find DB entry for AirDocument [$docIdent] type [$docType]" , E_USER_NOTICE);
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			throw new Exception('Missing DB Item ['.$docIdent.']');
			}

		// examine the rowset
		foreach ($dbData as $dbRow)
			{
			if ($GLOBALS['AF_INSTANCE']->debugCoreFcns)
				{
				// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __FUNCTION__ . " examining row ... ");
				}

			if ($GLOBALS['AF_INSTANCE']->debugCoreFcns)
				{
				foreach ($dbRow as $key => $value)
					{
					// $GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __FUNCTION__ . " parsing[".$key."] ");

					if ($key == 'Air_Ele_EleContent')
						{
						$parser = new C_AirXmlPainter();
						$parser->initialize();
						if (! $parser->parse($value))
							{
							$displayObject = htmlspecialchars($value, ENT_QUOTES);
							}
						else
							{
							$displayObject = $parser->getStream();
							}
						$parser->terminate();
						unset($parser);
						$temp = ' restored DB AirDocument data:('.$key.'= <br/><code>'.$displayObject.'</code><br/>)';
						}
					else
						{
						$temp = ' restored DB AirDocument data:('.$key.'='.$value.')';
						}
//					echo                $temp;
					$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, $temp);
					}
				}

			// extract the AirDocument as a document from the dbRow
			// trigger_error("<here>" , E_USER_NOTICE);
			$myResult = $this->extractDbAirDocument($docIdent, $docType, $dbRow);
			// trigger_error("<here>" , E_USER_NOTICE);

			break;	// s/b the only one here
			}

		// trigger_error("<here>" , E_USER_NOTICE);
		return($myResult);
		}

	/**********************************************************
	 * extractDbAirDocument()
	 *
	 * extracts AIR session data from a persisted session in an
	 * AIR_Elements record. Validates the session content document
	 * and restores the session data to memory.
	 **********************************************************/
	private function & extractDbAirDocument($docId, $docType, &$db_row)
		{
		$myAirDoc	= NULL;

		// trigger_error("<here>" , E_USER_NOTICE);
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns)
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __FUNCTION__ . "() examining row ... ");
			}

		// trigger_error("<here>" , E_USER_NOTICE);
		$db_Ele_Id						= $db_row['Air_Ele_Id'];
		$db_Ele_RowStatus				= $db_row['Air_Ele_RowStatus'];
		$db_Ele_KeyDiscriminator	= $db_row['Air_Ele_KeyDiscriminator'];
		$db_Ele_KeySerial				= $db_row['Air_Ele_KeySerial'];
		$db_Ele_SerialFlag			= $db_row['Air_Ele_SerialFlag'];
		$db_Ele_EleContentSize		= $db_row['Air_Ele_EleContentSize'];
		$db_Ele_EleContent			= $db_row['Air_Ele_EleContent'];
		// trigger_error("<here>" , E_USER_NOTICE);

		if ($db_Ele_Id != $docId)
			{
			trigger_error("Problem with AirDoc ID=$docId" , E_USER_NOTICE);
			echo "database Air_Ele_Id   = " . $db_Ele_Id . "\n";
			echo "extracting AirDoc ID  = " . $docId . "\n";
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid AirDocument[Air_Ele_Id] in __FUNCTION__");
			}
			// trigger_error("<here>" , E_USER_NOTICE);
		if ($db_Ele_RowStatus != AIR_EleState_Current)
			{
			trigger_error("Problem with AirDoc ID=$docId" , E_USER_NOTICE);
			echo "extracting AirDoc ID  = " . $docId . "\n";
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid AirDocument[Air_Ele_RowStatus] in __FUNCTION__");
			}
			// trigger_error("<here>" , E_USER_NOTICE);
		if ($db_Ele_KeyDiscriminator != '0')
			{
			trigger_error("Problem with AirDoc ID=$docId" , E_USER_NOTICE);
			echo "extracting AirDoc ID  = " . $docId . "\n";
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid AirDocument[Air_Ele_KeyDiscriminator] in __FUNCTION__");
			}
			// trigger_error("<here>" , E_USER_NOTICE);
		if ($db_Ele_KeySerial != 0)
			{
			trigger_error("Problem with AirDoc ID=$docId" , E_USER_NOTICE);
			echo "extracting AirDoc ID  = " . $docId . "\n";
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid AirDocument[Air_Ele_KeySerial] in __FUNCTION__");
			}
			// trigger_error("<here>" , E_USER_NOTICE);
		if ($db_Ele_SerialFlag != AIR_EleSerial_Only)
			{
			trigger_error("Problem with AirDoc ID=$docId" , E_USER_NOTICE);
			echo "extracting AirDoc ID  = " . $docId . "\n";
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ("Invalid AirDocument[Air_Ele_SerialFlag] in __FUNCTION__");
			}
			// trigger_error("<here>" , E_USER_NOTICE);
		if ($db_Ele_EleContentSize != strlen($db_Ele_EleContent))
			{
			$actualSize = strlen($db_Ele_EleContent);
			trigger_error("Problem with AirDoc ID=$docId" , E_USER_NOTICE);
			trigger_error("Size mis-match spec=$db_Ele_EleContentSize, actual=$actualSize" , E_USER_NOTICE);
			}

		if(empty($docType))
			{
			/*
			 * Pre-instantiate the document to determine the type
			 */
			$myTempDoc = & $this->instantiateAirDocument($docType);
			if (!$myTempDoc->load($db_Ele_EleContent))
				{
				trigger_error("Problem with AirDoc ID=$docId" , E_USER_NOTICE);
				echo "extracting AirDoc ID  = " . $docId . "\n";
				echo $GLOBALS['AF_INSTANCE']->whereAmI();
				die ('Invalid AirDocument[Air_Ele_EleContent] in '.__FUNCTION__);
				}
			$docType = $myTempDoc->getDocType();
			unset($myTempDoc);
			}
		// Instantiate the correct form of document based on type
		$myAirDoc = & $this->instantiateAirDocument($docType);

//		$currentDir 	= '/temp/';
//		$pathName 		= $currentDir.$docId.'.txt';
//		$this->putFileContent($pathName, $db_Ele_EleContent);

		if (!$myAirDoc->load($db_Ele_EleContent))
			{
			trigger_error('Problem with AirDoc ID='.$docId , E_USER_NOTICE);
			echo 'Anchor['.__LINE__.'] problem extracting AirDoc ID  = '. $docId . "\n";
			echo $GLOBALS['AF_INSTANCE']->whereAmI();
			die ('Invalid AirDocument[Air_Ele_EleContent] in '.__FUNCTION__);
			}

		/*
		 * At this point we have the XML session data re-loaded
		 *
		 * For strong validation we should assure that the values
		 * checked above also match the values inside the XML tree
		 * in the AirHeader.
		 */

		// if a type was specified, verify the type
		if ($docType != NULL)
			{
			$newdocType = $myAirDoc->getDocType();
			if ($newdocType != $docType)
				{
				trigger_error("docType misMatch! pgm=$docType doc=$newdocType" , E_USER_NOTICE);
				}
			}

		return($myAirDoc);
		}

	/**********************************************************
	 * instantiateAirDocument()
	 *
	 * Instantiates an AirDocument according to type.
	 **********************************************************/
	private function & instantiateAirDocument($docType)
		{
		$myAirDoc	= NULL;

		// trigger_error("<here>" , E_USER_NOTICE);
		if ($GLOBALS['AF_INSTANCE']->debugCoreFcns)
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, __FUNCTION__ . " [$docType] executing ... ");
			}

		// Instantiate the correct form of document based on type
		switch ($docType)
			{
			case AIR_EleType_ArchMessage:
				$myAirDoc = new C_AF_AirMessageDoc();
//				trigger_error(__FUNCTION__ . " created C_AF_AirMessageDoc" , E_USER_NOTICE);
				break;
			case AIR_EleType_WebSession:
				$myAirDoc = new C_AF_AirSessionDoc();
//				trigger_error(__FUNCTION__ . " created C_AF_AirSessionDoc" , E_USER_NOTICE);
				break;
			case AIR_EleType_TextContent:
				$myAirDoc = new C_AF_AirEleTextDoc();
//				trigger_error(__FUNCTION__ . " created C_AF_AirEleTextDoc" , E_USER_NOTICE);
				break;
			case AIR_EleType_LinkContent:
				$myAirDoc = new C_AF_AirEleLinkDoc();
//				trigger_error(__FUNCTION__ . " created C_AF_AirEleLinkDoc" , E_USER_NOTICE);
				break;
			case AIR_EleType_StructContent:
				$myAirDoc = new C_AF_AirEleStructDoc();
//				trigger_error(__FUNCTION__ . " created C_AF_AirEleStructDoc" , E_USER_NOTICE);
				break;
			case AIR_EleType_PropRule:
			case AIR_EleType_AssocRule:
				$myAirDoc = new C_AF_AirEleAssocDoc();
//				trigger_error(__FUNCTION__ . " created C_AF_AirEleAssocDoc" , E_USER_NOTICE);
				break;
			case AIR_EleType_CoordRule:
				$myAirDoc = new C_AF_AirEleCoordDoc();
//				trigger_error(__FUNCTION__ . " created C_AF_AirEleCoordDoc" , E_USER_NOTICE);
				break;
			default:
				$myAirDoc = new C_AF_AirElementDoc();
//				trigger_error(__FUNCTION__ . " created C_AF_AirElementDoc" , E_USER_NOTICE);
				break;
			}

		return($myAirDoc);
		}

} // End of class C_AF_AirRepository

 ?>