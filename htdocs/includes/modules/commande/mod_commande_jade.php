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
    \file       htdocs/includes/modules/commande/mod_commande_jade.php
    \ingroup    commande
    \brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de commande Jade
    \version    $Revision$
*/

include_once("modules_commande.php");


/**
   \class      mod_commande_jade
   \brief      Classe du mod�le de num�rotation de r�f�rence de commande Jade
*/

class mod_commande_jade extends ModeleNumRefCommandes
{

  /**   \brief      Constructeur
   */
  function mod_commande_jade()
  {
    $this->nom = "Jade";
  }


  /**     \brief      Renvoi la description du modele de num�rotation
   *      \return     string      Texte descripif
   */
  function info()
  {
    return "Renvoie le num�ro sous la forme num�rique CYY00001, CYY00002, CYY00003, ... o� YY repr�sente l'ann�e. Le num�ro d'incr�ment qui suit l'ann�e n'est PAS remis � z�ro en d�but d'ann�e.";
  }
  
  /**     \brief      Renvoi prochaine valeur attribu�e
     *      \return     string      Valeur
     */
    function getNextValue()
    {
        global $db;

        // D'abord on r�cup�re la valeur max (r�ponse imm�diate car champ ind�x�)
        $cyy='';
        $sql = "SELECT MAX(ref)";
        $sql.= " FROM ".MAIN_DB_PREFIX."commande";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) $cyy = substr($row[0],0,4);
        }
    
        // Si au moins un champ respectant le mod�le a �t� trouv�e
        if (eregi('C[0-9][0-9]',$cyy))
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
        }
        else
        {
            $max=0;
        }        
        $yy = strftime("%y",time());
        $num = sprintf("%05s",$max+1);
        
        return  "C$yy$num";
    }

  
    /**     \brief      Renvoie la r�f�rence de commande suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Texte descripif
     */
    function commande_get_num($objsoc=0)
    {
        return $this->getNextValue();
    }
}
?>
