<?php
/* Copyright (C) 2005      Patrick Rouillon     <patrick@rouillon.net>
 * Copyright (C) 2005-2006 Destailleur Laurent  <eldy@users.sourceforge.net>
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
        \file       htdocs/comm/propal/contact.php
        \ingroup    propal
        \brief      Onglet de gestion des contacts de propal
        \version    $Revision$
*/

require ("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/propal.class.php");
require_once(DOL_DOCUMENT_ROOT."/contact.class.php");
require_once(DOL_DOCUMENT_ROOT."/lib/propal.lib.php");

$langs->load("facture");
$langs->load("orders");
$langs->load("sendings");
$langs->load("companies");

$user->getrights('propale');
if (!$user->rights->propale->lire)
	accessforbidden();

// les methodes locales
/**
  *    \brief      Retourne la liste d�roulante des soci�t�s
  *    \param      selected        Societe pr�s�lectionn�e
  *    \param      htmlname        Nom champ formulaire
  */
function select_societes_for_newconcat($propal, $selected = '', $htmlname = 'newcompany'){
	// On recherche les societes
	$sql = "SELECT s.idp, s.nom FROM";
	$sql .= " ".MAIN_DB_PREFIX."societe as s";
//	if ($filter) $sql .= " WHERE $filter";
	$sql .= " ORDER BY nom ASC";

	$resql = $propal->db->query($sql);
	if ($resql)
	{
		$javaScript = "window.location='./contact.php?propalid=".$propal->id."&amp;".$htmlname."=' + form.".$htmlname.".options[form.".$htmlname.".selectedIndex].value;";
		print '<select class="flat" name="'.$htmlname.'" onChange="'.$javaScript.'">';
		$num = $propal->db->num_rows($resql);
		$i = 0;
		if ($num)
		{
			while ($i < $num)
			{
				$obj = $propal->db->fetch_object($resql);
				if ($i == 0)
					$firstCompany = $obj->idp;
				if ($selected > 0 && $selected == $obj->idp)
				{
					print '<option value="'.$obj->idp.'" selected="true">'.dolibarr_trunc($obj->nom,24).'</option>';
					$firstCompany = $obj->idp;
				} else
				{
					print '<option value="'.$obj->idp.'">'.dolibarr_trunc($obj->nom,24).'</option>';
				}
				$i ++;
			}
		}
		print "</select>\n";
		return $firstCompany;
	} else
	{
		dolibarr_print_error($propal->db);
	}
}

/**
 * 
 */
function select_type_contact($propal, $defValue, $htmlname = 'type', $source)
{
	$lesTypes = $propal->liste_type_contact($source);
	print '<select class="flat" name="'.$htmlname.'">';
	foreach($lesTypes as $key=>$value)
	{
		print '<option value="'.$key.'">'.$value.'</option>';
	}
	print "</select>\n";
}


// S�curit� acc�s client
if ($user->societe_id > 0)
{
	$action = '';
	$socid = $user->societe_id;
}

/*
 * Ajout d'un nouveau contact
 */

if ($_POST["action"] == 'addcontact' && $user->rights->propale->creer)
{
	
	$result = 0;
	$propal = new Propal($db);
	$result = $propal->fetch($_GET["propalid"]);

    if ($result > 0 && $_GET["propalid"] > 0)
    {
  		$result = $propal->add_contact($_POST["contactid"], $_POST["type"], $_POST["source"]);
    }
    
	if ($result >= 0)
	{
		Header("Location: contact.php?propalid=".$propal->id);
		exit;
	} else
	{
		$mesg = '<div class="error">'.$propal->error.'</div>';
	}
}
// modification d'un contact. On enregistre le type
if ($_POST["action"] == 'updateligne' && $user->rights->propale->creer)
{
	$propal = new Propal($db);
	if ($propal->fetch($_GET["propalid"]))
	{
		$contact = $propal->detail_contact($_POST["elrowid"]);
		$type = $_POST["type"];
		$statut = $contact->statut;

		$result = $propal->update_contact($_POST["elrowid"], $statut, $type);
		if ($result >= 0)
		{
			$db->commit();
		} else
		{
			dolibarr_print_error($db, "result=$result");
			$db->rollback();
		}
	} else
	{
		dolibarr_print_error($db);
	}
}

