<?php
/* Copyright (C) 2005       Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006  Regis Houssin        <regis.houssin@cap-networks.com>
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
 * or see http://www.gnu.org/
 *
 * $Id$
 * $Source$
 *
 */

/** 
    \file       htdocs/includes/modules/commande/mod_commande_emeraude.php
    \ingroup    commande
    \brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de commande Emeraude
    \version    $Revision$
*/

include_once("modules_commande.php");


/**
   \class      mod_commande_emeraude
   \brief      Classe du mod�le de num�rotation de r�f�rence de commande Emeraude
*/

class mod_commande_emeraude extends ModeleNumRefCommandes
{

  /**   \brief      Constructeur
   */
  function mod_commande_emeraude()
  {
    $this->nom = "Emeraude";
  }


  /**     \brief      Renvoi la description du modele de num�rotation
   *      \return     string      Texte descripif
   */
  function info()
    {
      $texte = "Renvoie le num�ro sous la forme CYYNNNNN o� YY est l'ann�e et NNNNN le num�ro d'incr�ment qui commence � 1.<br>\n";
      $texte.= "L'ann�e s'incr�mente de 1 et le num�ro d'incr�ment se remet � zero en d�but d'ann�e d'exercice.<br>\n";
      $texte.= "D�finir la variable FISCAL_MONTH_START avec le mois du d�but d'exercice, ex: 9 pour septembre.<br>\n";
      $texte.= "Dans cette exemple nous aurons au 1er septembre 2006 une commande nomm�e C0700001.<br>\n";
      
      if (defined("FISCAL_MONTH_START"))
      {
      	$texte.= "FISCAL_MONTH_START est d�finie et vaut: ".FISCAL_MONTH_START."";
      }
      else
      {
      	$texte.= "FISCAL_MONTH_START n'est pas d�finie.";
      }
      return $texte;
    }
    
   /**     \brief      Renvoi un exemple de num�rotation
   *      \return     string      Example
   */
   function getExample()
   {
       return "C0600001";
   }

  
  /**   \brief      Renvoie le prochaine num�ro de r�f�rence de commande non utilis�
        \param      obj_soc     objet soci�t�
        \return     string      num�ro de r�f�rence de commande non utilis�
   */
  function commande_get_num($obj_soc=0)
  { 
    global $db;
    
    $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."commande WHERE fk_statut <> 0";
    
    $resql = $db->query($sql);

    if ( $resql ) 
      {
	$row = $db->fetch_row($resql);
	
	$num = $row[0];
      }
    $current_month = date("n");
	if($current_month >= FISCAL_MONTH_START)
        $y = strftime("%y",mktime(0,0,0,date("m"),date("d"),date("Y")+1));
	else
    $y = strftime("%y",time());

    return 'C'.$y.substr("0000".($num+1),-5);
  }
}
?>
