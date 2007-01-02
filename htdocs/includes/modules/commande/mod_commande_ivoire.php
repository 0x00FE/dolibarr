<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/** 	\file       htdocs/includes/modules/commande/mod_commande_ivoire.php
		\ingroup    commande
		\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de commande Ivoire
		\version    $Revision$
*/

include_once("modules_commande.php");


/**
    	\class      mod_commande_ivoire
		\brief      Classe du mod�le de num�rotation de r�f�rence de commande Ivoire
*/

class mod_commande_ivoire extends ModeleNumRefCommandes
{

	/**   \brief      Constructeur
	*/
	function mod_commande_ivoire()
	{
		$this->nom = "Ivoire";
	}


    /**     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
  function info()
    {
      return "Renvoie le num�ro sous la forme num�rique C0M1,COM2,COM3,...<br><b>OBSOLETE, NE PLUS UTILISER CAR BUGGUE. Sera supprim� dans prochaines versions</b>";
    }


    /**     \brief      Renvoi prochaine valeur attribu�e
     *      \return     string      Valeur
     */
    function getNextValue()
    {
      global $db;
      
      $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."commande WHERE fk_statut <> 0";
      
      if ( $db->query($sql) ) 
	{
	  $row = $db->fetch_row(0);
	  
	  $num = $row[0];
	}
      
      
      return 'COM'.($num+1);
    }

  /**     \brief      Renvoi un exemple de num�rotation
   *      \return     string      Example
   */
    function getExample()
    {
        return "COM12";
    }


  /**   \brief      Renvoie le prochaine num�ro de r�f�rence de commande non utilis�
        \param      obj_soc     objet soci�t�
        \return     string      num�ro de r�f�rence de commande non utilis�
   */
  function commande_get_num($obj_soc=0)
    { 
      global $db;
      
      $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."commande WHERE fk_statut <> 0";
      
      if ( $db->query($sql) ) 
	{
	  $row = $db->fetch_row(0);
	  
	  $num = $row[0];
	}
      
      $y = strftime("%y",time());
      
      return 'COM'.($num+1);
    }
}

?>
