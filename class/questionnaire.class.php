<?php

if (!class_exists('TObjetStd'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}


class Questionnaire extends SeedObject
{
	/**
	 * Draft status
	 */
	const STATUS_DRAFT = 0;
	/**
	 * Validated status
	 */
	const STATUS_VALIDATED = 1;
	/**
	 * Closed status
	 */
	const STATUS_CLOSED = 2;
	
	public static $TStatus = array(
			self::STATUS_DRAFT => 'Draft'
			,self::STATUS_VALIDATED => 'Validate'
			,self::STATUS_CLOSED=> 'Closed'
	);
	
	public $table_element = 'quest_questionnaire';
	
	public $element = 'questionnaire';
	
	public $picto = 'questionnaire@questionnaire';
	
	public function __construct($db)
	{
		global $conf,$langs;
		
		$this->db = $db;
		
		$this->fields=array(
				'ref'=>array('type'=>'string','length'=>50,'index'=>true)
				,'title'=>array('type'=>'string')
				,'description'=>array('type'=>'string')
				,'entity'=>array('type'=>'integer','index'=>true)
				,'fk_statut'=>array('type'=>'integer','index'=>true) // date, integer, string, float, array, text
				,'import_key'=>array('type'=>'integer','index'=>true)
				,'type_object_linked'=>array('type'=>'string')
				,'fk_object_linked'=>array('type'=>'integer','index'=>true)
				,'fk_user_author'=>array('type'=>'integer','index'=>true)
		);
		
		$this->TTypeObjectLinked = array(
				'Propal' => $langs->trans('Propal')
				,'Facture' => $langs->trans('Invoice')
				,'Commande' => $langs->trans('Order')
				,'SupplierOrder' => $langs->trans('SuppierOrder')
		);
		
		$this->init();
		
		$this->fk_statut = self::STATUS_DRAFT;
		$this->entity = $conf->entity;
	}

	public function save($addprov=false)
	{
		global $user;
		
		if (!$this->id) $this->fk_user_author = $user->id;
		
		$res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
		
		if ($addprov || !empty($this->is_clone))
		{
			$this->ref = '(PROV'.$this->id.')';
			
			if (!empty($this->is_clone)) $this->fk_statut = self::STATUS_DRAFT;
			
			$wc = $this->withChild;
			$this->withChild = false;
			$res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
			$this->withChild = $wc;
		}
		
		return $res;
	}
	
	public function load($id, $ref=null, $loadChild = true)
	{
		global $db;
		
		$res = parent::fetchCommon($id, $ref);
		
		if ($loadChild) $this->fetchObjectLinked();
		
		return $res;
	}
	
	public function delete()
	{
		global $user;
		
		if(empty($this->questions)) $this->loadQuestions();
		if(!empty($this->questions)) {
			foreach($this->questions as &$question) $question->delete();
		}
		
		if(empty($this->invitations)) $this->loadInvitations();
		if(!empty($this->invitations)) {
			foreach($this->invitations as &$inv) $inv->delete();
		}
		
		parent::deleteCommon($user);
	}
	
	public function setDraft()
	{
		if ($this->fk_statut == self::STATUS_VALIDATED)
		{
			$this->fk_statut = self::STATUS_DRAFT;
			$this->withChild = false;
			
			return self::save();
		}
		
		return 0;
	}
	
	public function setValid()
	{
//		global $user;
		
		$this->ref = $this->getNumero();
		$this->fk_statut = self::STATUS_VALIDATED;
		
		return self::save();
	}
	
	public function getNumero()
	{
		if (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))
		{
			return $this->getNextNumero();
		}
		
		return $this->ref;
	}
	
	private function getNextNumero()
	{
		global $db,$conf;
		
		require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		/*echo '<pre>';
		print_r($conf->global);exit;*/
		if($conf->global->QUESTIONNAIRE_ADDON === 'mod_questionnaire_universal') $mask = $conf->global->QUESTIONNAIRE_UNIVERSAL_MASK;
		else $mask = 'QU{yy}{mm}-{0000}';
		
		$numero = get_next_value($db, $mask, 'quest_questionnaire', 'ref');
		
		return $numero;
	}
	
	public function getNomUrl($withpicto=0, $get_params='')
	{
		global $langs;

        $result='';
        $label = '<u>' . $langs->trans("Showquestionnaire") . '</u>';
        if (! empty($this->ref)) $label.= '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;
        
        $linkclose = '" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
        $link = '<a href="'.dol_buildpath('/questionnaire/card.php', 1).'?id='.$this->id. $get_params .$linkclose;
       
        $linkend='</a>';

        $picto='generic';
		
        if ($withpicto) $result.=($link.img_object($label, $picto, 'class="classfortooltip"').$linkend);
        if ($withpicto && $withpicto != 2) $result.=' ';
		
        $result.=$link.$this->ref.$linkend;
		
        return $result;
	}
	
	public static function getStaticNomUrl($id, $withpicto=0)
	{
		global $db;
		
		$object = new questionnaire($db);
		$object->load($id, '',false);
		
		return $object->getNomUrl($withpicto);
	}
	
	public function getLibStatut($mode=0)
    {
        return self::LibStatut($this->fk_statut, $mode);
    }
	
	public static function LibStatut($status, $mode)
	{
		global $langs, $questionnaire_status_forced_key;
		
		$langs->load('questionnaire@questionnaire');
		
		if ($status==self::STATUS_DRAFT) { $statustrans='statut0'; $keytrans='questionnaireStatusDraft'; $shortkeytrans='Draft'; }
		if ($status==self::STATUS_VALIDATED) { $statustrans='statut1'; $keytrans='questionnaireStatusValidated'; $shortkeytrans='questionnaireStatusValidatedShort'; }
		if ($status==self::STATUS_CLOSED) { $statustrans='statut6'; $keytrans='questionnaireStatusClosed'; $shortkeytrans='Closed'; }

		
		if ($mode == 0) return img_picto($langs->trans($keytrans), $statustrans);
		elseif ($mode == 1) return img_picto($langs->trans($keytrans), $statustrans).' '.$langs->trans($keytrans);
		elseif ($mode == 2) return $langs->trans($keytrans).' '.img_picto($langs->trans($keytrans), $statustrans);
		elseif ($mode == 3) return img_picto($langs->trans($keytrans), $statustrans).' '.$langs->trans($shortkeytrans);
		elseif ($mode == 4) return $langs->trans($shortkeytrans).' '.img_picto($langs->trans($keytrans), $statustrans);
		elseif ($mode == 5)	return '<span class="hideonsmartphone">'.$this->labelstatut_short[$statut].' </span>'.img_picto($this->labelstatut[$statut],$statuttrans);
		// mode 6 used by dol_banner() function
		elseif ($mode == 6)	return '<span class="hideonsmartphone">'.$langs->trans(empty($questionnaire_status_forced_key) ? $keytrans : $questionnaire_status_forced_key).' </span>'.img_picto($langs->trans(empty($questionnaire_status_forced_key) ? $keytrans : $questionnaire_status_forced_key),$statustrans);
	}
	
	function loadQuestions() {
		
		global $db;
		
		dol_include_once('/questionnaire/class/question.class.php');
		$q = new Question($db);
		
		$sql = 'SELECT rowid
				FROM '.MAIN_DB_PREFIX.$q->table_element.'
				WHERE fk_questionnaire = '.$this->id;
		$resql = $db->query($sql);
		if(!empty($resql) && $db->num_rows($resql) > 0) {
			$this->questions = array();
			
			while($res = $db->fetch_object($resql)) {
				$q = new Question($db);
				$q->load($res->rowid);
				$this->questions[] = $q;
			}
			
		} else return 0;
		
		return 1;
		
	}
	
	function loadInvitations() {
		
		global $db;
		
		dol_include_once('/questionnaire/class/invitation.class.php');
		
		$invitation = new Invitation($db);
		
		$sql = 'SELECT rowid
				FROM '.MAIN_DB_PREFIX.$invitation->table_element.'
				WHERE fk_questionnaire = '.$this->id;
		$resql = $db->query($sql);
		if(!empty($resql) && $db->num_rows($resql) > 0) {
			$this->invitations = array();
			
			while($res = $db->fetch_object($resql)) {
				$invitation= new Invitation($db);
				$invitation->load($res->rowid);
				$this->invitations[] = $invitation;
			}
			
		} else return 0;
		
		return 1;
		
	}
	
	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	function initAsSpecimen() {
		$this->id = 0;
		
		$this->entity = '';
		$this->title = '';
		$this->element_type = '';
		$this->fk_statut = '';
		$this->import_key = '';
		$this->fk_user_author = '';
		$this->datec = '';
		$this->fk_user_mod = '';
		$this->tms = '';
	}
	
	/**
	 * Returns the reference to the following non used model letters used depending on the active numbering module
	 * defined into REF_LETTER_ADDON
	 *
	 * @param int $fk_user Id
	 * @param societe $objsoc Object
	 * @return string Reference libre pour la lead
	 */
	function getNextNumRef() {
		global $conf, $langs;
		$langs->load("questionnaire@questionnaire");
		
		$dirmodels = array_merge(array (
				'/'
		), ( array ) $conf->modules_parts['models']);
		
		if (! empty($conf->global->QUESTIONNAIRE_ADDON)) {
			foreach ( $dirmodels as $reldir ) {
				$dir = dol_buildpath($reldir . "core/modules/questionnaire/");
				if (is_dir($dir)) {
					$handle = opendir($dir);
					if (is_resource($handle)) {
						$var = true;
						
						while ( ($file = readdir($handle)) !== false ) {
							if ($file == $conf->global->QUESTIONNAIRE_ADDON. '.php') {
								$file = substr($file, 0, dol_strlen($file) - 4);
								require_once $dir . $file . '.php';
								
								$module = new $file();
								
								// Chargement de la classe de numerotation
								$classname = $conf->global->QUESTIONNAIRE_ADDON;
								
								$obj = new $classname();
								
								$numref = "";
								$numref = $obj->getNextValue();
								
								if ($numref != "") {
									return $numref;
								} else {
									$this->error = $obj->error;
									return "";
								}
							}
						}
					}
				}
			}
		} else {
			$langs->load("errors");
			print $langs->trans("Error") . " " . $langs->trans("ErrorModuleSetupNotComplete");
			return "";
		}
	}
	
}


/*
class questionnaireDet extends TObjetStd
{
	public $table_element = 'questionnairedet';

	public $element = 'questionnairedet';
	
	public function __construct($db)
	{
		global $conf,$langs;
		
		$this->db = $db;
		
		$this->init();
		
		$this->user = null;
	}
	
	
}
*/
