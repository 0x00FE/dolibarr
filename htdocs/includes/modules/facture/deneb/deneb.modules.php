<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Regis Houssin        <regis.houssin@cap-networks.com>
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
        \file       htdocs/includes/modules/facture/deneb/deneb.modules.php
		\ingroup    facture
		\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de facture Deneb
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/facture/modules_facture.php");

/**	
        \class      mod_facture_deneb
		\brief      Classe du mod�le de num�rotation de r�f�rence de facture Deneb
*/
class mod_facture_deneb extends ModeleNumRefFactures
{
    
    /**     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
    
      $texte = "Renvoie le num�ro de facture sous la forme, PREF-31-12-2004-01, o� PREF est le pr�fixe commercial de la soci�t�, et est suivi de la date (ici le 31 d�cembre 2004) et d'un compteur.<br>";
      $texte.= "Si la constante FACTURE_DENEB_DELTA est d�finie, un offset est appliqu� sur le compteur";
      if (defined("FACTURE_DENEB_DELTA"))
        {
          $texte .= " (D�finie et vaut : ".FACTURE_DENEB_DELTA.")";
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
        return "PREF-31-12-2004-01";
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
    
      if (!defined("FACTURE_DENEB_DELTA"))
        {
          define("FACTURE_DENEB_DELTA", 0);
        }
    
      $num = $num + FACTURE_DENEB_DELTA;
    
      return  $objsoc->prefix_comm . "-" .strftime("%d-%m-%Y", time()) . "-".$num;
    }

}

?>
