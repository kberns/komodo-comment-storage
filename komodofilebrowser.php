<?php
#load login information to sql database
require('config.inc.php');

#connect to sql database
$link=mysql_connect($database_host,$username,$password) or die(mysql_error());
mysql_select_db($database,$link);mysql_set_charset('utf8');
$password='fs3SadhGFar4gd21';$username='sql';


echo'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
  <title>Komodo File Comment and Tags Toggler</title>

  <script src="ext/dynatree-master/jquery/jquery.js" type="text/javascript"></script>
  <script src="ext/dynatree-master/jquery/jquery-ui.custom.js" type="text/javascript"></script>
  <script src="ext/dynatree-master/jquery/jquery.cookie.js" type="text/javascript"></script>

  <link href="ext/dynatree-master/src/skin/ui.dynatree.css" rel="stylesheet" type="text/css">
  <script src="ext/dynatree-master/src/jquery.dynatree.js" type="text/javascript"></script>

  <!-- (Irrelevant source removed.) -->

<script type="text/javascript">
  $(function(){
    $("#tree").dynatree({
      persist: true,
      checkbox: true,
      selectMode: 3,
      onPostInit: function(isReloading, isError) {
         logMsg("onPostInit(%o, %o)", isReloading, isError);
         // Re-fire onActivate, so the text is update
         this.reactivate();
      },
      onActivate: function(node) {
        $("#echoActive").text(node.data.title);
      },
      onDeactivate: function(node) {
        $("#echoActive").text("-");
      },
      onDblClick: function(node, event) {
        logMsg("onDblClick(%o, %o)", node, event);
        node.toggleExpand();
      }
    });
  });
</script>
</head>

<body class="example">

  <!-- Tree container -->
  <div id="tree">
  
    <ul>';
error_reporting(0);
$fileids=array();$dirids=array();
function filedirs($dirstart,$level) {
    global $fileids,$dirids;
    $i=1;
    if($dirstart!=='.'){$dirextra="$dirstart/";}else{$rootfolder=1;}
    if ($handle = opendir($dirstart)) {
        ++$level;
    $dirs=array();$diri=0;
    $files=array();$filei=0;
    while (false != ($entry = readdir($handle))) {
      if ($entry != "." && $entry != "..") {
          if (is_dir($dirextra.$entry) === true){
              $dirs[$diri]=$entry;++$diri;$thefile="";
          }else{
            preg_match('/\.([^\.]*)$/i',$entry,$match);
            $thefile=$match[1];
          }
          if($thefile=='php'){
              $files[$filei]=$dirextra.$entry;++$filei;
          }
      }
    }
    foreach($dirs as $dirpart){
        $theid="id$level.$i";
        echo "<li id='$theid' class='folder'>$dirpart<ul>\n";
        ++$i;
        if($dirstart != "."){$newdir=$dirstart.'/'.$dirpart;}
        else{$newdir=$dirpart;}
        
        $dirids[$theid]=$newdir.$dirpart;
        filedirs($newdir,$level);
        echo'</ul>';
    }
    if(isset($rootfolder)){echo "<li id='id$level.$i' class='folder expanded'>ROOT_FOLDER<ul>\n";}
    foreach($files as $filepart){
        $theid="id$level.$i";
        $fileids[$theid]=$filepart;
        preg_match('/([^\/]*)$/i',$filepart,$match);
        echo "<li id='$theid'>".$match[1]."\n";
        ++$i;
    }
    if(isset($rootfolder)){echo'</ul>';}
    closedir($handle);
    }
}
filedirs(".",0) ;

$rand=rand();
echo'</ul>
  </div><div class="dynatree-drag-helper">
  <a href="?con&r='.$rand.'">Turn Comments On</a> 
  <a href="?coff&r='.$rand.'">Turn Comments Off</a>';
