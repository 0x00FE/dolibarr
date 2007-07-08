<?php
/* Copyright (C) 2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/user/fiche.php
        \brief      Onglet user et permissions de la fiche utilisateur
        \version    $Revision$
*/

require("../master.inc.php");
require_once(DOL_DOCUMENT_ROOT."/contact.class.php");
require_once(DOL_DOCUMENT_ROOT."/lib/ldap.class.php");
require_once(DOL_DOCUMENT_ROOT."/lib/usergroups.lib.php");

$user->getrights('user');

$langs->load("main");
$langs->load("other");
$langs->load("users");
$langs->load("companies");
$langs->load("ldap");

$action=isset($_GET["action"])?$_GET["action"]:$_POST["action"];
$mode=$dolibarr_main_authentication;
if (! $mode) $mode='http';


/**
 * Actions
 */

// Action modif mot de passe
if ($_GET["action"] == 'validatenewpassword' && $_GET["username"] && $_GET["passwordmd5"])
{
    $edituser = new User($db);
    $result=$edituser->fetch($_GET["username"]);
	if ($result < 0)
	{
        $message = '<div class="error">'.$langs->trans("ErrorLoginDoesNotExists",$_GET["username"]).'</div>';
	}
	else
	{
		if (md5($edituser->pass_temp) == $_GET["passwordmd5"])
		{
			$newpassword=$edituser->password($user,$edituser->pass_temp,$conf->password_encrypted,0);
			dolibarr_syslog("passwordforgotten.php new password saved in database");
			//session_start();
			//$_SESSION["loginmesg"]=$langs->trans("PasswordChanged");
			header("Location: ".DOL_URL_ROOT.'/');
			exit;
		}
		else
		{
	        $message = '<div class="error">'.$langs->trans("ErrorFailedToValidatePassword").'</div>';
		}
	}
}
// Action modif mot de passe
if ($_POST["action"] == 'buildnewpassword' && $_POST["username"])
{
    $edituser = new User($db);
    $result=$edituser->fetch($_POST["username"]);
	if ($result < 0)
	{
        $message = '<div class="error">'.$langs->trans("ErrorLoginDoesNotExists",$_POST["username"]).'</div>';
	}
	else
	{
		if (! $edituser->email)
		{
	        $message = '<div class="error">'.$langs->trans("ErrorLoginHasNoEmail").'</div>';
		}
		else
		{
			$newpassword=$edituser->password($user,'',$conf->password_encrypted,1);
		    if ($newpassword < 0)
		    {
		        // Echec
		        $message = '<div class="error">'.$langs->trans("ErrorFailedToChangePassword").'</div>';
		    }
		    else 
		    {
		        // Succes
		        if ($edituser->send_password($user,$newpassword,1) > 0)
		        {
		        	$message = '<div class="ok">'.$langs->trans("PasswordChangedAndSentTo",$edituser->email).'</div>';
					    //$message.=$newpassword;
					  }
					  else
				    {
				    	//$message = '<div class="ok">'.$langs->trans("PasswordChangedTo",$newpassword).'</div>';
					    $message.= '<div class="error">'.$edituser->error.'</div>';
				    }
		    }
		}
	}
}



/*
 * Affichage page
 */
 
$conf->css  = "theme/".$conf->theme."/".$conf->theme.".css";
// Si feuille de style en php existe
if (file_exists(DOL_DOCUMENT_ROOT.'/'.$conf->css.".php")) $conf->css.=".php";

print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'."\n";

// En tete html
print "<html>\n";
print "<head>\n";
print '<meta name="robots" content="noindex,nofollow">'."\n";      // Evite indexation par robots
print "<title>Dolibarr Authentification</title>\n";
print '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.'/'.$conf->css.'">'."\n";
print '<style type="text/css">'."\n";
print '<!--'."\n";
print '#login {';
print '  margin-top: 70px;';
print '  margin-bottom: 30px;';
print '  text-align: center;';
print '  font: 12px arial,helvetica;';
print '}'."\n";
print '#login table {';
print '  border: 1px solid #C0C0C0;';
if (file_exists(DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/img/login_background.png'))
{
  print 'background: #F0F0F0 url('.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/login_background.png) repeat-x;';
}
else
{
  print 'background: #F0F0F0 url('.DOL_URL_ROOT.'/theme/login_background.png) repeat-x;';
}
print 'font-size: 12px;';
print '}'."\n";
print '-->'."\n";
print '</style>'."\n";
print '<script language="javascript" type="text/javascript">'."\n";
print "function donnefocus() {\n";
print "document.getElementsByTagName('INPUT')[0].focus();";
print "}\n";
print '</script>'."\n";
print '</head>'."\n";

// Body
print '<body class="body" onload="donnefocus();">';

// Form
print '<form id="login" action="'.$_SERVER["PHP_SELF"].'" method="post" name="login">';
print '<input type="hidden" name="action" value="buildnewpassword">';

// Table 1
print '<table cellpadding="0" cellspacing="0" border="0" align="center" width="400">';
if (file_exists(DOL_DOCUMENT_ROOT.'/logo.png'))
{
  print '<tr><td colspan="3" style="text-align:center;">';
  print '<img src="/logo.png"></td></tr>';
}
else
{
  print '<tr class="vmenu"><td align="center">Dolibarr '.DOL_VERSION.'</td></tr>';
}
print '</table>';
print '<br>';

// Table 2
print '<table cellpadding="2" align="center" width="400">';

print '<tr><td colspan="3">&nbsp;</td></tr>';

print '<tr><td align="left"> &nbsp; <b>'.$langs->trans("Login").'</b>  &nbsp;</td>';
$disabled='disabled';
if ($mode == 'dolibarr') $disabled='';

print '<td><input '.$disabled.' name="username" class="flat" size="15" maxlength="25" value="" tabindex="1" /></td>';

// Affiche logo du theme si existe, sinon logo commun
if (file_exists(DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/img/login_logo.png'))
{
	print '<td><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/login_logo.png"></td>';
}
else
{
	print '<td><img src="'.DOL_URL_ROOT.'/theme/login_logo.png"></td>';
}

print '</tr>';


print "<tr>".'<td align="center" colspan="3"><input class="button" value="'.$langs->trans("SendNewPassword").'" type="submit"></td></tr>';
print "</table>\n";

print "</form>";

print '<center>';
if ($mode == 'dolibarr')
{
	print '<table width="90%"><tr><td><font class="warning" style="font-size: 14px;">'.$langs->trans("SendNewPasswordDesc").'</font></td></tr></table><br>';
}
else
{
	print '<div class="warning">'.$langs->trans("AuthenticationDoesNotAllowSendNewPassword",$mode).'</div>';
}
print '<br>';
if ($message) { print $message.'<br>'; }
print '<a href="'.DOL_URL_ROOT.'/">'.$langs->trans("BackToLoginPage").'</a>';
print '</center>';

print "<br>";
print "<br>";


// Fin entete html
print "\n</body>\n</html>";
?>