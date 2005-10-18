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
 
/**
        \file       htdocs/admin/modules.php
        \brief      Page de configuration et activation des modules
        \version    $Revision$
*/

require("./pre.inc.php");

if (!$user->admin)
    accessforbidden();

/*
 * Actions
 */

if ($_GET["action"] == 'set' && $user->admin)
{
    $result=Activate($_GET["value"]);
    $mesg='';
    if ($result) $mesg=$result;
    Header("Location: modules.php?spe=".$_GET["spe"]."&mesg=".urlencode($mesg));
}

if ($_GET["action"] == 'reset' && $user->admin)
{
    $result=UnActivate($_GET["value"]);
    $mesg='';
    if ($result) $mesg=$result;
    Header("Location: modules.php?spe=".$_GET["spe"]."&mesg=".urlencode($mesg));
}


/**     \brief      Active un module
        \param      value   Nom du module a activer
*/
function Activate($value)
{
    global $db, $modules, $langs;

    $modName = $value;

    // Activation du module
    if ($modName)
    {
        $file = $modName . ".class.php";
        include_once("../includes/modules/$file");
        $objMod = new $modName($db);

        // Test si version PHP ok
        $verphp=versionphp();
        $vermin=$objMod->phpmin;
        if (is_array($vermin) && versioncompare($verphp,$vermin) < 0)
        {
            return $langs->trans("ErrorModuleRequirePHPVersion",versiontostring($vermin));
        }

        $objMod->init();
    }

    // Activation des modules dont le module d�pend
    for ($i = 0; $i < sizeof($objMod->depends); $i++)
    {
        Activate($objMod->depends[$i]);
    }

    // Desactivation des modules qui entrent en conflit
    for ($i = 0; $i < sizeof($objMod->conflictwith); $i++)
    {
        UnActivate($objMod->conflictwith[$i],0);
    }

    return 0;
}


/**     \brief      D�sactive un module
        \param      value               Nom du module a d�sactiver
        \param      requiredby          1=Desactive aussi modules d�pendants
*/
function UnActivate($value,$requiredby=1)
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
    if ($requiredby)
    {
        for ($i = 0; $i < sizeof($objMod->requiredby); $i++)
        {
            UnActivate($objMod->requiredby[$i]);
        }
    }
    
    return 0;
}


/*
 * Affichage page
 */
 
llxHeader("","");

$h = 0;

$head[$h][0] = DOL_URL_ROOT."/admin/modules.php?spe=0";
$head[$h][1] = $langs->trans("ModulesCommon");
if (!$_GET["spe"]) $hselected=$h;
$h++;

$head[$h][0] = DOL_URL_ROOT."/admin/modules.php?spe=1";
$head[$h][1] = $langs->trans("ModulesSpecial");
if ($_GET["spe"]) $hselected=$h;
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

if ($_GET["mesg"]) 
{
    $mesg=urldecode($_GET["mesg"]);
    print '<div class="error">'.$mesg.'</div>';
}

print "<br>\n";
print "<table class=\"noborder\" width=\"100%\">\n";
print "<tr class=\"liste_titre\">\n";
print "  <td>".$langs->trans("Family")."</td>\n";
print "  <td colspan=\"2\">".$langs->trans("Module")."</td>\n";
print "  <td>".$langs->trans("Description")."</td>\n";
print "  <td align=\"center\">".$langs->trans("Version")."</td>\n";
print "  <td align=\"center\">".$langs->trans("DbVersion")."</td>\n";
print "  <td align=\"center\">".$langs->trans("Activated")."</td>\n";
print "  <td align=\"center\">".$langs->trans("Action")."</td>\n";
print "  <td>".$langs->trans("SetupShort")."</td>\n";
print "</tr>\n";


$dir = DOL_DOCUMENT_ROOT . "/includes/modules/";

