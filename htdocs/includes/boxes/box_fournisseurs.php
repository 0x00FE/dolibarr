<?php
/* Copyright (C) 2004-2005 Destailleur Laurent <eldy@users.sourceforge.net>
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
 *
 */

/**
    \file       htdocs/includes/boxes/box_fournisseurs.php
    \ingroup    fournisseurs
    \brief      Module de g�n�ration de l'affichage de la box fournisseurs
*/

$info_box_head = array();
$info_box_head[] = array('text' => "Les 5 derniers fournisseurs enregistr�s");

$info_box_contents = array();

$sql = "SELECT s.nom,s.idp";
$sql .= " FROM ".MAIN_DB_PREFIX."societe as s WHERE s.fournisseur = 1";  
if ($user->societe_id > 0)
{
  $sql .= " AND s.idp = $user->societe_id";
}
$sql .= " ORDER BY s.datec DESC ";
$sql .= $db->plimit(5, 0);

$result = $db->query($sql);

if ($result) 
{
  $num = $db->num_rows();
    
  $i = 0;
    
  while ($i < $num)
    {
      $objp = $db->fetch_object($result);
      
      $info_box_contents[$i][0] = array('align' => 'left',
   					'logo' => 'object_company',
					'text' => $objp->nom,
					'url' => DOL_URL_ROOT."/comm/fiche.php?socid=".$objp->idp);

      $i++;
    }
}

new infoBox($info_box_head, $info_box_contents);
?>
