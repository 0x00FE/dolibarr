<?php
/* Copyright (C) 2008-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * or see http://www.gnu.org/
 */

/**
 *  \file		htdocs/lib/files.lib.php
 *  \brief		Library for file managing functions
 *  \version		$Id$
 */

/**
 *  \brief		Scan a directory and return a list of files/directories. Content for string is UTF8.
 *  \param		$path        	Starting path from which to search
 *  \param		$types        	Can be "directories", "files", or "all"
 *  \param		$recursive		Determines whether subdirectories are searched
 *  \param		$filter        	Regex for filter
 *  \param		$exludefilter  	Regex for exclude filter (example: '\.meta$')
 *  \param		$sortcriteria	Sort criteria ("name","date","size")
 *  \param		$sortorder		Sort order (SORT_ASC, SORT_DESC)
 *	\param		$mode			0=Return array minimum keys loaded (faster), 1=Force all keys like date and size to be loaded (slower)
 *  \return		array			Array of array('name'=>'xxx','fullname'=>'/abc/xxx','date'=>'yyy','size'=>99,'type'=>'dir|file')
 */
function dol_dir_list($path, $types="all", $recursive=0, $filter="", $excludefilter="", $sortcriteria="name", $sortorder=SORT_ASC, $mode=0)
{
	dol_syslog("files.lib.php::dol_dir_list path=".$path." types=".$types." recursive=".$recursive." filter=".$filter." excludefilter=".$excludefilter);

	$loaddate=$mode?true:false;
	$loadsize=$mode?true:false;

	// Clean parameters
	$path=preg_replace('/([\\/]+)$/i','',$path);
	$newpath=(utf8_check($path)?utf8_decode($path):$path);

	if (! is_dir($newpath)) return array();

	if ($dir = opendir($newpath))
	{
		$file_list = array();
		while (false !== ($file = readdir($dir)))
		{
			// readdir return value in ISO and we want UTF8 in memory
			$newfile=$file;
			if (! utf8_check($file)) $file=utf8_encode($file);

			$qualified=1;

			// Check if file is qualified
			if (eregi('^\.',$file)) $qualified=0;
			if ($excludefilter && eregi($excludefilter,$file)) $qualified=0;

			if ($qualified)
			{
				// Check whether this is a file or directory and whether we're interested in that type
				if (is_dir($newpath."/".$newfile) && (($types=="directories") || ($types=="all")))
				{
					// Add entry into file_list array
					if ($loaddate || $sortcriteria == 'date') $filedate=dol_filemtime($path."/".$file);
					if ($loadsize || $sortcriteria == 'size') $filesize=dol_filesize($path."/".$file);

					if (! $filter || eregi($filter,$path.'/'.$file))
					{
						$file_list[] = array(
						"name" => $file,
						"fullname" => $path.'/'.$file,
						"date" => $filedate,
						"size" => $filesize,
						"type" => 'dir'
						);
					}

					// if we're in a directory and we want recursive behavior, call this function again
					if ($recursive)
					{
						$file_list = array_merge($file_list,dol_dir_list($path."/".$file."/", $types, $recursive, $filter, $excludefilter, $sortcriteria, $sortorder));
					}
				}
				else if (! is_dir($newpath."/".$newfile) && (($types == "files") || ($types == "all")))
				{
					// Add file into file_list array
					if ($loaddate || $sortcriteria == 'date') $filedate=dol_filemtime($path."/".$file);
					if ($loadsize || $sortcriteria == 'size') $filesize=dol_filesize($path."/".$file);
					if (! $filter || eregi($filter,$path.'/'.$file))
					{
						$file_list[] = array(
						"name" => $file,
						"fullname" => $path.'/'.$file,
						"date" => $filedate,
						"size" => $filesize,
						"type" => 'file'
						);
					}
				}
			}
		}
		closedir($dir);

		// Obtain a list of columns
		$myarray=array();
		foreach ($file_list as $key => $row)
		{
			$myarray[$key]  = $row[$sortcriteria];
		}
		// Sort the data
		array_multisort($myarray, $sortorder, $file_list);

		return $file_list;
	}
	else
	{
		return array();
	}
}

/**
 * \brief	Compare 2 files
 *
 * @param 	unknown_type $a		File 1
 * @param 	unknown_type $b		File 2
 * @return 	int					1, 0, 1
 */
function dol_compare_file($a, $b)
{
	global $sortorder;
	global $sortfield;

	$sortorder=strtoupper($sortorder);

	if ($sortorder == 'ASC') { $retup=-1; $retdown=1; }
	else { $retup=1; $retdown=-1; }

	if ($sortfield == 'name')
	{
		if ($a->name == $b->name) return 0;
		return ($a->name < $b->name) ? $retup : $retdown;
	}
	if ($sortfield == 'date')
	{
		if ($a->date == $b->date) return 0;
		return ($a->date < $b->date) ? $retup : $retdown;
	}
	if ($sortfield == 'size')
	{
		if ($a->size == $b->size) return 0;
		return ($a->size < $b->size) ? $retup : $retdown;
	}
}