// bascule du statut d'un contact
if ($_GET["action"] == 'swapstatut' && $user->rights->propale->creer)
{
	$propal = new Propal($db);
	if ($propal->fetch($_GET["propalid"]))
	{
		$contact = $propal->detail_contact($_GET["ligne"]);
		$id_type_contact = $contact->fk_c_type_contact;
		$statut = ($contact->statut == 4) ? 5 : 4;

		$result = $propal->update_contact($_GET["ligne"], $statut, $id_type_contact);
		if ($result >= 0)
		{
			$db->commit();
		} else
		{
			dolibarr_print_error($db, "result=$result");
			$db->rollback();
		}
	} else
	{
		dolibarr_print_error($db);
	}
}

// Efface un contact
if ($_GET["action"] == 'deleteline' && $user->rights->propale->creer)
{
	$propal = new Propal($db);
	$propal->fetch($_GET["propalid"]);
	$result = $propal->delete_contact($_GET["lineid"]);

	if ($result >= 0)
	{
		Header("Location: contact.php?propalid=".$propal->id);
		exit;
	}
	else {
		dolibarr_print_error($db);
	}
}


llxHeader('', $langs->trans("Proposal"), "Propal");

$html = new Form($db);

/* *************************************************************************** */
/*                                                                             */
/* Mode vue et edition                                                         */
/*                                                                             */
/* *************************************************************************** */
if ( isset($mesg))
	print $mesg;
