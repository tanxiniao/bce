<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class discuz_ftp{
	var $enabled = false;
	var $config = array();
	var $func;
	var $_error;
	var $currentdir = '/';
	var $connectid;
	
	function &instance($config = array()) {
		static $object;
		if(empty($object)) {
			$object = new discuz_ftp($config);
		}
		return $object;
	}

	function __construct($config = array()) {
		$this->set_error(0);
		$this->config = !$config ? getglobal('setting/ftp') : $config;
		$this->enabled = false;
		if(empty($this->config['on'])) {
			$this->set_error(FTP_ERR_CONFIG_OFF);
		} else {
			$this->enabled = true;
		}
		require_once "bos_util.php";
		loadcache('plugin');
		global $_G;
		$setting = $_G['cache']['plugin']['baidu_bcebos'];
		$ak = $setting['BOS_AK'];
		$sk = $setting['BOS_SK'];
		$bucket=$setting['BOS_BUCKET'];
		$this->bos_util=new bos_util($ak,$sk,$bucket);
	}

	function upload($source, $target){
		return $this->ftp_put($target,$source);
	}

	function connect() {
		$this->connectid = true;
		return true;
	}
	
	function ftp_connect($ftphost, $username, $password, $ftppath, $ftpport = 21, $timeout = 30, $ftpssl = 0, $ftppasv = 0) {
		$this->connectid = true;
		return true;

	}

	function set_error($code = 0) {
		$this->_error = $code;
	}

	function error() {
		return $this->_error;
	}

	function clear($str) {
		return str_replace(array( "\n", "\r", '..'), '', $str);
	}

	function set_option($cmd, $value) {
		return;
	}
	function ftp_mkdir($directory) {
		return true;
	}
	
	function ftp_rmdir($directory) {
		return true;
	}
	
	function ftp_put($remote_file, $local_file, $mode = FTP_BINARY) {
		if($this->error()){
			return false;
		}
		$remote_file = discuz_ftp::clear($remote_file);
		$local_file = discuz_ftp::clear($local_file);
		return $this->bos_util->putObjectFromFile($remote_file,$local_file);
	}
	
	function ftp_size($remote_file) {
		$remote_file = discuz_ftp::clear($remote_file);
		return $this->bos_util->get_object_size($remote_file);
	}

	function ftp_close() {
		return true;
	}

	function ftp_delete($path){
		#print_r("-----------------------<br>");
		#die("die");
		$path = discuz_ftp::clear($path);
		return $this->bos_util->deleteObject($path);
	}

	function ftp_get($local_file, $remote_file, $mode, $resumepos = null) {
		print "---------------------------------------------<br>";
		print "$local_file, $remote_file, $mode, $resumepos <br>";
		print "---------------------------------------------<br>";
		die("hah");
		return $this->bos_util->getObjectToFile($local_file,$remote_file,$resumepos);
	}
	
	function ftp_login($username, $password) {
		return true;
	}

	function ftp_pasv($pasv) {
		return true;
	}
}
