<?PHP
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org> 
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

if (defined('MAIN_MODULE_COMMERCIAL') && MAIN_MODULE_COMMERCIAL == 1)
{
  if (strstr($GLOBALS["SCRIPT_URL"],DOL_URL_ROOT.'/comm/'))
    {
      print '<TD width="15%" class="menusel" align="center">';
      if ($user->comm > 0 && $conf->commercial ) 
	{
	  print '<A class="menusel" href="'.DOL_URL_ROOT.'/comm/">Commercial</A></TD>';
	}
      else
	{
	  print '-</td>';
	}
    }
  else
    {
      print '<TD width="15%" class="menu" align="center">';
      if ($user->comm > 0 && $conf->commercial ) 
	{
	  print '<A class="menu" href="'.DOL_URL_ROOT.'/comm/">Commercial</A></TD>';
	}
      else
	{
	  print '-</td>';
	}
    }
}
elseif (defined('MAIN_MODULE_ADHERENT') && MAIN_MODULE_ADHERENT == 1)
{
  print '<TD width="15%" class="menu" align="center">';
  print '<A class="menu" href="'.DOL_URL_ROOT.'/adherents/">Adh�rents</A></TD>';
}
else
{
  print '<TD width="15%" class="menu" align="center">-</td>';
}


if (defined('MAIN_MODULE_COMPTABILITE') && MAIN_MODULE_COMPTABILITE == 1)
{
  if (strstr($GLOBALS["SCRIPT_URL"],DOL_URL_ROOT.'/compta/'))
    {
      print '<TD width="15%" class="menusel" align="center">';
      if ($user->compta > 0)
	{
	  print '<A class="menusel" href="'.DOL_URL_ROOT.'/compta/">Compta</A></TD>';
	} 
      else
	{
	  print '-';
	}
    }
  else
    {
      
      print '<TD width="15%" class="menu" align="center">';
      if ($user->compta > 0)
	{
	  print '<A class="menu" href="'.DOL_URL_ROOT.'/compta/">Compta</A></TD>';
	} 
      else
	{
	  print '-';
	}
    }
}
else
{
  print '<TD width="15%" class="menu" align="center">-</td>';
}

if (strstr($GLOBALS["SCRIPT_URL"],DOL_URL_ROOT.'/product/'))
{
  $class = "menusel";
}
else
{
  $class = "menu";
}

print '<TD width="15%" class="'.$class.'" align="center">';
if ($conf->produit->enabled ) 
{
  print '<A class="'.$class.'" href="'.DOL_URL_ROOT.'/product/?type=0">Produits</a>';
}
else
{
  print '-';
}


print '</td><td width="15%" class="menu" align="center">';

if(defined("MAIN_MODULE_WEBCALENDAR") && MAIN_MODULE_WEBCALENDAR)
{
  print '<a class="menu" href="'. PHPWEBCALENDAR_URL .'">Calendrier</a>';
};
print '&nbsp;</TD>';

?>
