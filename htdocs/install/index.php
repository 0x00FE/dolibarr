<?PHP
//
// Essaye de cr�er le fichier de conf
//

$conf = "../conf/conf.php";

if (file_exists($conf))
{
  include ($conf);
}
else
{
  $fp = @fopen("$conf", "w");
  if($fp)
    {
      @fwrite($fp, '<?PHP');
      @fputs($fp,"\n");
      @fputs($fp,"?>");
      fclose($fp);
    }
}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=iso8859-1">
<link rel="stylesheet" type="text/css" href="./default.css">
<title>Dolibarr Install</title>
</head>
<body>

<?PHP

$docurl = '<a href="doc/dolibarr-install.html">documentation</a>';

if (!file_exists("../conf/conf.php"))
{
  print '<div class="error">';
  print "Le fichier <b>conf.php</b> n'existe pas reportez-vous � la ".$docurl." pour cr�er ce fichier<br>";

  print '</div>';
}
else
{

  if (!is_writable("../conf/conf.php"))
    {
      print '<div class="error">';
      print "Le fichier <b>conf.php</b> n'est pas accessible en �criture, v�rififiez les droits sur celui-ci, reportez-vous � la ".$docurl."<br>";
      
      print '</div>';
    }
}

?>

<div class="main">
<div class="main-inside">
<h2>Installation de Dolibarr</h2>
<form action="etape1.php" method="POST">
<input type="hidden" name="action" value="set">
<table border="0" cellpadding="4" cellspacing="0">
<tr>
<td valign="top">
<?php print "R�pertoire d'installation"; ?>
</td><td valign="top"><input type="text" size="60" value="
<?PHP

if(strlen($dolibarr_main_url_root) == 0)
{
$dolibarr_main_document_root = substr($_SERVER["SCRIPT_FILENAME"],0,strlen($_SERVER["SCRIPT_FILENAME"])-18);
}


 print $dolibarr_main_document_root 
?>
" name="main_dir">
</td><td>
Sans le slash "/" � la fin<br>
exemple : /var/www/dolibarr/htdocs

</td>
</tr>

<tr class="bg1">
<td valign="top">
URL Racine</td><td valign="top"><input type="text" size="60" name="main_url" value="
<?PHP 
if(strlen($dolibarr_main_url_root) == 0)
{
$dolibarr_main_url_root = substr($_SERVER["SCRIPT_URI"],0,strlen($_SERVER["SCRIPT_URI"])-9);
}

print $dolibarr_main_url_root ;

?>">
</td><td>
exemples : 
<br>
<ul>
<li>http://dolibarr.lafrere.net</li>
<li>http://www.lafrere.net/dolibarr</li>
</ul>
</tr>


<tr>
<td colspan="3" align="center"><h2>Base de donn�es<h2></td>
</tr>

<tr class="bg1">
<td valign="top">Serveur</td><td valign="top"><input type="text" name="db_host" value="<?PHP print $dolibarr_main_db_host ?>"></td>
<td>Nom du serveur de base de donn�es, g�n�ralement 'localhost' quand le serveur est install� sur la m�me machine que le serveur web</td>
</tr>

<tr class="bg2">
<td>Nom de la base de donn�es</td><td valign="top"><input type="text" name="db_name" value="<?PHP print $dolibarr_main_db_name ?>"></td>
<td><div class="comment">Nom de votre base de donn�es</div></td>
</tr>

<tr class="bg1">
<td valign="top">Login</td>
<td>
<input type="text" name="db_user" value="<?PHP print $dolibarr_main_db_user ?>">
</td><td><div class="comment">Laisser vide si vous vous connectez en anonymous</div>
</td>
</tr>

<tr class="bg2">
<td valign="top">Mot de passe</td>
<td>
<input type="text" name="db_pass" value="<?PHP print $dolibarr_main_db_pass ?>">
</td><td><div class="comment">Laisser vide si vous vous connectez en anonymous</div>
</td>
</tr>

<td colspan="3" align="center"><h2>Base de donn�es - Acc�s super utilisateur</h2></td>

<tr class="bg1">
<td valign="top">Login</td>
<td>
<input type="text" name="db_user_root">
</td><td><div class="comment">Login de l'utilisateur ayant les droits de cr�ation de la base de donn�es, inutile si vous �tes chez un h�bergeur, votre base de donn�es est d�j� cr��e. Laisser vide si vous vous connectez en anonymous</div>
</td>
</tr>

<tr class="bg2">
<td valign="top">Mot de passe</td>
<td>
<input type="text" name="db_pass_root">
</td><td><div class="comment">Laisser vide si vous vous connectez en anonymous</div>
</td>
</tr>



</table>
</div>
</div>

<div class="barrebottom">
<input type="submit" value="Etape suivante ->">
</form>
</div>
</body>
</html>
