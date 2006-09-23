<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *
 * $Id$
 * $Source$
 */

/**
        \file       htdocs/projet/fiche.php
        \ingroup    projet
        \brief      Fiche projet
        \version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/propal.class.php");
require_once(DOL_DOCUMENT_ROOT."/facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/commande/commande.class.php");
require_once(DOL_DOCUMENT_ROOT."/lib/project.lib.php");

if (!$user->rights->projet->lire) accessforbidden();

/*
 * S�curit� acc�s client
 */
$projetid='';
if ($_GET["id"]) { $projetid=$_GET["id"]; }

if ($projetid == '' && ($_GET['action'] != "create" && $_POST['action'] != "add" && $_POST["action"] != "update" && !$_POST["cancel"])) accessforbidden();

if ($user->societe_id > 0) 
{
	$socid = $user->societe_id;
}

// Protection restriction commercial
if ($projetid && !$user->rights->commercial->client->voir)
{
	$sql = "SELECT p.rowid, p.fk_soc";
	$sql.= " FROM ".MAIN_DB_PREFIX."projet as p";
	if (!$user->rights->commercial->client->voir) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc ";
	$sql.= " WHERE p.rowid = ".$projetid;
	if (!$user->rights->commercial->client->voir) $sql .= " AND p.fk_soc = sc.fk_soc AND sc.fk_user = ".$user->id;
	if ($socid) $sql .= " AND p.fk_soc = ".$socid;

	if ( $db->query($sql) )
	{
		if ( $db->num_rows() == 0) accessforbidden();
	}
}


if ($_POST["action"] == 'add' && $user->rights->projet->creer)
{
	$pro = new Project($db);
	$pro->socid = $_GET["socid"];
	$pro->ref = $_POST["ref"];
	$pro->title = $_POST["title"];
	$result = $pro->create($user);

	if ($result > 0)
	{
		Header("Location:fiche.php?id=".$pro->id);
		exit;
	}
	else
	{
		$mesg='<div class="error">'.$pro->error.'</div>';
		$_GET["action"] = 'create';
	}
}

if ($_POST["action"] == 'update' && $user->rights->projet->creer)
{
	if (! $_POST["cancel"])
	{
		if (!(empty($_POST["id"]) || empty($_POST["ref"]) || empty($_POST["title"])))
		{
			$projet = new Project($db);
			$projet->id = $_POST["id"];
			$projet->ref = $_POST["ref"];
			$projet->title = $_POST["title"];
			$projet->update($user);

			$_GET["id"]=$projet->id;  // On retourne sur la fiche projet
		}
		else
		{
			$_GET["id"]=$_POST["id"]; // On retourne sur la fiche projet
		}
	}
	else
	{
		$_GET["id"]=$_POST["id"]; // On retourne sur la fiche projet
	}
}

if ($_POST["action"] == 'confirm_delete' && $_POST["confirm"] == "yes" && $user->rights->projet->supprimer)
{
	$projet = new Project($db);
	$projet->id = $_GET["id"];
	if ($projet->delete($user) == 0)
	{
		Header("Location: index.php");
		exit;
	}
	else
	{
		Header("Location: fiche.php?id=".$projet->id);
		exit;
	}
}


llxHeader("",$langs->trans("Project"),"Projet");


if ($_GET["action"] == 'create' && $user->rights->projet->creer)
{
  print_titre($langs->trans("NewProject"));

  if ($mesg) print $mesg;
  
  print '<form action="fiche.php?socid='.$_GET["socid"].'" method="post">';

  print '<table class="border" width="100%">';
  print '<input type="hidden" name="action" value="add">';

  // Ref
  print '<tr><td>'.$langs->trans("Ref").'</td><td><input size="8" type="text" name="ref"></td></tr>';

	// Label
  print '<tr><td>'.$langs->trans("Label").'</td><td><input size="30" type="text" name="title"></td></tr>';

  print '<tr><td>'.$langs->trans("Company").'</td><td>';
  $societe = new Societe($db);
  $societe->fetch($_GET["socid"]); 
  print $societe->getNomUrl(1);
  print '</td></tr>';

  print '<tr><td>'.$langs->trans("Author").'</td><td>'.$user->fullname.'</td></tr>';

  print '<tr><td colspan="2" align="center"><input type="submit" class="button" value="'.$langs->trans("Create").'"></td></tr>';
  print '</table>';
  print '</form>';

} else {

	/*
	* Fiche projet en mode visu
	*
	*/

	$projet = new Project($db);
	$projet->fetch($_GET["id"]);
	$projet->societe->fetch($projet->societe->id);

	$head=project_prepare_head($projet);
	dolibarr_fiche_head($head, 'project', $langs->trans("Project"));


	if ($_GET["action"] == 'delete')
	{
		$htmls = new Form($db);
		$htmls->form_confirm("fiche.php?id=".$_GET["id"],$langs->trans("DeleteAProject"),$langs->trans("ConfirmDeleteAProject"),"confirm_delete");
		print "<br>";
	}

	if ($_GET["action"] == 'edit')
	{
		print '<form method="post" action="fiche.php">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="'.$_GET["id"].'">';

		print '<table class="border" width="100%">';

		// Ref
		print '<tr><td>'.$langs->trans("Ref").'</td><td><input size="8" name="ref" value="'.$projet->ref.'"></td></tr>';

		// Label
		print '<tr><td>'.$langs->trans("Label").'</td><td><input size="30" name="title" value="'.$projet->title.'"></td></tr>';

		print '<tr><td>'.$langs->trans("Company").'</td><td>'.$projet->societe->getNomUrl(1).'</td></tr>';
		print '<tr><td align="center" colspan="2"><input name="update" class="button" type="submit" value="'.$langs->trans("Modify").'"> &nbsp; <input type="submit" class="button" name="cancel" Value="'.$langs->trans("Cancel").'"></td></tr>';
		print '</table>';
		print '</form>';
	}
	else
	{
		print '<table class="border" width="100%">';

		print '<tr><td>'.$langs->trans("Ref").'</td><td>'.$projet->ref.'</td></tr>';
		print '<tr><td>'.$langs->trans("Label").'</td><td>'.$projet->title.'</td></tr>';

		print '<tr><td>'.$langs->trans("Company").'</td><td>'.$projet->societe->getNomUrl(1).'</td></tr>';
		print '</table>';
	}

	print '</div>';

	/*
	* Boutons actions
	*/
	print '<div class="tabsAction">';

	if ($_GET["action"] != "edit")
	{
		if ($user->rights->projet->creer)
		{
			print '<a class="butAction" href="fiche.php?id='.$projet->id.'&amp;action=edit">'.$langs->trans("Edit").'</a>';
		}
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
