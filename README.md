PHP-Crawler
===========

PHP crawler and spider. working with UTF8, MySQL, Random host, Supports robots.txt and many more surprises 


Install It
==========
0. `sudo apt-get install curl php5-curl php5` - Validate that you have this packages
1. on /sql folder you will find `schema_create.sql` file run it in sql
2. on /libs folder you will find  `config.php` you should configure as well
3. give read + write + delete permissions to `/writable` folder

RUN IT
======

There two ways to run 
To run it with multi processes (if you configure on crowle.php ) Default 4 processes

	`sh do_it.sh` 

To run single proccess 
	
	`php crowle.php`

To Add new URL
==============
Create php file and run
Method 1 :

	<?php
	
	include 'libs/general.php';
	
	$urlArray=array(
		'http://some-url-1.com/'=>"some url 1 description",
		'http://some-url-2.com/"=>"some url 2 description"
	);
	
	Providers::insert_url_list($urlArray);
	
	?>

Method 2 :

	<?php	
	include 'libs/general.php';
	$temp = Providers::get_or_create_url_by_url("http://some-url-1.com/");
	$temp = Providers::get_or_create_url_by_url("http://some-url-2.com/");
	?>

Stay in contact
===============

http://www.korotkin.co.il/
- or - 
info@korotkin.co.il

Hope it fine :-)
Give me feedback !