$handle=opendir($dir);
$modules = array();
$orders = array();
$i = 0;
$j = 0;
while (($file = readdir($handle))!==false)
{
    if (is_readable($dir.$file) && substr($file, 0, 3) == 'mod'  && substr($file, strlen($file) - 10) == '.class.php')
    {
        $modName = substr($file, 0, strlen($file) - 10);

        if ($modName)
        {
            include_once($dir.$file);
            $objMod = new $modName($db);

            if ($objMod->numero > 0)
            {
                $j = $objMod->numero;
            }
            else
            {
                $j = 1000 + $i;
            }

            $modules[$i] = $objMod;

            $nom[$i]     = $modName;
            $numero[$i]  = $j;
            $orders[$i]  = "$objMod->family"."_".$j;   // Tri par famille puis numero module
            $j++;
            $i++;
        }
    }
}

asort($orders);
$var=True;

$familylib=array(
'base'=>$langs->trans("ModuleBase"),
'crm'=>$langs->trans("ModuleFamilyCrm"),
'products'=>$langs->trans("ModuleFamilyProducts"),
'hr'=>$langs->trans("ModuleFamilyHr"),
'projects'=>$langs->trans("ModuleFamilyProjects"),
'other'=>$langs->trans("ModuleFamilyOther"),
'technic'=>$langs->trans("ModuleFamilyTechnic"),
'financial'=>$langs->trans("ModuleFamilyFinancial"),
);
foreach ($orders as $key => $value)
{
    $tab=split('_',$value);
    $family=$tab[0]; $numero=$tab[1];

    $modName = $nom[$key];
    $objMod  = $modules[$key];

    // On affiche pas module si en version 'development' et que
    // constante MAIN_SHOW_DEVELOPMENT_MODULES non d�finie
    if ($objMod->version == 'development' && ! $conf->global->MAIN_SHOW_DEVELOPMENT_MODULES)
    {
        continue;
    }
    
    $const_name = $objMod->const_name;

    if ($oldfamily && $family!=$oldfamily && $atleastoneforfamily) {
        print "<tr class=\"liste_titre\">\n  <td colspan=\"9\"></td>\n</tr>\n";
        $atleastoneforfamily=0;
    }

    if((!$objMod->special && !$_GET["spe"] ) or ($objMod->special && $_GET["spe"]))
    {
        $atleastoneforfamily=1;
        $var=!$var;

        print "<tr $bc[$var]>\n";

        print "  <td class=\"body\" valign=\"top\">";
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
        print $objMod->getDbVersion();
        print "</td>\n  <td align=\"center\">";

        if ($conf->global->$const_name == 1)
        {
            print img_tick();
        }
        else
        {
            print "&nbsp;";
        }

        print "</td>\n  <td align=\"center\">";

        if ($conf->global->$const_name == 1)
        {
            // Module actif
            print "<a href=\"modules.php?id=".$objMod->numero."&amp;action=reset&amp;value=" . $modName . "&amp;spe=" . $_GET["spe"] . "\">" . $langs->trans("Disable") . "</a></td>\n";


            if ($objMod->config_page_url)
            {
                if (is_array($objMod->config_page_url)) {
                    print '  <td align="center">';
                    $i=0;
                    foreach ($objMod->config_page_url as $page)
                    {
                        if ($i++)
                        {
                            print '<a href="'.$page.'">'.ucfirst($page).'</a>&nbsp;';
                        }
                        else
                        {
                            //print '<a href="'.$page.'">'.$langs->trans("Setup").'</a>&nbsp;';
                            print '<a href="'.$page.'">'.img_picto($langs->trans("Setup"),"setup").'</a>&nbsp;';
                        }
                    }
                    print "</td>\n";
                }
                else
                {
                    //print '  <td align="center"><a href="'.$objMod->config_page_url.'">'.$langs->trans("Setup").'</a></td>';
                    print '  <td align="center"><a href="'.$objMod->config_page_url.'">'.img_picto($langs->trans("Setup"),"setup").'</a></td>';
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


// Pour eviter bug mise en page IE
print '<div class="tabsAction">';
print '</div>';


llxFooter('$Date$ - $Revision$');
?>
