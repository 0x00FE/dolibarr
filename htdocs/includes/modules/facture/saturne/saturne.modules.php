<?php
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/*!	\file htdocs/includes/modules/facture/saturne/saturne.modules.php
		\ingroup    facture
		\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de facture Saturne
		\version    $Revision$
*/


/*!	\class mod_facture_saturne
		\brief      Classe du mod�le de num�rotation de r�f�rence de facture Saturne
*/

class mod_facture_saturne extends ModeleNumRefFactures
{

    /*!     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
      return '
    Renvoie le num�ro de facture avec un pr�fixe suivi du mois sur 2 digits et l\'ann�e sur un digit.';
    }

    /*!     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        return "FA123084";
    }

    /*!     \brief      Renvoie la r�f�rence de facture suivante non utilis�e
     *      \param      objsoc                  Objet soci�t�
     *      \param      objfac                  Objet facture
     *      \return     string                  Texte descripif
     */
    function getNumRef($objsoc=0,$objfac)
    { 
      $prefix='FA';
       
      $y = strftime("%y",time());
      $m = strftime("%m",time());
    
      return  $prefix.$objfac->id . $m . substr($y, -1);
    
    }    
}

?>
