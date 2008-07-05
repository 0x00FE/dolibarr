<?php
/* Copyright (C) 2005      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2007 Regis Houssin        <regis@dolibarr.fr>
 * Copyright (C) 2004-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 */

/** 
    \file       htdocs/includes/modules/commande/mod_livraison_jade.php
    \ingroup    livraison
    \brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de bon de livraison Jade
    \version    $Id$
*/

require_once(DOL_DOCUMENT_ROOT ."/livraison/mods/modules_livraison.php");


/**
   \class      mod_livraison_jade
   \brief      Classe du mod�le de num�rotation de r�f�rence de bon de livraison Jade
*/

class mod_livraison_jade extends ModeleNumRefDeliveryOrder
{
	var $version='dolibarr';		// 'development', 'experimental', 'dolibarr'
	var $error = '';
	var $nom = "Jade";


	/**     \brief      Renvoi la description du modele de num�rotation
	*      \return     string      Texte descripif
	*/
	function info()
	{
		return "Renvoie le num�ro sous la forme num�rique BLYY00001, BLYY00002, BLYY00003, ... o� YY repr�sente l'ann�e. Le num�ro d'incr�ment qui suit l'ann�e n'est PAS remis � z�ro en d�but d'ann�e.";
	}
  
      /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        return "BL0600001";
    }
  
	/**		\brief      Return next value
	*      	\param      objsoc      Object third party
	*      	\param      livraison	Object delivery
	*      	\return     string      Value if OK, 0 if KO
	*/
    function getNextValue($objsoc=0,$livraison='')
    {
        global $db;

        // D'abord on r�cup�re la valeur max (r�ponse imm�diate car champ ind�x�)
        $blyy='';
        $sql = "SELECT MAX(ref)";
        $sql.= " FROM ".MAIN_DB_PREFIX."livraison";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) $blyy = substr($row[0],0,4);
        }
    
        // Si au moins un champ respectant le mod�le a �t� trouv�e
        if (eregi('BL[0-9][0-9]',$blyy))
        {
            // Recherche rapide car restreint par un like sur champ index�
            $posindice=5;
            $sql = "SELECT MAX(0+SUBSTRING(ref,$posindice))";
            $sql.= " FROM ".MAIN_DB_PREFIX."livraison";
            $sql.= " WHERE ref like '".$blyy."%'";
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
        //$date=time();
        $date=$livraison->date_livraison;
        $yy = strftime("%y",$date);
        $num = sprintf("%05s",$max+1);
        
        return  "BL$yy$num";
    }

  
    /**     \brief      Renvoie la r�f�rence de commande suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \param      livraison	Objet livraison
     *      \return     string      Texte descripif
     */
    function livraison_get_num($objsoc=0,$livraison='')
    {
        return $this->getNextValue($objsoc,$livraison);
    }
}
?>
