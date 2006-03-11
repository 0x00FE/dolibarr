<?php
/* Copyright (C) 2001-2003,2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005      Laurent Destailleur  <eldy@users.sourceforge.net>
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

$langs->load("companies");

// Protection quand utilisateur externe
$socidp = isset($_GET["socid"])?$_GET["socid"]:'';

//if ($socidp == '') accessforbidden(); //probl�me apr�s update des notes

if ($user->societe_id > 0)
{
    $socidp = $user->societe_id;
}

// Protection restriction commercial
if (!$user->rights->commercial->client->voir && $socidp && !$user->societe_id > 0)
{
        $sql = "SELECT sc.fk_soc, s.client";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe_commerciaux as sc, ".MAIN_DB_PREFIX."societe as s";
        $sql .= " WHERE fk_soc = ".$socidp." AND fk_user = ".$user->id." AND s.client = 1";

        if ( $db->query($sql) )
        {
          if ( $db->num_rows() == 0) accessforbidden();
        }
}


if ($_POST["action"] == 'add') {
  $sql = "UPDATE ".MAIN_DB_PREFIX."societe SET note='".addslashes($_POST["note"])."' WHERE idp=".$_POST["socid"];
  $result = $db->query($sql);

  $_GET["socid"]=$_POST["socid"];   // Pour retour sur fiche
  $socidp = $_GET["socid"];
}


/*
 *
 */

llxHeader();

if ($socidp > 0)
{
    $societe = new Societe($db, $socidp);
    $societe->fetch($socidp);
    
    
    $h=0;
    
    $head[$h][0] = DOL_URL_ROOT.'/soc.php?socid='.$societe->id;
    $head[$h][1] = $langs->trans("Company");
    $h++;
    
    if ($societe->client==1)
    {
        $head[$h][0] = DOL_URL_ROOT.'/comm/fiche.php?socid='.$societe->id;
        $head[$h][1] = $langs->trans("Customer");
        $h++;
    }
    
    if ($societe->client==2)
    {
        $head[$h][0] = DOL_URL_ROOT.'/comm/prospect/fiche.php?id='.$societe->id;
        $head[$h][1] = $langs->trans("Prospect");
        $h++;
    }
    if ($societe->fournisseur)
    {
        $head[$h][0] = DOL_URL_ROOT.'/fourn/fiche.php?socid='.$societe->id;
        $head[$h][1] = $langs->trans("Supplier");
        $h++;
    }
    
    if ($conf->compta->enabled) {
        $langs->load("compta");
        $head[$h][0] = DOL_URL_ROOT.'/compta/fiche.php?socid='.$societe->id;
        $head[$h][1] = $langs->trans("Accountancy");
        $h++;
    }
    
    $head[$h][0] = DOL_URL_ROOT.'/socnote.php?socid='.$societe->id;
    $head[$h][1] = $langs->trans("Note");
    $hselected = $h;
    $h++;
    
    if ($user->societe_id == 0)
    {
        $head[$h][0] = DOL_URL_ROOT.'/docsoc.php?socid='.$societe->id;
        $head[$h][1] = $langs->trans("Documents");
        $h++;
    }
    
    $head[$h][0] = DOL_URL_ROOT.'/societe/notify/fiche.php?socid='.$societe->id;
    $head[$h][1] = $langs->trans("Notifications");
    $h++;
    
    $head[$h][0] = DOL_URL_ROOT.'/societe/info.php?socid='.$societe->id;
    $head[$h][1] = $langs->trans("Info");
    $h++;
    
    dolibarr_fiche_head($head, $hselected, $societe->nom);


  print "<form method=\"post\" action=\"socnote.php\">";

  print '<table class="border" width="100%">';
  print '<tr><td width="50%" valign="top">'.$langs->trans("Note").'</td><td>'.$langs->trans("CurrentNote").'</td></tr>';
  print '<tr><td width="50%" valign="top">';
  print "<input type=\"hidden\" name=\"action\" value=\"add\">";
  print "<input type=\"hidden\" name=\"socid\" value=\"".$societe->id."\">";
  print '<textarea name="note" cols="70" rows="10">'.$societe->note.'</textarea><br>';
  print '</td><td width="50%" valign="top">'.nl2br($societe->note).'</td>';
  print "</td></tr>";
  print '<tr><td colspan="2" align="center"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td></tr>';
  print "</table>";

  print '</form>';
}

print '</div><br>';

$db->close();

llxFooter('$Date$ - $Revision$');
?>
