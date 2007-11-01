<?php
/* Copyright (C) 2005        Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006   Regis Houssin        <regis@dolibarr.fr>
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
    \file       htdocs/fourn/commande/modules/pdf/mod_commande_fournisseur_rose.php
    \ingroup    fournisseur
    \brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de commande fournisseur Rose
    \version    $Revision$
*/


require_once(DOL_DOCUMENT_ROOT ."/fourn/commande/modules/modules_commandefournisseur.php");


/**
   \class      mod_commande_fournisseur_rose
   \brief      Classe du mod�le de num�rotation de r�f�rence de commande fournisseur Rose
*/

class mod_commande_fournisseur_rose extends ModeleNumRefSuppliersOrders
{

  /**   \brief      Constructeur
   */
  function mod_commande_fournisseur_rose()
  {
    $this->nom = "Rose";
  }


  /**     \brief      Renvoi la description du modele de num�rotation
   *      \return     string      Texte descripif
   */
  function info()
  {
    $texte = "Renvoie le num�ro sous la forme num�rique CFYYNNNN, o� YY repr�sente l'ann�e et NNNN Le num�ro d'incr�ment. Ce dernier n'est PAS remis � z�ro en d�but d'ann�e.<br>\n";
    $texte.= "Si la constante COMMANDE_FOURNISSEUR_ROSE_DELTA est d�finie, un offset est appliqu� sur le compteur";
    
    if (defined("COMMANDE_FOURNISSEUR_ROSE_DELTA"))
        {
          $texte .= " (D�finie et vaut: ".COMMANDE_FOURNISSEUR_ROSE_DELTA.")";
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
    	$y = strftime("%y",time());
    	
    	if (defined("COMMANDE_FOURNISSEUR_ROSE_DELTA"))
        {
        	$num = sprintf("%02d",COMMANDE_FOURNISSEUR_ROSE_DELTA);
          return "CF".$y.substr("000".$num, strlen("000".$num)-4,4);
        }
        else 
        {
            return "CF".$y."0001";
        }            
    }
    
  
  	/**
  	 *	\brief      Renvoie le prochaine num�ro de r�f�rence de commande non utilis�
     *  \param      objsoc     	Objet soci�t�
     *  \return     string		Num�ro de r�f�rence de commande non utilis�
   	 */
	function getNextValue($objsoc=0)
	{
		global $db;
	
		$sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."commande_fournisseur WHERE fk_statut <> 0";
	
		$resql = $db->query($sql);
	
		if ( $resql )
		{
			$row = $db->fetch_row($resql);
	
			$num = $row[0];
		}
	
		if (!defined("COMMANDE_FOURNISSEUR_ROSE_DELTA"))
		{
			define("COMMANDE_FOURNISSEUR_ROSE_DELTA", 0);
		}
	
		$num = $num + COMMANDE_FOURNISSEUR_ROSE_DELTA;
	
		$y = strftime("%y",time());
	
		return 'CF'.$y.sprintf("%04s",$num);
	}
	
	/**     \brief      Renvoie la r�f�rence de commande suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Texte descripif
     */
    function commande_get_num($objsoc=0)
    {
        return $this->getNextValue($objsoc);
    }

}
?>
