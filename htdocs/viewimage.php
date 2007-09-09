<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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
		\file       htdocs/viewimage.php
		\brief      Wrapper permettant l'affichage de fichiers images Dolibarr
        \remarks    L'appel est viewimage.php?file=pathrelatifdufichier&modulepart=repfichierconcerne
		\version    $Revision$
*/

if (! defined('NOREQUIREMENU')) define('NOREQUIREMENU','1');


// C'est un wrapper, donc header vierge
function llxHeader() { }


$original_file = urldecode($_GET["file"]);
$modulepart = urldecode($_GET["modulepart"]);
$type = urldecode($_GET["type"]);

// Protection, on interdit les .. dans les chemins
$original_file = eregi_replace('\.\.','',$original_file);


if ($modulepart == 'companylogo')
{
	// Pour companylogo, on charge juste environnement sans logon qui charge le user
	require_once("master.inc.php");
}
else
{
	// Pour autre que companylogo, on charge environnement + info issus de logon comme le user
	require_once("main.inc.php");
}



$accessallowed=0;
if ($modulepart)
{
    // On fait une v�rification des droits et on d�finit le r�pertoire concern�

    // Wrapping pour les photo utilisateurs
    if ($modulepart == 'companylogo')
    {
    	$accessallowed=1;
   		$original_file=$conf->societe->dir_logos.'/'.$original_file;
    }

    // Wrapping pour les photos utilisateurs
    if ($modulepart == 'userphoto')
    {
    	$accessallowed=1;
    	$original_file=$conf->users->dir_output.'/'.$original_file;
    }

    // Wrapping pour les photos adherents
    if ($modulepart == 'memberphoto')
    {
    	$accessallowed=1;
    	$original_file=$conf->adherent->dir_output.'/'.$original_file;
    }
    
    // Wrapping pour les apercu factures
    if ($modulepart == 'apercufacture')
    {
        $user->getrights('facture');
        if ($user->rights->facture->lire)
        {
            $accessallowed=1;
        }
        $original_file=$conf->facture->dir_output.'/'.$original_file;
    }

    // Wrapping pour les apercu propal
    if ($modulepart == 'apercupropal')
    {
        $user->getrights('propale');
        if ($user->rights->propale->lire)
        {
            $accessallowed=1;
        }
        $original_file=$conf->propal->dir_output.'/'.$original_file;
    }
    
    // Wrapping pour les apercu commande
    if ($modulepart == 'apercucommande')
    {
        $user->getrights('commande');
        if ($user->rights->commande->lire)
        {
            $accessallowed=1;
        }
        $original_file=$conf->commande->dir_output.'/'.$original_file;
    }
    
    // Wrapping pour les apercu intervention
    if ($modulepart == 'apercufichinter')
    {
        $user->getrights('ficheinter');
        if ($user->rights->ficheinter->lire)
        {
            $accessallowed=1;
        }
        $original_file=$conf->fichinter->dir_output.'/'.$original_file;
    }

    // Wrapping pour les images des stats propales
    if ($modulepart == 'propalstats')
    {
        $user->getrights('propale');
        if ($user->rights->propale->lire)
        {
            $accessallowed=1;
        }
        $original_file=$conf->propal->dir_temp.'/'.$original_file;
    }

    // Wrapping pour les images des stats commandes
    if ($modulepart == 'orderstats')
    {
        $user->getrights('commande');
        if ($user->rights->commande->lire)
        {
            $accessallowed=1;
        }
        $original_file=$conf->commande->dir_temp.'/'.$original_file;
    }

    // Wrapping pour les images des stats factures
    if ($modulepart == 'billstats')
    {
        $user->getrights('facture');
        if ($user->rights->facture->lire)
        {
            $accessallowed=1;
        }
        $original_file=$conf->facture->dir_temp.'/'.$original_file;
    }

    // Wrapping pour les images des stats expeditions
    if ($modulepart == 'expeditionstats')
    {
        $user->getrights('expedition');
        if ($user->rights->expedition->lire)
        {
            $accessallowed=1;
        }
        $original_file=$conf->expedition->dir_temp.'/'.$original_file;
    }

    // Wrapping pour les images des stats produits
    if (eregi('^productstats_',$modulepart))
    {
        $user->getrights('produit');
        if ($user->rights->produit->lire)
        {
            $accessallowed=1;
        }
        $original_file=$conf->produit->dir_temp.'/'.$original_file;
    }

    // Wrapping pour les produits
    if ($modulepart == 'product')
    {
        $user->getrights('produit');
        if ($user->rights->produit->lire)
        {
            $accessallowed=1;
        }
        $original_file=$conf->produit->dir_output.'/'.$original_file;
    }

    // Wrapping pour les prelevements
    if ($modulepart == 'prelevement')
    {
        $user->getrights('prelevement');
        if ($user->rights->prelevement->bons->lire) $accessallowed=1;

        $original_file=$conf->prelevement->dir_output.'/bon/'.$original_file;
    }

    // Wrapping pour les graph telephonie
    if ($modulepart == 'telephoniegraph')
    {
        $user->getrights('telephonie');
        if ($user->rights->telephonie->lire)
        {
            $accessallowed=1;
        }
        $original_file=DOL_DATA_ROOT.'/graph/telephonie/'.$original_file;
    }

    // Wrapping pour les graph energie
    if ($modulepart == 'energie')
    {
      $accessallowed=1;
      $original_file=DOL_DATA_ROOT.'/energie/graph/'.$original_file;
    }

    // Wrapping pour les graph bank
    if ($modulepart == 'bank')
    {
      $accessallowed=1;
      $original_file=$conf->banque->dir_temp.'/'.$original_file;
    }

    // Wrapping pour les images wysiwyg
    if ($modulepart == 'fckeditor')
    {
      $accessallowed=1;
      $original_file=$conf->fckeditor->dir_output.'/'.$original_file;
    }

    // Wrapping pour les images wysiwyg mailing
    if ($modulepart == 'mailing')
    {
      $accessallowed=1;
      $original_file=$conf->mailing->dir_output.'/'.$original_file;
    }

    // Wrapping pour les graph energie
    if ($modulepart == 'graph_stock')
    {
      $accessallowed=1;
      $original_file=DOL_DATA_ROOT.'/graph/entrepot/'.$original_file;
    }

    // Wrapping pour les graph fournisseurs
    if ($modulepart == 'graph_fourn')
    {
      $accessallowed=1;
      $original_file=DOL_DATA_ROOT.'/graph/fournisseur/'.$original_file;
    }
    // Wrapping pour les graph des produits
    if ($modulepart == 'graph_product')
    {
      $accessallowed=1;
      $original_file=DOL_DATA_ROOT.'/graph/product/'.$original_file;
    }

}

// Security:
// Limite acc�s si droits non corrects
if (! $accessallowed)
{
    accessforbidden();
}

// Security:
// On interdit les remont�es de repertoire ainsi que les pipe dans 
// les noms de fichiers.
if (eregi('\.\.',$original_file) || eregi('[<>|]',$original_file))
{
	$langs->load("main");
	dolibarr_syslog("Refused to deliver file ".$original_file);
	// Do no show plain path in shown error message
	dolibarr_print_error(0,$langs->trans("ErrorFileNameInvalid",$_GET["file"]));
	exit;
}



// Ouvre et renvoi fichier
clearstatcache(); 
$filename = basename($original_file);

dolibarr_syslog("viewimage.php return file $original_file $filename content-type=$type");

if (! file_exists($original_file))
{
	$langs->load("main");
	dolibarr_print_error(0,$langs->trans("ErrorFileDoesNotExists",$_GET["file"]));
	exit;
}

// Les drois sont ok et fichier trouv�
if ($type)
{
  header('Content-type: '.$type);
}
else
{
  header('Content-type: image/png');
}

readfile($original_file);

?>
