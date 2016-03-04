<?php
/*
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
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
require_once dirname(__FILE__) . "/BosClient.php";
require_once dirname(__FILE__) . "/BosHttpClient.php";
require_once dirname(__FILE__) . "/../../util/Coder.php";

define('MIN_PART_SIZE', 5242880);      // 5M
define('MAX_PART_SIZE', 5368709120);   // 5G
define('MAX_PARTS', 10000);

class baidubce_services_bos_BosClient {

    /**
     * @type BosHttpClient
     */
    private $http_client;

    /**
     * @type mixed
     */
    private $config;

    /**
     * The BosClient constructor
     *
     * @param array $config The client configuration
     */
    function __construct(array $config) {
        $this->config = $config;
        $this->http_client = new baidubce_services_bos_BosHttpClient($config);
    }

    // --- B E G I N ---

    /**
     * Get an authorization url with expire time
     *
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     * @param number $timestamp
     * @param number $expiration_in_seconds The valid time in seconds.
     * @param mixed $options The extra http request headers or params.
     *
     * @return string
     */
    public function generatePresignedUrl($bucket_name, $object_name,
        $timestamp = 0, $expiration_in_seconds = 1800, $options = array()) {
        list($headers, $params) = $this->checkOptions($options);
        $headers['Host'] = $this->config['Host'];
        $method = 'GET';
        $resource = sprintf("/%s/%s", $bucket_name, $object_name);
        return $this->http_client->generatePresignedUrl($method, $resource, $params, $headers,
            $timestamp, $expiration_in_seconds);
    }

    /**
     * List buckets of user.
     *
     * @return mixed All of the available buckets.
     */
    public function listBuckets() {
        return $this->http_client->sendRequest('GET');
    }

    /**
     * Create a new bucket.
     *
     * @param string $bucket_name The bucket name.
     *
     * @return mixed
     */
    public function createBucket($bucket_name) {
        return $this->http_client->sendRequest('PUT', $bucket_name);
    }

    /**
     * Get Object Information of bucket.
     *
     * @param string $bucket_name The bucket name.
     * @param string $delimiter The default value is null.
     * @param string $marker The default value is null.
     * @param number $max_keys The default value is 1000.
     * @param string $prefix The default value is null.
     *
     * @return mixed
     */
    public function listObjects($bucket_name, $delimiter = null, $marker = null, $max_keys = 1000, $prefix = null) {
        $headers = array();
        $params = array(
            'maxKeys' => $max_keys,
        );

        if (!is_null($delimiter)) { $params['delimiter'] = $delimiter; }
        if (!is_null($marker)) { $params['marker'] = $marker; }
        if (!is_null($prefix)) { $params['prefix'] = $prefix; }

        return $this->http_client->sendRequest('GET',
            $bucket_name, '', $headers, '', $params);
    }

    /**
     * Check whether there is some user access to this bucket.
     *
     * @param string $bucket_name The bucket name.
     *
     * @return boolean true means the bucket does exists.
     */
    public function doesBucketExist($bucket_name) {
        $response = $this->http_client->sendRequest('HEAD', $bucket_name);

        return in_array($response['status'], array(200, 204, 403), true);
    }

    /**
     * Delete a Bucket(Must Delete all the Object in Bucket before)
     *
     * @param string $bucket_name The bucket name.
     * @return mixed
     */
    public function deleteBucket($bucket_name) {
        return $this->http_client->sendRequest('DELETE', $bucket_name);
    }

    /**
     * Set Access Control Level of bucket
     *
     * @param string $bucket_name The bucket name.
     * @param string $acl The grant list.
     * @return mixed
     */
    public function setBucketCannedAcl($bucket_name, $acl) {
        $headers = array('x-bce-acl' => $acl);
        $params = array('acl' => null);

        return $this->http_client->sendRequest('PUT',
            $bucket_name, '', $headers, '', $params);
    }

    /**
     * Set Access Control Level of bucket
     *
     * @param string $bucket_name The bucket name.
     * @param mixed $acl The grant list.
     * @return mixed
     */
    public function setBucketAcl($bucket_name, $acl) {
        $headers = array();
        $body = json_encode(array('accessControlList' => $acl));
        $params = array('acl' => null);
        return $this->http_client->sendRequest('PUT',
            $bucket_name, '', $headers, $body, $params);
    }

    /**
     * Get Access Control Level of bucket
     *
     * @param string $bucket_name The bucket name.
     * @return mixed
     */
    public function getBucketAcl($bucket_name) {
        $headers = array();
        $params = array('acl' => null);

        $response = $this->http_client->sendRequest('GET',
            $bucket_name, '', $headers, '', $params);

        $MAX_SUPPORTED_ACL_VERSION = 1;
        if (!isset($response['body']['version'])) {
            $response['body']['version'] = $MAX_SUPPORTED_ACL_VERSION;
        }

        if ($response['body']['version'] > $MAX_SUPPORTED_ACL_VERSION) {
            throw new Exception('Unsupported acl version.');
        }

        return $response;
    }

    /**
     * Create object and put content of string to the object
     *
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     * @param string $input_content The object content.
     * @param mixed $options
     *
     * @return mixed
     */
    public function putObjectFromString($bucket_name, $object_name, $input_content, $options = array()) {
        list($headers, $params) = $this->checkOptions($options);

        if (empty($object_name)) {
            throw new baidubce_exception_BceIllegalArgumentException("Object name is empty.");
        }

        $content_length = strlen($input_content);
        if ($content_length > MAX_PART_SIZE) {
            throw new baidubce_exception_BceIllegalArgumentException("File size is too large.");
        }

        if (!isset($headers['Content-MD5'])) {
            $headers['Content-MD5'] = base64_encode(md5($input_content, true));
            // $headers['x-bce-meta-md5'] = md5($input_content);
        }

        if (!isset($headers['Content-Type'])) {
            // Return the default content-type
            $headers['Content-Type'] = baidubce_util_Coder::guessMimeType('');
        }

        return $this->http_client->sendRequest('PUT',
            $bucket_name, $object_name, $headers, $input_content, $params);
    }

    /**
     * Put object and copy content of file to the object
     *
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     * @param string $file_name The absolute file path.
     * @param mixed $options
     *
     * @return mixed
     */
    public function putObjectFromFile($bucket_name, $object_name, $file_name, $options = array()) {
        list($headers, $params) = $this->checkOptions($options);
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = baidubce_util_Coder::guessMimeType($file_name);
        }
        $fp = fopen($file_name, 'rb');
        $file_size = filesize($file_name);
        $offset = 0;
        $length = $file_size;
        try {
            $response = $this->putObjectFromHandle($bucket_name, $object_name, $fp, $file_size,
                $offset, $length, $headers, $params);
            fclose($fp);
            return $response;
        }
        catch(Exception $ex) {
            fclose($fp);
            throw $ex;
        }
    }

    /**
     * Put object and put content of file to the object
     *
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     * @param resource $fp The opened file handle.
     * @param number $file_size The file size.
     * @param number $offset The default value is 0.
     * @param number $length The default value is 0.
     * @param mixed $headers The http request headers.
     * @param mixed $params The http request query strings.
     *
     * @return mixed
     */
    public function putObjectFromHandle($bucket_name, $object_name, $fp, $file_size,
        $offset = 0, $length = 0, $headers = array(), $params = array()) {

        if (empty($object_name)) {
            throw new baidubce_exception_BceIllegalArgumentException("Object name is empty.");
        }

        if ($length <= 0) {
            $length = $file_size;
        }

        if ($length > MAX_PART_SIZE) {
            throw new baidubce_exception_BceIllegalArgumentException("File size is too large.");
        }

        if (!isset($headers['Content-MD5'])) {
            $md5 = baidubce_util_Coder::md5FromStream($fp, $offset, $length, true);
            $headers['Content-MD5'] = base64_encode($md5);
        }

        if (!isset($headers['Content-Length'])) {
            $headers['Content-Length'] = $length;
        }

        return $this->putObjectFromHandleInternal($bucket_name, $object_name, $fp, $file_size,
            $offset, $length, $headers, $params);
    }

    /**
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     * @param resource $fp The file handle.
     * @param number $file_size The file size.
     * @param number $offset The file offset.
     * @param number $length The part size.
     * @param array $headers The extra http request headers.
     * @param array $params The query strings.
     *
     * @return mixed
     */
    private function putObjectFromHandleInternal($bucket_name, $object_name, $fp, $file_size,
        $offset, $length, $headers, $params) {

        if ($offset + $length > $file_size) {
            throw new baidubce_exception_BceIllegalArgumentException(
                sprintf("Invalid offset error. offset = [%d], length = [%d], file_size = [%d]",
                    $offset, $length, $file_size));
        }

        $body = '';
        $input_stream = fopen('php://memory', 'r+');
        stream_copy_to_stream($fp, $input_stream, $length, $offset);
        rewind($input_stream);
        $response = $this->http_client->sendRequest('PUT',
            $bucket_name, $object_name, $headers, $body, $params, $input_stream);
        fclose($input_stream);
        return $response;
    }

    /**
     * @param array $options The mixed headers and params.
     *
     * @return array The headers and params.
     */
    private function checkOptions($options) {
        $headers = array();
        $params = array();

        $headers_options = array(
            'Content-MD5',
            'Content-Length',
            'Content-Type',
            'x-bce-copy-source-if-match',
            'x-bce-date',
            'x-bce-metadata-directive',
        );
        $params_options = array();
        foreach ($options as $key => $value) {
            if (in_array($key, $headers_options)) {
                $headers[$key] = $value;
            } else if (in_array($key, $params_options)) {
                $params[$key] = $value;
            } else if (strpos($key, 'x-bce-meta-') === 0) {
                $headers[$key] = $value;
            }
        }

        return array($headers, $params);
    }

    /**
     * Get Content of Object and Put Content to File
     *
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     * @param string $file_name The destination file name.
     * @param string $range The HTTP 'Range' header.
     *
     * @return mixed
     */
    public function getObjectToFile($bucket_name, $object_name, $file_name, $range = null) {
        $method = 'GET';

        $headers = array();
        if (!is_null($range)) {
            $headers['Range'] = sprintf("bytes=%s", $range);
        }

        $output_stream = fopen($file_name, 'w+');
        return $this->http_client->sendRequest('GET',
            $bucket_name, $object_name, $headers, '', array(), null, $output_stream);
    }

    /**
     * Delete Object
     *
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     *
     * @return mixed
     */
    public function deleteObject($bucket_name, $object_name) {
        return $this->http_client->sendRequest('DELETE', $bucket_name, $object_name);
    }

    /**
     * Get Object meta information
     *
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     *
     * @return mixed
     */
    public function getObjectMetadata($bucket_name, $object_name) {
        return $this->http_client->sendRequest('HEAD', $bucket_name, $object_name);
    }

    /**
     * Copy one object to another.
     *
     * @param string $source_bucket The source bucket name.
     * @param string $source_key The source object path.
     * @param string $target_bucket The target bucket name.
     * @param string $target_key The target object path.
     * @param mixed $options
     *
     * @return mixed
     */
    public function copyObject($source_bucket, $source_key,
        $target_bucket, $target_key, $options = array()) {

        if (empty($source_bucket) || empty($target_bucket)) {
            throw new baidubce_exception_BceIllegalArgumentException('Bucket is empty.');
        }

        if (empty($source_key) || empty($target_key)) {
            throw new baidubce_exception_BceIllegalArgumentException('Key is empty.');
        }

        list($headers, $params) = $this->checkOptions($options);

        $copy_source = sprintf("/%s/%s", $source_bucket, $source_key);
        $headers['x-bce-copy-source'] = baidubce_util_Coder::urlEncodeExceptSlash($copy_source);

        return $this->http_client->sendRequest('PUT',
            $target_bucket, $target_key, $headers, '', $params);
    }

    /**
     * Initialize multi_upload_file.
     *
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     * @param string $file_name Init the content-type by file name extension.
     *
     * @return mixed
     */
    public function initiateMultipartUpload($bucket_name, $object_name, $file_name = '') {
        $content_type = empty($file_name) ? baidubce_util_Coder::guessMimeType($object_name)
                                          : baidubce_util_Coder::guessMimeType($file_name);
        $headers = array(
            'Content-Type' => $content_type,
        );
        $params = array('uploads' => '');
        return $this->http_client->sendRequest('POST',
            $bucket_name, $object_name, $headers, '', $params);
    }

    /**
     * Abort upload a part which is being uploading.
     *
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     * @param string $upload_id The uploadId returned by initiateMultipartUpload.
     *
     * @return mixed
     */
    public function abortMultipartUpload($bucket_name, $object_name, $upload_id) {
        return $this->http_client->sendRequest('DELETE',
            $bucket_name, $object_name, array(), '', array('uploadId' => $upload_id));
    }

    /**
     * Upload a part from starting with offset.
     *
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     * @param string $file_name The file which will be uploaded.
     * @param number $offset The file offset.
     * @param number $part_size The uploaded part size.
     * @param string $upload_id The uploadId returned by initiateMultipartUpload.
     * @param number $part_number The part index.
     * @param mixed $options The extra http request headers or params.
     *
     * @return mixed
     */
    public function uploadPart($bucket_name, $object_name, $file_name,
        $offset, $part_size, $upload_id, $part_number, $options = array()) {

        if ($part_number < 1 || $part_number > MAX_PARTS) {
            throw new baidubce_exception_BceIllegalArgumentException("Invalid part number.");
        }

        // Only the last part's size can less than MIN_PART_SIZE, but
        // we don't know the total part count, so we have no way to do this check.
        if ($part_size >= MAX_PART_SIZE) {
            throw new baidubce_exception_BceIllegalArgumentException(
                sprintf("Invalid size, the maximum part size is %d", MAX_PART_SIZE));
        }

        list($headers, $params) = $this->checkOptions($options);
        $params['partnumber'] = $part_number;
        $params['uploadId'] = $upload_id;

        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = baidubce_util_Coder::guessMimeType($file_name);
        }

        $file_size = filesize($file_name);
        $fp = fopen($file_name, 'rb');

        try {
            $response = $this->putObjectFromHandle($bucket_name, $object_name, $fp, $file_size,
                $offset, $part_size, $headers, $params);
            fclose($fp);

            return $response;
        }
        catch(Exception $ex) {
            fclose($fp);
            throw $ex;
        }
    }

    /**
     * List all the parts that have been upload success.
     *
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     * @param string $upload_id The uploadId returned by initiateMultipartUpload.
     * @param number $max_keys The maximum size of returned parts, default 1000, maximum value is 1000.
     * @param string $part_number_marker Sort by uploaded partnumber, and returned parts from this given value.
     *
     * @return mixed
     */
    public function listParts($bucket_name, $object_name, $upload_id,
        $max_keys = 1000, $part_number_marker = null) {

        $params = array(
            'uploadId' => $upload_id,
            'maxParts' => $max_keys,
        );
        if (!is_null($part_number_marker)) {
            $params['partNumberMarker'] = $part_number_marker;
        }

        return $this->http_client->sendRequest('GET',
            $bucket_name, $object_name, array(), '', $params);
    }

    /**
     * After finish all the task, complete multi_upload_file.
     * bucket, key, upload_id, part_list, options=None
     * @param string $bucket_name The bucket name.
     * @param string $object_name The object path.
     * @param string $upload_id The upload id.
     * @param mixed $part_list (partnumber and etag) list
     * @param mixed $options extra http request header and params.
     *
     * @return mixed
     */
    public function completeMultipartUpload($bucket_name, $object_name, $upload_id,
        $part_list, $options = array()) {

        list($headers, $params) = $this->checkOptions($options);
        $params['uploadId'] = $upload_id;

        $body = json_encode(array('parts' => $part_list));
        return $this->http_client->sendRequest('POST',
            $bucket_name, $object_name, $headers, $body, $params);
    }

    /**
     * List all Multipart upload task which haven't been ended.
     * call initiateMultipartUpload but not call completeMultipartUpload or abortMultipartUpload
     *
     * @param string $bucket_name The bucket name.
     * @param string $delimiter
     * @param number $max_uploads The default value is 1000.
     * @param string $key_marker
     * @param string $prefix
     * @param string $upload_id_marker
     *
     * @return mixed
     */
    public function listMultipartUploads($bucket_name, $delimiter = '', $max_uploads = 1000,
        $key_marker = '', $prefix = '', $upload_id_marker = '') {

        $params = array('uploads' => null, 'maxUploads' => $max_uploads);
        if (!empty($delimiter)) { $params['delimiter'] = $delimiter; }
        if (!empty($key_marker)) { $params['keyMarker'] = $key_marker; }
        if (!empty($prefix)) { $params['prefix'] = $prefix; }
        if (!empty($upload_id_marker)) { $params['uploads'] = $upload_id_marker; }

        return $this->http_client->sendRequest('GET',
            $bucket_name, '', array(), '', $params);
    }

    // --- E N D ---
}
