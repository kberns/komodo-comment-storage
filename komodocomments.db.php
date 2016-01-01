<?php
if(isset($password)){
    mysql_query('drop table komodo_comments;');
    mysql_query('drop table komodo_comments_settings;');
    mysql_query('drop table komodo_comments_tags;');

    mysql_query('CREATE TABLE IF NOT EXISTS komodo_comments(
cid int unsigned AUTO_INCREMENT,
id varchar(255),
PRIMARY KEY(cid),
ct int unsigned,
txt TEXT NOT NULL,
`mode` tinyint(3) unsigned,
datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=MyISAM  CHARACTER SET=utf8 COLLATE=utf8_general_ci;') or die(mysql_error());

    mysql_query('CREATE TABLE IF NOT EXISTS komodo_comments_settings(
ct int unsigned AUTO_INCREMENT,
uniid varchar(255),
PRIMARY KEY(ct),
num int unsigned,
apicode varchar(255),
`mode` tinyint(3) unsigned,
datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=MyISAM  CHARACTER SET=utf8 COLLATE=utf8_general_ci;') or die(mysql_error());
    
    mysql_query('CREATE TABLE IF NOT EXISTS komodo_comments_tags(
tid int unsigned AUTO_INCREMENT,
ct int unsigned,
cid int unsigned,
PRIMARY KEY(tid),
txt varchar(255),
`mode` tinyint(3) unsigned,
UNIQUE KEY `cct` (`ct`,`cid`,`txt`),
datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=MyISAM  CHARACTER SET=utf8 COLLATE=utf8_general_ci;') or die(mysql_error());
    
    mysql_query("INSERT INTO komodo_comments_settings (uniid,apicode,`mode`) VALUES('$myidkey','$myapikey','1')");
    
    #update to latest version
    mysql_query("update komodo_comments_settings set num=1 where apicode='$myapikey' and uniid='$myidkey'");
    
    mysql_query('CREATE TABLE IF NOT EXISTS komodo_comments_files(
fid varchar(255),
PRIMARY KEY(fid),
file varchar(255),
`filetype` tinyint(3) unsigned,
datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=MyISAM  CHARACTER SET=utf8 COLLATE=utf8_general_ci;') or die(mysql_error());
}
?>