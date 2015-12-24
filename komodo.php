<?php
/* Komodo-Comment-Storage
 * Feature: Toggle comments on/off in editor content.
 * Feature: Store tags inside comments in sql database
 * Scimos javascript macro developed to be used in Komodo edit
 * Tag-format: ◙tags◘ (to be used inside comments)
 * Format:      ☺No-space-123ABC-ID The Text\nNew line text ◙tag1◘ ◙tag2◘☻
 * Or Format: ☺The Text\nNew line text☻(space after ☺ will make the id to be automatically generated)
 * Type:  On Demand
 *
 * @source        https://github.com/krizoek/komodo-comment-toggler
 * @author        Kristoffer Bernssen
 * @version       0.1
 * @copyright    Creative Commons Attribution 4.0 International (CC BY 4.0)
 * 
 *How to Upgrade: firstly toggle on the comments on all your files before you update.
 */

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


#api security
$sql=mysql_query('SELECT * FROM komodo_comments_settings where apicode="'.$apikey.'" and uniid="'.$myid.'";');
$codecheck=mysql_result($sql,0,'apicode');
if(empty($codecheck)){mysql_close($link);die('access denied');}
else{
    $ct=mysql_result($sql,0,'ct');
}

#create sql table if missing
$val = mysql_query('select cid from komodo_comments LIMIT 1');
if($val === FALSE){require('komodocomments.db.php');}

#load the comment sent by komodo js script
$t=$_POST['txt'];
$str=urldecode($t);

#regex matches
#_comments
#if($str=='☺INDEX☻'){$index=1;}#get with js instead (maybe used another time)
$re = '/\x{263A}(\S*)\s?([^\x{263B}\x{263A}]*)\x{263B}\s?/u'; 
preg_match($re, $str, $matches);
$commentid=addslashes($matches[1]);
$comment=addslashes($matches[2]);
$firstchar=substr($commentid,0,1);
if($firstchar=='-'){
    $commentid=substr($commentid,1);
    $rmnote=$commentid;
}else{
    #_◙tags◘  25d9 25d8
    if(preg_match('/\x{25d8}/u',$str)){
        $re = '/\x{25d9}([^\x{25d8}]*)\x{25d8}\s?/u'; 
        preg_match_all($re, $str, $matches_tags);$toadd="";
    }
}

#number the note if no commentid set
if((empty($commentid)or ctype_digit(strval($commentid)))&&!isset($rmnote)&&!isset($index)){
    if(empty($commentid)){$commentid=$num;++$num;}
    elseif($commentid>$num){$num=$commentid +1;}
    
    mysql_query("update komodo_comments_settings set num=$num where apicode='$apikey' and uniid='$myidkey'");
}

#get/check previous comment in sql database
$sql=mysql_query('SELECT * FROM komodo_comments where id="'.$commentid.'" and ct="'.$ct.'"');
$oldnote=mysql_result($sql,0,'txt');
$cid=mysql_result($sql,0,'cid');
foreach($matches_tags[1] as $match){
    mysql_query("INSERT INTO komodo_comments_tags (ct,cid,txt) VALUES('$ct','$cid','$match');");
}

if(!isset($rmnote)&&!isset($index)){
    if(empty($comment)){ #toggle note on
        echo$toadd."☺$commentid $oldnote"."☻";
    }else{#put note into sql database
        if(empty($oldnote)){mysql_query("INSERT INTO komodo_comments (txt,ct,id) VALUES('$comment','$ct','$commentid')");}
        else{mysql_query("update komodo_comments set txt='$comment' where id='$commentid' and ct='$ct'");}
        echo$toadd."☺$commentid ☻"; #toggle note off
    }
}else{
    mysql_query("delete from komodo_comments where id='$rmnote' and ct='$ct'");
}
mysql_close($link);

?>