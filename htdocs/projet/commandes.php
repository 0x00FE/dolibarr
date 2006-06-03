<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
        \file       htdocs/projet/commandes.php
        \ingroup    projet commande
		\brief      Page des commandes par projet
		\version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/propal.class.php");
require_once(DOL_DOCUMENT_ROOT."/facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/commande/commande.class.php");

$langs->load("projects");
$langs->load("companies");
$langs->load("orders");

$user->getrights('projet');

if (!$user->rights->projet->lire) accessforbidden();

// S�curit� acc�s client
$projetid='';
if ($_GET["id"]) { $projetid=$_GET["id"]; }

if ($projetid == '') accessforbidden();

if ($user->societe_id > 0) 
{
  $socidp = $user->societe_id;
}

// Protection restriction commercial
if ($projetid)
{
	$sql = "SELECT p.rowid, p.fk_soc";
	$sql.= " FROM ".MAIN_DB_PREFIX."projet as p";
	if (!$user->rights->commercial->client->voir) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc ";
	$sql.= " WHERE p.rowid = ".$projetid;
	if (!$user->rights->commercial->client->voir) $sql .= " AND p.fk_soc = sc.fk_soc AND sc.fk_user = ".$user->id;
	if ($socidp) $sql .= " AND p.fk_soc = ".$socidp;
	
	if ( $db->query($sql) )
	{
		if ( $db->num_rows() == 0) accessforbidden();
	}
}


llxHeader("","../");

$projet = new Project($db);
$projet->fetch($_GET["id"]);

$h=0;
$head[$h][0] = DOL_URL_ROOT.'/projet/fiche.php?id='.$projet->id;
$head[$h][1] = $langs->trans("Project");
$h++;

$head[$h][0] = DOL_URL_ROOT.'/projet/tasks/fiche.php?id='.$projet->id;
$head[$h][1] = $langs->trans("Tasks");
$h++;

if ($conf->propal->enabled) {
  $langs->load("propal");
  $head[$h][0] = DOL_URL_ROOT.'/projet/propal.php?id='.$projet->id;
  $head[$h][1] = $langs->trans("Proposals");
  $h++;
}  

if ($conf->commande->enabled) {
  $langs->load("orders");
  $head[$h][0] = DOL_URL_ROOT.'/projet/commandes.php?id='.$projet->id;
  $head[$h][1] = $langs->trans("Orders");
  $hselected=$h;
  $h++;
}

if ($conf->facture->enabled) {
  $langs->load("bills");
  $head[$h][0] = DOL_URL_ROOT.'/projet/facture.php?id='.$projet->id;
  $head[$h][1] = $langs->trans("Bills");
  $h++;
}
 
dolibarr_fiche_head($head, $hselected, $langs->trans("Project").": ".$projet->ref);


$projet->societe->fetch($projet->societe->id);

print '<table class="border" width="100%">';

print '<tr><td>'.$langs->trans("Ref").'</td><td>'.$projet->ref.'</td></tr>';
print '<tr><td>'.$langs->trans("Label").'</td><td>'.$projet->title.'</td></tr>';      

print '<tr><td>'.$langs->trans("Company").'</td><td>'.$projet->societe->getNomUrl(1).'</td></tr>';

print '</table>';

print '</div>';

/*
 * Barre d'action
 *
 */
 print '<div class="tabsAction">';

 if ($conf->commande->enabled && $user->rights->commande->creer)
 {
     $langs->load("orders");
     print '<a class="butAction" href="'.DOL_URL_ROOT.'/commande/fiche.php?socidp='.$projet->societe->id.'&amp;action=create&amp;projetid='.$projet->id.'">'.$langs->trans("AddOrder").'</a>';
 }
 print '</div>';

/*
 * Commandes
 *
 */
$commandes = $projet->get_commande_list();
$total = 0 ;
if (sizeof($commandes)>0 && is_array($commandes))
{
    print '<br>';
    
    print_titre($langs->trans("ListProposalsAssociatedProject"));
    print '<table class="noborder" width="100%">';
    
    print '<tr class="liste_titre">';
    print '<td width="15%">'.$langs->trans("Ref").'</td><td width="25%">Date</td><td align="right">'.$langs->trans("Amount").'</td><td>&nbsp;</td></tr>';
    
    for ($i = 0; $i<sizeof($commandes);$i++)
    {
        $commande = new Commande($db);
        $commande->fetch($commandes[$i]);
    
        $var=!$var;
        print "<tr $bc[$var]>";
        print "<td><a href=\"../commande/fiche.php?id=$commande->id\">$commande->ref</a></td>\n";
        print '<td>'.strftime("%d %B %Y",$commande->date).'</td>';
        print '<td align="right">'.price($commande->total_ht).'</td><td>&nbsp;</td></tr>';
    
        $total = $total + $commande->total_ht;
    }
    
    print '<tr class="liste_total"><td colspan="2">'.$i.' '.$langs->trans("Orders").'</td>';
    print '<td align="right">'.$langs->trans("TotalHT").': '.price($total).'</td>';
    print '<td align="left">'.$langs->trans("Currency".$conf->monnaie).'</td></tr>';
    print "</table>";
}    


// Juste pour �viter bug IE qui r�organise mal div pr�c�dents si celui-ci absent
print '<div class="tabsAction">';
print '</div>';

$db->close();

llxFooter('$Date$ - $Revision$');
?>
