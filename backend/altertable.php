<?php
/**
 * This file takes in a MYSQL create table definition and 
 * returns the alter table commands that will allow you to easily 
 * add comments.
 */

if(isset($_POST['submit'])){
        /*
         * Someone submitted something.  Lets try and convert it to
         * something sane.
         * First, explode on the new lines.
         */
        $thedata = explode("\n",$_POST['data']);
        /*
         * Reset newdata to blank.  Just in case.
         */
        $newdata = '';
        $tablename = '';
        /*
         * Then lets loop through every line.  Ignore the CREATE line, and
         * the PRIMARY KEY/ENGINE lines.
         */
        foreach($thedata as $aline){
                if(preg_match('/^  PRIMARY KEY/',$aline) || preg_match('/^\) ENGINE/',$aline)){
                        continue;
                }
                /*
                 * Capture the table name from the first line of the 
                 * Create Table definition.
                 */
                if(preg_match('/^CREATE TABLE/',$aline)){
                        preg_match('/`(.*)`/',$aline,$matches);
                        $tablename = '`' . $matches[1] . '`';
                        continue;
                }
                /*
                 * Now that we got here, need to pick apart the line and rewrite it
                 * as needed.
                 * Need to take the column name to repeat it, and then add the rest 
                 * of the junk.
                 */
                preg_match('/`(.*)`/',$aline,$matches);
                //echo $matches[1] . '<br />';
                //var_dump($matches);
                $columnname = "`" . $matches[1] . "`";
                /*
                 * If there is a comment specified already, strip if off.
                 * If not, just take the whole line as the definition.
                 */
                if(preg_match('/^  (.*) COMMENT /',$aline,$matches)){
                        $newdata .= 'ALTER TABLE ' . $tablename . ' CHANGE ' . $columnname . ' ' . trim(stripslashes($matches[1])) . " COMMENT '';\n";
                }
                elseif(preg_match('/^  (.*),/',$aline,$matches)){
                        $newdata .= 'ALTER TABLE ' . $tablename . ' CHANGE ' . $columnname . ' ' . trim(stripslashes($matches[1])) . " COMMENT '';\n";
                }
                else{
                        $newdata .= 'ALTER TABLE ' . $tablename . ' CHANGE ' . $columnname . ' ' . trim(stripslashes($aline)) . " COMMENT '';\n";
                }       
        }
}

?>
<html>
<head>
        <title>MySQL Create Table -> Alter Table v0.2</title>
</head>
<body>
<?php 
        if(isset($_POST['submit'])){
                echo '<h2>Here is your definition.  Use it wisely.</h2>';
                echo '<pre style="font-size: 10px;">';
                echo $newdata;
                echo '</pre>';
                echo '<hr />';
        }
?>
<h2>Insert your Create Table Definition here.</h2>
<form name="altertable" action="altertable.php" method="post">
        <textarea name="data" rows="10" cols="80"></textarea>
        <input type="submit" name="submit" value="Get Alter Table"></input>
</form>
<div style="font-size: 10px;">The definition should look something like this:
<pre>
CREATE TABLE `courses` (
  `courseid` bigint(20) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  `d2lcode` varchar(200) NOT NULL,
  `crns` varchar(100) DEFAULT NULL,
  `datein` datetime DEFAULT NULL,
  `transferasked` tinyint(1) NOT NULL DEFAULT '0',
  `transferaskeddate` datetime DEFAULT NULL,
  `transferred` tinyint(1) NOT NULL DEFAULT '0',
  `transferdate` datetime DEFAULT NULL,
  `tblopen` tinyint(1) NOT NULL DEFAULT '0',
  `tblopenstamp` datetime DEFAULT NULL,
  `enddate` datetime DEFAULT NULL,
  `transferenddate` datetime DEFAULT NULL,
  `campuscode` varchar(2) DEFAULT '?',
            $newdata .= 'ALTER TABLE ' . $tablename . ' CHANGE ' . $columnname . ' ' . trim(stripslashes($matches[1])) . " COMMENT '';\n";
                }
                elseif(preg_match('/^  (.*),/',$aline,$matches)){
                        $newdata .= 'ALTER TABLE ' . $tablename . ' CHANGE ' . $columnname . ' ' . trim(stripslashes($matches[1])) . " COMMENT '';\n";
                }
                else{
                        $newdata .= 'ALTER TABLE ' . $tablename . ' CHANGE ' . $columnname . ' ' . trim(stripslashes($aline)) . " COMMENT '';\n";
                }       
        }
}

?>
<html>
<head>
        <title>MySQL Create Table -> Alter Table v0.2</title>
</head>
<body>
<?php 
        if(isset($_POST['submit'])){
                echo '<h2>Here is your definition.  Use it wisely.</h2>';
                echo '<pre style="font-size: 10px;">';
                echo $newdata;
                echo '</pre>';
                echo '<hr />';
        }
?>
<h2>Insert your Create Table Definition here.</h2>
<form name="altertable" action="altertable.php" method="post">
        <textarea name="data" rows="10" cols="80"></textarea>
        <input type="submit" name="submit" value="Get Alter Table"></input>
</form>
<div style="font-size: 10px;">The definition should look something like this:
<pre>
CREATE TABLE `courses` (
  `courseid` bigint(20) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  `d2lcode` varchar(200) NOT NULL,
  `crns` varchar(100) DEFAULT NULL,
  `datein` datetime DEFAULT NULL,
  `transferasked` tinyint(1) NOT NULL DEFAULT '0',
  `transferaskeddate` datetime DEFAULT NULL,
  `transferred` tinyint(1) NOT NULL DEFAULT '0',
  `transferdate` datetime DEFAULT NULL,
  `tblopen` tinyint(1) NOT NULL DEFAULT '0',
  `tblopenstamp` datetime DEFAULT NULL,
  `enddate` datetime DEFAULT NULL,
  `transferenddate` datetime DEFAULT NULL,
  `campuscode` varchar(2) DEFAULT '?',
  `errorinchecking` tinyint(1) NOT NULL DEFAULT '0',
  `checkerror` text,
  `checkmesage` text,
  `errorintransfer` tinyint(1) NOT NULL DEFAULT '0',
  `transfererrormessage` text,
  PRIMARY KEY (`courseid`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1

And spit out something like this:
ALTER TABLE `courses` CHANGE `courseid` `courseid` bigint(20) NOT NULL AUTO_INCREMENT COMMENT ''
ALTER TABLE `courses` CHANGE  `user`  `user` varchar(50) NOT NULL COMMENT '';
ALTER TABLE `courses` CHANGE  `d2lcode` `d2lcode` varchar(200) NOT NULL COMMENT '';
ALTER TABLE `courses` CHANGE  `crns` `crns` varchar(100) DEFAULT NULL COMMENT '';
</pre>
</div>

</body>
</html>

