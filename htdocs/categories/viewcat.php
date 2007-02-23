<?php
/* Copyright (C) 2005      Matthieu Valleton    <mv@seeschloss.org>
 * Copyright (C) 2006-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/categories/edit.php
        \ingroup    categories
        \brief      Page de visualisation de categorie produit
        \version    $Revision$
*/

require "./pre.inc.php";

if ($_REQUEST['id'] == "")
{
  dolibarr_print_error('','Missing parameter id');
  exit();
}

// Securite
$user->getrights('categorie');
if (! $user->rights->categorie->lire)
{
	accessforbidden();
}

$mesg='';

$c = new Categorie($db);
$c->fetch($_REQUEST['id']);



/*
*	Actions
*/

if ($user->rights->categorie->supprimer && $_POST["action"] == 'confirm_delete' && $_POST['confirm'] == 'yes')
{
	if ($c->remove() >= 0)
	{
		header("Location: ".DOL_URL_ROOT.'/categories/index.php');
		exit;
	}
	else
	{
		$mesg='<div class="error">'.$c->error.'</div>';
	}
}



/*
 * Affichage fiche categorie
 */
llxHeader ("","",$langs->trans("Categories"));
$html=new Form($db);

if ($mesg) print $mesg.'<br>';


$h = 0;
$head = array();

$head[$h][0] = DOL_URL_ROOT.'/categories/viewcat.php?id='.$c->id;
$head[$h][1] = $langs->trans("Card");
$head[$h][2] = 'card';
$h++;

dolibarr_fiche_head($head, 'card', $langs->trans("Category"));


/*
* Confirmation suppression
*/
if ($_GET['action'] == 'delete')
{
	$html->form_confirm($_SERVER["PHP_SELF"].'?id='.$c->id,$langs->trans('DeleteCategory'),$langs->trans('ConfirmDeleteCategory'),'confirm_delete');
	print '<br />';
}

print '<table border="0" width="100%" class="border">';

print '<tr><td width="20%" class="notopnoleft">';
$ways = $c->print_all_ways ();
print $langs->trans("Ref").'</td><td>';
foreach ($ways as $way)
{
  print $way."<br />\n";
}
print '</td></tr>';


print '<tr><td width="20%" class="notopnoleft">';
print $langs->trans("Description").'</td><td>';
print nl2br($c->description);
print '</td></tr>';

print '<tr><td width="20%" class="notopnoleft">';
print $langs->trans("Status").'</td><td>';
print ($c->visible ? $langs->trans("Visible") : $langs->trans("Invisible"));
print '</td></tr>';

print '</table>';

print '</div>';


/*
 * Boutons actions
 */
print "<div class='tabsAction'>\n";

if ($user->rights->categorie->creer)
{
	print "<a class='butAction' href='edit.php?id=".$c->id."'>".$langs->trans("Edit")."</a>";
}

if ($user->rights->categorie->supprimer)
{
	print "<a class='butActionDelete' href='".DOL_URL_ROOT."/categories/viewcat.php?action=delete&amp;id=".$c->id."'>".$langs->trans("Delete")."</a>";
}

print "</div>";





$cats = $c->get_filles ();
if ($cats < 0)
{
	dolibarr_print_error();
}
else
{
	print "<br>";
	print "<table class='noborder' width='100%'>\n";
	print "<tr class='liste_titre'><td colspan='3'>".$langs->trans("SubCats")."</td></tr>\n";
	if (sizeof ($cats) > 0)
	{	
		$var=true;
		foreach ($cats as $cat)
		{
			$i++;
			$var=!$var;
			print "\t<tr ".$bc[$var].">\n";
			print "\t\t<td nowrap=\"nowrap\"><a href='viewcat.php?id=".$cat->id."'>".$cat->label."</a></td>\n";
			print "\t\t<td>".$cat->description."</td>\n";
			
			if ($cat->visible == 1)
			{
				print "\t\t<td>".$langs->trans("ContentsVisibleByAllShort")."</td>\n";
			}
			else
			{
				print "\t\t<td>".$langs->trans("ContentsNotVisibleByAllShort")."</td>\n";
			}
			
			print "\t</tr>\n";
		}
	}
	else
	{
		print "<tr><td>".$langs->trans("NoSubCat")."</td></tr>";
	}
	print "</table>\n";
}


$prods = $c->get_products ();
if ($prods < 0)
{
  dolibarr_print_error();
}
else
{
	print "<br>";
	print "<table class='noborder' width='100%'>\n";
	print "<tr class='liste_titre'><td colspan='3'>".$langs->trans("ProductsAndServices")."</td></tr>\n";
	
	if (sizeof ($prods) > 0)
	{
		$i = 0;
		$var=true;
		foreach ($prods as $prod)
		{
			$i++;
			$var=!$var;
			print "\t<tr ".$bc[$var].">\n";
			print '<td nowrap="nowrap" valign="top">';
			if ($prod->type == 1) print img_object($langs->trans("ShowService"),"service");
        	else print img_object($langs->trans("ShowProduct"),"product");
			print " <a href='".DOL_URL_ROOT."/product/fiche.php?id=".$prod->id."'>".$prod->ref."</a></td>\n";
			print '<td valign="top">'.$prod->libelle."</td>\n";
			print '<td valign="top">'.$prod->description."</td>\n";
			print "</tr>\n";
		}
	}
	else
	{
		print "<tr><td>".$langs->trans ("NoProd")."</td></tr>";
	}
	print "</table>\n";
}


$db->close();

llxFooter('$Date$ - $Revision$');
?>
