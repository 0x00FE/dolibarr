<?php
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
	    \file       htdocs/compta/tva/reglement.php
		\brief      Liste des r�glements de TVA effectu�s
		\version    $Revision$
*/

require("./pre.inc.php");
require("../../tva.class.php");

$langs->load("compta");


llxHeader();

$tva = new Tva($db);

print_titre($langs->trans("VATPayments"));

$sql = "SELECT rowid, amount, label, ".$db->pdate("f.datev")." as dm";
$sql .= " FROM ".MAIN_DB_PREFIX."tva as f ";
$sql .= " ORDER BY dm DESC";

$result = $db->query($sql);
if ($result)
{
    $num = $db->num_rows($result);
    $i = 0; 
    $total = 0 ;
    print '<br>';
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">';
    print '<td nowrap width="120">'.$langs->trans("Date").'</td>';
    print "<td>".$langs->trans("Label")."</td>";
    print "<td align=\"right\">".$langs->trans("Amount")."</td>";
    print "<td>&nbsp;</td>\n";
    print "</tr>\n";
    $var=1;
    while ($i < $num)
    {
        $obj = $db->fetch_object($result);
        $var=!$var;
        print "<tr $bc[$var]>";
        print "<td>".dolibarr_print_date($obj->dm)."</td>\n";
        print "<td>".$obj->label."</td>\n";
        $total = $total + $obj->amount;
        
        print "<td align=\"right\">".price($obj->amount)."</td><td>&nbsp;</td>";
        print "</tr>\n";
        
        $i++;
    }
    print '<tr class="liste_total"><td align="right" colspan="2">'.$langs->trans("Total").'</td>';
    print "<td align=\"right\"><b>".price($total)."</b></td></tr>";
    
    print "</table>";
    $db->free($result);
}
else
{
    dolibarr_print_error($db);
}
  

$db->close();

llxFooter('$Date$ - $Revision$');
?>
