<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Eric Seigne          <eric.seigne@ryxeo.com>
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
 *
 * $Id$
 * $Source$
 */

/**
        \file       htdocs/product/photos.php
        \ingroup    product
        \brief      Onglet photos de la fiche produit
        \version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/product.lib.php");
require_once(DOL_DOCUMENT_ROOT."/propal.class.php");
require_once(DOL_DOCUMENT_ROOT."/facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/product.class.php");

$langs->load("products");
$langs->load("bills");

$user->getrights('produit');
$user->getrights('propale');
$user->getrights('facture');
$mesg = '';

if (!$user->rights->produit->lire) accessforbidden();

$types[0] = $langs->trans("Product");
$types[1] = $langs->trans("Service");


/*
 * Actions
 */

if ($_POST["sendit"] && $conf->global->MAIN_UPLOAD_DOC)
{
    if ($_GET["id"])
    {
        $product = new Product($db);
        $result = $product->fetch($_GET["id"]);

        // if (doliMoveFileUpload($_FILES['userfile']['tmp_name'], $upload_dir . "/" . $_FILES['userfile']['name']))

        //      var_dump($_FILES);

        $product->add_photo($conf->produit->dir_output, $_FILES['photofile']);
    }
}

if ($_GET["action"] == 'delete' && $_GET["file"]) 
{
    unlink($conf->produit->dir_output."/".$_GET["file"]);
}


/*
 *
 */


