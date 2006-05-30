<?php
/* Copyright (C) 2005      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/user/group/fiche.php
        \brief      Onglet groupes utilisateurs
        \version    $Revision$
*/

require("./pre.inc.php");


// Defini si peux lire/modifier utilisateurs et permisssions
$canreadperms=($user->admin || $user->rights->user->user->lire);
$caneditperms=($user->admin || $user->rights->user->user->creer);
$candisableperms=($user->admin || $user->rights->user->user->supprimer);

$langs->load("users");

$action=isset($_GET["action"])?$_GET["action"]:$_POST["action"];


/**
 *  Action suppression groupe
 */
if ($_POST["action"] == 'confirm_delete' && $_POST["confirm"] == "yes")
{
  $editgroup = new Usergroup($db, $_GET["id"]);
  $editgroup->fetch($_GET["id"]);
  $editgroup->delete();
  Header("Location: index.php");
}

/**
 *  Action ajout groupe
 */
if ($_POST["action"] == 'add' && $caneditperms)
{
    $message="";
    if (! $_POST["nom"]) {
        $message='<div class="error">'.$langs->trans("NameNotDefined").'</div>';
        $action="create";       // Go back to create page
    }

    if (! $message) {
        $editgroup = new UserGroup($db,0);

        $editgroup->nom    = trim($_POST["nom"]);
        $editgroup->note   = trim($_POST["note"]);

        $db->begin();

        $id = $editgroup->create();

        if ($id > 0)
        {
            $db->commit();

            Header("Location: fiche.php?id=".$editgroup->id);
        }
        else
        {
            $db->rollback();

            $message='<div class="error">'.$langs->trans("ErrorGroupAlreadyExists",$editgroup->nom).'</div>';
            $action="create";       // Go back to create page
        }
    }
}

if ($_POST["action"] == 'adduser' && $caneditperms)
{
    if ($_POST["user"])
    {
        $edituser = new User($db, $_POST["user"]);
        $edituser->SetInGroup($_GET["id"]);

        Header("Location: fiche.php?id=".$_GET["id"]);
    }
}

if ($_GET["action"] == 'removeuser' && $caneditperms)
{
    if ($_GET["user"])
    {
        $edituser = new User($db, $_GET["user"]);
        $edituser->RemoveFromGroup($_GET["id"]);

        Header("Location: fiche.php?id=".$_GET["id"]);
    }
}

if ($_POST["action"] == 'update' && $caneditperms)
{
    $message="";

    $db->begin();

    $editgroup = new Usergroup($db, $_GET["id"]);
    $editgroup->fetch($_GET["id"]);

    $editgroup->nom           = $_POST["group"];
    $editgroup->note          = $_POST["note"];

    $ret=$editgroup->update();

    if ($ret >= 0) {
        $message.='<div class="ok">'.$langs->trans("GroupModified").'</div>';
        $db->commit();
    } else {
        $message.='<div class="error">'.$editgroup->error.'</div>';
        $db->rollback;
    }

}


llxHeader('',$langs->trans("GroupCard"));


/* ************************************************************************** */
/*                                                                            */
/* Affichage fiche en mode cr�ation                                           */
/*                                                                            */
/* ************************************************************************** */

if ($action == 'create')
{
    print_titre($langs->trans("NewGroup"));
    print "<br>";

    if ($message) { print $message."<br>"; }

    print '<form action="fiche.php" method="post">';
    print '<input type="hidden" name="action" value="add">';

    print '<table class="border" width="100%">';

    print "<tr>".'<td valign="top">'.$langs->trans("Name").'</td>';
    print '<td class="valeur"><input size="30" type="text" name="nom" value=""></td></tr>';

    print "<tr>".'<td valign="top">'.$langs->trans("Note").'</td><td>';
    print "<textarea name=\"note\" rows=\"12\" cols=\"40\">";
    print "</textarea></td></tr>\n";

    print "<tr>".'<td align="center" colspan="2"><input class="button" value="'.$langs->trans("CreateGroup").'" type="submit"></td></tr>';
    print "</table>\n";
    print "</form>";
}