$id = $_GET["propalid"];
if ($id > 0)
{
	$langs->trans("PropalCard");
	$propal = New Propal($db);
	if ( $propal->fetch($_GET['propalid'], $user->societe_id) > 0)
	{
		$soc = new Societe($db, $propal->socid);
		$soc->fetch($propal->socid);


		$head = propal_prepare_head($propal);
		dolibarr_fiche_head($head, 'contact', $langs->trans("Proposal"));


		/*
		*   Facture synthese pour rappel
		*/
		print '<table class="border" width="100%">';

		// Ref
		print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td colspan="3">';
		print $propal->ref_url;
		print "</td></tr>";

		// Customer
		if ( is_null($propal->client) )
			$propal->fetch_client();
			
		print "<tr><td>".$langs->trans("Company")."</td>";
		print '<td colspan="3">'.$propal->client->getNomUrl(1).'</td></tr>';
		print "</table>";

		print '</div>';

		/*
		* Lignes de contacts
		*/
		echo '<br><table class="noborder" width="100%">';

		/*
		* Ajouter une ligne de contact
		* Non affich� en mode modification de ligne
		*/
		if ($_GET["action"] != 'editline' && $user->rights->facture->creer)
		{
			print '<tr class="liste_titre">';
			print '<td>'.$langs->trans("Source").'</td>';
			print '<td>'.$langs->trans("Company").'</td>';
			print '<td>'.$langs->trans("Contacts").'</td>';
			print '<td>'.$langs->trans("ContactType").'</td>';
			print '<td colspan="3">&nbsp;</td>';
			print "</tr>\n";

			$var = false;

			print '<form action="contact.php?propalid='.$id.'" method="post">';
			print '<input type="hidden" name="action" value="addcontact">';
			print '<input type="hidden" name="source" value="internal">';
			print '<input type="hidden" name="propalid" value="'.$id.'">';

			// Ligne ajout pour contact interne
			print "<tr $bc[$var]>";

			print '<td>';
			print $langs->trans("Internal");
			print '</td>';

			print '<td colspan="1">';
			print $conf->global->MAIN_INFO_SOCIETE_NOM;
			print '</td>';

			print '<td colspan="1">';
			$html->select_users($user->id,'contactid');
			print '</td>';
			print '<td>';
			select_type_contact($propal, '', 'type','internal');
			print '</td>';
			print '<td align="right" colspan="3" ><input type="submit" class="button" value="'.$langs->trans("Add").'"></td>';
			print '</tr>';

			print '</form>';

			print '<form action="contact.php?propalid='.$id.'" method="post">';
			print '<input type="hidden" name="action" value="addcontact">';
			print '<input type="hidden" name="source" value="external">';
			print '<input type="hidden" name="propalid" value="'.$id.'">';

			// Ligne ajout pour contact externe
			$var=!$var;
			print "<tr $bc[$var]>";

			print '<td>';
			print $langs->trans("External");
			print '</td>';

			print '<td colspan="1">';
			$selectedCompany = isset($_GET["newcompany"])?$_GET["newcompany"]:$propal->client->id;
			$selectedCompany = select_societes_for_newconcat($propal, $selectedCompany, $htmlname = 'newcompany');
			print '</td>';

			print '<td colspan="1">';
			$html->select_contacts($selectedCompany, $selected = '', $htmlname = 'contactid');
			print '</td>';
			print '<td>';
			select_type_contact($propal, '', 'type','external');
			print '</td>';
			print '<td align="right" colspan="3" ><input type="submit" class="button" value="'.$langs->trans("Add").'"></td>';
			print '</tr>';

			print "</form>";

			print '<tr><td colspan="6">&nbsp;</td></tr>';
		}

		// Liste des contacts li�s
		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans("Source").'</td>';
		print '<td>'.$langs->trans("Company").'</td>';
		print '<td>'.$langs->trans("Contacts").'</td>';
		print '<td>'.$langs->trans("ContactType").'</td>';
		print '<td align="center">'.$langs->trans("Status").'</td>';
		print '<td colspan="2">&nbsp;</td>';
		print "</tr>\n";

		$societe = new Societe($db);
		$var = true;

		foreach(array('internal','external') as $source)
		{
			$tab = $propal->liste_contact(-1,$source);
			$num=sizeof($tab);

			$i = 0;
			while ($i < $num)
			{
				$var = !$var;

				print '<tr '.$bc[$var].' valign="top">';

				// Source
				print '<td align="left">';
				if ($tab[$i]['source']=='internal') print $langs->trans("Internal");
				if ($tab[$i]['source']=='external') print $langs->trans("External");
				print '</td>';

				// Societe
				print '<td align="left">';
				if ($tab[$i]['socid'] > 0)
				{
					print '<a href="'.DOL_URL_ROOT.'/soc.php?socid='.$tab[$i]['socid'].'">';
					print img_object($langs->trans("ShowCompany"),"company").' '.$societe->get_nom($tab[$i]['socid']);
					print '</a>';
				}
				if ($tab[$i]['socid'] < 0)
				{
					print $conf->global->MAIN_INFO_SOCIETE_NOM;
				}
				if (! $tab[$i]['socid'])
				{
					print '&nbsp;';
				}
				print '</td>';

				// Contact
				print '<td>';
				if ($tab[$i]['source']=='internal')
				{
					print '<a href="'.DOL_URL_ROOT.'/user/fiche.php?id='.$tab[$i]['id'].'">';
					print img_object($langs->trans("ShowUser"),"user").' '.$tab[$i]['nom'].'</a>';
				}
				if ($tab[$i]['source']=='external')
				{
					print '<a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$tab[$i]['id'].'">';
					print img_object($langs->trans("ShowContact"),"contact").' '.$tab[$i]['nom'].'</a>';
				}
				print '</td>';

				// Type de contact
				print '<td>'.$tab[$i]['libelle'].'</td>';

				// Statut
				print '<td align="center">';
				// Activation desativation du contact
				if ($propal->statut >= 0)
				print '<a href="contact.php?propalid='.$propal->id.'&amp;action=swapstatut&amp;ligne='.$tab[$i]['rowid'].'">';
				print img_statut($tab[$i]['status']);

				if ($propal->statut >= 0)
				print '</a>';
				print '</td>';

				// Icon update et delete
				print '<td align="center" nowrap>';
				if ($propal->statut < 5 && $user->rights->propale->creer)
				{
					print '&nbsp;';
					print '<a href="contact.php?propalid='.$propal->id.'&amp;action=deleteline&amp;lineid='.$tab[$i]['rowid'].'">';
					print img_delete();
					print '</a>';
				}
				print '</td>';

				print "</tr>\n";

				$i ++;
			}
			$db->free($result);
		}
		print "</table>";
	}
	else
	{
		// Contrat non trouv
		print "Contrat inexistant ou acc�s refus�";
	}
}

$db->close();

llxFooter('$Date$');
?>