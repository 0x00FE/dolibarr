<?php
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
    $texte = "Renvoie le num�ro sous la forme num�rique CYY00001, CYY00002, CYY00003, ... o� YY repr�sente l'ann�e. Le num�ro d'incr�ment qui suit l'ann�e n'est PAS remis � z�ro en d�but d'ann�e.<br>\n";
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
        if (defined("COMMANDE_DIAMANT_DELTA"))
        {
            return "C0400".sprintf("%02d",COMMANDE_DIAMANT_DELTA);
        }
        else 
        {
            return "C040001";
        }            
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
      
      if (!defined("COMMANDE_DIAMANT_DELTA"))
        {
          define("COMMANDE_DIAMANT_DELTA", 0);
        }
    
      $num = $num + FACTURE_NEPTUNE_DELTA;
    
    $y = strftime("%y",time());

    return 'C'.$y.substr("0000".$num, strlen("0000".$num)-5,5);
  }
}
?>
