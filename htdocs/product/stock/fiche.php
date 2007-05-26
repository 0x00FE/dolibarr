<?php
/* Copyright (C) 2003-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Simon Tosser         <simon@kornog-computing.com>
 * Copyright (C) 2005-2006 Regis Houssin        <regis.houssin@cap-networks.com>
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
        \file       htdocs/product/stock/fiche.php
        \ingroup    stock
        \brief      Page fiche entrepot
        \version    $Revision$
*/

require("./pre.inc.php");

$langs->load("products");
$langs->load("stocks");
$langs->load("companies");


$sortfield = isset($_GET["sortfield"])?$_GET["sortfield"]:$_POST["sortfield"];
$sortorder = isset($_GET["sortorder"])?$_GET["sortorder"]:$_POST["sortorder"];
if (! $sortfield) $sortfield="p.ref";
if (! $sortorder) $sortorder="DESC";

$mesg = '';


/*
 * Actions
 */

// Ajout entrepot
if ($_POST["action"] == 'add')
{
    $entrepot = new Entrepot($db);

    $entrepot->ref         = trim($_POST["ref"]);
    $entrepot->libelle     = trim($_POST["libelle"]);
    $entrepot->description = trim($_POST["desc"]);
    $entrepot->statut      = trim($_POST["statut"]);
    $entrepot->lieu        = trim($_POST["lieu"]);
    $entrepot->address     = trim($_POST["address"]);
    $entrepot->cp          = trim($_POST["cp"]);
    $entrepot->ville       = trim($_POST["ville"]);
    $entrepot->pays_id     = trim($_POST["pays_id"]);

    if ($entrepot->libelle) {
        $id = $entrepot->create($user);
        if ($id > 0) {
            Header("Location: fiche.php?id=$id");
        }

        $_GET["action"] = 'create';
        $mesg="<div class='error'>".$entrepot->error."</div>";
    }
    else {
        $mesg="<div class='error'>".$langs->trans("ErrorWarehouseRefRequired")."</div>";
        $_GET["action"]="create";   // Force retour sur page cr�ation
    }
}

// Modification entrepot
if ($_POST["action"] == 'update' && $_POST["cancel"] <> $langs->trans("Cancel"))
{
    $entrepot = new Entrepot($db);
    if ($entrepot->fetch($_POST["id"]))
    {
        $entrepot->libelle     = trim($_POST["libelle"]);
        $entrepot->description = trim($_POST["desc"]);
        $entrepot->statut      = trim($_POST["statut"]);
        $entrepot->lieu        = trim($_POST["lieu"]);
        $entrepot->address     = trim($_POST["address"]);
        $entrepot->cp          = trim($_POST["cp"]);
        $entrepot->ville       = trim($_POST["ville"]);
        $entrepot->pays_id     = trim($_POST["pays_id"]);

        if ( $entrepot->update($_POST["id"], $user) > 0)
        {
            $_GET["action"] = '';
            $_GET["id"] = $_POST["id"];
            //$mesg = '<div class="ok">Fiche mise � jour</div>';
        }
        else
        {
            $_GET["action"] = 'edit';
            $_GET["id"] = $_POST["id"];
            $mesg = '<div class="error">Fiche non mise � jour !' . "<br>" . $entrepot->error.'</div>';
        }
    }
    else
    {
        $_GET["action"] = 'edit';
        $_GET["id"] = $_POST["id"];
        $mesg = '<div class="error">Fiche non mise � jour !' . "<br>" . $entrepot->error.'</div>';
    }
}

if ($_POST["cancel"] == $langs->trans("Cancel"))
{
    $_GET["action"] = '';
    $_GET["id"] = $_POST["id"];
}



/*
* Affichage fiche en mode cr�ation
*
*/

llxHeader("","",$langs->trans("WarehouseCard"));

$form=new Form($db);

