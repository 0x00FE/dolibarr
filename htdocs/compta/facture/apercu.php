<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      �ric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
	    \file       htdocs/compta/facture/apercu.php
		\ingroup    facture
		\brief      Page de l'onglet aper�u d'une facture
		\version    $Revision$
*/

require("./pre.inc.php");

$user->getrights('facture');
if (!$user->rights->facture->lire)
  accessforbidden();

$langs->load("bills");


$warning_delay=31*24*60*60; // Delai affichage warning retard (si retard paiement facture > delai)


require_once(DOL_DOCUMENT_ROOT."/facture.class.php");
if ($conf->projet->enabled) {
    require_once(DOL_DOCUMENT_ROOT."/project.class.php");
}


/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socidp = $user->societe_id;
}

llxHeader('',$langs->trans("Bill"),'Facture');

$html = new Form($db);

/* *************************************************************************** */
/*                                                                             */
/* Mode fiche                                                                  */
/*                                                                             */
/* *************************************************************************** */

if ($_GET["facid"] > 0)
{
    $fac = New Facture($db);
    if ( $fac->fetch($_GET["facid"], $user->societe_id) > 0)
    {
        $soc = new Societe($db, $fac->socidp);
        $soc->fetch($fac->socidp);
        $author = new User($db);
        $author->id = $fac->user_author;
        $author->fetch();

        $h = 0;

        $head[$h][0] = DOL_URL_ROOT.'/compta/facture.php?facid='.$fac->id;
        $head[$h][1] = $langs->trans("CardBill");
        $h++;
        $head[$h][0] = DOL_URL_ROOT.'/compta/facture/apercu.php?facid='.$fac->id;
        $head[$h][1] = $langs->trans("Preview");
        $hselected = $h;
        $h++;

        if ($fac->mode_reglement_code == 'PRE')
        {
            $head[$h][0] = DOL_URL_ROOT.'/compta/facture/prelevement.php?facid='.$fac->id;
            $head[$h][1] = $langs->trans("StandingOrders");
            $h++;
        }

        $head[$h][0] = DOL_URL_ROOT.'/compta/facture/note.php?facid='.$fac->id;
        $head[$h][1] = $langs->trans("Note");
        $h++;
        $head[$h][0] = DOL_URL_ROOT.'/compta/facture/info.php?facid='.$fac->id;
        $head[$h][1] = $langs->trans("Info");
        $h++;

        dolibarr_fiche_head($head, $hselected, $langs->trans("Bill")." : $fac->ref");


        /*
        *   Facture
        */
        print '<table class="border" width="100%">';
        
        // Societe
        print '<tr><td>'.$langs->trans("Company").'</td>';
        print '<td colspan="5">';
        print '<a href="'.DOL_URL_ROOT.'/compta/fiche.php?socid='.$soc->id.'">'.$soc->nom.'</a></td>';
        print '</tr>';

        // Dates
        print '<tr><td>'.$langs->trans("Date").'</td>';
        print '<td colspan="3">'.dolibarr_print_date($fac->date,"%A %d %B %Y").'</td>';
        print '<td>'.$langs->trans("DateClosing").'</td><td>' . dolibarr_print_date($fac->date_lim_reglement,"%A %d %B %Y");
        if ($fac->paye == 0 && $fac->date_lim_reglement < (time() - $warning_delay)) print img_warning($langs->trans("Late"));
        print "</td></tr>";

        // Conditions et modes de r�glement
        print '<tr><td>'.$langs->trans("PaymentConditions").'</td><td colspan="3">';
        $html->form_conditions_reglement($_SERVER["PHP_SELF"]."?facid=$fac->id",$fac->cond_reglement_id,"none");
        print '</td>';
        print '<td width="25%">'.$langs->trans("PaymentMode").'</td><td width="25%">';
        $html->form_modes_reglement($_SERVER["PHP_SELF"]."?facid=$fac->id",$fac->mode_reglement_id,"none");
        print '</td></tr>';

        print '<tr>';
        if ($conf->projet->enabled)
        {
            $langs->load("projects");
            print '<td>'.$langs->trans("Project").'</td><td colspan="3">';
            if ($fac->projetid > 0)
            {
                $projet = New Project($db);
                $projet->fetch($fac->projetid);
                print '<a href="'.DOL_URL_ROOT.'/projet/fiche.php?id='.$fac->projetid.'">'.$projet->title.'</a>';
            }
            else
            {
                print '&nbsp;';
            }
            print "&nbsp;</td>";
        }
        else
        {
            print '<td>&nbsp;</td><td colspan="3">';
            print "&nbsp;</td>";
        }
        print '<td colspan="2" rowspan="4" valign="top" width="50%">';

        /*
        * Documents
        *
        */
        $facref = sanitize_string($fac->ref);
        $file = $conf->facture->dir_output . "/" . $facref . "/" . $facref . ".pdf";
        $filedetail = $conf->facture->dir_output . "/" . $facref . "/" . $facref . "-detail.pdf";
        $relativepath = "${facref}/${facref}.pdf";
        $relativepathdetail = "${facref}/${facref}-detail.pdf";
        $relativepathimage = "${facref}/${facref}.pdf.png";

        $fileimage = $file.".png";

        $var=true;

        // Si fichier PDF existe
        if (file_exists($file))
        {
            $encfile = urlencode($file);
            print_titre($langs->trans("Documents"));
            print '<table class="border" width="100%">';

            print "<tr $bc[$var]><td>".$langs->trans("Bill")." PDF</td>";

            print '<td><a href="'.DOL_URL_ROOT . '/document.php?modulepart=facture&file='.urlencode($relativepath).'">'.$fac->ref.'.pdf</a></td>';
            print '<td align="right">'.filesize($file). ' bytes</td>';
            print '<td align="right">'.strftime("%d %b %Y %H:%M:%S",filemtime($file)).'</td>';
            print '</tr>';

            // Si fichier detail PDF existe
            if (file_exists($filedetail)) // facture d�taill�e suppl�mentaire
            {
                print "<tr $bc[$var]><td>Facture d�taill�e</td>";

                print '<td><a href="'.DOL_URL_ROOT . '/document.php?modulepart=facture&file='.urlencode($relativepathdetail).'">'.$fac->ref.'-detail.pdf</a></td>';
                print '<td align="right">'.filesize($filedetail). ' bytes</td>';
                print '<td align="right">'.strftime("%d %b %Y %H:%M:%S",filemtime($filedetail)).'</td>';
                print '</tr>';
            }

            print "</table>\n";

            // Conversion du PDF en image png si fichier png non existant
            if (!file_exists($fileimage))
            {
                if (function_exists(imagick_readimage)) {
                    $handle = imagick_readimage( $file ) ;
                    if ( imagick_iserror( $handle ) )
                    {
                        $reason      = imagick_failedreason( $handle ) ;
                        $description = imagick_faileddescription( $handle ) ;

                        print "handle failed!<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n";
                    }

                    imagick_convert( $handle, "PNG" ) ;

                    if ( imagick_iserror( $handle ) )
                    {
                        $reason      = imagick_failedreason( $handle ) ;
                        $description = imagick_faileddescription( $handle ) ;

                        print "handle failed!<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n";
                    }

                    imagick_writeimage( $handle, $file .".png");
                }
                else {
                    $langs->load("other");
                    print $langs->trans("ErrorNoImagickReadimage");
                }
            }

        }

        /*
        *
        *
        */

        print "</td></tr>";

        print "<tr><td>".$langs->trans("Author")."</td><td colspan=\"3\">$author->fullname</td>";

        print '<tr><td nowrap>'.$langs->trans("GlobalDiscount").'</td>';
        print '<td align="right" colspan="2">'.$fac->remise_percent.'</td>';
        print '<td>%</td></tr>';

        print '<tr><td>'.$langs->trans("AmountHT").'</td>';
        print '<td align="right" colspan="2"><b>'.price($fac->total_ht).'</b></td>';
        print '<td>'.$langs->trans("Currency".$conf->monnaie).'</td></tr>';

        print '</table>';

    }
    else
    {
        // Facture non trouv�e
        print $langs->trans("ErrorBillNotFound",$_GET["facid"]);
    }
} 

if (file_exists($fileimage))
{	  
  print '<img src="'.DOL_URL_ROOT . '/viewimage.php?modulepart=apercufacture&file='.urlencode($relativepathimage).'">';
}
print '</div>';


$db->close();

llxFooter('$Date$ - $Revision$');
?>
