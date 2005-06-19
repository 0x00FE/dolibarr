<?PHP
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Christophe Combelles <ccomb@free.fr>
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
        \file       htdocs/includes/modules/facture/mars/mars.modules.php
		\ingroup    facture
		\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de facture Mars
		\version    $Revision$
*/


/**	\class mod_facture_mars
		\brief      Classe du mod�le de num�rotation de r�f�rence de facture Mars
*/
class mod_facture_mars extends ModeleNumRefFactures
{
    
    /**     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
    
      $texte = "Num�ro de facture sous la forme, PREF-10-2004-005, qui correspond � la 5�me facture d'octobre 2004 et o� PREF est le pr�fix de la soci�t�.";
      $texte.= "Le nombre final est format� sur 3 chiffres ou plus.<br>";
      $texte.= "Si la constante FACTURE_MARS_DELTA est d�finie, un offset est appliqu� sur le compteur";
      if (defined("FACTURE_MARS_DELTA"))
        {
          $texte .= " (D�finie et vaut : ".FACTURE_MARS_DELTA.")";
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
        return "PREF-10-2004-005";
    }

    /**     \brief      Renvoie la r�f�rence de facture suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Texte descriptif
     */
    function getNumRef($objsoc=0)
    { 
      global $db;
      # define the beginning of the invoice number
      $invnum=$objsoc->prefix_comm . "-" .strftime("%m-%Y", time()) . "-";
      # get the nb of invoices beginning with $invnum
      $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."facture WHERE fk_statut > 0 AND facnumber LIKE '".$invnum."%'";
      if ( $db->query($sql) ) 
        {
          $row = $db->fetch_row(0);
          $num = $row[0]+1;
        }
      # append a number of at least 3 digits on $invnum
      if ( $num >= 0 AND $num <=9 )
        {
          $num = "00".$num;
        }
      else if ( $num >= 10 AND $num <=99 )
        {
          $num = "0".$num;
        }
      return  $invnum.$num;
    }
}

?>
