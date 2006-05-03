<?php
/* Copyright (C) 2005        Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006   Regis Houssin        <regis.houssin@cap-networks.com>
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
    \file       htdocs/includes/modules/commande/mod_commande_diamant.php
    \ingroup    commande
    \brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de commande Diamant
    \version    $Revision$
*/

include_once("modules_commande.php");


/**
   \class      mod_commande_diamant
   \brief      Classe du mod�le de num�rotation de r�f�rence de commande Diamant
*/

class mod_commande_diamant extends ModeleNumRefCommandes
{

  /**   \brief      Constructeur
   */
  function mod_commande_diamant()
  {
    $this->nom = "Diamant";
  }


  /**     \brief      Renvoi la description du modele de num�rotation
   *      \return     string      Texte descripif
   */
  function info()
  {
    $texte = "Renvoie le num�ro sous la forme num�rique CYYNNNNN, o� YY repr�sente l'ann�e et NNNNN Le num�ro d'incr�ment. Ce dernier n'est PAS remis � z�ro en d�but d'ann�e.<br>\n";
    $texte.= "Si la constante COMMANDE_DIAMANT_DELTA est d�finie, un offset est appliqu� sur le compteur";
    
    if (defined("COMMANDE_DIAMANT_DELTA"))
        {
          $texte .= " (D�finie et vaut: ".COMMANDE_DIAMANT_DELTA.")";
        }
      else
        {
          $texte .= " (N'est pas d�finie)";
        }
      return $texte;
  }
  

    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
    	$y = strftime("%y",time());
    	
    	if (defined("COMMANDE_DIAMANT_DELTA"))
        {
        	$num = sprintf("%02d",COMMANDE_DIAMANT_DELTA);
          return "C".$y.substr("0000".$num, strlen("0000".$num)-5,5);
        }
        else 
        {
            return "C".$y."00001";
        }            
    }
    
    /**     \brief      Renvoi prochaine valeur attribu�e
     *      \return     string      Valeur
     */
    function getNextValue()
    {
        global $db;

        // D'abord on r�cup�re la valeur max (r�ponse imm�diate car champ ind�x�)
        $cyy='';
        $sql = "SELECT MAX(ref)";
        $sql.= " FROM ".MAIN_DB_PREFIX."commande";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) $cyy = substr($row[0],0,3);
        }
    
        // Si au moins un champ respectant le mod�le a �t� trouv�e
        if (eregi('C[0-9][0-9]',$cyy))
        {
            // Recherche rapide car restreint par un like sur champ index�
            $posindice=4;
            $sql = "SELECT MAX(0+SUBSTRING(ref,$posindice))";
            $sql.= " FROM ".MAIN_DB_PREFIX."commande";
            $sql.= " WHERE ref like '${cyy}%'";
            $resql=$db->query($sql);
            if ($resql)
            {
                $row = $db->fetch_row($resql);
                $max = $row[0];
            }
        }
        else
        {
            $max=0;
        }
        
        if (!defined("COMMANDE_DIAMANT_DELTA"))
        {
          define("COMMANDE_DIAMANT_DELTA", 0);
        }
        
        if ($max == 0)
        {
        	$delta = COMMANDE_DIAMANT_DELTA;
        	$num = sprintf("%05s",$delta);
        }
        else
        {
        	$num = sprintf("%05s",$max+1);
        }
        
        $yy = strftime("%y",time());
        
        return  "C$yy$num";
    }
    
    
        /**     \brief      Renvoie la r�f�rence de commande suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Texte descripif
     */
    function commande_get_num($objsoc=0)
    {
        return $this->getNextValue();
    }
}
?>
