<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Jean-Louis Bergamo   <jlb@j1b.org>
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
 *
 */

/**
   \file   	htdocs/compta/bank/pre.inc.php
   \ingroup    compta
   \brief  	Fichier gestionnaire du menu compta banque
*/

require_once("../../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/compta/bank/account.class.php");

$langs->load("banks");

$user->getrights('banque');

function llxHeader($head = "")
{
	global $db, $user, $conf, $langs;
	
	top_menu($head);
	
	$menu = new Menu();
	if ($user->rights->banque->lire)
	{
		$sql = "SELECT rowid, label, courant";
		$sql.= " FROM ".MAIN_DB_PREFIX."bank_account";
		$sql.= " WHERE clos = 0";
		$sql.= " AND courant in (1,2)";
		$resql = $db->query($sql);
		if ($resql)
		{
			$numr = $db->num_rows($resql);
			$i = 0;
	
			while ($i < $numr)
			{
				$objp = $db->fetch_object($resql);
				$menu->add(DOL_URL_ROOT."/compta/bank/account.php?account=" . $objp->rowid,  $objp->label);
				if ($objp->courant != 2) $menu->add_submenu(DOL_URL_ROOT."/compta/bank/releve.php?account=" . $objp->rowid ,$langs->trans("AccountStatements"));
				$menu->add_submenu(DOL_URL_ROOT."/compta/bank/annuel.php?account=" . $objp->rowid ,$langs->trans("IOMonthlyReporting"));
				$i++;
			}
		}
		$db->free($resql);
	}
	$menu->add(DOL_URL_ROOT."/compta/bank/index.php",$langs->trans("Bank"));
	
	$menu->add_submenu(DOL_URL_ROOT."/compta/bank/search.php",$langs->trans("SearchTransaction"));
	$menu->add_submenu(DOL_URL_ROOT."/compta/bank/budget.php",$langs->trans("ByCategories"));
	$menu->add_submenu(DOL_URL_ROOT."/compta/bank/bilan.php","Bilan");
	
	if ($user->rights->banque->modifier)
	{
		$menu->add_submenu(DOL_URL_ROOT."/compta/bank/virement.php",$langs->trans("Virements"));
	}
	
	if ($user->rights->banque->configurer)
	{
		$menu->add_submenu(DOL_URL_ROOT."/compta/bank/config.php",$langs->trans("Setup"));
	}
	
	if ($conf->global->COMPTA_ONLINE_PAYMENT_BPLC)
	{
		$menu->add(DOL_URL_ROOT."/compta/bank/bplc.php","Transactions BPLC");
	}
	
	left_menu($menu->liste);
}
?>
