<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *	\file       htdocs/projet/fiche.php
 *	\ingroup    projet
 *	\brief      Fiche projet
 *	\version    $Id$
 */

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/project.lib.php");
if ($conf->propal->enabled) require_once(DOL_DOCUMENT_ROOT."/propal.class.php");
if ($conf->facture->enabled) require_once(DOL_DOCUMENT_ROOT."/facture.class.php");
if ($conf->commande->enabled) require_once(DOL_DOCUMENT_ROOT."/commande/commande.class.php");

$langs->load("projects");

$projetid='';
$ref='';
if (isset($_GET["id"]))  { $projetid=$_GET["id"]; }
else $_GET["id"]=$_POST["id"];
if (isset($_GET["ref"])) { $ref=$_GET["ref"]; }

//var_dump($_REQUEST);exit;

// If socid provided by ajax company selector
if (! empty($_REQUEST['socid_id']))
{
	$_GET['socid'] = $_GET['socid_id'];
	$_POST['socid'] = $_POST['socid_id'];
	$_REQUEST['socid'] = $_REQUEST['socid_id'];
}


if ($projetid == '' && $ref == '' && ($_GET['action'] != "create" && $_POST['action'] != "add" && $_POST["action"] != "update" && !$_POST["cancel"])) accessforbidden();

// Security check
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'projet', $projetid);


/*
 * Actions
 */

if ($_POST["action"] == 'add' && $user->rights->projet->creer)
{
	$error=0;
	if (empty($_POST["ref"]))
	{
		$mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Ref")).'</div>';
		$error++;
	}
	if (empty($_POST["title"]))
	{
		$mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Label")).'</div>';
		$error++;
	}

	if (! $error)
	{
		//print $_POST["socid"];
		$project = new Project($db);
		$project->ref             = $_POST["ref"];
		$project->title           = $_POST["title"];
		$project->socid           = $_POST["socid"];
		$project->user_resp_id    = $_POST["officer_project"];
		$project->datec=dol_now('tzserver');
		$project->dateo=dol_mktime(12,0,0,$_POST['projectmonth'],$_POST['projectday'],$_POST['projectyear']);
		$project->datee=dol_mktime(12,0,0,$_POST['projectendmonth'],$_POST['projectendday'],$_POST['projectendyear']);

		$result = $project->create($user);
		if ($result > 0)
		{
			Header("Location:fiche.php?id=".$project->id);
			exit;
		}
		else
		{
			$langs->load("errors");
			$mesg='<div class="error">'.$langs->trans($project->error).'</div>';
			$_GET["action"] = 'create';
		}
	}
	else
	{
		$_GET["action"] = 'create';
	}
}

if ($_POST["action"] == 'update' && ! $_POST["cancel"] && $user->rights->projet->creer)
{
	$error=0;

	if (empty($_POST["ref"]))
	{
		$error++;
		//$_GET["id"]=$_POST["id"]; // On retourne sur la fiche projet
		$mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Ref")).'</div>';
	}
	if (empty($_POST["title"]))
	{
		$error++;
		//$_GET["id"]=$_POST["id"]; // On retourne sur la fiche projet
		$mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Label")).'</div>';
	}
	if (! $error)
	{
		$projet = new Project($db);
		$projet->fetch($_POST["id"]);

		$projet->ref          = $_POST["ref"];
		$projet->title        = $_POST["title"];
		$projet->socid        = $_POST["socid"];
		$projet->user_resp_id = $_POST["officer_project"];
		$projet->date_start   = dol_mktime(12,0,0,$_POST['projectmonth'],$_POST['projectday'],$_POST['projectyear']);
		$projet->date_end     = dol_mktime(12,0,0,$_POST['projectendmonth'],$_POST['projectendday'],$_POST['projectendyear']);

		$result=$projet->update($user);

		$_GET["id"]=$projet->id;  // On retourne sur la fiche projet
	}
	else
	{
		$_GET["id"]=$_POST["id"];
		$_GET['action']='edit';
	}
}

if ($_REQUEST['action'] == 'confirm_validate' && $_REQUEST['confirm'] == 'yes')
{
	$project = new Project($db);
	$project->fetch($_GET["id"]);

	$result = $project->setValid($user, $conf->projet->outputdir);
	if ($result <= 0)
	{
		$mesg='<div class="error">'.$project->error.'</div>';
	}
}

if ($_REQUEST['action'] == 'confirm_close' && $_REQUEST['confirm'] == 'yes')
{
	$project = new Project($db);
	$project->fetch($_GET["id"]);
	$result = $project->setClose($user, $conf->projet->outputdir);
	if ($result <= 0)
	{
		$mesg='<div class="error">'.$project->error.'</div>';
	}
}