/**
 *	\brief      Return mime type of a file
 *	\param      file		Filename
 *	\return     string     	Return mime type
 */
function dol_mimetype($file)
{
	$mime='application/octet-stream';
	// Text files
	if (eregi('\.txt',$file))         $mime='text/plain';
	if (eregi('\.csv$',$file))        $mime='text/csv';
	if (eregi('\.tsv$',$file))        $mime='text/tab-separated-values';
	// MS office
	if (eregi('\.mdb$',$file))        $mime='application/msaccess';
	if (eregi('\.doc$',$file))        $mime='application/msword';
	if (eregi('\.dot$',$file))        $mime='application/msword';
	if (eregi('\.xls$',$file))        $mime='application/vnd.ms-excel';
	if (eregi('\.ppt$',$file))        $mime='application/vnd.ms-powerpoint';
	// Open office
	if (eregi('\.odp$',$file))        $mime='application/vnd.oasis.opendocument.presentation';
	if (eregi('\.ods$',$file))        $mime='application/vnd.oasis.opendocument.spreadsheet';
	if (eregi('\.odt$',$file))        $mime='application/vnd.oasis.opendocument.text';
	// Mix
	if (eregi('\.(html|htm)$',$file)) $mime='text/html';
	if (eregi('\.pdf$',$file))        $mime='application/pdf';
	if (eregi('\.sql$',$file))        $mime='text/plain';
	if (eregi('\.(sh|ksh|bash)$',$file))        $mime='text/plain';
	// Images
	if (eregi('\.jpg$',$file)) 	      $mime='image/jpeg';
	if (eregi('\.jpeg$',$file)) 	  $mime='image/jpeg';
	if (eregi('\.png$',$file)) 	      $mime='image/png';
	if (eregi('\.gif$',$file)) 	      $mime='image/gif';
	if (eregi('\.bmp$',$file)) 	      $mime='image/bmp';
	if (eregi('\.tiff$',$file))       $mime='image/tiff';
	// Calendar
	if (eregi('\.vcs$',$file))        $mime='text/calendar';
	if (eregi('\.ics$',$file))        $mime='text/calendar';
	// Other
	if (eregi('\.torrent$',$file))    $mime='application/x-bittorrent';
	// Audio
	if (eregi('\.(mp3|ogg|au)$',$file))           $mime='audio';
	// Video
	if (eregi('\.(avi|mvw|divx|xvid)$',$file))    $mime='video';
	// Archive
	if (eregi('\.(zip|rar|gz|tgz|z|cab|bz2)$',$file)) $mime='archive';
	return $mime;
}


/**
 * 	\brief		Test if a folder is empty
 * 	\param		folder		Name of folder
 * 	\return 	boolean		True if dir is empty or non-existing, False if it contains files
 */
function dol_dir_is_emtpy($folder)
{
	$newfolder=utf8_check($folder)?utf8_decode($folder):$folder;	// The is_dir and opendir need ISO strings
	if (is_dir($newfolder))
	{
		$handle = opendir($newfolder);
		while ((gettype( $name = readdir($handle)) != "boolean"))
		{
			$name_array[] = $name;
		}
		foreach($name_array as $temp) $folder_content .= $temp;

		if ($folder_content == "...") return true;
		else return false;

		closedir($handle);
	}
	else
	return true; // Dir does not exists
}

/**
 * 	\brief		Count number of lines in a file
 * 	\param		file		Filename
 * 	\return 	int			<0 if KO, Number of lines in files if OK
 */
function dol_count_nb_of_line($file)
{
	$nb=0;

	$newfile=utf8_check($file)?utf8_decode($file):$file;	// The fopen need ISO strings
	//print 'x'.$file;
	$fp=fopen($newfile,'r');
	if ($fp)
	{
		while (!feof($fp))
		{
			$line=fgets($fp);
			$nb++;
		}
		fclose($fp);
	}
	else
	{
		$nb=-1;
	}

	return $nb;
}


/**
 * Return size of a file
 *
 * @param 	$pathoffile
 * @return 	string		File size
 */
function dol_filesize($pathoffile)
{
	$newpathoffile=utf8_check($pathoffile)?utf8_decode($pathoffile):$pathoffile;
	return filesize($newpathoffile);
}

/**
 * Return time of a file
 *
 * @param 	$pathoffile
 * @return 	timestamp	Time of file
 */
function dol_filemtime($pathoffile)
{
	$newpathoffile=utf8_check($pathoffile)?utf8_decode($pathoffile):$pathoffile;
	return filemtime($newpathoffile);
}

/**
 * Return if path is a file
 *
 * @param 	$pathoffile
 * @return 	boolean			True or false
 */
function dol_is_file($pathoffile)
{
	$newpathoffile=utf8_check($pathoffile)?utf8_decode($pathoffile):$pathoffile;
	return is_file($newpathoffile);
}

?>