if(isset($_GET['coff'])){$coff=1;}
elseif(isset($_GET['con'])){$con=1;}
if(isset($con)or isset($coff)){
    $securityforkomodo=1;require('komodo.php');
    $thefilesids=$_COOKIE['dynatree-select'];
    $thefilesids_a=explode(',',$thefilesids);
    $securityforkomodo=1;
    $re = '/\x{263A}(\S*)\s?([^\x{263B}\x{263A}]*)\x{263B}/u'; 
    $re2 = '/\x{25d9}([^\x{25d8}]*)\x{25d8}/u';
    $sql=mysql_query('SELECT * FROM komodo_comments_settings where apicode="'.$myapikey.'" and uniid="'.$myidkey.'";');
    $codecheck=mysql_result($sql,0,'apicode');$numcheck=mysql_result($sql,0,'num');
    if(empty($codecheck)){mysql_close($link);die('access denied');}
    else{
        $ct=mysql_result($sql,0,'ct');
    }
    foreach($thefilesids_a as $theid){
        $thefile=$fileids[$theid];
        if(!empty($thefile)){
            $sql="INSERT INTO komodo_comments_files (fid,file,filetype) VALUES ('$theid','$thefile',1) ON DUPLICATE KEY UPDATE file='$thefile',filetype=1;";
            mysql_query($sql);
            $loadedfile=file_get_contents($thefile);
            #$loadedfile=str_replace('\n','\\n',$loadedfile);
            preg_match_all($re, $loadedfile, $matches_comments);
            foreach($matches_comments[2] as $matchkey => $comment){
                
                $commentid=$matches_comments[1][$matchkey];
                $tochange=$matches_comments[0][$matchkey];
                $firstchar=substr($commentid,0,1);
                if($firstchar=='-'){
                    $commentid=substr($commentid,1);
                    $rmnote=$commentid;
                }elseif(preg_match('/\x{25d8}/u',$comment)){
                    preg_match_all($re2, $comment, $matches_tags);
                    $foundtag=1;
                }
                if((empty($commentid)or ctype_digit(strval($commentid)))&&!isset($rmnote)&&!isset($index)){
                    if(empty($commentid)){$num=$numcheck;++$num;$commentid=$num;}
                    elseif($commentid>$numcheck){$num=$commentid +1;}
                    if($numcheck<$num){
                        mysql_query("update komodo_comments_settings set num=$num where apicode='$apikey' and uniid='$myidkey'");
                    }
                }
                $sql=mysql_query('SELECT * FROM komodo_comments where id="'.$commentid.'" and ct="'.$ct.'"');
                $oldnote=mysql_result($sql,0,'txt');
                $cid=mysql_result($sql,0,'cid');
                
                if(!isset($rmnote)&&!isset($index)){
                    if(empty($comment)or isset($con)){ #toggle note on
                        $loadedfile=str_ireplace($tochange,$toadd."☺$commentid $oldnote"."☻",$loadedfile);
                    }elseif(!empty($comment)&&isset($coff)){#put note into sql database
                        if(empty($oldnote)){mysql_query("INSERT INTO komodo_comments (txt,ct,id) VALUES('$comment','$ct','$commentid')");}
                        else{mysql_query("update komodo_comments set txt='$comment' where id='$commentid' and ct='$ct'");}
                        $loadedfile=str_ireplace($tochange,$toadd."☺$commentid ☻",$loadedfile);#toggle note off
                    }
                }else{
                    mysql_query("delete from komodo_comments where id='$rmnote' and ct='$ct'");
                    $loadedfile=str_ireplace($tochange,"",$loadedfile);unset($rmnote,$index);
                }
                if(isset($foundtag)){
                    foreach($matches_tags[1] as $matchtag){
                        mysql_query("INSERT INTO komodo_comments_tags (ct,cid,txt) VALUES('$ct','$cid','$matchtag');");
                    }unset($foundtag);
                }
            }
            file_put_contents($thefile, $loadedfile);#.PHP_EOL, FILE_APPEND
        }
    }
}
mysql_close($link);
echo'<div>Active node: <span id="echoActive">-</span></div>
<p class="description">
    Cookie persistence is enabled here.<br>
    Also, double-click handler expands document nodes.<br>
    Select a node and hit [F5] to refresh, to see how the active node and
    expansion and selection states are restored.<br>
    <br>
    NOTE: if this doesn\'t seem to work, it\'s probably because the frame
    content is cached by the browser.<br>
    Try this example as an
    <a href="#" target="_blank">unframed page</a>.
  </p>
  <!-- (Irrelevant source removed.) -->
</body>
</html>';


/*
echo'<li id="id1" title="Look, a tool tip!">item1 with key and tooltip
      <li id="id2">item2
      <li id="id3" class="folder">Folder with some children
        <ul>
          <li id="id3.1">Sub-item 3.1
            <ul>
              <li id="id3.1.1">Sub-item 3.1.1
              <li id="id3.1.2">Sub-item 3.1.2
            </ul>
          <li id="id3.2">Sub-item 3.2
            <ul>
              <li id="id3.2.1">Sub-item 3.2.1
              <li id="id3.2.2">Sub-item 3.2.2
            </ul>
        </ul>
      <li id="id4" class="expanded">Document with some children (expanded on init)
        <ul>
          <li id="id4.1"  class="active focused">Sub-item 4.1 (active and focus on init)
            <ul>
              <li id="id4.1.1">Sub-item 4.1.1
              <li id="id4.1.2">Sub-item 4.1.2
            </ul>
          <li id="id4.2">Sub-item 4.2
            <ul>
              <li id="id4.2.1">Sub-item 4.2.1
              <li id="id4.2.2">Sub-item 4.2.2
            </ul>
        </ul>';
*/
?>