/* ************************************************************************** */
/*                                                                            */
/* Visu et edition                                                            */
/*                                                                            */
/* ************************************************************************** */
else
{
    if ($_GET["id"] )
    {
        $group = new UserGroup($db);
        $group->fetch($_GET["id"]);

        /*
         * Affichage onglets
         */

        $h = 0;

        $head[$h][0] = DOL_URL_ROOT.'/user/group/fiche.php?id='.$group->id;
        $head[$h][1] = $langs->trans("GroupCard");
        $hselected=$h;
        $h++;

        $head[$h][0] = DOL_URL_ROOT.'/user/group/perms.php?id='.$group->id;
        $head[$h][1] = $langs->trans("GroupRights");
        $h++;

        dolibarr_fiche_head($head, $hselected, $langs->trans("Group").": ".$group->nom);


        /*
         * Confirmation suppression
         */
        if ($action == 'delete')
        {
            $html = new Form($db);
            $html->form_confirm("fiche.php?id=$group->id",$langs->trans("DeleteAGroup"),$langs->trans("ConfirmDeleteGroup",$group->name),"confirm_delete");
        }


        /*
         * Fiche en mode visu
         */

        if ($_GET["action"] != 'edit') {

            print '<table class="border" width="100%">';
            print '<tr><td width="25%" valign="top">'.$langs->trans("Name").'</td>';
            print '<td width="75%" class="valeur">'.$group->nom.'</td>';
            print "</tr>\n";
            print '<tr><td width="25%" valign="top">'.$langs->trans("Note").'</td>';
            print '<td class="valeur">'.nl2br($group->note).'&nbsp;</td>';
            print "</tr>\n";
            print "</table>\n";
    
            print '</div>';
    
            if ($message) { print $message; }
    
            /*
             * Barre d'actions
             *
             */
            print '<div class="tabsAction">';
    
            if ($caneditperms)
            {
                print '<a class="tabAction" href="fiche.php?id='.$group->id.'&amp;action=edit">'.$langs->trans("Edit").'</a>';
            }
    
            if ($candisableperms)
            {
                print '<a class="butDelete" href="fiche.php?action=delete&amp;id='.$group->id.'">'.$langs->trans("DeleteGroup").'</a>';
            }
    
            print "</div>\n";
            print "<br>\n";
    
    
            /*
             * Liste des utilisateurs dans le groupe
             */
    
            print_fiche_titre($langs->trans("ListOfUsersInGroup"));
            
            // On s�lectionne les users qui ne sont pas d�j� dans le groupe
            $uss = array();
    
            $sql = "SELECT u.rowid, u.login, u.name, u.firstname, u.code, u.admin";
            $sql.= " FROM ".MAIN_DB_PREFIX."user as u";
            #      $sql .= " LEFT JOIN llx_usergroup_user ug ON u.rowid = ug.fk_user";
            #      $sql .= " WHERE ug.fk_usergroup IS NULL";
            $sql .= " ORDER BY u.name";
    
            $result = $db->query($sql);
            if ($result)
            {
                $num = $db->num_rows($result);
                $i = 0;
    
                while ($i < $num)
                {
                    $obj = $db->fetch_object($result);
    
                    $uss[$obj->rowid] = ucfirst(stripslashes($obj->name)).' '.ucfirst(stripslashes($obj->firstname));
                    if ($obj->login) $uss[$obj->rowid].=' ('.$obj->login.')';
                    $i++;
                }
            }
            else {
                dolibarr_print_error($db);
            }
    
            if ($user->admin)
            {
                $form = new Form($db);
                print '<form action="fiche.php?id='.$group->id.'" method="post">'."\n";
                print '<input type="hidden" name="action" value="adduser">';
                print '<table class="noborder" width="100%">'."\n";
                //	  print '<tr class="liste_titre"><td width="25%">'.$langs->trans("NonAffectedUsers").'</td>'."\n";
                print '<tr class="liste_titre"><td width="25%">'.$langs->trans("UsersToAdd").'</td>'."\n";
                print '<td>';
                print $form->select_array("user",$uss);
                print ' &nbsp; ';
                print '<input type="submit" class=button value="'.$langs->trans("Add").'">';
                print '</td></tr>'."\n";
                print '</table></form>'."\n";
            }
    
            /*
             * Membres du groupe
             */
            $sql = "SELECT u.rowid, u.login, u.name, u.firstname, u.code, u.admin";
            $sql.= " FROM ".MAIN_DB_PREFIX."user as u,";
            $sql.= " ".MAIN_DB_PREFIX."usergroup_user as ug";
            $sql.= " WHERE ug.fk_user = u.rowid";
            $sql.= " AND ug.fk_usergroup = ".$group->id;
            $sql.= " ORDER BY u.name";
    
            $result = $db->query($sql);
            if ($result)
            {
                $num = $db->num_rows($result);
                $i = 0;
    
                print '<br>';
    
                print '<table class="noborder" width="100%">';
                print '<tr class="liste_titre">';
                print '<td width="25%">'.$langs->trans("Login").'</td>';
                print '<td width="25%">'.$langs->trans("Lastname").'</td>';
                print '<td width="25%">'.$langs->trans("Firstname").'</td>';
                print '<td>'.$langs->trans("Code").'</td>';
                print "<td>&nbsp;</td></tr>\n";
                if ($num) {
                    $var=True;
                    while ($i < $num)
                    {
                        $obj = $db->fetch_object($result);
                        $var=!$var;
        
                        print "<tr $bc[$var]>";
                        print '<td>';
                        print '<a href="'.DOL_URL_ROOT.'/user/fiche.php?id='.$obj->rowid.'">'.img_object($langs->trans("ShowUser"),"user").' '.$obj->login.'</a>';
                        if ($obj->admin) print img_picto($langs->trans("Administrator"),'star');
                        print '</td>';
                        print '<td>'.ucfirst(stripslashes($obj->name)).'</td>';
                        print '<td>'.ucfirst(stripslashes($obj->firstname)).'</td>';
                        print '<td>'.$obj->code.'</td><td>';
        
                        if ($user->admin)
                        {
        
                            print '<a href="fiche.php?id='.$group->id.'&amp;action=removeuser&amp;user='.$obj->rowid.'">';
                            print img_delete($langs->trans("RemoveFromGroup"));
                        }
                        else
                        {
                            print "-";
                        }
                        print "</td></tr>\n";
                        $i++;
                    }
                }
                else
                {
                    print '<tr><td colspan=2>'.$langs->trans("None").'</td></tr>';    
                }
                print "</table>";
                print "<br>";
                $db->free($result);
            }
            else {
                dolibarr_print_error($db);
            }
        }

        /*
         * Fiche en mode edition
         */
        if ($_GET["action"] == 'edit' && $user->admin)
        {
            print '<form action="fiche.php?id='.$group->id.'" method="post" name="updategroup" enctype="multipart/form-data">';
            print '<input type="hidden" name="action" value="update">';

            print '<table class="border" width="100%">';
            print '<tr><td width="25%" valign="top">'.$langs->trans("Name").'</td>';
            print '<td width="75%" class="valeur"><input size="12" maxlength="8" type="text" name="group" value="'.$group->nom.'"></td>';
            print "</tr>\n";
            print '<tr><td width="25%" valign="top">'.$langs->trans("Note").'</td>';
            print '<td class="valeur"><textarea name="note" rows="12" cols="40">'.nl2br($group->note).'</textarea></td>';
            print "</tr>\n";
            print '<tr><td align="center" colspan="2"><input value="'.$langs->trans("Save").'" type="submit"></td></tr>';
            print "</table>\n";
            print "<br>\n";
            print '</form>';
    
            print '</div>';
        }

    }
}

$db->close();

llxFooter('$Date$ - $Revision$');
?>
