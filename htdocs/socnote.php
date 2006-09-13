<?php
/* Copyright (C) 2001-2003,2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006      Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2006      Regis Houssin        <regis.houssin@cap-networks.com>
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
    \file       htdocs/socnote.php
    \brief      Fichier onglet notes li�es � la soci�t�
    \ingroup    societe
    \version    $Revision$
*/
 
require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/company.lib.php");

$action = isset($_GET["action"])?$_GET["action"]:$_POST["action"];

$langs->load("companies");

$user->getrights('commercial');

// Protection quand utilisateur externe
$socid = isset($_GET["socid"])?$_GET["socid"]:$_POST["socid"];
if ($user->societe_id > 0)
{
    $socid = $user->societe_id;
}

// Protection restriction commercial
if (!$user->rights->commercial->client->voir && $socid && !$user->societe_id > 0)
{
        $sql = "SELECT sc.fk_soc, s.client";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe_commerciaux as sc, ".MAIN_DB_PREFIX."societe as s";
        $sql .= " WHERE sc.fk_soc = ".$socid." AND sc.fk_user = ".$user->id." AND s.client = 1";

        if ( $db->query($sql) )
        {
          if ( $db->num_rows() == 0) accessforbidden();
        }
}


if ($_POST["action"] == 'add')
{
  $sql = "UPDATE ".MAIN_DB_PREFIX."societe SET note='".addslashes($_POST["note"])."' WHERE idp=".$_POST["socid"];
  $result = $db->query($sql);

  $_GET["socid"]=$_POST["socid"];   // Pour retour sur fiche
  $socid = $_GET["socid"];
}


/*
 *
 */

llxHeader();

if ($socid > 0)
{
    $societe = new Societe($db, $socid);
    $societe->fetch($socid);
    
	/*
	 * Affichage onglets
	 */
	$head = societe_prepare_head($societe);

	dolibarr_fiche_head($head, 'note', $societe->nom);
	
	
	print "<form method=\"post\" action=\"socnote.php\">";
	
	print '<table class="border" width="100%">';

    print '<tr><td width="20%">'.$langs->trans('Name').'</td><td colspan="3">'.$societe->nom.'</td></tr>';

    print '<tr><td>'.$langs->trans('Prefix').'</td><td colspan="3">'.$societe->prefix_comm.'</td></tr>';

    if ($societe->client) {
        print '<tr><td>';
        print $langs->trans('CustomerCode').'</td><td colspan="3">';
        print $societe->code_client;
        if ($societe->check_codeclient() <> 0) print ' '.$langs->trans("WrongCustomerCode");
        print '</td></tr>';
    }

    if ($societe->fournisseur) {
        print '<tr><td>';
        print $langs->trans('SupplierCode').'</td><td colspan="3">';
        print $societe->code_fournisseur;
        if ($societe->check_codefournisseur() <> 0) print ' '.$langs->trans("WrongSupplierCode");
        print '</td></tr>';
    }

	print '<tr><td valign="top">'.$langs->trans("Note").'</td>';
	print '<td valign="top">';
	if ($action == 'edit' && $user->rights->societe->creer)
	{
		print "<input type=\"hidden\" name=\"action\" value=\"add\">";
		print "<input type=\"hidden\" name=\"socid\" value=\"".$societe->id."\">";

		if ($conf->fckeditor->enabled && $conf->global->FCKEDITOR_ENABLE_SOCIETE)
	    {
		    // Editeur wysiwyg
			require_once(DOL_DOCUMENT_ROOT."/lib/doleditor.class.php");
			$doleditor=new DolEditor('note',$societe->note,280,'dolibarr_notes','In',true);
			$doleditor->Create();
	    }
	    else
	    {
			print '<textarea name="note" cols="70" rows="10">'.$societe->note.'</textarea>';
	    }
	}
	else
	{
		print nl2br($societe->note);
	}
	print "</td></tr>";

	if ($action == 'edit')
	{
		print '<tr><td colspan="2" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td></tr>';
	}
	
	print "</table>";

	print '</form>';
}

print '</div>';


/*
 * Boutons actions
 */
if ($_GET["action"] == '')
{
    print '<div class="tabsAction">';

    if ($user->rights->societe->creer)
    {
        print '<a class="butAction" href="'.DOL_URL_ROOT.'/socnote.php?socid='.$societe->id.'&amp;action=edit">'.$langs->trans("Edit").'</a>';
    }
    
    print '</div>';
}


$db->close();

llxFooter('$Date$ - $Revision$');
?>
