<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: avatar.php 1144 2013-01-31 06:47:43Z zhangjie $
*/
error_reporting(0);

_get_script_url();
define('UC_API', strtolower(($_SERVER['HTTPS'] == 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'))));

$uid = isset($_GET['uid']) ? $_GET['uid'] : 0;
$size = isset($_GET['size']) ? $_GET['size'] : '';
$random = isset($_GET['random']) ? $_GET['random'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$check = isset($_GET['check_file_exists']) ? $_GET['check_file_exists'] : '';
$avatar = get_avatar($uid, $size, $type);

$status = get_avatar_status($avatar);
print $status;
if($check){

	return $status;

} else {
	if($status){ // 这里http不改了，除非你真的使用https
		$avatar_url = 'http://flask-zxd.bj.bcebos.com/avatar/'.$avatar;
	} else {
		// 没有头像
		$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
		$avatar_url = 'images/noavatar_'.$size.'.gif';
	}
}

header("Location: $avatar_url");
exit;

function get_avatar($uid, $size = 'middle', $type = '') {
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$typeadd = $type == 'real' ? '_real' : '';
	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
}

function get_avatar_status($path){
		date_default_timezone_set('UTC');
		$timestamp = date("Y-m-d")."T".date("H:i:s")."Z";
		$host       = 'http://bj.bcebos.com';
		$AK  = 'fm9Cf5SM5UIeX2egIeGMWwGx';
		$SK = 'Q8fGdGR64tXD0Y094jBAHTF63tlvHHgI';
		$expirationPeriodInSeconds = "3600";
	    $bucket = "flask-zxd";

		$authStringPrefix = "bce-auth-v1"."/".$AK."/".$timestamp."/".$expirationPeriodInSeconds;
		$SigningKey=hash_hmac('SHA256',$authStringPrefix,$SK);
		$CanonicalHeaders1 = "host;"."x-bce-date";
		$CanonicalHeaders2 = "host:bj.bcebos.com\n"."x-bce-date:".urlencode($timestamp);
		$CanonicalString = "";
		$CanonicalURI = '/' . $bucket . '/avatar/' . $path;
		$CanonicalRequest = "HEAD\n".$CanonicalURI."\n".$CanonicalString."\n".$CanonicalHeaders2;    //第二步
		
		$Signature = hash_hmac('SHA256',$CanonicalRequest,$SigningKey);
		$Authorization = "bce-auth-v1/{$AK}/".$timestamp."/{$expirationPeriodInSeconds}/{$CanonicalHeaders1}/{$Signature}";
		print $Authorization."\n";	

		$head =  array("Authorization:{$Authorization}","x-bce-date:{$timestamp}");
		$timeout    = 1;
	    // Authorization
		$uri = '/' . $bucket . '/avatar/' . $path;
		$ch  = curl_init($host . $uri);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "HEAD");
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		$response = curl_exec($ch);
		print $response; //output
		$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($status == 200) {
		return true;
		} else {
		return false;
		}
}

function _get_script_url() {
	$scriptName = basename($_SERVER['SCRIPT_FILENAME']);
	if(basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
		$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
	} else if(basename($_SERVER['PHP_SELF']) === $scriptName) {
		$_SERVER['PHP_SELF'] = $_SERVER['PHP_SELF'];
	} else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
		$_SERVER['PHP_SELF'] = $_SERVER['ORIG_SCRIPT_NAME'];
	} else if(($pos = strpos($_SERVER['PHP_SELF'],'/'.$scriptName)) !== false) {
		$_SERVER['PHP_SELF'] = substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
	} else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT']) === 0) {
		$_SERVER['PHP_SELF'] = str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
		$_SERVER['PHP_SELF'][0] != '/' && $_SERVER['PHP_SELF'] = '/'.$_SERVER['PHP_SELF'];
	} else {
		return false;
	}
	return $_SERVER['PHP_SELF'];
}

?>