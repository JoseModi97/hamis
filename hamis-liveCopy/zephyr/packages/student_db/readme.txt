to run this package you must do teh followings
1. run the following script in ur mysql

CREATE TABLE `std` (
  `roll` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `class` varchar(50) NOT NULL default '',
  `blood_grp` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`roll`)
)

2. change the dbinfo.class.php file in "helper" folder of this package and set ur database informations. 

thats it!