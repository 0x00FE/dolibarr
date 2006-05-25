<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville  <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C)      2005 Marc Barilley / Ocebo <marc@ocebo.com>
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
        \file       htdocs/commande/liste.php
        \ingroup    commande
        \brief      Page liste des commandes
        \version    $Revision$
*/


require('./pre.inc.php');

$langs->load('orders');

if (!$user->rights->commande->lire)
	accessforbidden();

$sref=isset($_GET['sref'])?$_GET['sref']:$_POST['sref'];
$sref_client=isset($_GET['sref_client'])?$_GET['sref_client']:(isset($_POST['sref_client'])?$_POST['sref_client']:'');
$snom=isset($_GET['snom'])?$_GET['snom']:$_POST['snom'];
$sall=isset($_GET['sall'])?$_GET['sall']:$_POST['sall'];

// S�curit� acc�s client
$socidp = $_GET['socidp'];
if ($user->societe_id > 0)
{
	$action = '';
	$socidp = $user->societe_id;
}


/*
 * Affichage page
 */
 
llxHeader();

$begin=$_GET['begin'];
$sortorder=$_GET['sortorder'];
$sortfield=$_GET['sortfield'];

if (! $sortfield) $sortfield='c.rowid';
if (! $sortorder) $sortorder='DESC';

$limit = $conf->liste_limit;
$offset = $limit * $_GET['page'] ;

$sql = 'SELECT s.nom, s.idp, c.rowid, c.ref, c.total_ht,';
$sql.= ' '.$db->pdate('c.date_commande').' as date_commande, c.fk_statut, c.facture as facturee';
if (!$user->rights->commercial->client->voir && !$socidp) $sql .= ", sc.fk_soc, sc.fk_user";
$sql.= ' FROM '.MAIN_DB_PREFIX.'societe as s, '.MAIN_DB_PREFIX.'commande as c';
if (!$user->rights->commercial->client->voir && !$socidp) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
$sql.= ' WHERE c.fk_soc = s.idp';
if (!$user->rights->commercial->client->voir && !$socidp) //restriction
{
  $sql .= " AND s.idp = sc.fk_soc AND sc.fk_user = " .$user->id;
}
if ($sref)
{
	$sql .= " AND c.ref like '%".addslashes($sref)."%'";
}
if ($sall)
{
	$sql .= " AND (c.ref like '%".addslashes($sall)."%' OR c.note like '%".addslashes($sall)."%')";
}
if ($socidp)
{
	$sql .= ' AND s.idp = '.$socidp;
}
if ($_GET['month'] > 0)
{
	$sql .= " AND date_format(c.date_commande, '%Y-%m') = '$year-$month'";
}
if ($_GET['year'] > 0)
{
	$sql .= " AND date_format(c.date_commande, '%Y') = $year";
}
if (isset($_GET['status']))
{
	$sql .= " AND fk_statut = ".$_GET['status'];
}
if (isset($_GET['afacturer']) && $_GET['afacturer'] == 1)
{
	$sql .= ' AND c.facture = 0';
}
if (strlen($_POST['sf_ref']) > 0)
{
	$sql .= " AND c.ref like '%".addslashes($_POST['sf_ref']) . "%'";
}
if (!empty($snom))
{
	$sql .= ' AND s.nom like \'%'.addslashes($snom).'%\'';
}
if (!empty($sref_client))
{
	$sql .= ' AND c.ref_client like \'%'.addslashes($sref_client).'%\'';
}

$sql .= ' ORDER BY '.$sortfield.' '.$sortorder;
$sql .= $db->plimit($limit + 1,$offset);

$resql = $db->query($sql);

if ($resql)
{
	if ($socidp)
	{
		$soc = new Societe($db);
		$soc->fetch($socidp);
		$title = $langs->trans('ListOfOrders') . ' - '.$soc->nom;
	}
	else
	{
		$title = $langs->trans('ListOfOrders');
	}
	if ($_GET['status'] == 3)
		$title.=' - '.$langs->trans('StatusOrderToBill');
	$num = $db->num_rows($resql);
	print_barre_liste($title, $_GET['page'], 'liste.php','&amp;socidp='.$socidp,$sortfield,$sortorder,'',$num);
	$i = 0;
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans('Ref'),'liste.php','c.ref','','&amp;socidp='.$socidp,'width="25%"',$sortfield);
	print_liste_field_titre($langs->trans('Company'),'liste.php','s.nom','','&amp;socidp='.$socidp,'width="30%"',$sortfield);
	print_liste_field_titre($langs->trans('RefCdeClient'),'liste.php','c.ref_client','','&amp;socidp='.$socidp,'width="15%"',$sortfield);
	print_liste_field_titre($langs->trans('Date'),'liste.php','c.date_commande','','&amp;socidp='.$socidp, 'width="20%" align="right" colspan="2"',$sortfield);
	print_liste_field_titre($langs->trans('Status'),'liste.php','c.fk_statut','','&amp;socidp='.$socidp,'width="10%" align="center"',$sortfield);
	print '</tr>';
	// Lignes des champs de filtre
	print '<form method="get" action="liste.php">';
	print '<tr class="liste_titre">';
	print '<td class="liste_titre" valign="right">';
	print '<input class="flat" size="10" type="text" name="sref" value="'.$sref.'">';
	print '</td><td class="liste_titre" align="left">';
	print '<input class="flat" type="text" name="snom" value="'.$snom.'">';
	print '</td><td class="liste_titre" align="left">';
	print '<input class="flat" type="text" size="10" name="sref_client" value="'.$sref_client.'">';
	print '</td><td class="liste_titre">&nbsp;';
	print '</td><td class="liste_titre">&nbsp;';
	print '</td><td align="right" class="liste_titre">';
	print '<input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" alt="'.$langs->trans('Search').'">';
	print '</td></tr>';
	print '</form>';
	$var=True;
	$generic_commande = new Commande($db);
	while ($i < min($num,$limit))
	{
		$objp = $db->fetch_object($resql);
		$var=!$var;
		print '<tr '.$bc[$var].'>';
		print '<td><a href="fiche.php?id='.$objp->rowid.'">'.img_object($langs->trans('ShowOrder'),'order').' '.$objp->ref.'</a></td>';
		print '<td><a href="../comm/fiche.php?socid='.$objp->idp.'">'.img_object($langs->trans('ShowCompany'),'company').' '.$objp->nom.'</a></td>';
		print '<td>'.$objp->ref_client.'</td>';
		print '<td>&nbsp;</td>';
		print '<td align="right">';
		$y = strftime('%Y',$objp->date_commande);
		$m = strftime('%m',$objp->date_commande);
		print strftime('%d',$objp->date_commande);
		print ' <a href="liste.php?year='.$y.'&amp;month='.$m.'">';
		print strftime('%B',$objp->date_commande).'</a>';
		print ' <a href="liste.php?year='.$y.'">';
		print strftime('%Y',$objp->date_commande).'</a>';
		if (($objp->fk_statut > 0) && ($objp->fk_statut < 3) && $objp->date_commande < (time() - $conf->commande->warning_delay)) print img_picto($langs->trans("Late"),"warning");
		print '</td>';
		print '<td align="right">'.$generic_commande->LibStatut($objp->fk_statut,$objp->facturee,5).'</td>';
		print '</tr>';
		$total = $total + $objp->price;
		$subtotal = $subtotal + $objp->price;
		$i++;
	}
	print '</table>';
	$db->free($resql);
}
else
{
	print dolibarr_print_error($db);
}

$db->close();

llxFooter('$Date$ - $Revision$');
?>
