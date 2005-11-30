<?php
/* Copyright (C) 2005 Patrick Rouillon     <patrick@rouillon.net>
 * Copyright (C) 2005 Destailleur Laurent  <eldy@users.sourceforge.net>
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
        \file       htdocs/compta/contact.php
        \ingroup    facture
        \brief      Onglet de gestion des contacts des factures
        \version    $Revision$
*/

require ("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/contact.class.php");

$langs->load("facture");
// $langs->load("orders");
$langs->load("companies");

$user->getrights('facture');

if (!$user->rights->facture->lire)
	accessforbidden();

// les methodes locales
/**
  *    \brief      Retourne la liste d�roulante des soci�t�s
  *    \param      selected        Societe pr�s�lectionn�e
  *    \param      htmlname        Nom champ formulaire
  */
function select_societes_for_newconcat($facture, $selected = '', $htmlname = 'newcompany')
{
		// On recherche les societes
	$sql = "SELECT s.idp, s.nom FROM";
	$sql .= " ".MAIN_DB_PREFIX."societe as s";
//	if ($filter) $sql .= " WHERE $filter";
	$sql .= " ORDER BY nom ASC";

	$resql = $facture->db->query($sql);
	if ($resql)
	{
		$javaScript = "window.location='./contact.php?facid=".$facture->id."&amp;".$htmlname."=' + form.".$htmlname.".options[form.".$htmlname.".selectedIndex].value;";
		print '<select class="flat" name="'.$htmlname.'" onChange="'.$javaScript.'">';
		$num = $facture->db->num_rows($resql);
		$i = 0;
		if ($num)
		{
			while ($i < $num)
			{
				$obj = $facture->db->fetch_object($resql);
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
		dolibarr_print_error($facture->db);
	}
}

/**
 * 
 */
function select_type_contact($facture, $defValue, $htmlname = 'type', $source)
{
	$lesTypes = $facture->liste_type_contact($source);
	print '<select size="0" name="'.$htmlname.'">';
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
	$socidp = $user->societe_id;
}

/*
 * Ajout d'un nouveau contact
 */

if ($_POST["action"] == 'addcontact' && $user->rights->facture->creer)
{
	
	$result = 0;
	$facture = new Facture($db);
	$result = $facture->fetch($_GET["facid"]);

    if ($result > 0 && $_GET["facid"] > 0)
    {
  		$result = $facture->add_contact($_POST["contactid"], $_POST["type"], $_POST["source"]);
    }
    
	if ($result >= 0)
	{
		Header("Location: contact.php?facid=".$facture->id);
		exit;
	} else
	{
		$mesg = '<div class="error">'.$facture->error.'</div>';
	}
}
// modification d'un contact. On enregistre le type
if ($_POST["action"] == 'updateligne' && $user->rights->facture->creer)
{
	$facture = new Facture($db);
	if ($facture->fetch($_GET["facid"]))
	{
		$contact = $facture->detail_contact($_POST["elrowid"]);
		$type = $_POST["type"];
		$statut = $contact->statut;

		$result = $facture->update_contact($_POST["elrowid"], $statut, $type);
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
if ($_GET["action"] == 'swapstatut' && $user->rights->facture->creer)
{
	$facture = new Facture($db);
	if ($facture->fetch($_GET["facid"]))
	{
		$contact = $facture->detail_contact($_GET["ligne"]);
		$id_type_contact = $contact->fk_c_type_contact;
		$statut = ($contact->statut == 4) ? 5 : 4;

		$result = $facture->update_contact($_GET["ligne"], $statut, $id_type_contact);
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
if ($_GET["action"] == 'deleteline' && $user->rights->facture->creer)
{
	$facture = new Facture($db);
	$facture->fetch($_GET["facid"]);
	$result = $facture->delete_contact($_GET["lineid"]);

	if ($result >= 0)
	{
		Header("Location: contact.php?facid=".$facture->id);
		exit;
	}
	else {
		dolibarr_print_error($db);
	}
}


llxHeader('', $langs->trans("Bill"), "Facture");

$html = new Form($db);

/* *************************************************************************** */
/*                                                                             */
/* Mode vue et edition                                                         */
/*                                                                             */
/* *************************************************************************** */
if ( isset($mesg))
	print $mesg;
$id = $_GET["facid"];
if ($id > 0)
{
		$facture = New Facture($db);
		if ( $facture->fetch($_GET['facid'], $user->societe_id) > 0)
		{
			$soc = new Societe($db, $facture->socidp);
			$soc->fetch($facture->socidp);

			$head = facture_prepare_head($facture);

			dolibarr_fiche_head($head, 1, $langs->trans('Bill').' : '.$facture->ref);

		/*
		 *   Facture synthese pour rappel
		 */
		print '<table class="border" width="100%">';

		// Reference du facture
		print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td colspan="3">';
		print $facture->ref;
		print "</td></tr>";

		// Customer
		if ( is_null($facture->client) )
			$facture->fetch_client();
			
		print "<tr><td>".$langs->trans("Customer")."</td>";
		print '<td colspan="3">';
		print '<b><a href="'.DOL_URL_ROOT.'/comm/fiche.php?socid='.$facture->client->id.'">'.$facture->client->nom.'</a></b></td></tr>';
		print "</table>";

		print '</div>';

		/*
		 * Lignes de contacts
		 */
		echo '<br><table class="noborder" width="100%">';

		/*
		 * Ajouter une ligne de contact
		 * Non affich� en mode modification de ligne
		 * ou si facture valid�e.
		 */
		if ($facture->statut <= 0 && $_GET["action"] != 'editline' && $user->rights->facture->creer)
		{
			print '<tr class="liste_titre">';
			print '<td>'.$langs->trans("Source").'</td>';
			print '<td>'.$langs->trans("Company").'</td>';
			print '<td>'.$langs->trans("Contacts").'</td>';
			print '<td>'.$langs->trans("ContactType").'</td>';
			print '<td colspan="3">&nbsp;</td>';
			print "</tr>\n";

			$var = false;

			print '<form action="contact.php?facid='.$id.'" method="post">';
			print '<input type="hidden" name="action" value="addcontact">';
			print '<input type="hidden" name="source" value="internal">';
			print '<input type="hidden" name="id" value="'.$id.'">';

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
			select_type_contact($facture, '', 'type','internal');
			print '</td>';
			print '<td align="right" colspan="3" ><input type="submit" class="button" value="'.$langs->trans("Add").'"></td>';
			print '</tr>';

            print '</form>';

			print '<form action="contact.php?facid='.$id.'" method="post">';
			print '<input type="hidden" name="action" value="addcontact">';
			print '<input type="hidden" name="source" value="external">';
			print '<input type="hidden" name="id" value="'.$id.'">';

            // Ligne ajout pour contact externe
			$var=!$var;
			print "<tr $bc[$var]>";
			
			print '<td>';
			print $langs->trans("External");
            print '</td>';			
			
			print '<td colspan="1">';
			$selectedCompany = isset($_GET["newcompany"])?$_GET["newcompany"]:$facture->client->id;
			$selectedCompany = select_societes_for_newconcat($facture, $selectedCompany, $htmlname = 'newcompany');
			print '</td>';

			print '<td colspan="1">';
			$html->select_contacts($selectedCompany, $selected = '', $htmlname = 'contactid');
			print '</td>';
			print '<td>';
			select_type_contact($facture, '', 'type','external');
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
    			$tab = $facture->liste_contact(-1,$source);
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
				if ($facture->statut >= 0)
					print '<a href="contact.php?facid='.$facture->id.'&amp;action=swapstatut&amp;ligne='.$tab[$i]['rowid'].'">';
				print img_statut($tab[$i]['status']);

				if ($facture->statut >= 0)
					print '</a>';
				print '</td>';

				// Icon update et delete (statut contrat 0=brouillon,1=valid�,2=ferm�)
				print '<td align="center" nowrap>';
				if ($facture->statut == 0 && $user->rights->facture->creer)
				{
					print '&nbsp;';
					print '<a href="contact.php?facid='.$facture->id.'&amp;action=deleteline&amp;lineid='.$tab[$i]['rowid'].'">';
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
		// Contrat non trouv�
		print "Contrat inexistant ou acc�s refus�";
	}
}

$db->close();

llxFooter('$Date$');
?>