if ($_GET["action"] == 'create')
{
    print "<form action=\"fiche.php\" method=\"post\">\n";
    print '<input type="hidden" name="action" value="add">';
    print '<input type="hidden" name="type" value="'.$type.'">'."\n";
    print_titre($langs->trans("NewWarehouse"));

    if ($mesg)
    {
        print $mesg;
    }

    print '<table class="border" width="100%">';

	// Ref
    print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td colspan="3"><input name="libelle" size="20" value=""></td></tr>';

    print '<tr><td >'.$langs->trans("LocationSummary").'</td><td colspan="3"><input name="lieu" size="40" value="'.$entrepot->lieu.'"></td></tr>';

	// Description
    print '<tr><td valign="top">'.$langs->trans("Description").'</td><td colspan="3">';
	if ($conf->fckeditor->enabled)
    {
	    // Editeur wysiwyg
		require_once(DOL_DOCUMENT_ROOT."/lib/doleditor.class.php");
		$doleditor=new DolEditor('desc',$entrepot->description,180,'dolibarr_notes','In',false);
		$doleditor->Create();
    }
    else
    {
		print '<textarea name="desc" cols="70" rows="5">'.$entrepot->description.'</textarea>';
    }
    print '</td></tr>';

    print '<tr><td>'.$langs->trans('Address').'</td><td colspan="3"><textarea name="address" cols="60" rows="3" wrap="soft">';
    print $entrepot->address;
    print '</textarea></td></tr>';

    print '<tr><td width="25%">'.$langs->trans('Zip').'</td><td width="25%"><input size="6" type="text" name="cp" value="'.$entrepot->cp.'"></td>';
    print '<td width="25%">'.$langs->trans('Town').'</td><td width="25%"><input type="text" name="ville" value="'.$entrepot->ville.'"></td></tr>';
    
    print '<tr><td>'.$langs->trans('Country').'</td><td colspan="3">';
    $form->select_pays($entrepot->pays_id?$entrepot->pays_id:$mysoc->pays_code, 'pays_id');
    print '</td></tr>';

    print '<tr><td>'.$langs->trans("Status").'</td><td colspan="3">';
    print '<select name="statut">';
    print '<option value="0" selected="true">'.$langs->trans("WarehouseClosed").'</option>';
    print '<option value="1">'.$langs->trans("WarehouseOpened").'</option>';
    print '</select>';
    print '</td></tr>';

    print '<tr><td colspan="4" align="center"><input type="submit" class="button" value="'.$langs->trans("Create").'"></td></tr>';

    print '</table>';
    print '</form>';
}
else
{
    if ($_GET["id"])
    {
        if ($mesg) print $mesg;

        $entrepot = new Entrepot($db);
        $result = $entrepot->fetch($_GET["id"]);
        if ($result < 0)
        {
            dolibarr_print_error($db);
        }

        /*
         * Affichage fiche
         */
        if ($_GET["action"] <> 'edit' && $_GET["action"] <> 're-edit')
        {

            /*
             * Affichage onglets
             */
            $h = 0;

            $head[$h][0] = DOL_URL_ROOT.'/product/stock/fiche.php?id='.$entrepot->id;
            $head[$h][1] = $langs->trans("WarehouseCard");
            $hselected=$h;
            $h++;

	          $head[$h][0] = DOL_URL_ROOT.'/product/stock/mouvement.php?id='.$entrepot->id;
	          $head[$h][1] = $langs->trans("StockMovements");
	          $h++;
	    
	          $head[$h][0] = DOL_URL_ROOT.'/product/stock/fiche-valo.php?id='.$entrepot->id;
	          $head[$h][1] = $langs->trans("EnhancedValue");
	          $h++;

	          $head[$h][0] = DOL_URL_ROOT.'/product/stock/user.php?id='.$entrepot->id;
	          $head[$h][1] = $langs->trans("Users");
	          $h++;

            $head[$h][0] = DOL_URL_ROOT.'/product/stock/info.php?id='.$entrepot->id;
            $head[$h][1] = $langs->trans("Info");
            $h++;

            dolibarr_fiche_head($head, $hselected, $langs->trans("Warehouse").': '.$entrepot->libelle);

            print '<table class="border" width="100%">';

			// Ref
            print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td colspan="3">'.$entrepot->libelle.'</td>';

            print '<tr><td>'.$langs->trans("LocationSummary").'</td><td colspan="3">'.$entrepot->lieu.'</td></tr>';

			// Description
            print '<tr><td valign="top">'.$langs->trans("Description").'</td><td colspan="3">'.nl2br($entrepot->description).'</td></tr>';

            print '<tr><td>'.$langs->trans('Address').'</td><td colspan="3">';
            print $entrepot->address;
            print '</td></tr>';

            print '<tr><td width="25%">'.$langs->trans('Zip').'</td><td width="25%">'.$entrepot->cp.'</td>';
            print '<td width="25%">'.$langs->trans('Town').'</td><td width="25%">'.$entrepot->ville.'</td></tr>';

            print '<tr><td>'.$langs->trans('Country').'</td><td colspan="3">';
            print $entrepot->pays;
            print '</td></tr>';

            // Statut
            print '<tr><td>'.$langs->trans("Status").'</td><td colspan="3">'.$entrepot->getLibStatut(4).'</td></tr>';

            print '<tr><td valign="top">'.$langs->trans("NumberOfProducts").'</td><td colspan="3">';
            print $entrepot->nb_products();
            print "</td></tr>";

            // Dernier mouvement
            $sql = "SELECT max( ".$db->pdate("m.datem").") as datem";
            $sql .= " FROM llx_stock_mouvement as m";
            $sql .= " WHERE m.fk_entrepot = '".$entrepot->id."';";
            $resql = $db->query($sql);
            if ($resql)
            {
                $row = $db->fetch_row($resql);
            }
            else
            {
                dolibarr_print_error($db);
            }

            print '<tr><td valign="top">'.$langs->trans("LastMovement").'</td><td colspan="3">';
            print '<a href="mouvement.php?id='.$entrepot->id.'">'.dolibarr_print_date($row[0]).'</a>';
            print "</td></tr>";

            print "</table>";

            print '</div>';
            
            
            /* ************************************************************************** */
            /*                                                                            */
            /* Barre d'action                                                             */
            /*                                                                            */
            /* ************************************************************************** */
            
            print "<div class=\"tabsAction\">\n";
            
            if ($_GET["action"] == '')
            {
                print "<a class=\"butAction\" href=\"fiche.php?action=edit&id=".$entrepot->id."\">".$langs->trans("Edit")."</a>";
            }
            
            print "</div>";


            /* ************************************************************************** */
            /*                                                                            */
            /* Affichage de la liste des produits de l'entrepot                           */
            /*                                                                            */
            /* ************************************************************************** */
            print '<br>';
            
            print '<table class="noborder" width="100%">';
            print "<tr class=\"liste_titre\">";

            print_liste_field_titre($langs->trans("Product"),"", "p.ref","&amp;id=".$_GET['id'],"","",$sortfield);
            print_liste_field_titre($langs->trans("Label"),"", "p.label","&amp;id=".$_GET['id'],"","",$sortfield);
            print_liste_field_titre($langs->trans("Units"),"", "ps.reel","&amp;id=".$_GET['id'],"",'align="right"',$sortfield);
            
            print "</tr>";
            $sql = "SELECT p.rowid as rowid, p.ref, p.label as produit, ps.reel as value ";
            $sql .= " FROM ".MAIN_DB_PREFIX."product_stock ps, ".MAIN_DB_PREFIX."product p ";
            if ($conf->categorie->enabled && !$user->rights->categorie->voir)
            {
            	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."categorie_product as cp ON cp.fk_product = p.rowid";
              $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."categorie as c ON cp.fk_categorie = c.rowid";
            }
            $sql .= " WHERE ps.fk_product = p.rowid ";
            $sql .= " AND ps.reel >0";
            $sql .= " AND ps.fk_entrepot = ".$entrepot->id;
            if ($conf->categorie->enabled && !$user->rights->categorie->voir)
            {
            	$sql.= ' AND IFNULL(c.visible,1)=1';
            }

			$sql .=  " ORDER BY " . $sortfield . " " . $sortorder;

            //$sql .= $db->plimit($limit + 1 ,$offset);

            $resql = $db->query($sql) ;
            if ($resql)
            {
                $num = $db->num_rows($resql);
                $i = 0;
                $var=True;
                while ($i < $num)
                {
                    $objp = $db->fetch_object($resql);
                    
                   // Multilangs
					        if ($conf->global->MAIN_MULTILANGS) // si l'option est active
					        {
						        $sql = "SELECT label FROM ".MAIN_DB_PREFIX."product_det";
						        $sql.= " WHERE fk_product=".$objp->rowid." AND lang='". $langs->getDefaultLang() ."'";
						        $sql.= " LIMIT 1";

						        $result = $db->query($sql);
						        if ($result)
						        {
							        $objtp = $db->fetch_object($result);
							        if ($objtp->label != '') $objp->produit = $objtp->label;
						         }
					         }
                    
                    $var=!$var;
                    //print '<td>'.dolibarr_print_date($objp->datem).'</td>';
                    print "<tr $bc[$var]>";
                    print "<td><a href=\"../fiche.php?id=$objp->rowid\">";
                    print img_object($langs->trans("ShowProduct"),"product").' '.$objp->ref;
                    print "</a></td>";
                    print '<td>'.$objp->produit.'</td>';
                    print '<td align="right">'.$objp->value.'</td>';
                    //print "<td><a href=\"fiche.php?id=$objp->entrepot_id\">";
                    //print img_object($langs->trans("ShowWarehous"),"stock").' '.$objp->stock;
                    //print "</a></td>\n";
                    print "</tr>";
                    $i++;
                }
                $db->free($resql);
            }
            else
            {
                dolibarr_print_error($db);
            }
            print "</table>\n";
        }


        /*
         * Edition fiche
         */
        if (($_GET["action"] == 'edit' || $_GET["action"] == 're-edit') && 1)
        {
            print_fiche_titre($langs->trans("WarehouseEdit"), $mesg);

            print '<form action="fiche.php" method="POST">';
            print '<input type="hidden" name="action" value="update">';
            print '<input type="hidden" name="id" value="'.$entrepot->id.'">';

            print '<table class="border" width="100%">';

			// Ref
            print '<tr><td width="20%">'.$langs->trans("Ref").'</td><td colspan="3"><input name="libelle" size="20" value="'.$entrepot->libelle.'"></td></tr>';
            
            print '<tr><td width="20%">'.$langs->trans("LocationSummary").'</td><td colspan="3"><input name="lieu" size="40" value="'.$entrepot->lieu.'"></td></tr>';

            // Description
            print '<tr><td valign="top">'.$langs->trans("Description").'</td><td colspan="3">';
			if ($conf->fckeditor->enabled)
		    {
			    // Editeur wysiwyg
				require_once(DOL_DOCUMENT_ROOT."/lib/doleditor.class.php");
				$doleditor=new DolEditor('desc',$entrepot->description,180,'dolibarr_notes','In',false);
				$doleditor->Create();
		    }
		    else
		    {
				print '<textarea name="desc" cols="70" rows="5">'.$entrepot->description.'</textarea>';
		    }
            print '</td></tr>';

            print '<tr><td>'.$langs->trans('Address').'</td><td colspan="3"><textarea name="address" cols="60" rows="3" wrap="soft">';
            print $entrepot->address;
            print '</textarea></td></tr>';

            print '<tr><td>'.$langs->trans('Zip').'</td><td><input size="6" type="text" name="cp" value="'.$entrepot->cp.'"></td>';
            print '<td>'.$langs->trans('Town').'</td><td><input type="text" name="ville" value="'.$entrepot->ville.'"></td></tr>';

            print '<tr><td>'.$langs->trans('Country').'</td><td colspan="3">';
            $form->select_pays($entrepot->pays_id, 'pays_id');
            print '</td></tr>';

            print '<tr><td width="20%">'.$langs->trans("Status").'</td><td colspan="3">';
            print '<select name="statut">';
            print '<option value="0" '.($entrepot->statut == 0?'selected="true"':'').'>'.$langs->trans("WarehouseClosed").'</option>';
            print '<option value="1" '.($entrepot->statut == 0?'':'selected="true"').'>'.$langs->trans("WarehouseOpened").'</option>';
            print '</select>';
            print '</td></tr>';

            print '<tr><td colspan="4" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'">&nbsp;';
            print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'"></td></tr>';
            print '</table>';
            print '</form>';

        }
    }
}




$db->close();

llxFooter('$Date$ - $Revision$');
?>
