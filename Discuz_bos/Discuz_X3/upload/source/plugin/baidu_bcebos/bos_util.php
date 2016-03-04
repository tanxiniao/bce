<?php
/*
 
*
* Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with
* the License. You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on
* an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the
* specific language governing permissions and limitations under the License.
*/

define('BOS_SDK_ROOT', dirname(__FILE__) . "/bosSDK/");
require_once BOS_SDK_ROOT . "/services/bos/BosClient.php";
require_once BOS_SDK_ROOT . "/util/Time.php";
require_once BOS_SDK_ROOT . "/util/Coder.php";
require_once BOS_SDK_ROOT . "/exception/BceServiceException.php";

class bos_util{
	private $client;
	private $bucket;
	private $filename;
	
	public function __construct($ak,$sk,$bucket){
		// Only scalar and null values are allowed
		$BOS_CLIENT_CONF=array(
			'AccessKeyId' => $ak,
			'AccessKeySecret' => $sk,
			'TimeOut' => 5000,//5 seconds
			'Host' => 'bj.bcebos.com',
			'User-Agent' => 'discuzPlugin'
		);
		$this->client = new baidubce_services_bos_BosClient($BOS_CLIENT_CONF, true);
		$this->bucket = $bucket;
	}
	/**
	 * 字符串写入文件
	 * @param unknown $keyFile
	 * @param unknown $strContents
	 * @return boolean
	 */
    public function putObjectFromString($keyFile, $strContents)
    {
    	try{
    	
    		 $response = $this->client->putObjectFromString($this->bucket, $keyFile, $strContents);
    		 if (isset($response['status']) && $response['status'] == 200){
    		 	return true;
    		 }
    		 else {
    		 	return false;
    		 }
    	}catch(Exception $ex){
    		return false;
    	}   	
    }
    /**
     * 从文件到文件
     * @param unknown $keyFile
     * @param unknown $targetFile
     * @return boolean
     */
    public function putObjectFromFile($keyFile, $targetFile)
    {
    	try{
    		$response = $this->client->putObjectFromFile($this->bucket, $keyFile, $targetFile);	
    		if (isset($response['status']) && $response['status'] == 200){
    			return true;
    		}
    		else {
    			return false;
    		}
    	}catch(Exception $ex){
    		return false;
    	}
    }
    /**
     * 上传二进制文件
     * @param unknown $keyFile
     * @param unknown $targetFile
     * @return boolean
     */
    public function putObjectFromStream($keyFile, $targetFile)
    {
    	try{
    		$options = array(
    				'Content-Type' => 'application/octet-stream',
    		);
    		 
    		//$this->client->setBucketCannedAcl($this->bucket, 'public-read');
    		//$this->client->setBucketCannedAcl($this->bucket, 'private');
    
    		$response = $this->client->putObjectFromFile($this->bucket, $keyFile, $targetFile,$options);
    		if (isset($response['status']) && $response['status'] == 200){
    			return true;
    		}
    		else {
    			return false;
    		}
    	}catch(Exception $ex){
    		return false;
    	}
    }
    /**
     * 获取远程文件内容
     * @param unknown $keyFile
     * @return unknown|boolean
     */
    public function getObjectToFile($saveFile,$keyFile,$resumepos=null)
    {
    	try{
            $response = $this->client->getObjectToFile($this->bucket, $keyFile, $saveFile,$resumepos);
            if (!isset($response['status'])){
            	return false;
            }
    		if ( $response['status'] == 200 || $response['status'] == 206){
    			//@file_get_contents($saveFile);
    			//@unlink($saveFile);
    			//return $strContents;
				return true;
    		}
    		else {
    			return false;
    		}
    	}catch(Exception $ex){
    		return false;
    	}
    }
    /**
     * 复制object
     * @param unknown $keyFile
     * @param unknown $backupFile
     * @return boolean
     */
    public function copyObject($keyFile, $backupFile)
    {
    	try{
    		$response = $this->client->copyObject($this->bucket, $keyFile, $this->bucket, $backupFile);
    		if (isset($response['status']) && $response['status'] == 200){
    			return true;
    		}
    		else {
    			return false;
    		}
    	}catch(Exception $ex){
    		return false;
    	}
    }
    /**
     * 删除文件
     * @param unknown $keyFile
     * @return boolean
     */
    public function deleteObject($keyFile)
    {
    	try{
    		$response = $this->client->deleteObject($this->bucket, $keyFile);
    		return true;
    	}catch(Exception $ex){
    		return false;
    	}
    }
   /**
    * 文件是否存在
    * @param unknown $keyFile
    * @return boolean
    */
    public function is_object_exist($keyFile)
    {
    	try{
    		$response = $this->client->getObjectMetadata($this->bucket, $keyFile);
    		if (isset($response['status']) && $response['status'] == 200){
    		 	return true;
    		}
    		else {
    			return false;
    		}
    	}catch(Exception $ex){
    		return false;
    	}   	
    }
	
    public function get_object_size($keyFile)
    {
    	try{
    		$response = $this->client->getObjectMetadata($this->bucket, $keyFile);
    		if (isset($response['status']) && $response['status'] == 200 && isset($response['Content-Length'])){
    		 	return $response['Content-Length'];
    		}
    		else {
    			return 0;
    		}
    	}catch(Exception $ex){
    		return 0;
    	}   	
    }
}
