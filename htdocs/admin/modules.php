<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Jean-Louis Bergamo   <jlb@j1b.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      �ric Seigne          <eric.seigne@ryxeo.com>
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
 
/**     \file       htdocs/admin/modules.php
        \brief      Page de configuration et activation des modules
        \version    $Revision$
*/

require("./pre.inc.php");

if (!$user->admin)
    accessforbidden();



if ($_GET["action"] == 'set' && $user->admin)
{
    Activate($_GET["value"]);

    Header("Location: modules.php?spe=".$_GET["spe"]);
}

if ($_GET["action"] == 'reset' && $user->admin)
{
    UnActivate($_GET["value"]);

    Header("Location: modules.php?spe=".$_GET["spe"]);
}


/**     \brief      Active un module
        \param      value   Nom du module a activer
*/
function Activate($value)
{
    global $db, $modules;

    $modName = $value;

    // Activation du module
    if ($modName)
    {
        $file = $modName . ".class.php";
        include_once("../includes/modules/$file");
        $objMod = new $modName($db);
        $objMod->init();
    }

    // Activation des modules dont le module d�pend
    for ($i = 0; $i < sizeof($objMod->depends); $i++)
    {
        Activate($objMod->depends[$i]);
    }

}


/**     \brief      D�sactive un module
        \param      value   Nom du module a d�sactiver
*/
function UnActivate($value)
{
    global $db, $modules;

    $modName = $value;

    // Desactivation du module
    if ($modName)
    {
        $file = $modName . ".class.php";
        include_once("../includes/modules/$file");
        $objMod = new $modName($db);
        $objMod->remove();
    }

    // Desactivation des modules qui dependent de lui
    for ($i = 0; $i < sizeof($objMod->requiredby); $i++)
    {
        UnActivate($objMod->requiredby[$i]);
    }

    Header("Location: modules.php");
}



llxHeader("","");

if (!$_GET["spe"])
{
    $hselected = 0;
}
else
{
    $hselected = 1;
}

$h = 0;
$head[$h][0] = DOL_URL_ROOT."/admin/modules.php?spe=0";
$head[$h][1] = $langs->trans("ModulesCommon");

$h++;

$head[$h][0] = DOL_URL_ROOT."/admin/modules.php?spe=1";
$head[$h][1] = $langs->trans("ModulesSpecial");
$h++;

dolibarr_fiche_head($head, $hselected, $langs->trans("Modules"));


if (!$_GET["spe"])
{
    print $langs->trans("ModulesDesc")."<br>\n";
}
else
{
    print $langs->trans("ModulesSpecialDesc")."<br>\n";
}



print "<br>\n";
print "<table class=\"noborder\" width=\"100%\">\n";
print "<tr class=\"liste_titre\">\n";
print "  <td>".$langs->trans("Family")."</td>\n";
print "  <td colspan=\"2\">".$langs->trans("Module")."</td>\n";
print "  <td>".$langs->trans("Description")."</td>\n";
print "  <td align=\"center\">".$langs->trans("Version")."</td>\n";
print "  <td align=\"center\">".$langs->trans("Activated")."</td>\n";
print "  <td align=\"center\">".$langs->trans("Action")."</td>\n";
print "  <td>&nbsp;</td>\n";
print "</tr>\n";


$dir = DOL_DOCUMENT_ROOT . "/includes/modules/";

$handle=opendir($dir);
$modules = array();
$i = 0;
$j = 0;
while (($file = readdir($handle))!==false)
{
    if (is_readable($dir.$file) && substr($file, 0, 3) == 'mod'  && substr($file, strlen($file) - 10) == '.class.php')
    {
        $modName = substr($file, 0, strlen($file) - 10);

        if ($modName)
        {
            include_once("../includes/modules/$file");
            $objMod = new $modName($db);

            if ($objMod->numero > 0)
            {
                $j = $objMod->numero;
            }
            else
            {
                $j = 1000 + $i;
            }
            $modules[$i] = $modName;
            $numero[$i] = $j;
            $orders[$i] = "$objMod->family"."_".$j;   // Tri par famille puis numero module
            $j++;
            $i++;
        }
    }
}

