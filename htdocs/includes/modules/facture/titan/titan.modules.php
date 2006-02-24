<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2006 Regis Houssin        <regis.houssin@cap-networks.com>
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
	\file       htdocs/includes/modules/facture/neptune/titan.modules.php
	\ingroup    facture
	\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de facture Titan
	\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/facture/modules_facture.php");

/**
	\class      mod_facture_titan
	\brief      Classe du mod�le de num�rotation de r�f�rence de facture Titan
*/
class mod_facture_titan extends ModeleNumRefFactures
{

    /**     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
function info()
    {
      $texte = "Renvoie le num�ro sous la forme FAYYNNNN o� YY est l'ann�e et NNNN le num�ro d'incr�ment qui commence � 1.<br>\n";
      $texte.= "L'ann�e s'incr�mente de 1 et le num�ro d'incr�ment se remet � zero en d�but d'ann�e d'exercice.<br>\n";
      $texte.= "D�finir la variable FISCAL_MONTH_START avec le mois du d�but d'exercice, ex: 9 pour septembre.<br>\n";
      $texte.= "Dans cette exemple nous aurons au 1er septembre 2006 une facture nomm�e FA070001.<br>\n";
      
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
        return "FA060001";           
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
    
      $num = $num + 1;
    	$current_month = date("n");
		if($current_month >= FISCAL_MONTH_START)
        $y = strftime("%y",mktime(0,0,0,date("m"),date("d"),date("Y")+1));
		else
      	$y = strftime("%y",time());
      return  "FA" . "$y" . substr("000".$num, strlen("000".$num)-4,4);
    
    }
    
}    

?>
