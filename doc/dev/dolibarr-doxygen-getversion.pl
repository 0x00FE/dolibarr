#!/usr/bin/perl
#--------------------------------------------------------------------
# Script recup version d'un source
#
# \version $Id$
#--------------------------------------------------------------------

# Usage: dolibarr-doxygen-getversion.pl pathtofilefromdolibarrroot

$file=$ARGV[0];
if (! $file) 
{
	print "Usage: dolibarr-doxygen-getversion.pl pathtofilefromdolibarrroot\n";
	exit;
}

$commande='cvs status "'.$file.'" | sed -n \'s/^[ \]*Working revision:[ \t]*\([0-9][0-9\.]*\).*/\1/p\'';
#print $commande;
$result=`$commande 2>&1`;

print $result;
