<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2006      Andre Cianfarani     <acianfa@free.fr>
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
            \file       htdocs/includes/modules/commande/modules_commandefournisseur.php
                \ingroup    commande
                \brief      Fichier contenant la classe m�re de generation des commandes fournisseurs en PDF
                            et la classe m�re de num�rotation des commandes fournisseurs
                \version    $Revision$
*/

require_once(FPDF_PATH.'fpdf.php');


/**
            \class      ModelePDFCommandesSuppliers
                \brief      Classe m�re des mod�les de commandes
*/

class ModelePDFCommandesSuppliers extends FPDF
{
    var $error='';

   /** 
        \brief Renvoi le dernier message d'erreur de cr�ation de PDF de commande
    */
    function pdferror()
    {
        return $this->error;
    }

    /** 
     *      \brief      Renvoi la liste des mod�les actifs
     */
    function liste_modeles($db)
    {
        $type='supplier_order';
        $liste=array();
        $sql ="SELECT nom as id, nom as lib";
        $sql.=" FROM ".MAIN_DB_PREFIX."document_model";
        $sql.=" WHERE type = '".$type."'";
        
        $resql = $db->query($sql);
        if ($resql)
        {
            $num = $db->num_rows($resql);
            $i = 0;
            while ($i < $num)
            {
                $row = $db->fetch_row($resql);
                $liste[$row[0]]=$row[1];
                $i++;
            }
        }
        else
        {
            $this->error=$db->error();
            return -1;
        }
        return $liste;
    }

}



/**
        \class      ModeleNumRefCommandesSuppliers
            \brief      Classe m�re des mod�les de num�rotation des r�f�rences de commandes fournisseurs
*/

class ModeleNumRefCommandesSuppliers
{
    var $error='';

    /**     \brief      Renvoi la description par defaut du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
        global $langs;
        $langs->load("orders");
        return $langs->trans("NoDescription");
    }

    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        global $langs;
        $langs->load("orders");
        return $langs->trans("NoExample");
    }

    /**     \brief      Test si les num�ros d�j� en vigueur dans la base ne provoquent pas de
     *                  de conflits qui empechera cette num�rotation de fonctionner.
     *      \return     boolean     false si conflit, true si ok
     */
    function canBeActivated()
    {
        return true;
    }

    /**     \brief      Renvoi prochaine valeur attribu�e
     *      \return     string      Valeur
     */
    function getNextValue()
    {
        global $langs;
        return $langs->trans("NotAvailable");
    }
    
}
function commande_supplier_pdf_create($db, $comid, $modele='',$outputlangs='')
{
  global $langs;
  $langs->load("suppliers");
 
  $dir = DOL_DOCUMENT_ROOT."/fourn/commande/modules/pdf/";

  // Positionne modele sur le nom du modele de commande fournisseur � utiliser
  if (! strlen($modele))
    {
      if (defined("COMMANDE_SUPPLIER_ADDON_PDF") && COMMANDE_SUPPLIER_ADDON_PDF)
        {
          $modele = COMMANDE_SUPPLIER_ADDON_PDF;
        }
      else
        {
      print $langs->trans("Error")." ".$langs->trans("Error_COMMANDE_SUPPLIER_ADDON_PDF_NotDefined");
          return 0;
        }
    }
  // Charge le modele
  $file = "pdf_".$modele.".modules.php";
  if (file_exists($dir.$file))
    {
      $classname = "pdf_".$modele;
      require_once($dir.$file);
  
      $obj = new $classname($db);

      if ($obj->write_pdf_file($comid,$outputlangs) > 0)
        {
          // on supprime l'image correspondant au preview
           commande_supplier_delete_preview($db, $comid);
          return 1;
        }
      else
        {
          dolibarr_syslog("Erreur dans commande_supplier_pdf_create");
          dolibarr_print_error($db,$obj->pdferror());
          return 0;
        }
    }
  else
    {
      print $langs->trans("Error")." ".$langs->trans("ErrorFileDoesNotExists",$dir.$file);
      return 0;
    }
}
function commande_supplier_delete_preview($db, $propalid)
{
        global $langs,$conf;

        $comfourn = new CommandeFournisseur($db,"",$propalid);
        $comfourn->fetch($propalid);  
        $client = new Societe($db);
        $client->fetch($comfourn->socid);

        if ($conf->fournisseur->commande->dir_output)
                {
                $comfournref = sanitize_string($comfourn->ref); 
                $dir = $conf->commande->dir_output . "/" . $comfournref ; 
                $file = $dir . "/" . $comfournref . ".pdf.png";

                if ( file_exists( $file ) && is_writable( $file ) )
                        {
                        if ( ! unlink($file) )
                                {
                                $this->error=$langs->trans("ErrorFailedToOpenFile",$file);
                                return 0;
                                }
                        }
                }
}
?>
