<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org> 
 * Copyright (C) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *
 */

/*!
  \file   	htdocs/compta/pre.inc.php
  \ingroup    compta
  \brief  	Fichier gestionnaire du menu compta
*/

require("../main.inc.php");

function llxHeader($head = "", $title="", $help_url='')
{
  global $user, $conf, $langs;

  $user->getrights('banque');
  
  top_menu($head, $title);

  $menu = new Menu();

  // Les recettes

  $menu->add(DOL_URL_ROOT."/compta/clients.php", $langs->trans("Customers"));

  if ($user->comm > 0 && $conf->commercial->enabled && $conf->propal->enabled) 
    {
      $langs->load("propal");
      $menu->add(DOL_URL_ROOT."/compta/propal.php",$langs->trans("Prop"));
    }

  if ($conf->contrat->enabled)
    {
      $langs->load("contracts");
      $menu->add(DOL_URL_ROOT."/contrat/",$langs->trans("Contracts"));
    }

  if ($conf->don->enabled)
    {
      $langs->load("donations");
      $menu->add(DOL_URL_ROOT."/compta/dons/",$langs->trans("Donations"));
    }

  if ($conf->facture->enabled)
    {
      $langs->load("bills");
      $menu->add(DOL_URL_ROOT."/compta/facture.php",$langs->trans("Bills"));
      $menu->add_submenu(DOL_URL_ROOT."/compta/paiement/liste.php",$langs->trans("Payments"));
      if (! defined(FACTURE_DISABLE_RECUR) || ! FACTURE_DISABLE_RECUR) {
        $menu->add_submenu(DOL_URL_ROOT."/compta/facture/fiche-rec.php","R�currentes");
      }
      $menu->add_submenu(DOL_URL_ROOT."/compta/facture/stats/", $langs->trans("Statistics"));
    }
   
    
  // Les d�penses
  if ($conf->fournisseur->enabled)
    {
      $langs->load("suppliers");
      $menu->add(DOL_URL_ROOT."/fourn/index.php", $langs->trans("Suppliers"));
    }

  if ($user->societe_id == 0)
    {
      $menu->add(DOL_URL_ROOT."/compta/deplacement/", "D�placement");
    }

  if ($conf->compta->enabled && $conf->compta->tva && $user->societe_id == 0)
    {
      $menu->add(DOL_URL_ROOT."/compta/tva/index.php",$langs->trans("VAT"));
    }
    
  if ($conf->compta->enabled)
    {
    $menu->add(DOL_URL_ROOT."/compta/charges/index.php","Charges");
    }


  // Vision des recettes-d�penses
  if ($conf->banque->enabled && $user->rights->banque->lire)
    { 
      $menu->add(DOL_URL_ROOT."/compta/bank/",$langs->trans("Bank"));
    }
  
  if ($conf->caisse->enabled)
    {
      $menu->add(DOL_URL_ROOT."/compta/caisse/index.php","Caisses");
    }

  $menu->add(DOL_URL_ROOT."/compta/stats/","CA / R�sultats");

  if ($conf->prelevement->enabled)
    {
      $menu->add(DOL_URL_ROOT."/compta/prelevement/","Bon pr�l�v.");
    }


  $menu->add(DOL_URL_ROOT."/compta/ventilation.php",$langs->trans("Ventilation"));

  if ($user->rights->compta->ventilation->param)
    $menu->add(DOL_URL_ROOT."/compta/param/",$langs->trans("Param"));

  if (! $user->compta) 
    {
      $menu->clear();
      $menu->add(DOL_URL_ROOT."/",$langs->trans("Home"));
    }

  left_menu($menu->liste, $help_url);
}

?>
