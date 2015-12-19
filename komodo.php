<?php
/*☺Komodo-Comment-Toggler
 * Store comments in sql database
 * Scimos javascript macro developed to be used in Komodo edit
 * Format: ☺No-space-123ABC-ID The Text\nNew line text☻
 * Type:  On Demand
 *
 * @source        https://github.com/krizoek/komodo-comment-toggler
 * @author        Kristoffer Bernssen
 * @version       0.1
 * @copyright    Creative Commons Attribution 4.0 International (CC BY 4.0)
 * 
 ☻*/

error_reporting(0);

$myapikey="SKDFK89338fnASDkf3"; #initial key
$myidkey="X1"; #initial id

$apikey=addslashes($_GET['key']); #apikey from komodo
#getting the id matched with the comments. (for generating multiple id's on single server)
$myid=addslashes($_GET['id']); #my id from komodo

#load login information to sql database
require('config.inc.php');

#connect to sql database
$link=mysql_connect($database_host,$username,$password) or die(mysql_error());
mysql_select_db($database,$link);mysql_set_charset('utf8');
$password='fs3SadhGFar4gd21';$username='sql';

#create sql table if missing
$val = mysql_query('select 1 from `komodo_comments` LIMIT 1');
if($val === FALSE){
    mysql_query('CREATE TABLE IF NOT EXISTS komodo_comments(
id varchar(255),
PRIMARY KEY(id),
uniid varchar(255),
txt TEXT NOT NULL,
`mode` tinyint(3),
datetime TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=MyISAM  CHARACTER SET=utf8 COLLATE=utf8_general_ci;') or die(mysql_error());

    mysql_query('CREATE TABLE IF NOT EXISTS komodo_comments_settings(
uniid varchar(255),
PRIMARY KEY(uniid),
apicode varchar(255),
`mode` tinyint(3),
datetime TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=MyISAM  CHARACTER SET=utf8 COLLATE=utf8_general_ci;') or die(mysql_error());

mysql_query("INSERT INTO komodo_comments_settings (uniid,apicode,`mode`) VALUES('$myidkey','$myapikey','1')");
}

#load the comment sent by komodo js script
$t=$_POST['txt'];
$str=urldecode($t);

#regex match
$re = '/\x{263A}(\S*)\s([^\x{263B}\x{263A}]*)\x{263B}/u'; 
preg_match($re, $str, $matches);
$commentid=addslashes($matches[1]);
$comment=addslashes($matches[2]);
#api security
$sql=mysql_query('SELECT apicode FROM komodo_comments_settings where apicode="'.$apikey.'" and uniid="'.$myid.'";');
$codecheck=mysql_result($sql,0,'apicode');
if(empty($codecheck)){mysql_close($link);die('access denied');}

#get/check previous comment in sql database
$sql=mysql_query('SELECT * FROM komodo_comments where id="'.$commentid.'" and uniid="'.$myid.'"');
$oldnote=mysql_result($sql,0,'txt');

if(empty($comment)){ #toggle note on
    echo"☺$commentid $oldnote ☻";
}else{#put note into sql database
    if(empty($oldnote)){mysql_query("INSERT INTO komodo_comments (txt,uniid,id) VALUES('$comment','$myid','$commentid')");}
    else{mysql_query('update komodo_comments set txt="'.$comment.'" where id="'.$commentid.'" and uniid="'.$myid.'"');}
    echo"☺$commentid ☻"; #toggle note off
}
mysql_close($link);

?>