asort($orders);
$var=True;

$familylib=array(
'crm'=>$langs->trans("ModuleFamilyCrm"),
'products'=>$langs->trans("ModuleFamilyProducts"),
'hr'=>$langs->trans("ModuleFamilyHr"),
'projects'=>$langs->trans("ModuleFamilyProjects"),
'other'=>$langs->trans("ModuleFamilyOther"),
'technic'=>$langs->trans("ModuleFamilyTechnic"),
'financial'=>$langs->trans("ModuleFamilyFinancial"),
'experimental'=>$langs->trans("ModuleFamilyExperimental")
);
foreach ($orders as $key => $value)
{
    $tab=split('_',$value);
    $family=$tab[0]; $numero=$tab[1];

    $modName = $modules[$key];
    if ($modName)
    {
        $objMod = new $modName($db);
    }

    $const_name = $objMod->const_name;
    $const_value = $objMod->const_config;

    if ($oldfamily && $family!=$oldfamily && $atleastoneforfamily) {
        print "<tr class=\"liste_titre\">\n  <td colspan=\"8\"></td>\n</tr>\n";
        $atleastoneforfamily=0;
    }

    if((!$objMod->special && !$_GET["spe"] ) or ($objMod->special && $_GET["spe"]))
      {
        $atleastoneforfamily=1;
        $var=!$var;
        
        print "<tr $bc[$var]>\n";
	
        print "  <td class=\"body\">";
        if ($family!=$oldfamily)
	  { 
	    print "<div class=\"titre\">".$familylib[$family]."</div>";
	    $oldfamily=$family;
	  }
        else
	  { 
	    print '&nbsp;';
	  }
        print "</td>\n";
        print '  <td valign="top" width="14" align="center">';
        print $objMod->picto?img_object('',$objMod->picto):img_object('','generic');
        print '</td><td valign="top">'.$objMod->getName();
        print "</td>\n  <td valign=\"top\">";
        print $objMod->getDesc();
        print "</td>\n  <td align=\"center\">";
        print $objMod->getVersion();
        print "</td>\n  <td align=\"center\">";

        if ($const_value == 1)
	  {
	    print '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/tick.png" border="0"></a>';
	  }
        else
	  {
            print "&nbsp;";
	  }
	
        print "</td>\n  <td align=\"center\">";
	

        if ($const_value == 1)
	  {
            // Module actif
            print "<a href=\"modules.php?id=".$objMod->numero."&amp;action=reset&amp;value=" . $modName . "&amp;spe=" . $_GET["spe"] . "\">" . $langs->trans("Disable") . "</a></td>\n";


            if ($objMod->config_page_url)
            {
                if (is_array($objMod->config_page_url)) {
                    print "  <td>";
                    $i=0;
                    foreach ($objMod->config_page_url as $page) 
		      {
                        if ($i++)
			  {
			    print '<a href="'.$page.'">'.ucfirst($page).'</a>&nbsp;'; 
			  }
                        else
			  {
			    print '<a href="'.$page.'">'.$langs->trans("Setup").'</a>&nbsp;'; 
			  }
		      }
                    print "</td>\n";
                }
		else
		  {
                    print '  <td><a href="'.$objMod->config_page_url.'">'.$langs->trans("Setup").'</a></td>';
		  }
            }
            else
	      {
                print "  <td>&nbsp;</td>";
	      }
	    
        }
        else
	  {
            // Module non actif
            print "<a href=\"modules.php?id=".$objMod->numero."&amp;action=set&amp;value=" . $modName . "&amp;spe=" . $_GET["spe"] . "\">" . $langs->trans("Activate") . "</a></td>\n  <td>&nbsp;</td>\n";
	  }
	
        print "</tr>\n";
    }
    
}
print "</table></div>\n";

llxFooter();
?>
