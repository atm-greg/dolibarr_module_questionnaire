<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/questionnaire.lib.php
 *	\ingroup	questionnaire
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function questionnaireAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("questionnaire@questionnaire");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/questionnaire/admin/questionnaire_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/questionnaire/admin/questionnaire_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@questionnaire:/questionnaire/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@questionnaire:/questionnaire/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'questionnaire');

    return $head;
}

/**
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	Questionnaire	$object		Object company shown
 * @return 	array				Array of tabs
 */
function questionnaire_prepare_head(Questionnaire $object)
{
	global $db, $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	$head[$h][0] = dol_buildpath('/questionnaire/card.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("questionnaireCard");
	$head[$h][2] = 'card';
	$h++;
	
	if($object->fk_statut > 0) {
		
		$head[$h][0] = dol_buildpath('/questionnaire/invitation.php', 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("questionnaireInvitationCard");
		$head[$h][2] = 'invitation';
		$h++;
		
		$head[$h][0] = dol_buildpath('/questionnaire/answer.php', 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("questionnaireAnswerCard");
		$head[$h][2] = 'answer';
		$h++;
		
	}
	
	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@questionnaire:/questionnaire/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@questionnaire:/questionnaire/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'questionnaire');
	
	return $head;
}

function getFormConfirmquestionnaire(&$form, &$object, $action)
{
    global $langs,$conf,$user;

    $formconfirm = '';

    if ($action == 'validate'/* && !empty($user->rights->questionnaire->write)*/)
    {
    	$error = 0;
    	
    	// We verifie whether the object is provisionally numbering
    	$ref = substr($object->ref, 1, 4);
    	if ($ref == 'PROV') {
    		$numref = $object->getNextNumRef();
    		
    		if (empty($numref)) {
    			$error ++;
    			setEventMessages($object->error, $object->errors, 'errors');
    		}
    	} else {
    		$numref = $object->ref;
    	}
    	
    	$text = $langs->trans('ConfirmValidateQuestionnaire', $numref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ValidateQuestionnaire'), $text, 'confirm_validate', '', 0, 1);
    }
    elseif ($action == 'delete'/* && !empty($user->rights->questionnaire->write)*/)
    {
        $text = $langs->trans('ConfirmDeleteQuestionnaire');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('DeleteQuestionnaire'), $text, 'confirm_delete', '', 0, 1);
    }
    elseif ($action == 'clone'/* && !empty($user->rights->questionnaire->write)*/)
    {
    	$text = $langs->trans('ConfirmCloneQuestionnaire', $object->ref);
    	$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('CloneQuestionnaire'), $text, 'confirm_clone', '', 0, 1);
    }
    elseif ($action == 'modif'/* && !empty($user->rights->questionnaire->write)*/)
    {
    	$text = $langs->trans('ConfirmModifyQuestionnaire');
    	$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ModifyQuestionnaire'), $text, 'confirm_modif', '', 0, 1);
    }
    elseif ($action == 'validate_answers'/* && !empty($user->rights->questionnaire->write)*/)
    {
    	$text = $langs->trans('ConfirmValidateAnswersQuestionnaire');
    	$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ValidateAnswersQuestionnaire'), $text, 'confirm_validate_answers', array(array('type'=>'hidden', 'name'=>'fk_invitation', 'value'=>GETPOST('fk_invitation')),array('type'=>'hidden', 'name'=>'fk_userinvit', 'value'=>GETPOST('fk_userinvit')),array('type'=>'hidden', 'name'=>'token', 'value'=>GETPOST('token'))), 0, 1);
    }

    return $formconfirm;
}

function draw_question(&$q, $fk_statut_questionnaire=0) {
	
	global $db, $langs, $bg_color;
	
	if(!isset($bg_color)) $bg_color = 0;
	
	$bgcol_questionnaire = array(0=>'rgb(248,248,248)', 1=>'rgb(255,255,255)');
	
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
	dol_include_once('/questionnaire/class/choice.class.php');
	
	$question_est_une_grille = strpos($q->type, 'grille') !== false;
	
	$form = new Form($db);
	
	//$res = '<div style="background-color:'.$bgcol_questionnaire[$bg_color].';" class="element" type="question" id="question'.$q->id.'">';
	$res = '<div class="element" type="question" id="question'.$q->id.'">';
	$res.= '<div class="refid">Question : '.$q->TTypes[$q->type].'<br /></div>';
	if(empty($fk_statut_questionnaire)) $res.= '<input size="100" placeholder="Question" type="text" name="label" class="field" id="label" name="label" value="'.$q->label.'"/>';
	else $res.= '<STRONG>'.$q->label.'</STRONG>&nbsp;';
	if(empty($fk_statut_questionnaire)) {
		$res.= '<input type="checkbox" title="Réponse obligatoire ?" class="field"';
		$res.= ' name="compulsory_answer"';
		$res.= (int)$q->compulsory_answer > 0 ? 'checked="checked"' : '';
		$res.= '/>';
	} elseif(!empty($q->compulsory_answer)) $res.= ' <STRONG>(réponse obligatoire)</STRONG>';
	if(empty($fk_statut_questionnaire)) $res.= '&nbsp;<a id="del_element_'.$q->id.'" name="del_element_'.$q->id.'" href="#" onclick="return false;">'.img_delete($langs->trans('questionnaireDeleteQuestion')).'</a>';
	$res.= '<br /><br />';
	
	// Pas de choix pour les types string et textarea
	
	if($q->type !== 'string' && $q->type !== 'textarea' && $q->type !== 'date' && $q->type !== 'hour' && $q->type !== 'linearscale') {
			// Liste des choix (lignes)
			$style_div_lines = ' width: 300px; ';
			if($question_est_une_grille) $style_div_lines.= ' float: left; ';
			$res.= '<div style="'.$style_div_lines.'" id="allChoicesLeft_q'.$q->id.'" name="allChoicesLeft_q'.$q->id.'">';
			$res.= '<div class="refid">Lignes<br /><br /></div>';
			$q->loadChoices();
			if(!empty($q->choices)) {
				foreach($q->choices as &$choice) {
					if($choice->type === 'line') $res.= draw_choice($choice, $fk_statut_questionnaire);
				}
			}
			if(empty($fk_statut_questionnaire)) $res.= '<button class="butAction" id="butAddChoiceLine_q'.$q->id.'" name="butAddChoiceLine_q'.$q->id.'">Ajouter une ligne</button>';
			$res.= '</div>';
			
			// Liste des choix (colonnes => Uniquement pour les grilles)
			if($question_est_une_grille) {
				$res.= '<div style="float: left;" id="allChoicesRight_q'.$q->id.'">';
				$res.= '<div class="refid">Colonnes<br /><br /></div>';
				if(!empty($q->choices)) {
					foreach($q->choices as &$choice) {
						if($choice->type === 'column') $res.= draw_choice($choice, $fk_statut_questionnaire);
					}
				}
				if(empty($fk_statut_questionnaire)) $res.= '<button class="butAction" id="butAddChoiceColumn_q'.$q->id.'" name="butAddChoiceColumn_q'.$q->id.'">Ajouter un colonne</button>';
				$res.= '</div>';
			}
			
	} elseif($q->type === 'linearscale') {
		
		$res.= '<div style="'.$style_div_lines.'" id="allChoicesLeft_q'.$q->id.'" name="allChoicesLeft_q'.$q->id.'">';
		
		if(empty($q->choices)) $q->loadChoices();
		$res.= draw_choice($q->choices[0], $fk_statut_questionnaire, 'linearscale', 'De');
		$res.= draw_choice($q->choices[1], $fk_statut_questionnaire, 'linearscale', 'à');
		$res.= draw_choice($q->choices[2], $fk_statut_questionnaire, 'linearscale', 'Pas');
		$res.= '</div>';
		
	}
	
	$res.= '<div style="clear: both;"></div><br /><br /></div>';
	
	$bg_color = !$bg_color;
	
	return $res;
	
}

function draw_choice(&$choice, $fk_statut_questionnaire=0, $type='', $title='') {
	
	if(empty($type)) return draw_standard_choice($choice, $fk_statut_questionnaire);
	elseif($type === 'linearscale') return draw_linearscale_choice($choice, $title, $fk_statut_questionnaire);
	
}

function draw_standard_choice(&$choice, $fk_statut_questionnaire=0) {
	
	global $langs;
	
	$res.= '<div class="element" type="choice" id="choice'.$choice->id.'">';
	
	if(empty($fk_statut_questionnaire)) {
		$res.= '<input placeholder="Libellé choix" type="text" name="label" class="field" value="'.$choice->label.'" />&nbsp;';
		$res.= '<a id="del_element_'.$choice->id.'" name="del_element_'.$choice->id.'" href="#" onclick="return false;">'.img_delete($langs->trans('questionnaireDeleteChoice')).'</a>';
	}
	else $res.= $choice->label;
	$res.= '<br /><br /></div>';
	
	return $res;
	
}

function draw_linearscale_choice(&$choice, $title, $fk_statut_questionnaire=0) {
	
	$res.= '<div style="float:left;" class="element" type="choice" id="choice'.$choice->id.'">';
	$res.= $title;
	if(empty($fk_statut_questionnaire)) $res.= '&nbsp;&nbsp;<input type="number" style="width:50px;" name="label" class="field" value="'.$choice->label.'" />&nbsp;&nbsp;';
	else $res.= '&nbsp;&nbsp;<STRONG>'.$choice->label.'</STRONG>&nbsp;&nbsp;';
	$res.= '</div>';
	
	return $res;
	
}

function draw_question_for_user(&$q) {
	
	if(empty($q->choices)) $q->loadChoices();
	if(!empty($q->choices) || $q->type === 'string' || $q->type === 'textarea' || $q->type === 'date' || $q->type === 'hour' || $q->type === 'linearscale'/*Pas de choix pour ces types là*/) {
		$res = '<div class="element" type="question" id="question'.$q->id.'">';
		$res.= '<div class="refid">'.$q->label.(!empty($q->compulsory_answer) ? ' (Réponse obligatoire)' : '').'</div>';
		
		switch($q->type) {
			
			case 'string':
				$res.= draw_string_for_user($q);
				break;
			
			case 'grillestring':
				$res.= draw_grillestring_for_user($q);
				break;
				
			case 'textarea':
				$res.= draw_textarea_for_user($q);
				break;
				
			case 'select':
				$res.= draw_select_for_user($q);
				break;
				
			case 'listradio':
				$res.= draw_listradio_for_user($q);
				break;
				
			case 'grilleradio':
				$res.= draw_grilleradio_for_user($q);
				break;
				
			case 'listcheckbox':
				$res.= draw_listcheckbox_for_user($q);
				break;
				
			case 'grillecheckbox':
				$res.= draw_grillecheckbox_for_user($q);
				break;
				
			case 'date':
				$res.= draw_date_for_user($q);
				break;
			
			case 'hour':
				$res.= draw_hour_for_user($q);
				break;
			
			case 'linearscale':
				$res.= draw_linearscale_for_user($q);
				break;
				
		}
		
		$res.= '</div>';
	}
	return $res;
}

function draw_string_for_user(&$q) {
	
	return '<input type="text" name="TAnswer['.$q->id.']" value="'.$q->answers[0]->value.'" />';
	
}

function draw_textarea_for_user(&$q) {
	
	return '<textarea rows="7" cols="50" type="text" name="TAnswer['.$q->id.']" id="rep_q'.$q->id.'">'.$q->answers[0]->value.'</textarea>';
	
}

function draw_select_for_user(&$q) {
	
	global $form;
	
	$tab = array();
	foreach($q->choices as &$choix) {
		$tab[$choix->id] = $choix->label;
	}
	return $form->selectarray('TAnswer['.$q->id.'][]', $tab, $q->answers[0]->fk_choix);
	
}

function draw_listradio_for_user(&$q) {
	
	$res = '<br />';
	//var_dump($q->choices);exit;
	foreach($q->choices as &$choix) {
		$res.= '<input type="radio" ';
		if(!empty($q->answers)) {
			if($choix->id == $q->answers[0]->fk_choix) {
				$res.= 'checked';
			}
		}
		$res.= ' name="TAnswer['.$q->id.'][]" value="'.$choix->id.'">&nbsp;'.$choix->label.'<br />';
	}
	
	return $res;
	
}

function draw_listcheckbox_for_user(&$q) {
	
	$res = '<br />';
	foreach($q->choices as &$choix) {
		$res.= '<input type="checkbox" ';
		if(!empty($q->answers)) {
			foreach($q->answers as &$answer) {
				if($choix->id == $answer->fk_choix) {
					$res.= 'checked';
					break;
				}
			}
		}
		$res.= ' name="TAnswer['.$q->id.'][]" value="'.$choix->id.'" />&nbsp;'.$choix->label.'<br />';
	}
	
	return $res;
	
}

function draw_grilleradio_for_user(&$q) {
	
	$res = '<br /><table class="noborder"><tr><td></td>';
	foreach($q->choices as &$choix_col) {
		if($choix_col->type === 'column') $res.= '<td>'.$choix_col->label.'</td>';
	}
	$res.= '</tr>';
	
	$first_line=true;
	foreach($q->choices as &$choix_line) {
		
		if($choix_line->type === 'line') $res.= '<tr><td>'.$choix_line->label.'</td>';
		else continue;
		
		foreach($q->choices as &$choix_col) {
			if($choix_col->type === 'column')  {
				$res.= '<td><input type="radio" ';
				if(!empty($q->answers)) {
					foreach($q->answers as &$answer) {
						if($answer->fk_choix == $choix_line->id && $answer->fk_choix_col == $choix_col->id) {
							$res.= 'checked';
							break;
						}
					}
				}
				$res.= ' name="TAnswer['.$q->id.']['.$choix_line->id.']" value="'.$choix_line->id.'_'.$choix_col->id.'"/></td>';
			}
			else continue;
		}
		
		$first_line=false;
		$res.= '</tr>';
		
	}
	$res.= '</table>';
	
	return $res;
	
}

function draw_grillecheckbox_for_user(&$q) {
	
	$res = '<br /><table class="noborder"><tr><td></td>';
	foreach($q->choices as &$choix_col) {
		if($choix_col->type === 'column') $res.= '<td>'.$choix_col->label.'</td>';
	}
	$res.= '</tr>';
	
	$first_line=true;
	foreach($q->choices as &$choix_line) {
		
		if($choix_line->type === 'line') $res.= '<tr><td>'.$choix_line->label.'</td>';
		else continue;
		
		foreach($q->choices as &$choix_col) {
			if($choix_col->type === 'column') {
				$res.= '<td><input type="checkbox" ';
				if(!empty($q->answers)) {
					foreach($q->answers as &$answer) {
						if($answer->fk_choix == $choix_line->id && $answer->fk_choix_col == $choix_col->id) {
							$res.= 'checked';
							break;
						}
					}
				}
				$res.= ' name="TAnswer['.$q->id.']['.$choix_line->id.'_'.$choix_col->id.']" value="'.$choix_line->id.'_'.$choix_col->id.'"/></td>';
			}
			else continue;
		}
		
		$first_line=false;
		$res.= '</tr>';
		
	}
	$res.= '</table>';
	
	return $res;
	
}

function draw_grillestring_for_user(&$q) {
	
	$res = '<br /><table class="noborder"><tr><td></td>';
	foreach($q->choices as &$choix_col) {
		if($choix_col->type === 'column') $res.= '<td>'.$choix_col->label.'</td>';
	}
	$res.= '</tr>';
	
	$first_line=true;
	foreach($q->choices as &$choix_line) {
		
		if($choix_line->type === 'line') $res.= '<tr><td>'.$choix_line->label.'</td>';
		else continue;
		
		foreach($q->choices as &$choix_col) {
			if($choix_col->type === 'column') {
				$res.= '<td><input type="text" ';
				if(!empty($q->answers)) {
					foreach($q->answers as &$answer) {
						
						if($answer->fk_choix == $choix_line->id && $answer->fk_choix_col == $choix_col->id) {
							
							$res.= 'value="'.$answer->value.'"';
							break;
						}
					}
				}
				$res.= ' name="TAnswer['.$q->id.']['.$choix_line->id.'_'.$choix_col->id.']"/></td>';
			}
			else continue;
		}
		
		$first_line=false;
		$res.= '</tr>';
		
	}
	$res.= '</table>';
	
	return $res;
	
}

function draw_date_for_user(&$q) {
	
	global $form;
	
	return '<br />'.custom_select_date($q->answers[0]->value, 'date_q'.$q->id, 0, 0, 0, "", 1, 0, 1);
	
}

function draw_hour_for_user(&$q) {
	
	global $form;
	
	return $form->select_duration('time_q'.$q->id, $q->answers[0]->value, 0, 'text', 0, 1);
	
}

function draw_linearscale_for_user(&$q) {
	if(empty($q->choices)) $q->loadChoices();
	return '<br /><div class="slidecontainer"><input type="range" class="slider-color" id="linearscal_q'.$q->id.'" name="linearscal_q'.$q->id.'" min="'.$q->choices[0]->label.'" max="'.$q->choices[1]->label.'" step="'.$q->choices[2]->label.'" value="'.$q->answers[0]->value.'"/><br />
			<span>Valeur :&nbsp;</span><span style="font-weight:bold;color:red" id="val_linearscal_q'.$q->id.'">'.$q->answers[0]->value.'</span></div>';
}

function setField($type_object, $fk_object, $field, $value) {
	
	global $db;
	
	$type_object = ucfirst($type_object);
	$obj = new $type_object($db);
	$obj->load($fk_object);
	$obj->{$field} = $value;
	return $obj->save();
	
}

function _getDateFr($date) {
	
	return date('d/m/Y', strtotime($date));
	
}

function draw_answer(&$q) {
	
	if(empty($q->choices)) $q->loadChoices();
	if(!empty($q->choices) || $q->type === 'string' || $q->type === 'textarea' || $q->type === 'date' || $q->type === 'hour' || $q->type === 'linearscale'/*Pas de choix pour ces types là*/) {
		$res = '<div class="element" type="question" id="question'.$q->id.'">';
		$res.= '<div class="refid">'.$q->label.'</div>';
		
		switch($q->type) {
			
			case 'string':
				$res.= draw_string_answer($q);
				break;
			
			case 'grillestring':
				$res.= draw_grillestring_answer($q);
				break;
				
			case 'textarea':
				$res.= draw_textarea_answer($q);
				break;
				
			case 'select':
				$res.= draw_select_answer($q);
				break;
				
			case 'listradio':
				$res.= draw_listradio_answer($q);
				break;
				
			case 'grilleradio':
				$res.= draw_grilleradio_answer($q);
				break;
				
			case 'listcheckbox':
				$res.= draw_listcheckbox_answer($q);
				break;
				
			case 'grillecheckbox':
				$res.= draw_grillecheckbox_answer($q);
				break;
				
			case 'date':
				$res.= draw_date_answer($q);
				break;
				
			case 'hour':
				$res.= draw_hour_answer($q);
				break;
				
			case 'linearscale':
				$res.= draw_linearscale_answer($q);
				break;
				
		}
		
		$res.= '</div>';
	}
	return $res;
	
}

function draw_string_answer(&$q) {
	
	return $q->answers[0]->value;
	
}

function draw_textarea_answer(&$q) {
	
	return nl2br($q->answers[0]->value);
	
}

function draw_select_answer(&$q) {
	
	foreach($q->choices as &$choix) {
		if($choix->id == $q->answers[0]->fk_choix) return $choix->label;
	}
	
}

function draw_listradio_answer(&$q) {
	
	foreach($q->choices as &$choix) {
		if($choix->id == $q->answers[0]->fk_choix) return $choix->label;
	}
	
}

function draw_listcheckbox_answer(&$q) {
	
	global $db;
	
	$TRes=array();
	
	if(!empty($q->answers)) {
		foreach($q->answers as &$answer) {
			$choix= new Choice($db);
			$choix->fetch($answer->fk_choix);
			$TRes[] = $choix->label;
		}
	}
	
	return implode('<br />', $TRes);
	
}

function draw_grilleradio_answer(&$q) {
	
	global $db;
	
	$TRes=array();
	
	if(!empty($q->answers)) {
		foreach($q->answers as &$answer) {
			$choix= new Choice($db);
			$choix->fetch($answer->fk_choix);
			$res = $choix->label;
			$choix->fetch($answer->fk_choix_col);
			$res.= ' : '.$choix->label;
			$TRes[] = $res;
		}
	}
	
	return implode('<br />', $TRes);
	
}

function draw_grillecheckbox_answer(&$q) {
	
	global $db;
	
	$res='';
	$TLines=array();
	
	if(!empty($q->answers)) {
		foreach($q->answers as &$answer) {
			
			$choix = new Choice($db);
			$choix_col = new Choice($db);
			$choix->fetch($answer->fk_choix);
			$choix_col->fetch($answer->fk_choix_col);
			$TLines[$choix->label][] = $choix_col->label;
			
		}
	}
	
	if(!empty($TLines)) {
		//var_dump($TLines);
		foreach($TLines as $k=>$v) {
			$res.= $k.' :<ul>';
			foreach($v as $col) {
				$res.= '<li>'.$col.'</li>';
			}
			$res.= '</ul>';
		}
	}
	
	return $res;
	
}

function draw_grillestring_answer(&$q) {
	
	global $db;
	
	$res='';
	$TLines=array();

	if(!empty($q->answers)) {
		foreach($q->answers as &$answer) {
			$choix = new Choice($db);
			$choix_col = new Choice($db);
			$choix->fetch($answer->fk_choix);
			$choix_col->fetch($answer->fk_choix_col);
			$TLines[$choix->label][$choix_col->label] = $answer->value;
			
		}
	}
	if(!empty($TLines)) {
		//var_dump($TLines);
		foreach($TLines as $k=>$v) {
			$res.= $k.' :<ul>';
			foreach($v as $col=>$value) {
				
				if(!empty($value))$res.= '<li>'.$col.' => '.$value.'</li>';
			}
			$res.= '</ul>';
		}
	}
	
	return $res;
	
}

function draw_date_answer(&$q) {
	
	$date = $q->answers[0]->value;
	return !empty($date) ? date('d/m/Y', $date) : '';
	
}

function draw_hour_answer(&$q) {
	
	require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
	
	$time = $q->answers[0]->value;
	return !empty($time) ? convertSecondToTime($time, 'allhourmin') : '';
	
}

function draw_linearscale_answer(&$q) {
	
	return $q->answers[0]->value;
	
}

function _getBanner(&$object, $action, $print_link_apercu=true, $shownav=true, $show_linkback=true) {
	
	global $langs;
	
	if($show_linkback) $linkback = '<a href="'.dol_buildpath('/questionnaire/list.php', 1).'">' . $langs->trans("BackToList") . '</a>';
	$morehtmlref = '<div class="refidno">'.getFieldVal($object, 'Title', 'title').'</div>';
	//$morehtmlref.= '<div class="refidno">'.getFieldVal($object, 'LinkedObject', 'origin').'</div>';
	if($action !== 'create' && $action !== 'answer' && $print_link_apercu) $morehtmlref.= '<div class="refidno">'.($action === 'apercu' ? '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">Retour au mode édition</a>' : '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=apercu">Visualiser un aperçu</a>').'</div>';
	dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref', 'ref', $morehtmlref, '', 0, '', '');
	
}

function _getBannerToAnswer(&$object, $action, $print_link_apercu = true, $shownav = true, $show_linkback = true)
{

	global $langs, $form;

	if ($show_linkback)
		$linkback = '<a href="'.dol_buildpath('/questionnaire/list.php', 1).'">'.$langs->trans("BackToList").'</a>';
	$morehtmlref = '<div class="refidno">'.getFieldVal($object, 'Title', 'title').'</div>';
	//$morehtmlref.= '<div class="refidno">'.getFieldVal($object, 'LinkedObject', 'origin').'</div>';
	if ($action !== 'create' && $action !== 'answer' && $print_link_apercu)
		$morehtmlref .= '<div class="refidno">'.($action === 'apercu' ? '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">Retour au mode édition</a>' : '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=apercu">Visualiser un aperçu</a>').'</div>';
//	dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref', 'ref', $morehtmlref, '', 0, '', '');
	$morehtmlleft = '<div class="floatleft inline-block valignmiddle divphotoref">';
	if ($object->element == 'action')
	{
		$width = 80;
		$cssclass = 'photorefcenter';
		$nophoto = img_picto('', 'title_agenda', '', false, 1);
	}
	else
	{
		$width = 14;
		$cssclass = 'photorefcenter';
		$picto = $object->picto;
		if ($object->element == 'project' && !$object->public)
			$picto = 'project'; // instead of projectpub
		$nophoto = img_picto('', 'object_'.$picto, '', false, 1);
	}
	$morehtmlleft .= '<!-- No photo to show -->';
	$morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="No photo" border="0"'.($width ? ' width="'.$width.'"' : '').' src="'.$nophoto.'"></div></div>';

	$morehtmlleft .= '</div>';

	$tmptxt = $object->getLibStatut(7);
	if (empty($tmptxt) || $tmptxt == $object->getLibStatut(3) || $conf->browser->layout == 'phone')
		$tmptxt = $object->getLibStatut(5);
	$morehtmlstatus .= $tmptxt;
	// Add alias for thirdparty
	if (!empty($object->name_alias))
		$morehtmlref .= '<div class="refidno">'.$object->name_alias.'</div>';

	// Add label
	if ($object->element == 'product' || $object->element == 'bank_account' || $object->element == 'project_task')
	{
		if (!empty($object->label))
			$morehtmlref .= '<div class="refidno">'.$object->label.'</div>';
	}

	if (method_exists($object, 'getBannerAddress') && $object->element != 'product' && $object->element != 'bookmark' && $object->element != 'ecm_directories' && $object->element != 'ecm_files')
	{
		$morehtmlref .= '<div class="refidno">';
		$morehtmlref .= $object->getBannerAddress('refaddress', $object);
		$morehtmlref .= '</div>';
	}
	if (!empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && in_array($object->element, array('societe', 'contact', 'member', 'product')))
	{
		$morehtmlref .= '<div style="clear: both;"></div><div class="refidno">';
		$morehtmlref .= $langs->trans("TechnicalID").': '.$object->id;
		$morehtmlref .= '</div>';
	}

	print '<div class="'.($onlybanner ? 'arearefnobottom ' : 'arearef ').'heightref valignmiddle" width="100%">';
	print $form->showrefnav($object, $paramid, $morehtml, $shownav, $fieldid, $fieldref, '', $moreparam, $nodbprefix, $morehtmlleft, $morehtmlstatus, $morehtmlright);
	print '</div>';
	print '<div class="underrefbanner clearboth"></div>';
}

function getFieldVal(&$object, $trans, $field) {
	
	global $form, $langs;
	
	if($field === 'origin') {
		
		if(GETPOST('action') !== 'editorigin') {
			$object->origin = _showLinkedObject($object->origin, $object->originid);
			$res.=$form->editfieldkey($trans, $field, $object->{$field}, $object, 1, 'string', '', 0, 1);
			$res.=$form->editfieldval($trans, $field, $object->{$field}, $object, 1, 'string', '', null, null, '', 1);
		} else {
			$res.= _formSetObjectLinked($object->origin, $object->originid);
		}
		
	} else {
		$res.=$form->editfieldkey($trans, $field, $object->{$field}, $object, 1, 'string', '', 0, 1);
		$res.=$form->editfieldval($trans, $field, $object->{$field}, $object, 1, 'string', '', null, null, '', 1);
	}
	
	return $res;
}

function _formSetObjectLinked($origin, $originid, $print_form=true) {
	
	global $db, $form, $langs;
	
	$langs->load('propal');
	$langs->load('bills');
	$langs->load('orders');
	$langs->load('supplierorder');
	
	if($print_form) {
		$res = $langs->trans('LinkedObject').' : <form name="updateLinkedObject" method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.GETPOST('id').'">';
		$res.= '<input type="hidden" name="action" value="setorigin" />';
	}
	
	// Tableau type origine
	$res.= $form->selectarray('origin', array(
												''=>''
												,'Propal'=>$langs->trans('Proposal')
												,'Commande'=>$langs->trans('Order')
												,'Facture'=>$langs->trans('Invoice')
												,'CommandeFournisseur'=>$langs->trans('SupplierOrder')
												,'FactureFournisseur'=>$langs->trans('SupplierInvoice')
											), $origin);
	
	// Tableau de pièces
	$array_ids = _getIdsObject($origin);
	$res.= $form->selectarray('originid', $array_ids, $originid);
	
	if($print_form) {
		$res.= '<input type="SUBMIT" class="button" name="subFormUpdateObjectLinked" value="'.$langs->trans('Modify').'" />';
		$res.= '<a href="'.dol_buildpath('/questionnaire/card.php', 1).'?id='.GETPOST('id').'" class="button" name="subFormUpdateObjectLinked">'.$langs->trans('Cancel').'</a>';
		$res.= '</form>';
	}
	
	return $res;
}

function _getIdsObject($origin, $get_input=false) {
	
	global $db, $form;
	
	if(empty($form)) $form = new Form($db);
	
	$TRes=array();
	$table = strtolower($origin);
	$fieldref = 'ref';
	if($table === 'commandefournisseur') $table = 'commande_fournisseur';
	if($table === 'facturefournisseur') $table = 'facture_fournisseur';
	elseif($table === 'facture') {
		$fieldref = 'facnumber';
	}
	
	$sql = 'SELECT rowid, '.$fieldref.'
			FROM '.MAIN_DB_PREFIX.$table.'
			ORDER BY rowid';
	
	$resql = $db->query($sql);
	if(!empty($resql) && $db->num_rows($resql) > 0) {
		while($res = $db->fetch_object($resql)) $TRes[$res->rowid] = $res->{$fieldref};
	}
	
	if($get_input) return $form->selectarray('originid', $TRes);
	return $TRes;
	
}

function _showLinkedObject($origin, $originid, $print_form_inputs=true, $get_form_add=true) {
	
	global $db;
	
	if(!empty($origin) && !empty($originid)) {
		dol_include_once('/comm/propal/class/propal.class.php');
		dol_include_once('/compta/facture/class/facture.class.php');
		dol_include_once('/commande/class/commande.class.php');
		dol_include_once('/fourn/class/fournisseur.commande.class.php');
		dol_include_once('/fourn/class/fournisseur.facture.class.php');
		
		if(class_exists($origin)) {
			$obj = new $origin($db);
			if($obj->fetch($originid) > 0) {
				$inputs = $print_form_inputs ? '<input type="hidden" name="origin" value="'.$origin.'"/><input type="hidden" name="originid" value="'.$originid.'"/>' : '';
				return $obj->getNomUrl(1).$inputs;
			}
		}
	}
	
}

function _getQuestionnaireLink($fk_questionnaire, $action, $fk_invitation='',$more='')
{
	global $db;
	
	$q = new Questionnaire($db);
	$more_param = '';
	if($action === 'to_answer') $more_param.= '&action=answer';
	if(!empty($fk_invitation)) $more_param.= '&fk_invitation='.$fk_invitation;
	
	$more_param.=$more;
	if ($q->fetch($fk_questionnaire) > 0) return $q->getNomUrl(1, $more_param);
	
	return '';
}

function _getLibStatus($fk_questionnaire, $fk_statut)
{
	global $db;
	
	$q = new Questionnaire($db);
	if ($q->fetch($fk_questionnaire) > 0) return $q->LibStatut($fk_statut, 1);
	
	return '';
}
function prepareMailContent($invuser,$fk_questionnaire)
{
	$content = "Bonjour, \nNous vous invitons à répondre au questionnaire suivant : ";
		
	if(!empty($invuser->fk_user))$content.=dol_buildpath('/questionnaire/card.php?id='.$fk_questionnaire.'&action=answer&fk_invitation='.$invuser->id.'&token='.$invuser->token, 2);
	else $content.=dol_buildpath('/questionnaire/public/toAnswer.php?id='.$fk_questionnaire.'&action=answer&fk_invitation='.$invuser->id.'&token='.$invuser->token, 2);
	$content.=" \nVous avez jusqu'au ".date('d/m/Y',$invuser->date_limite_reponse).' pour y répondre.';
	
	
	
	

	return $content;

	
}

function llxHeaderQuest()
{
	print '<!doctype html>
	<html lang="fr">
	<head>
	<meta name="robots" content="noindex,nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="author" content="Dolibarr Development Team">
	<title>Questionnaire</title>
	<!-- Includes CSS for JQuery (Ajax library) -->
	<link rel="stylesheet" type="text/css" href="'.dol_buildpath('/questionnaire/includes/jquery/css/base/jquery-ui.css',1).'">
	<link rel="stylesheet" type="text/css" href="'.dol_buildpath('/questionnaire/includes/jquery/plugins/jnotify/jquery.jnotify-alt.min.css',1).'">
	<link rel="stylesheet" type="text/css" href="'.dol_buildpath('/questionnaire/includes//jquery/plugins/select2/dist/css/select2.css',1).'">
	<!-- Includes CSS for font awesome -->
	<link rel="stylesheet" type="text/css" href="'.dol_buildpath('/questionnaire/includes/common/fontawesome/css/font-awesome.min.css',1).'">
	<!-- Includes CSS added by page -->
	<link rel="stylesheet" type="text/css" title="default" href="'.dol_buildpath('/questionnaire/css/styles.css',1).'">
		<!-- Includes JS for JQuery -->
	<script type="text/javascript" src="'.dol_buildpath('/questionnaire/includes/jquery/js/jquery.min.js',1).'"></script>
	<script type="text/javascript" src="'.dol_buildpath('/questionnaire/includes/jquery/js/jquery-ui.min.js',1).'"></script>
	<script type="text/javascript" src="'.dol_buildpath('/questionnaire/includes/jquery/plugins/tablednd/jquery.tablednd.min.js',1).'"></script>
	<script type="text/javascript" src="'.dol_buildpath('/questionnaire/includes/jquery/plugins/jnotify/jquery.jnotify.min.js',1).'"></script>
	<script type="text/javascript" src="'.dol_buildpath('/questionnaire/includes/jquery/plugins/flot/jquery.flot.min.js',1).'"></script>
	<script type="text/javascript" src="'.dol_buildpath('/questionnaire/includes/jquery/plugins/flot/jquery.flot.pie.min.js',1).'"></script>
	<script type="text/javascript" src="'.dol_buildpath('/questionnaire/includes/jquery/plugins/flot/jquery.flot.stack.min.js',1).'"></script>
	<script type="text/javascript" src="'.dol_buildpath('/questionnaire/includes/jquery/plugins/select2/dist/js/select2.full.min.js',1).'"></script>
		<script type="text/javascript" src="'.dol_buildpath('/questionnaire/includes/lib_head.js.php',1).'"></script>
	</head>
	

	<body id="mainbody">
	<!-- Begin div id-container --><div id="id-container" class="id-container">
	<!-- Begin right area -->
	<div id="id-right">
	<!-- Begin div class="fiche" -->
	<div class="fiche">';
	
	
}


function custom_select_date($set_time = '', $prefix = 're', $h = 0, $m = 0, $empty = 0, $form_name = "", $d = 1, $addnowlink = 0, $nooutput = 0, $disabled = 0, $fullday = '', $addplusone = '', $adddateof = '')
{
	global $conf, $langs;

	$retstring = '';

	if ($prefix == '')
		$prefix = 're';
	if ($h == '')
		$h = 0;
	if ($m == '')
		$m = 0;
	$emptydate = 0;
	$emptyhours = 0;
	if ($empty == 1)
	{
		$emptydate = 1;
		$emptyhours = 1;
	}
	if ($empty == 2)
	{
		$emptydate = 0;
		$emptyhours = 1;
	}
	$orig_set_time = $set_time;

	if ($set_time === '' && $emptydate == 0)
	{
		include_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
		$set_time = dol_now('tzuser') - (getServerTimeZoneInt('now') * 3600); // set_time must be relative to PHP server timezone
	}

	// Analysis of the pre-selection date
	if (preg_match('/^([0-9]+)\-([0-9]+)\-([0-9]+)\s?([0-9]+)?:?([0-9]+)?/', $set_time, $reg)) // deprecated usage
	{
		// Date format 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'
		$syear = (!empty($reg[1]) ? $reg[1] : '');
		$smonth = (!empty($reg[2]) ? $reg[2] : '');
		$sday = (!empty($reg[3]) ? $reg[3] : '');
		$shour = (!empty($reg[4]) ? $reg[4] : '');
		$smin = (!empty($reg[5]) ? $reg[5] : '');
	}
	elseif (strval($set_time) != '' && $set_time != -1)
	{
		// set_time est un timestamps (0 possible)
		$syear = dol_print_date($set_time, "%Y");
		$smonth = dol_print_date($set_time, "%m");
		$sday = dol_print_date($set_time, "%d");
		if ($orig_set_time != '')
		{
			$shour = dol_print_date($set_time, "%H");
			$smin = dol_print_date($set_time, "%M");
			$ssec = dol_print_date($set_time, "%S");
		}
		else
		{
			$shour = '';
			$smin = '';
			$ssec = '';
		}
	}
	else
	{
		// Date est '' ou vaut -1
		$syear = '';
		$smonth = '';
		$sday = '';
		$shour = !isset($conf->global->MAIN_DEFAULT_DATE_HOUR) ? ($h == -1 ? '23' : '') : $conf->global->MAIN_DEFAULT_DATE_HOUR;
		$smin = !isset($conf->global->MAIN_DEFAULT_DATE_MIN) ? ($h == -1 ? '59' : '') : $conf->global->MAIN_DEFAULT_DATE_MIN;
		$ssec = !isset($conf->global->MAIN_DEFAULT_DATE_SEC) ? ($h == -1 ? '59' : '') : $conf->global->MAIN_DEFAULT_DATE_SEC;
	}
	if ($h == 3)
		$shour = '';
	if ($m == 3)
		$smin = '';

	// You can set MAIN_POPUP_CALENDAR to 'eldy' or 'jquery'
	$usecalendar = 'combo';
	if (!empty($conf->use_javascript_ajax) && (empty($conf->global->MAIN_POPUP_CALENDAR) || $conf->global->MAIN_POPUP_CALENDAR != "none"))
	{
		$usecalendar = ((empty($conf->global->MAIN_POPUP_CALENDAR) || $conf->global->MAIN_POPUP_CALENDAR == 'eldy') ? 'jquery' : $conf->global->MAIN_POPUP_CALENDAR);
	}
	//if (! empty($conf->browser->phone)) $usecalendar='combo';

	if ($d)
	{
		// Show date with popup
		if ($usecalendar != 'combo')
		{
			$formated_date = '';
			//print "e".$set_time." t ".$conf->format_date_short;
			if (strval($set_time) != '' && $set_time != -1)
			{
				//$formated_date=dol_print_date($set_time,$conf->format_date_short);
				$formated_date = dol_print_date($set_time, $langs->trans("FormatDateShortInput"));  // FormatDateShortInput for dol_print_date / FormatDateShortJavaInput that is same for javascript
			}

			// Calendrier popup version eldy
			if ($usecalendar == "eldy")
			{
				// Zone de saisie manuelle de la date
				$retstring .= '<input id="'.$prefix.'" name="'.$prefix.'" type="text" class="maxwidth75" maxlength="11" value="'.$formated_date.'"';
				$retstring .= ($disabled ? ' disabled' : '');
				$retstring .= ' onChange="dpChangeDay(\''.$prefix.'\',\''.$langs->trans("FormatDateShortJavaInput").'\'); "';  // FormatDateShortInput for dol_print_date / FormatDateShortJavaInput that is same for javascript
				$retstring .= '>';

				// Icone calendrier
				if (!$disabled)
				{
					$retstring .= '<button id="'.$prefix.'Button" type="button" class="dpInvisibleButtons"';
					$base = DOL_URL_ROOT.'/core/';
					$retstring .= ' onClick="showDP(\''.$base.'\',\''.$prefix.'\',\''.$langs->trans("FormatDateShortJavaInput").'\',\''.$langs->defaultlang.'\');"';
					$retstring .= '>'.img_object($langs->trans("SelectDate"), 'calendarday', 'class="datecallink"').'</button>';
				}
				else
					$retstring .= '<button id="'.$prefix.'Button" type="button" class="dpInvisibleButtons">'.img_object($langs->trans("Disabled"), 'calendarday', 'class="datecallink"').'</button>';

				$retstring .= '<input type="hidden" id="'.$prefix.'day"   name="'.$prefix.'day"   value="'.$sday.'">'."\n";
				$retstring .= '<input type="hidden" id="'.$prefix.'month" name="'.$prefix.'month" value="'.$smonth.'">'."\n";
				$retstring .= '<input type="hidden" id="'.$prefix.'year"  name="'.$prefix.'year"  value="'.$syear.'">'."\n";
			}
			elseif ($usecalendar == 'jquery')
			{
				if (!$disabled)
				{
					// Output javascript for datepicker
					$retstring .= "<script type='text/javascript'>";
					$retstring .= "$(function(){ $('#".$prefix."').datepicker({
							dateFormat: '".$langs->trans("FormatDateShortJQueryInput")."',
							autoclose: true,
							todayHighlight: true,";
					if (!empty($conf->dol_use_jmobile))
					{
						$retstring .= "
								beforeShow: function (input, datePicker) {
									input.disabled = true;
								},
								onClose: function (dateText, datePicker) {
									this.disabled = false;
								},
								";
					}
					
					// Note: We don't need monthNames, monthNamesShort, dayNames, dayNamesShort, dayNamesMin, they are set globally on datepicker component in lib_head.js.php
					if (empty($conf->global->MAIN_POPUP_CALENDAR_ON_FOCUS))
					{
						$retstring .= "
								showOn: 'button',
								buttonImage: '".dol_buildpath("/questionnaire/img/object_calendarday.png", 1)."',
								buttonImageOnly: true";
					}
					$retstring .= "
							}) });";
					$retstring .= "</script>";
				}

				// Zone de saisie manuelle de la date
				$retstring .= '<input id="'.$prefix.'" name="'.$prefix.'" type="text" class="maxwidth75" maxlength="11" value="'.$formated_date.'"';
				$retstring .= ($disabled ? ' disabled' : '');
				$retstring .= ' onChange="dpChangeDay(\''.$prefix.'\',\''.$langs->trans("FormatDateShortJavaInput").'\'); "';  // FormatDateShortInput for dol_print_date / FormatDateShortJavaInput that is same for javascript
				$retstring .= '>';

				// Icone calendrier
				if (!$disabled)
				{
					/* Not required. Managed by option buttonImage of jquery
					  $retstring.=img_object($langs->trans("SelectDate"),'calendarday','id="'.$prefix.'id" class="datecallink"');
					  $retstring.="<script type='text/javascript'>";
					  $retstring.="jQuery(document).ready(function() {";
					  $retstring.='	jQuery("#'.$prefix.'id").click(function() {';
					  $retstring.="    	jQuery('#".$prefix."').focus();";
					  $retstring.='    });';
					  $retstring.='});';
					  $retstring.="</script>"; */
				}
				else
				{
					$retstring .= '<button id="'.$prefix.'Button" type="button" class="dpInvisibleButtons">'.img_object($langs->trans("Disabled"), 'calendarday', 'class="datecallink"').'</button>';
				}

				$retstring .= '<input type="hidden" id="'.$prefix.'day"   name="'.$prefix.'day"   value="'.$sday.'">'."\n";
				$retstring .= '<input type="hidden" id="'.$prefix.'month" name="'.$prefix.'month" value="'.$smonth.'">'."\n";
				$retstring .= '<input type="hidden" id="'.$prefix.'year"  name="'.$prefix.'year"  value="'.$syear.'">'."\n";
			}
			else
			{
				$retstring .= "Bad value of MAIN_POPUP_CALENDAR";
			}
		}
		// Show date with combo selects
		else
		{
			//$retstring.='<div class="inline-block">';
			// Day
			$retstring .= '<select'.($disabled ? ' disabled' : '').' class="flat valignmiddle maxwidth50imp" id="'.$prefix.'day" name="'.$prefix.'day">';

			if ($emptydate || $set_time == -1)
			{
				$retstring .= '<option value="0" selected>&nbsp;</option>';
			}

			for ($day = 1; $day <= 31; $day++)
			{
				$retstring .= '<option value="'.$day.'"'.($day == $sday ? ' selected' : '').'>'.$day.'</option>';
			}

			$retstring .= "</select>";

			$retstring .= '<select'.($disabled ? ' disabled' : '').' class="flat valignmiddle maxwidth75imp" id="'.$prefix.'month" name="'.$prefix.'month">';
			if ($emptydate || $set_time == -1)
			{
				$retstring .= '<option value="0" selected>&nbsp;</option>';
			}

			// Month
			for ($month = 1; $month <= 12; $month++)
			{
				$retstring .= '<option value="'.$month.'"'.($month == $smonth ? ' selected' : '').'>';
				$retstring .= dol_print_date(mktime(12, 0, 0, $month, 1, 2000), "%b");
				$retstring .= "</option>";
			}
			$retstring .= "</select>";

			// Year
			if ($emptydate || $set_time == -1)
			{
				$retstring .= '<input'.($disabled ? ' disabled' : '').' placeholder="'.dol_escape_htmltag($langs->trans("Year")).'" class="flat maxwidth50imp valignmiddle" type="number" min="0" max="3000" maxlength="4" id="'.$prefix.'year" name="'.$prefix.'year" value="'.$syear.'">';
			}
			else
			{
				$retstring .= '<select'.($disabled ? ' disabled' : '').' class="flat valignmiddle maxwidth75imp" id="'.$prefix.'year" name="'.$prefix.'year">';

				for ($year = $syear - 10; $year < $syear + 10; $year++)
				{
					$retstring .= '<option value="'.$year.'"'.($year == $syear ? ' selected' : '').'>'.$year.'</option>';
				}
				$retstring .= "</select>\n";
			}
			//$retstring.='</div>';
		}
	}

	if ($d && $h)
		$retstring .= ($h == 2 ? '<br>' : ' ');

	if ($h)
	{
		// Show hour
		$retstring .= '<select'.($disabled ? ' disabled' : '').' class="flat valignmiddle maxwidth50 '.($fullday ? $fullday.'hour' : '').'" id="'.$prefix.'hour" name="'.$prefix.'hour">';
		if ($emptyhours)
			$retstring .= '<option value="-1">&nbsp;</option>';
		for ($hour = 0; $hour < 24; $hour++)
		{
			if (strlen($hour) < 2)
				$hour = "0".$hour;
			$retstring .= '<option value="'.$hour.'"'.(($hour == $shour) ? ' selected' : '').'>'.$hour.(empty($conf->dol_optimize_smallscreen) ? '' : 'H').'</option>';
		}
		$retstring .= '</select>';
		if ($m && empty($conf->dol_optimize_smallscreen))
			$retstring .= ":";
	}

	if ($m)
	{
		// Show minutes
		$retstring .= '<select'.($disabled ? ' disabled' : '').' class="flat valignmiddle maxwidth50 '.($fullday ? $fullday.'min' : '').'" id="'.$prefix.'min" name="'.$prefix.'min">';
		if ($emptyhours)
			$retstring .= '<option value="-1">&nbsp;</option>';
		for ($min = 0; $min < 60; $min++)
		{
			if (strlen($min) < 2)
				$min = "0".$min;
			$retstring .= '<option value="'.$min.'"'.(($min == $smin) ? ' selected' : '').'>'.$min.(empty($conf->dol_optimize_smallscreen) ? '' : '').'</option>';
		}
		$retstring .= '</select>';

		$retstring .= '<input type="hidden" name="'.$prefix.'sec" value="'.$ssec.'">';
	}

	// Add a "Now" link
	if ($conf->use_javascript_ajax && $addnowlink)
	{
		// Script which will be inserted in the onClick of the "Now" link
		$reset_scripts = "";

		// Generate the date part, depending on the use or not of the javascript calendar
		$reset_scripts .= 'jQuery(\'#'.$prefix.'\').val(\''.dol_print_date(dol_now(), 'day').'\');';
		$reset_scripts .= 'jQuery(\'#'.$prefix.'day\').val(\''.dol_print_date(dol_now(), '%d').'\');';
		$reset_scripts .= 'jQuery(\'#'.$prefix.'month\').val(\''.dol_print_date(dol_now(), '%m').'\');';
		$reset_scripts .= 'jQuery(\'#'.$prefix.'year\').val(\''.dol_print_date(dol_now(), '%Y').'\');';
		/* if ($usecalendar == "eldy")
		  {
		  $base=DOL_URL_ROOT.'/core/';
		  $reset_scripts .= 'resetDP(\''.$base.'\',\''.$prefix.'\',\''.$langs->trans("FormatDateShortJavaInput").'\',\''.$langs->defaultlang.'\');';
		  }
		  else
		  {
		  $reset_scripts .= 'this.form.elements[\''.$prefix.'day\'].value=formatDate(new Date(), \'d\'); ';
		  $reset_scripts .= 'this.form.elements[\''.$prefix.'month\'].value=formatDate(new Date(), \'M\'); ';
		  $reset_scripts .= 'this.form.elements[\''.$prefix.'year\'].value=formatDate(new Date(), \'yyyy\'); ';
		  } */
		// Update the hour part
		if ($h)
		{
			if ($fullday)
				$reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
			//$reset_scripts .= 'this.form.elements[\''.$prefix.'hour\'].value=formatDate(new Date(), \'HH\'); ';
			$reset_scripts .= 'jQuery(\'#'.$prefix.'hour\').val(\''.dol_print_date(dol_now(), '%H').'\');';
			if ($fullday)
				$reset_scripts .= ' } ';
		}
		// Update the minute part
		if ($m)
		{
			if ($fullday)
				$reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
			//$reset_scripts .= 'this.form.elements[\''.$prefix.'min\'].value=formatDate(new Date(), \'mm\'); ';
			$reset_scripts .= 'jQuery(\'#'.$prefix.'min\').val(\''.dol_print_date(dol_now(), '%M').'\');';
			if ($fullday)
				$reset_scripts .= ' } ';
		}
		// If reset_scripts is not empty, print the link with the reset_scripts in the onClick
		if ($reset_scripts && empty($conf->dol_optimize_smallscreen))
		{
			$retstring .= ' <button class="dpInvisibleButtons datenowlink" id="'.$prefix.'ButtonNow" type="button" name="_useless" value="now" onClick="'.$reset_scripts.'">';
			$retstring .= $langs->trans("Now");
			$retstring .= '</button> ';
		}
	}

	// Add a "Plus one hour" link
	if ($conf->use_javascript_ajax && $addplusone)
	{
		// Script which will be inserted in the onClick of the "Add plusone" link
		$reset_scripts = "";

		// Generate the date part, depending on the use or not of the javascript calendar
		$reset_scripts .= 'jQuery(\'#'.$prefix.'\').val(\''.dol_print_date(dol_now(), 'day').'\');';
		$reset_scripts .= 'jQuery(\'#'.$prefix.'day\').val(\''.dol_print_date(dol_now(), '%d').'\');';
		$reset_scripts .= 'jQuery(\'#'.$prefix.'month\').val(\''.dol_print_date(dol_now(), '%m').'\');';
		$reset_scripts .= 'jQuery(\'#'.$prefix.'year\').val(\''.dol_print_date(dol_now(), '%Y').'\');';
		// Update the hour part
		if ($h)
		{
			if ($fullday)
				$reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
			$reset_scripts .= 'jQuery(\'#'.$prefix.'hour\').val(\''.dol_print_date(dol_now(), '%H').'\');';
			if ($fullday)
				$reset_scripts .= ' } ';
		}
		// Update the minute part
		if ($m)
		{
			if ($fullday)
				$reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
			$reset_scripts .= 'jQuery(\'#'.$prefix.'min\').val(\''.dol_print_date(dol_now(), '%M').'\');';
			if ($fullday)
				$reset_scripts .= ' } ';
		}
		// If reset_scripts is not empty, print the link with the reset_scripts in the onClick
		if ($reset_scripts && empty($conf->dol_optimize_smallscreen))
		{
			$retstring .= ' <button class="dpInvisibleButtons datenowlink" id="'.$prefix.'ButtonPlusOne" type="button" name="_useless2" value="plusone" onClick="'.$reset_scripts.'">';
			$retstring .= $langs->trans("DateStartPlusOne");
			$retstring .= '</button> ';
		}
	}

	// Add a "Plus one hour" link
	if ($conf->use_javascript_ajax && $adddateof)
	{
		$tmparray = dol_getdate($adddateof);
		$retstring .= ' - <button class="dpInvisibleButtons datenowlink" id="dateofinvoice" type="button" name="_dateofinvoice" value="now" onclick="jQuery(\'#re\').val(\''.dol_print_date($adddateof, 'day').'\');jQuery(\'#reday\').val(\''.$tmparray['mday'].'\');jQuery(\'#remonth\').val(\''.$tmparray['mon'].'\');jQuery(\'#reyear\').val(\''.$tmparray['year'].'\');">'.$langs->trans("DateInvoice").'</a>';
	}

	if (!empty($nooutput))
		return $retstring;

	print $retstring;
	return;
}
