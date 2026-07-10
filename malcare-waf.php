<?php
// Please validate auto_prepend_file setting before removing this file

if (file_exists('/home/forge/brieftaubenshop.de/public/wp-content/plugins/malcare-security/protect/prepend/ignitor.php') && !defined("MCDATAPATH")) {
	define("MCDATAPATH", '/home/forge/brieftaubenshop.de/public/wp-content/mc_data/');
	define("MCCONFKEY", '8daa08d880b0592da5ea40fbb8c45156');
	include_once('/home/forge/brieftaubenshop.de/public/wp-content/plugins/malcare-security/protect/prepend/ignitor.php');
}
