<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org> 
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
	    \file       htdocs/includes/menus/barre_top/default.php
		\brief      Gestionnaire par d�faut du menu du haut
		\version    $Revision$

        \remarks    La construction d'un gestionnaire pour le menu du haut est simple:
        \remarks    Toutes les entr�es de menu � faire apparaitre dans la barre du haut
        \remarks    doivent �tre affich�es par <a class="tmenu" href="...?mainmenu=...">...</a>
        \remarks    On peut �ventuellement ajouter l'attribut id="sel" dans la balise <a>
        \remarks    quand il s'agit de l'entr�e du menu qui est s�lectionn�e.
*/


/**
        \class      MenuTop
	    \brief      Classe permettant la gestion par d�faut du menu du haut
*/

class MenuTop {

    var $require_left=array();  // Si doit etre en phase avec un gestionnaire de menu gauche particulier
    var $atarget="";            // Valeur du target a utiliser dans les liens
    
    /**
     *    \brief      Constructeur
     *    \param      db      Handler d'acc�s base de donn�e
     */
    function MenuTop($db)
    {
        $this->db=$db;
    }
    
    
    /**
     *    \brief      Affiche le menu
     */
    function showmenu()
    {

        global $conf,$langs;
        
        // Entr�e home
        $id="";
        if ($_GET["mainmenu"] == "home" || ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "home"))
        {
            $id="sel";
        }
        if (! ereg("^".DOL_URL_ROOT."\/(adherents|comm|compta|product|fourn|projet)\/",$_SERVER["PHP_SELF"])) {
            $id="sel";
        }
        print '<a class="tmenu" id="'.$id.'" href="'.DOL_URL_ROOT.'/index.php?mainmenu=home&leftmenu="'.($this->atarget?" target=$this->atarget":"").'>'.$langs->trans("Home").'</a>';

        // Entr�e adherent
        if ($conf->adherent->enabled)
        {
            $langs->load("members");
        
            $class="";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "members")
            {
                $class='class="tmenu" id="sel"';
            }
            elseif (ereg("^".DOL_URL_ROOT."\/adherents\/",$_SERVER["PHP_SELF"]))
            {
                $class='class="tmenu" id="sel"';
            }
            else
            {
                $class = 'class="tmenu"';
            }
        
            print '<a '.$class.' href="'.DOL_URL_ROOT.'/adherents/index.php?mainmenu=members"'.($this->atarget?" target=$this->atarget":"").'>'.$langs->trans("Members").'</a>';
        }
        
        // Entr�e commercial
        if ($conf->commercial->enabled)
        {
            $langs->load("commercial");
        
            $class="";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "commercial")
            {
                $class='class="tmenu" id="sel"';
            }
            elseif (ereg("^".DOL_URL_ROOT."\/comm\/",$_SERVER["PHP_SELF"]))
            {
                $class='class="tmenu" id="sel"';
            }
            else
            {
                $class = 'class="tmenu"';
            }
        
            print '<a '.$class.' href="'.DOL_URL_ROOT.'/comm/index.php?mainmenu=commercial"'.($this->atarget?" target=$this->atarget":"").'>'.$langs->trans("Commercial").'</a>';
        
        }
        
        // Entr�e compta
        if ($conf->compta->enabled || $conf->banque->enabled || $conf->caisse->enabled)
        {
            $langs->load("compta");
        
            $class="";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "compta")
            {
                $class='class="tmenu" id="sel"';
            }
            elseif (ereg("^".DOL_URL_ROOT."\/compta\/",$_SERVER["PHP_SELF"]))
            {
                $class='class="tmenu" id="sel"';
            }
            else
            {
                $class = 'class="tmenu"';
            }
        
            print '<a '.$class.' href="'.DOL_URL_ROOT.'/compta/index.php?mainmenu=accountancy"'.($this->atarget?" target=$this->atarget":"").'>'.$langs->trans("Accountancy")."/".$langs->trans("Treasury").'</a>';
        
        }
        
        // Entr�e produit/service
        if ($conf->produit->enabled || $conf->service->enabled)
        {
            $langs->load("products");
        
            $class="";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "product")
            {
                $class='class="tmenu" id="sel"';
            }
            elseif (ereg("^".DOL_URL_ROOT."\/product\/",$_SERVER["PHP_SELF"]))
            {
                $class='class="tmenu" id="sel"';
            }
            else
            {
                $class = 'class="tmenu"';
            }
            $chaine="";
            if ($conf->produit->enabled) { $chaine.=$langs->trans("Products"); }
            if ($conf->produit->enabled && $conf->service->enabled) { $chaine.="/"; }
            if ($conf->service->enabled) { $chaine.=$langs->("Services"); }
        
        
            print '<a '.$class.' href="'.DOL_URL_ROOT.'/product/index.php?mainmenu=products"'.($this->atarget?" target=$this->atarget":"").'>'.$chaine.'</a>';
        
        }
        
        // Entr�e fournisseur
        if ($conf->fournisseur->enabled)
        {
            $langs->load("suppliers");
        
            $class="";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "suppliers")
            {
                $class='class="tmenu" id="sel"';
            }
            elseif (ereg("^".DOL_URL_ROOT."\/fourn\/",$_SERVER["PHP_SELF"]))
            {
                $class='class="tmenu" id="sel"';
            }
            else
            {
                $class = 'class="tmenu"';
            }
        
            print '<a '.$class.' href="'.DOL_URL_ROOT.'/fourn/index.php?mainmenu=suppliers"'.($this->atarget?" target=$this->atarget":"").'>'.$langs->trans("Suppliers").'</a>';
        }
        
        // Entr�e webcal
        if ($conf->webcal->enabled)
        {
            $langs->load("other");
        
            $class="";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "webcalendar")
            {
                $class='class="tmenu" id="sel"';
            }
            elseif (ereg("^".DOL_URL_ROOT."\/projet\/",$_SERVER["PHP_SELF"]) || ereg("^".DOL_URL_ROOT."\/webcalendar\/",$_SERVER["PHP_SELF"]))
            {
                $class='class="tmenu" id="sel"';
            }
            else
            {
                $class = 'class="tmenu"';
            }
        
            print '<a '.$class.' href="'.DOL_URL_ROOT.'/projet/webcal.php?mainmenu=webcal"'.($this->atarget?" target=$this->atarget":"").'>'.$langs->trans("Calendar").'</a>';
        };

    }
    
}

?>
