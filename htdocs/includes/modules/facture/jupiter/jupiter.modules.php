<?php
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/*!	\file htdocs/includes/modules/facture/jupiter/jupiter.modules.php
		\ingroup    facture
		\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de facture Jupiter
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/facture/modules_facture.php");

/*!	\class mod_facture_jupiter
		\brief      Classe du mod�le de num�rotation de r�f�rence de facture Jupiter
*/
class mod_facture_jupiter extends ModeleNumRefFactures
{
    
    /*!     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
      return '
    Syst�me de num�rotation mensuel sous la forme F20030715, qui correspond � la 15�me facture du mois de Juillet 2003';
    }

    /*!     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        return "F2004121";
    }

    /*!     \brief      Renvoie la r�f�rence de facture suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Texte descripif
     */
    function getNumRef($objsoc=0)
    { 
      global $db;
      
      $prefix='F';
      $date = strftime("%Y%m", time());
      $num=0;
      
      $sql = "SELECT max(0+substring(facnumber,8)) FROM ".MAIN_DB_PREFIX."facture";
      $sql .= " WHERE facnumber like '$prefix".$date."%'";
    
      if ( $db->query($sql) ) 
        {
          $row = $db->fetch_row(0);
       
          $num = $row[0];
        }
      $num++;
      return  "$prefix" . $date . $num;
    }
    
}

?>
