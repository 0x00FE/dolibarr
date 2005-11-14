<?php
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005 Regis Houssin        <regis.houssin@cap-networks.com>
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

/*!	
  \file htdocs/includes/modules/facture/uranus/uranus.modules.php
  \ingroup    facture
  \brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de facture Uranus
  \version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/facture/modules_facture.php");

/*!
  \class mod_facture_uranus
  \brief      Classe du mod�le de num�rotation de r�f�rence de facture Uranus
*/

class mod_facture_uranus extends ModeleNumRefFactures
{

    /*!     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
      return "
    Renvoie le num�ro de facture sous une forme num�rique simple, pr�fix� par l'ann�e sur un seul chiffre";
    }

    /*!     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        return "5000001";
    }

    /*!     \brief      Renvoie la r�f�rence de facture suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Texte descripif
     */
    function getNumRef($objsoc=0)
    { 
      global $db;
    
      $y = substr(strftime("%y",time()), -1);

      $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."facture";
      $sql .= " WHERE fk_statut > 0";
      $sql .= " AND facnumber LIKE '$y%'"; 
      $sql .= " AND CHAR_LENGTH(facnumber) = 7";

      if ( $db->query($sql) ) 
        {
          $row = $db->fetch_row(0);
          
          $num = $row[0] + 1;
        }
    
      return  "$y" . substr("000000".$num, -6 );
    
    }    
}
?>
