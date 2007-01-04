<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 */

/**
     	\file       htdocs/includes/modules/propale/mod_propale_ivoire.php
		\ingroup    propale
		\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de propale Ivoire
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/propale/modules_propale.php");


/**	    \class      mod_propale_ivoire
		\brief      Classe du mod�le de num�rotation de r�f�rence de propale Ivoire
*/

class mod_propale_ivoire extends ModeleNumRefPropales
{

    /**   \brief      Constructeur
     */
    function mod_propale_ivoire()
    {
      $this->nom = "Ivoire";
    }
    
    /**     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
      return "Renvoie le num�ro sous la forme PRyynnnn o� yy est l'ann�e et nnnn un compteur sans remise � 0";
    }


    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        return "PR050001";
    }


    /**     \brief      Renvoi prochaine valeur attribu�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Valeur
     */
    function getNextValue($objsoc=0)
    {
        global $db;
    
    // D'abord on r�cup�re la valeur max (r�ponse imm�diate car champ ind�x�)

        $current_year = strftime("%y",time());
        $last_year = strftime("%y",mktime(0,0,0,date("m"),date("d"),date("Y")+1));
        
        $pryy = 'PR'.$current_year;
        
        $sql = "SELECT MAX(ref)";
        $sql.= " FROM ".MAIN_DB_PREFIX."propal";
        $sql.= " WHERE ref like '${pryy}%'";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            $pryy='';
            if ($row)
            {
            	$pryy = substr($row[0],0,4);
            }
            else
            {
            	$pryy = 'PR'.$last_year;
            }
        }
    
        // Si au moins un champ respectant le mod�le a �t� trouv�e
        if (eregi('PR[0-9][0-9]',$pryy))
        {
            // Recherche rapide car restreint par un like sur champ index�
            $posindice=5;
            $sql = "SELECT MAX(0+SUBSTRING(ref,$posindice))";
            $sql.= " FROM ".MAIN_DB_PREFIX."propal";
            $sql.= " WHERE ref like '${pryy}%'";
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
        
        $num = sprintf("%04s",$max+1);
        $yy = strftime("%y",time());
        
        return  "PR$yy$num";
    }

    /**     \brief      Renvoie la r�f�rence de propale suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Texte descripif
     */
    function getNumRef($objsoc=0)
    { 
        return $this->getNextValue();
    }
        
}

?>
