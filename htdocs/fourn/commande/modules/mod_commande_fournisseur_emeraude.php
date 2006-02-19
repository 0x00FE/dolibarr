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
    \file       htdocs/fourn/commande/modules/pdf/mod_commande_fournisseur_emeraude.php
    \ingroup    fournisseur
    \brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de commande fournisseur Emeraude
    \version    $Revision$
*/

include_once("modules_commandefournisseur.php");


/**
   \class      mod_commande_fournisseur_emeraude
   \brief      Classe du mod�le de num�rotation de r�f�rence de commande fournisseur Emeraude
*/

class mod_commande_fournisseur_emeraude extends ModeleNumRefCommandesSuppliers
{

  /**   \brief      Constructeur
   */
  function mod_commande_fournisseur_emeraude()
  {
    $this->nom = "Emeraude";
  }


  /**     \brief      Renvoi la description du modele de num�rotation
   *      \return     string      Texte descripif
   */
  function info()
  {
    $texte = "Renvoie le num�ro sous la forme num�rique CFNNNNNN, o� NNNNNN repr�sente num�ro d'incr�ment. Ce dernier n'est PAS remis � z�ro en d�but d'ann�e.";
    return $texte;
  }
  

    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
      return "CF000001";
    }
    
  
  /**   \brief      Renvoie le prochaine num�ro de r�f�rence de commande non utilis�
        \param      obj_soc     objet soci�t�
        \return     string      num�ro de r�f�rence de commande non utilis�
   */
  function commande_get_num($obj_soc=0)
  { 
    global $db;
    
    $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."commande_fournisseur WHERE fk_statut <> 0";
    
    $resql = $db->query($sql);

    if ( $resql ) 
      {
	      $row = $db->fetch_row($resql);
	
	      $num = $row[0];
      }

    return 'CF'.substr("000000".$num,strlen("000000".$num)-6,6);
}
?>
