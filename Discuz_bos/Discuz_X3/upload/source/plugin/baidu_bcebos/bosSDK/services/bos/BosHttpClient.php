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
require_once dirname(dirname(dirname(__FILE__))) . "/auth/Auth.php";
require_once dirname(dirname(dirname(__FILE__))) . "/http/HttpClient.php";
require_once dirname(dirname(dirname(__FILE__))) . "/util/Coder.php";
require_once dirname(dirname(dirname(__FILE__))) . "/util/Time.php";
require_once dirname(dirname(dirname(__FILE__))) . "/util/BceTools.php";
require_once dirname(dirname(dirname(__FILE__))) . "/exception/BceServiceException.php";


/**
 * Standard http request of Bos.
 */
class baidubce_services_bos_BosHttpClient extends baidubce_http_HttpClient {
    private $config;
    private $auth;

    /**
     * @param array $config The BosHttpClient configuration
     */
    function __construct(array $config){
        $this->config = $config;
        $this->auth = new baidubce_auth_Auth(
            $config['AccessKeyId'],
            $config['AccessKeySecret']
        );
    }

    /**
     * @param string $method one of PUT, GET, DELETE, HEAD, POST.
     * @param string $bucket The bucket name.
     * @param string $object The object name.
     * @param array $headers The http request headers.
     * @param string $body The request body.
     * @param array $params The query string
     * @param ?mixed $input_stream Read the http request body from this stream.
     * @param ?mixed $output_stream Write the http response to this stream.
     *
     * @return mixed status, body, http_headers
     */
    public function sendRequest($method, $bucket = '', $object = '', $headers = array(),
        $body = '', $params = array(), $input_stream = null, $output_stream = null) {

        $meta_size = 0;
        foreach ($headers as $k => $v) {
            if (strpos($k, 'x-bce-meta-') === 0) {
                $meta_size += strlen($v);
            } else if (strcmp($k, 'x-bce-copy-source') === 0) {
                $headers[$k] = baidubce_util_Coder::urlEncodeExceptSlash($v);
            }
        }

        if ($meta_size > 2 * 1024) {
            throw new Exception('Meta data is too large.');
        }

        $resource = empty($object) ? sprintf('/%s', $bucket) : sprintf('/%s/%s', $bucket, $object);

        $content_length = 0;
        if (is_resource($input_stream)) {
            $stat = fstat($input_stream);
            $content_length = $stat['size'];
        }
        else if ((!is_null($input_stream) && method_exists($input_stream, 'getSize'))) {
            $content_length = $input_stream->getSize();
        }
        else {
            $content_length = strlen($body);
        }

        $user_agent = isset($this->config['User-Agent']) ? $this->config['User-Agent'] : 'BOS PHP SDK v1';
        $default_headers = array(
            'x-bce-date' => baidubce_util_Time::bceTimeNow(),
            'x-bce-request-id' => baidubce_util_BceTools::genUUid(),
            'Expect' => '',
            'Transfer-Encoding' => '',
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => $content_length,
            'User-Agent' => $user_agent,
        );
        foreach ($default_headers as $k => $v) {
            if (!isset($headers[$k])) {
                $headers[$k] = $v;
            }
        }

        $authorization = $this->auth->generateAuthorization($method, $resource, $params, $headers);
        $headers['Authorization'] = $authorization;

        $url = sprintf('http://%s%s', $this->config['Host'], $this->getRequestUrl($resource, $params));
        $response = parent::sendRequest($method, $url, $body, $headers, $input_stream, $output_stream);

        $body = $response['body'];
        if (is_array($body) && isset($body['code']) && isset($body['message'])) {
            // Error hanppend.
            throw new baidubce_exception_BceServiceException($body['requestId'], $body['code'], $body['message'],
                $response['status']);
        }

        return $response;
    }

    /**
     * Generated url with authorization query string, so that we can access it via GET request.
     *
     * @param string $method one of PUT, GET, DELETE, HEAD, POST.
     * @param string $resource The bucket name and object path.
     * @param array $params The querystrings.
     * @param array $headers The http request headers.
     * @param number $timestamp 
     * @param number $expiration_in_seconds
     *
     * @return string
     */
    public function generatePresignedUrl($method, $resource, &$params, &$headers,
        $timestamp = 0, $expiration_in_seconds = 1800) {
        $authorization = $this->auth->generateAuthorization($method, $resource, $params, $headers,
            $timestamp, $expiration_in_seconds);
        $params["authorization"] = $authorization;

        return sprintf('http://%s%s', $this->config['Host'], $this->getRequestUrl($resource, $params));
    }

    /**
     * @param string $k query string key
     * @param string $v query string value
     *
     * @return string
     */
    private function encodeValue($k, $v) {
        return $k . "=" . baidubce_util_Coder::urlEncode($v);
    }

    /**
     * @param string $resource The bucket and object path.
     * @param array @param The query strings
     *
     * @return string The complete request url path with query string.
     */
    private function getRequestUrl($resource, $params) {
        $uri = "/v1" . baidubce_util_Coder::urlEncodeExceptSlash($resource);

        // TODO(leeight) 
        // k = k.replace('_', '-')
        $query_string = implode("&", array_map(
            array($this, 'encodeValue'), array_keys($params), $params));

        if (!is_null($query_string) && $query_string != "") {
            return $uri . "?" . $query_string;
        }

        return $uri;
    }
}