if ($_REQUEST['action'] == 'confirm_reopen' && $_REQUEST['confirm'] == 'yes')
{
	$project = new Project($db);
	$project->fetch($_GET["id"]);
	$result = $project->setValid($user, $conf->projet->outputdir);
	if ($result <= 0)
	{
		$mesg='<div class="error">'.$project->error.'</div>';
	}
}

if ($_REQUEST["action"] == 'confirm_delete' && $_REQUEST["confirm"] == "yes" && $user->rights->projet->supprimer)
{
	$projet = new Project($db);
	$projet->id = $_GET["id"];
	$result=$projet->delete($user);
	if ($result >= 0)
	{
		Header("Location: index.php");
		exit;
	}
	else
	{
		$mesg='<div class="error">'.$langs->trans("CantRemoveProject").'</div>';
	}
}


/*
 *	View
 */

$help_url="EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos";
llxHeader("",$langs->trans("Projects"),$help_url);

$html = new Form($db);

if ($_GET["action"] == 'create' && $user->rights->projet->creer)
{
	/*
	 * Create
	 */
	print_fiche_titre($langs->trans("NewProject"));

	if ($mesg) print $mesg.'<br>';

	print '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	//if ($_REQUEST["socid"]) print '<input type="hidden" name="socid" value="'.$_REQUEST["socid"].'">';
	print '<table class="border" width="100%">';
	print '<input type="hidden" name="action" value="add">';

	// Ref
	print '<tr><td>'.$langs->trans("Ref").'*</td><td><input size="8" type="text" name="ref" value="'.$_POST["ref"].'"></td></tr>';

	// Label
	print '<tr><td>'.$langs->trans("Label").'*</td><td><input size="30" type="text" name="title" value="'.$_POST["title"].'"></td></tr>';

	// Client
	print '<tr><td>'.$langs->trans("Company").'</td><td>';
	//print $_REQUEST["socid"];
	print $html->select_societes($_REQUEST["socid"],'socid','',1,1);
	print '</td></tr>';

	// Responsable du projet
	print '<tr><td>'.$langs->trans("OfficerProject").'</td><td>';
	if ($_REQUEST["mode"] != 'mine')
	{
		$html->select_users($projet->user_resp_id,'officer_project',1);
	}
	else
	{
		print $user->getNomUrl(1);
		print '<input type="hidden" name="officer_project" value="'.$user->id.'">';
	}
	print '</td></tr>';

	// Date start
	print '<tr><td>'.$langs->trans("DateStart").'</td><td>';
	print $html->select_date('','project');
	print '</td></tr>';

	// Date end
	print '<tr><td>'.$langs->trans("DateEnd").'</td><td>';
	print $html->select_date(-1,'projectend');
	print '</td></tr>';

	print '<tr><td colspan="2" align="center"><input type="submit" class="button" value="'.$langs->trans("Create").'"></td></tr>';
	print '</table>';
	print '</form>';

}
else
{
	/*
	 * Show or edit
	 */

	if ($mesg) print $mesg;

	$projet = new Project($db);
	$projet->fetch($_GET["id"],$_GET["ref"]);

	if ($projet->societe->id > 0)  $result=$projet->societe->fetch($projet->societe->id);
	if ($projet->user_resp_id > 0) $result=$projet->fetch_user($projet->user_resp_id);

	$head=project_prepare_head($projet);
	dol_fiche_head($head, 'project', $langs->trans("Project"),0,'project');

	// Confirmation validation
	if ($_GET['action'] == 'validate')
	{
		$ret=$html->form_confirm($_SERVER["PHP_SELF"].'?id='.$projet->id, $langs->trans('ValidateProject'), $langs->trans('ConfirmValidateProject'), 'confirm_validate','',0,1);
		if ($ret == 'html') print '<br>';
	}
	// Confirmation close
	if ($_GET["action"] == 'close')
	{
		$ret=$html->form_confirm($_SERVER["PHP_SELF"]."?id=".$_GET["id"],$langs->trans("CloseAProject"),$langs->trans("ConfirmCloseAProject"),"confirm_close",'','',1);
		if ($ret == 'html') print '<br>';
	}
	// Confirmation reopen
	if ($_GET["action"] == 'reopen')
	{
		$ret=$html->form_confirm($_SERVER["PHP_SELF"]."?id=".$_GET["id"],$langs->trans("ReOpenAProject"),$langs->trans("ConfirmReOpenAProject"),"confirm_reopen",'','',1);
		if ($ret == 'html') print '<br>';
	}
	// Confirmation delete
	if ($_GET["action"] == 'delete')
	{
		$ret=$html->form_confirm($_SERVER["PHP_SELF"]."?id=".$_GET["id"],$langs->trans("DeleteAProject"),$langs->trans("ConfirmDeleteAProject"),"confirm_delete",'','',1);
		if ($ret == 'html') print '<br>';
	}


	if ($_GET["action"] == 'edit')
	{
		print '<form method="post" action="fiche.php">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="'.$_GET["id"].'">';

		print '<table class="border" width="100%">';

		// Ref
		print '<tr><td width="30%">'.$langs->trans("Ref").'</td><td><input size="8" name="ref" value="'.$projet->ref.'"></td></tr>';

		// Label
		print '<tr><td>'.$langs->trans("Label").'</td><td><input size="30" name="title" value="'.$projet->title.'"></td></tr>';

		// Client
		print '<tr><td>'.$langs->trans("Company").'</td><td>';
		print $html->select_societes($projet->societe->id,'socid','',1,1);
		print '</td></tr>';

		// Responsable du projet
		print '<tr><td>'.$langs->trans("OfficerProject").'</td><td>';
		$html->select_users($projet->user_resp_id,'officer_project',1);
		print '</td></tr>';

		// Statut
		print '<tr><td>'.$langs->trans("Status").'</td><td>'.$projet->getLibStatut(4).'</td></tr>';

		// Date start
		print '<tr><td>'.$langs->trans("DateStart").'</td><td>';
		print $html->select_date($projet->date_start,'project');
		print '</td></tr>';

		// Date end
		print '<tr><td>'.$langs->trans("DateEnd").'</td><td>';
		print $html->select_date($projet->date_end?$projet->date_end:-1,'projectend');
		print '</td></tr>';

		print '<tr><td align="center" colspan="2"><input name="update" class="button" type="submit" value="'.$langs->trans("Modify").'"> &nbsp; <input type="submit" class="button" name="cancel" Value="'.$langs->trans("Cancel").'"></td></tr>';
		print '</table>';
		print '</form>';
	}
	else
	{
		print '<table class="border" width="100%">';

		// Ref
		print '<tr><td width="30%">'.$langs->trans("Ref").'</td><td>';
		print $html->showrefnav($projet,'ref','',1,'ref','ref');
		print '</td></tr>';

		// Label
		print '<tr><td>'.$langs->trans("Label").'</td><td>'.$projet->title.'</td></tr>';

		// Third party
		print '<tr><td>'.$langs->trans("Company").'</td><td>';
		if ($projet->societe->id > 0) print $projet->societe->getNomUrl(1);
		else print'&nbsp;';
		print '</td></tr>';

		// Project leader
		print '<tr><td>'.$langs->trans("OfficerProject").'</td><td>';
		if ($projet->user->id) print $projet->user->getNomUrl(1);
		else print $langs->trans('SharedProject');
		print '</td></tr>';

		// Statut
		print '<tr><td>'.$langs->trans("Status").'</td><td>'.$projet->getLibStatut(4).'</td></tr>';

		// Date start
		print '<tr><td>'.$langs->trans("DateStart").'</td><td>';
		print dol_print_date($projet->date_start,'day');
		print '</td></tr>';

		// Date end
		print '<tr><td>'.$langs->trans("DateEnd").'</td><td>';
		print dol_print_date($projet->date_end,'day');
		print '</td></tr>';

		print '</table>';
	}

	print '</div>';

	/*
	 * Boutons actions
	 */
	print '<div class="tabsAction">';

	if ($_GET["action"] != "edit")
	{
		// Validate
		if ($projet->statut == 0 && $user->rights->projet->creer)
		{
			print '<a class="butAction" href="fiche.php?id='.$_GET["id"].'&action=validate"';
			print '>'.$langs->trans("Valid").'</a>';
		}

		// Modify
		if ($projet->statut != 2 && $user->rights->projet->creer)
		{
			print '<a class="butAction" href="fiche.php?id='.$projet->id.'&amp;action=edit">'.$langs->trans("Modify").'</a>';
		}

		// Close
		if ($projet->statut != 2 && $user->rights->projet->creer)
		{
			print '<a class="butAction" href="fiche.php?id='.$projet->id.'&amp;action=close">'.$langs->trans("Close").'</a>';
		}

		// Reopen
		if ($projet->statut == 2 && $user->rights->projet->creer)
		{
			print '<a class="butAction" href="fiche.php?id='.$projet->id.'&amp;action=reopen">'.$langs->trans("ReOpen").'</a>';
		}

		// Delete
		if ($user->rights->projet->supprimer)
		{
			print '<a class="butActionDelete" href="fiche.php?id='.$projet->id.'&amp;action=delete">'.$langs->trans("Delete").'</a>';
		}
	}

	print "</div>";

}

$db->close();

llxFooter('$Date$ - $Revision$');
?>