if ($_GET["id"] || $_GET["ref"])
{

    $product = new Product($db);
    if ($_GET["ref"]) $result = $product->fetch('',$_GET["ref"]);
    if ($_GET["id"]) $result = $product->fetch($_GET["id"]);
    
    llxHeader("","",$langs->trans("CardProduct".$product->type));

    if ($result)
    {
        /*
         *  En mode visu
         */
		$head=product_prepare_head($product);
		$titre=$langs->trans("CardProduct".$product->type);
		dolibarr_fiche_head($head, 'photos', $titre);


        print($mesg);

        print '<table class="border" width="100%">';

        // Reference
        print '<tr>';
        print '<td width="15%">'.$langs->trans("Ref").'</td><td colspan="2">';
        $product->load_previous_next_ref();
        $previous_ref = $product->ref_previous?'<a href="'.$_SERVER["PHP_SELF"].'?ref='.$product->ref_previous.'">'.img_previous().'</a>':'';
        $next_ref     = $product->ref_next?'<a href="'.$_SERVER["PHP_SELF"].'?ref='.$product->ref_next.'">'.img_next().'</a>':'';
        if ($previous_ref || $next_ref) print '<table class="nobordernopadding" width="100%"><tr class="nobordernopadding"><td class="nobordernopadding">';
        print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$product->id.'">'.$product->ref.'</a>';
        if ($previous_ref || $next_ref) print '</td><td class="nobordernopadding" align="center" width="20">'.$previous_ref.'</td><td class="nobordernopadding" align="center" width="20">'.$next_ref.'</td></tr></table>';
        print '</td>';
        print '</tr>';

        // Libelle
        print '<tr><td>'.$langs->trans("Label").'</td><td colspan="2">'.$product->libelle.'</td>';
        print '</tr>';

        // Prix
        print '<tr><td>'.$langs->trans("SellingPrice").'</td><td colspan="2">'.price($product->price).'</td></tr>';

        // Statut
        print '<tr><td>'.$langs->trans("Status").'</td><td colspan="2">';
		print $product->getLibStatut(2);
        print '</td></tr>';

        print "</table>\n";

        print "</div>\n";



        /* ************************************************************************** */
        /*                                                                            */
        /* Barre d'action                                                             */
        /*                                                                            */
        /* ************************************************************************** */

        print "\n<div class=\"tabsAction\">\n";

        if ($_GET["action"] != 'ajout_photo' && $user->rights->produit->creer && $conf->upload)
        {
            print '<a class="tabAction" href="'.DOL_URL_ROOT.'/product/photos.php?action=ajout_photo&amp;id='.$product->id.'">';
            print $langs->trans("AddPhoto").'</a>';
        }

        print "\n</div>\n";

        /*
         * Ajouter une photo
         */
        if ($_GET["action"] == 'ajout_photo' && $conf->upload && $user->rights->produit->creer)
        {
            print_titre($langs->trans("AddPhoto"));

            print '<form name="userfile" action="'.DOL_URL_ROOT.'/product/photos.php?id='.$product->id.'" enctype="multipart/form-data" METHOD="POST">';
            print '<input type="hidden" name="max_file_size" value="'.$conf->maxfilesize.'">';
            print '<input type="hidden" name="id" value="'.$product->id.'">';

            print '<table class="border" width="100%"><tr>';
            print '<td>'.$langs->trans("File").' ('.$langs->trans("Size").' <= '.$conf->maxfilesize.')</td>';
            print '<td><input type="file" class="flat" name="photofile" size="80"></td></tr>';

            print '<tr><td colspan="2" align="center">';
            print '<input type="submit" class="button" name="sendit" value="'.$langs->trans("Upload").'"> &nbsp; ';

            print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'"></td></tr>';
            print '</table>';
            
            print '</form>';
        }

        // Affiche photos
        if ($_GET["action"] != 'ajout_photo')
        {
            $nbphoto=0;
            $nbbyrow=5;
            
            $pdir = get_exdir($product->id,2) . $product->id ."/photos/";
            $dir = $conf->produit->dir_output . '/'. $pdir;

            print '<br>';
            print '<table width="100%" valign="top" align="center" border="0" cellpadding="2" cellspacing="2">';

            foreach ($product->liste_photos($dir) as $key => $obj)
            {
                $nbphoto++;

//                if ($nbbyrow && $nbphoto == 1) print '<table width="100%" valign="top" align="center" border="0" cellpadding="2" cellspacing="2">';

                if ($nbbyrow && ($nbphoto % $nbbyrow == 1)) print '<tr align=center valign=middle border=1>';
                if ($nbbyrow) print '<td width="'.ceil(100/$nbbyrow).'%" class="photo">';
                
                print '<a href="'.DOL_URL_ROOT.'/viewimage.php?modulepart=product&file='.urlencode($pdir.$obj['photo']).'" alt="Taille origine" target="_blank">';

                // Si fichier vignette disponible, on l'utilise, sinon on utilise photo origine
                if ($obj['photo_vignette']) $filename=$obj['photo_vignette'];
                else $filename=$obj['photo'];
                print '<img border="0" height="120" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=product&file='.urlencode($pdir.$filename).'">';

                print '</a>';
                print '<br>'.$langs->trans("File").': '.dolibarr_trunc($filename,16);
                if ($user->rights->produit->creer)
                {
                    print '<br>'.'<a href="'.$_SERVER["PHP_SELF"].'?id='.$_GET["id"].'&amp;action=delete&amp;file='.urlencode($pdir.$filename).'">'.img_delete().'</a>';
                }
                if ($nbbyrow) print '</td>';
                if ($nbbyrow && ($nbphoto % $nbbyrow == 0)) print '</tr>';
            }
            
            // Ferme tableau
            while ($nbphoto % $nbbyrow)
            {
                print '<td width="'.ceil(100/$nbbyrow).'%">&nbsp;</td>';
                $nbphoto++;
            }
            
            if ($nbphoto < 1)
            {
                print '<tr align=center valign=middle border=1><td class="photo">';
                print "<br>".$langs->trans("NoPhotoYet")."<br><br>";
                print '</td></tr>';
            }

           print '</table>';
        }
    }
}
else
{
    print $langs->trans("ErrorUnknown");
}



$db->close();

llxFooter('$Date$ - $Revision$');
?>
