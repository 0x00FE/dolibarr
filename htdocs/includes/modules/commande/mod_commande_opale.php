<?php
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
    \file       htdocs/includes/modules/commande/mod_commande_opale.php
    \ingroup    commande
    \brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de commande Opale
    \version    $Revision$
*/

include_once("modules_commande.php");


/**
   \class      mod_commande_opale
   \brief      Classe du mod�le de num�rotation de r�f�rence de commande Opale
*/

class mod_commande_opale extends ModeleNumRefCommandes
{

  /**   \brief      Constructeur
   */
  function mod_commande_opale()
  {
    $this->nom = "Opale";
  }


  /**     \brief      Renvoi la description du modele de num�rotation
   *      \return     string      Texte descripif
   */
  function info()
  {
    return "Renvoie le num�ro sous la forme num�rique COMhexa, o� hexa repr�sente un incr�ment global cod� en h�xad�cimal. (COM-000-001 � COM-FFF-FFF)";
  }
  
  /**     \brief      Renvoi un exemple de num�rotation
   *      \return     string      Example
   */
    function getExample()
    {
        return "COM-000-001";
    }

  
    /**     \brief      Renvoi prochaine valeur attribu�e
     *      \return     string      Valeur
     */
    function getNextValue()
    {
        global $db;

        // D'abord on r�cup�re la valeur max (r�ponse imm�diate car champ ind�x�)
        $com='';
        $sql = "SELECT MAX(ref)";
        $sql.= " FROM ".MAIN_DB_PREFIX."commande";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row)
            {
            	$com = substr($row[0],0,3);
            	$max = hexdec(substr($row[0],4,3)).(substr($row[0],8,3));
        }
/*    
        // Si au moins un champ respectant le mod�le a �t� trouv�e
        if (eregi('COM',$cyy))
        {
            // Recherche rapide car restreint par un like sur champ index�
            $posindice=4;
            $sql = "SELECT MAX(0+SUBSTRING(ref,$posindice))";
            $sql.= " FROM ".MAIN_DB_PREFIX."commande";
            $sql.= " WHERE ref like '${cyy}%'";
            $resql=$db->query($sql);
            if ($resql)
            {
                $row = $db->fetch_row($resql);
                $max = $row[0];
            }
            */
        }
        else
        {
            $max=0;
        }        
        $yy = strftime("%y",time());
        $hex = strtoupper(dechex($max+1));
        $ref = substr("000000".($hex),-6);
        
        return 'COM-'.substr($ref,0,3)."-".substr($ref,3,3);
    }
    
   /**     \brief      Renvoie la r�f�rence de commande suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Texte descripif
     */
    function commande_get_num($objsoc=0)
    {
        return $this->getNextValue();
    }


  
  /**   \brief      Renvoie le prochaine num�ro de r�f�rence de commande non utilis�
        \param      obj_soc     objet soci�t�
        \return     string      num�ro de r�f�rence de commande non utilis�
   */
/*  function commande_get_num($obj_soc=0)
  { 
    global $db;
    
    $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."commande WHERE fk_statut <> 0";
    
    $resql = $db->query($sql);

    if ( $resql ) 
      {
	$row = $db->fetch_row($resql);
	
	$num = $row[0];
      }
    
    $hex = strtoupper(dechex($num+1));

    $ref = substr("000000".($hex),-6);

    return 'COM-'.substr($ref,0,3)."-".substr($ref,3,3);
  }
  */
}
?>
