<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
	\file       htdocs/includes/modules/facture/neptune/neptune.modules.php
	\ingroup    facture
	\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de facture Neptune
	\version    $Revision$
*/


/**
	\class      mod_facture_neptune
	\brief      Classe du mod�le de num�rotation de r�f�rence de facture Neptune
*/
class mod_facture_neptune extends ModeleNumRefFactures
{

    /**     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
      $texte = "Renvoie le num�ro de facture sous une forme du pr�fix FA suivi de l'ann�e sur 2 chiffres et d'un compteur simple sur 4 chiffres.<br>\n";
      $texte.= "Si la constante FACTURE_NEPTUNE_DELTA est d�finie, un offset est appliqu� sur le compteur";
      if (defined("FACTURE_NEPTUNE_DELTA"))
        {
          $texte .= " (D�finie et vaut : ".FACTURE_NEPTUNE_DELTA.")";
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
        if (defined("FACTURE_NEPTUNE_DELTA"))
        {
            return "FA0400".sprintf("%02d",FACTURE_NEPTUNE_DELTA);
        }
        else 
        {
            return "FA040010";
        }            
    }

    /**     \brief      Renvoie la r�f�rence de facture suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Texte descripif
     */
    function getNumRef($objsoc=0)
    { 
      global $db;
    
      $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."facture WHERE fk_statut > 0";
    
      if ( $db->query($sql) ) 
        {
          $row = $db->fetch_row(0);
          
          $num = $row[0];
        }
    
      if (!defined("FACTURE_NEPTUNE_DELTA"))
        {
          define("FACTURE_NEPTUNE_DELTA", 0);
        }
    
      $num = $num + FACTURE_NEPTUNE_DELTA;
    
      $y = strftime("%y",time());
    
      return  "FA" . "$y" . substr("000".$num, strlen("000".$num)-4,4);
    
    }
    
}    

?>
