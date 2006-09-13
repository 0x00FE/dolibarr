<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Regis Houssin        <regis.houssin@cap-networks.com>
 * Copyright (C) 2006      Laurent Destailleur  <eldy@users.sourceforge.net>
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
		\file       htdocs/projet/activity/index.php
		\ingroup    projet
		\brief      Page activite du module projet
		\version    $Revision$
*/

require("./pre.inc.php");

if (!$user->rights->projet->lire) accessforbidden();

/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socid = $user->societe_id;
}

llxHeader("",$langs->trans("Activity"));

$now = time();

print_fiche_titre($langs->trans("Activity"));


print '<table border="0" width="100%" class="notopnoleftnoright">';
print '<tr><td width="30%" valign="top" class="notopnoleft">';

/*
 *
 * Affichage de la liste des projets
 * 
 */
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print_liste_field_titre($langs->trans("Project"),"index.php","s.nom","","","",$sortfield);
print '<td align="center">'.$langs->trans("NbOpenTasks").'</td>';
print "</tr>\n";

$sql = "SELECT p.title, p.rowid, count(t.rowid)";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user";
$sql .= " FROM ".MAIN_DB_PREFIX."projet as p";
$sql .= " , ".MAIN_DB_PREFIX."projet_task as t";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
$sql .= " WHERE t.fk_projet = p.rowid";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= " AND p.fk_soc = sc.fk_soc AND sc.fk_user = " .$user->id;

$sql .= " GROUP BY p.rowid";

$var=true;
$resql = $db->query($sql);
if ( $resql )
{
  $num = $db->num_rows($resql);
  $i = 0;

  while ($i < $num)
    {
      $row = $db->fetch_row( $resql);
      $var=!$var;
      print "<tr $bc[$var]>";
      print '<td><a href="'.DOL_URL_ROOT.'/projet/tasks/fiche.php?id='.$row[1].'">'.$row[0].'</a></td>';
      print '<td align="center">'.$row[2].'</td>';
      print "</tr>\n";
    
      $i++;
    }
  
  $db->free($resql);
}
else
{
  dolibarr_print_error($db);
}
print "</table>";

print '</td><td width="70%" valign="top" class="notopnoleft">';

/* Affichage de la liste des projets du mois */
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width="50%">Activit� sur les projets cette semaine</td>';
print '<td width="50%" align="center">Temps</td>';
print "</tr>\n";

$sql = "SELECT p.title, p.rowid, sum(tt.task_duration)";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user";
$sql .= " FROM ".MAIN_DB_PREFIX."projet as p";
$sql .= " , ".MAIN_DB_PREFIX."projet_task as t";
$sql .= " , ".MAIN_DB_PREFIX."projet_task_time as tt";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
$sql .= " WHERE t.fk_projet = p.rowid";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= " AND p.fk_soc = sc.fk_soc AND sc.fk_user = " .$user->id;
$sql .= " AND tt.fk_task = t.rowid";
$sql .= " AND week(task_date) = ".strftime("%W",time());
$sql .= " GROUP BY p.rowid";

$var=true;
$resql = $db->query($sql);
if ( $resql )
{
  $num = $db->num_rows($resql);
  $i = 0;

  while ($i < $num)
    {
      $row = $db->fetch_row( $resql);
      $var=!$var;
      print "<tr $bc[$var]>";
      print '<td><a href="'.DOL_URL_ROOT.'/projet/tasks/fiche.php?id='.$row[1].'">'.$row[0].'</a></td>';
      print '<td align="center">'.$row[2].'</td>';
      print "</tr>\n";    
      $i++;
    }
  
  $db->free($resql);
}
else
{
  dolibarr_print_error($db);
}
print "</table><br />";

/* Affichage de la liste des projets du mois */
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width="50%">'.$langs->trans("Project").' ce mois : '.strftime("%B %Y", $now).'</td>';
print '<td width="50%" align="center">Nb heures</td>';
print "</tr>\n";

$sql = "SELECT p.title, p.rowid, sum(tt.task_duration)";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user";
$sql .= " FROM ".MAIN_DB_PREFIX."projet as p";
$sql .= " , ".MAIN_DB_PREFIX."projet_task as t";
$sql .= " , ".MAIN_DB_PREFIX."projet_task_time as tt";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
$sql .= " WHERE t.fk_projet = p.rowid";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= " AND p.fk_soc = sc.fk_soc AND sc.fk_user = " .$user->id;
$sql .= " AND tt.fk_task = t.rowid";
$sql .= " AND month(task_date) = ".strftime("%m",$now);
$sql .= " GROUP BY p.rowid";

$var=true;
$resql = $db->query($sql);
if ( $resql )
{
  $num = $db->num_rows($resql);
  $i = 0;

  while ($i < $num)
    {
      $row = $db->fetch_row( $resql);
      $var=!$var;
      print "<tr $bc[$var]>";
      print '<td><a href="'.DOL_URL_ROOT.'/projet/tasks/fiche.php?id='.$row[1].'">'.$row[0].'</a></td>';
      print '<td align="center">'.$row[2].'</td>';
      print "</tr>\n";    
      $i++;
    }
  
  $db->free($resql);
}
else
{
  dolibarr_print_error($db);
}
print "</table>";

/* Affichage de la liste des projets du mois */
print '<br /><table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width="50%">'.$langs->trans("Project").' cette ann�e : '.strftime("%Y", $now).'</td>';
print '<td width="50%" align="center">Nb heures</td>';
print "</tr>\n";

$sql = "SELECT p.title, p.rowid, sum(tt.task_duration)";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user";
$sql .= " FROM ".MAIN_DB_PREFIX."projet as p";
$sql .= " , ".MAIN_DB_PREFIX."projet_task as t";
$sql .= " , ".MAIN_DB_PREFIX."projet_task_time as tt";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
$sql .= " WHERE t.fk_projet = p.rowid";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= " AND p.fk_soc = sc.fk_soc AND sc.fk_user = " .$user->id;
$sql .= " AND tt.fk_task = t.rowid";
$sql .= " AND YEAR(task_date) = ".strftime("%Y",$now);
$sql .= " GROUP BY p.rowid";

$var=true;
$resql = $db->query($sql);
if ( $resql )
{
  $num = $db->num_rows($resql);
  $i = 0;

  while ($i < $num)
    {
      $row = $db->fetch_row( $resql);
      $var=!$var;
      print "<tr $bc[$var]>";
      print '<td><a href="'.DOL_URL_ROOT.'/projet/tasks/fiche.php?id='.$row[1].'">'.$row[0].'</a></td>';
      print '<td align="center">'.$row[2].'</td>';
      print "</tr>\n";    
      $i++;
    }
  
  $db->free($resql);
}
else
{
  dolibarr_print_error($db);
}
print "</table>";

print '</td></tr></table>';

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
