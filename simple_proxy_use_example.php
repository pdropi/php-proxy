<?php
//Some security checks
 if(@$_SERVER['REMOTE_ADDR'] != '127.0.0.1') die('Your IP:'.@$_SERVER['REMOTE_ADDR'].' is not allowed'); //change '127.0.0.1' to real source
 // If this file is NOT on local net and is open on internet and is not reverse proxy, do not use only IP check as security check method 
 //if (@$_SERVER['HTTP_X_ADICIONAL_CHECK_ANY_NAME'] != 'hash_check_or_another') die ('Send a valid request header or change this');
$_SERVER['HTTP_PROXY_AUTH']= 'change_this_on_both_side'; //content must be same of static $AUTH_KEY on simple_proyx.php

$_SERVER['HTTP_REVERSE_PROXY'] = '';  //you can set real destination address to your service here. eg: localhost:8080 leave blank '' to disable

if (!empty($_SERVER['HTTP_REVERSE_PROXY'])) //filter this filename and send the rest of request_uri to target URL
	$_SERVER['HTTP_PROXY_TARGET_URL']= 'https://'.$_SERVER['HTTP_REVERSE_PROXY'].@explode($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI'],2)[1];
else //get from url or set fixed
	$_SERVER['HTTP_PROXY_TARGET_URL']= 'https://'.$_SERVER['HTTP_HOST'].@explode($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI'],2)[1];
//or not strip this script filename
//	$_SERVER['HTTP_PROXY_TARGET_URL']= 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
//or set fixed
//	$_SERVER['HTTP_PROXY_TARGET_URL']= 'http://realdestiny.com:port'.@explode($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI'],2)[1];

//I you want, you can use below function and simple_proxy.php script will automatically recognize
//function response_body_treatment($cont){
	//Do the treatment you need
	//return str_replace('<i> i buy from big companies</i>', '<strong> i support small business</strong>', $cont);
//}

include '/can_be_NOT_public/simple_proxy.php';