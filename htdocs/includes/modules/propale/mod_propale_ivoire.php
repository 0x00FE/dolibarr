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
    
        $sql = "SELECT count(*) FROM  ".MAIN_DB_PREFIX."propal";
    
        if ( $db->query($sql) )
        {
            $row = $db->fetch_row(0);
    
            $num = $row[0];
        }
    
        $y = strftime("%y",time());
    
        return  "PR" . "$y" . substr("000".$num, strlen("000".$num)-4,4);
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
