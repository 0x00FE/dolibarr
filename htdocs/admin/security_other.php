<?php
/* Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
	    \file       htdocs/admin/security_other.php
        \ingroup    setup
        \brief      Page de configuration du module s�curit� autre
		\version    $Revision$
*/

require("./pre.inc.php");

$langs->load("users");
$langs->load("admin");

if (!$user->admin) accessforbidden();


/*
 * Actions
 */
if ($_GET["action"] == 'set_main_upload_doc')
{
	if (! dolibarr_set_const($db, 'MAIN_UPLOAD_DOC',$_POST["MAIN_UPLOAD_DOC"]))
	{
		dolibarr_print_error($db);
	}
	else
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
}



/*
 * Affichage onglet
 */

llxHeader();

print_fiche_titre($langs->trans("SecuritySetup"),'','setup');

print $langs->trans("MiscellanousDesc")."<br>\n";
print "<br>\n";


$h = 0;

$head[$h][0] = DOL_URL_ROOT."/admin/perms.php";
$head[$h][1] = $langs->trans("DefaultRights");
$h++;

$head[$h][0] = DOL_URL_ROOT."/admin/security.php";
$head[$h][1] = $langs->trans("Passwords");
$h++;

$head[$h][0] = DOL_URL_ROOT."/admin/security_other.php";
$head[$h][1] = $langs->trans("Miscellanous");
$hselected=$h;
$h++;

dolibarr_fiche_head($head, $hselected, $langs->trans("Security"));


$var=false;
$form = new Form($db);


print '<table width="100%" class="noborder">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '<td>&nbsp;</td>';
print "</tr>\n";


print '<form action="'.$_SERVER["PHP_SELF"].'?action=set_main_upload_doc" method="POST">';
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("MaxSizeForUploadedFiles").'.';
$max=@ini_get('upload_max_filesize');
if ($max) print ' '.$langs->trans("MustBeLowerThanPHPLimit",$max*1024,$langs->trans("Kb")).'.';
else print ' '.$langs->trans("NoMaxSizeByPHPLimit").'.';
print '</td>';
print '<td nowrap="1">';
print '<input class="flat" name="MAIN_UPLOAD_DOC" type="text" size="6" value="'.$conf->global->MAIN_UPLOAD_DOC.'"> '.$langs->trans("Kb");
print '</td>';
print '<td align="center">';
print '<input type="submit" class="button" name="button" value="'.$langs->trans("Modify").'">';
print '</td>';
print '</tr></form>';
print '</table>';

//print '<tr><td colspan="2" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td></tr>';

print '</div>';

$db->close();

llxFooter('$Date$ - $Revision$');
?>
