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

require_once dirname(dirname(__FILE__)) . "/exception/BceRuntimeException.php";

/**
 * Standard http request of BCE.
 */
class baidubce_http_HttpClient {
    private $config;

    private $http_response_headers;

    private $input_stream;

    private $output_stream;

    /**
     * HttpClient's constructor
     * @param array $config The http client configuration.
     */
    public function __construct($config = array()) {
        $this->config = $config;
    }

    /**
     * Send request to BCE.
     *
     * @param {string} $method The http request method, uppercase.
     * @param {string} $url The http request url.
     * @param {string} $request_body The http request body.
     * @param {mixed} $headers The extra http request headers.
     * @param {?mixed} $input_stream Read the http request body from this stream.
     * @param {?mixed} $output_stream Write the http response to this stream.
     *
     * @return mixed status, body, http_headers
     */
    public function sendRequest($method, $url, $request_body = '', $headers = array(),
        $input_stream = null, $output_stream = null) {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_NOPROGRESS, true);
        curl_setopt($curl_handle, CURLINFO_HEADER_OUT, true);

        if (isset($this->config['TimeOut'])) {
            curl_setopt($curl_handle, CURLOPT_TIMEOUT_MS, $this->config['TimeOut']);
        }

        $header_line_list = array();
        foreach ($headers as $key => $val) {
            if ($key !== 'Host') {
                array_push($header_line_list, sprintf('%s: %s', $key, $val));
            }
        }
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header_line_list);
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, $method);

        if ($method === 'HEAD') {
            curl_setopt($curl_handle, CURLOPT_NOBODY, true);
        }

        // Handle the request body
        // 1. If $request_body exists, convert it from string to a stream 
        // 2. Set the ReadFunction Option to $read_callback which will return the request body.
        if ($request_body != '' && is_null($input_stream)) {
            $input_stream = fopen('php://memory','r+');
            fwrite($input_stream, $request_body);
            rewind($input_stream);
        }

        if (!is_null($input_stream)) {
            $this->input_stream = $input_stream;
            curl_setopt($curl_handle, CURLOPT_POST, true);
            curl_setopt($curl_handle, CURLOPT_READFUNCTION, array($this, 'readCallback'));
        }

        // Handle Http Response
        if (!is_null($output_stream)) {
            $this->output_stream = $output_stream;
            curl_setopt($curl_handle, CURLOPT_WRITEFUNCTION, array($this, 'writeCallback'));
        }
        else {
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        }

        // Handle Http Response Headers
        $this->http_response_headers = array();
        curl_setopt($curl_handle, CURLOPT_HEADERFUNCTION, array($this, 'responseHeaderCallback'));
        curl_setopt($curl_handle, CURLOPT_HEADER, false);

        // Send request
        $response = curl_exec($curl_handle);
        $error = curl_error($curl_handle);
        $errno = curl_errno($curl_handle);
        $status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($curl_handle, CURLINFO_CONTENT_TYPE);
        // print_r(curl_getinfo($curl_handle, CURLINFO_HEADER_OUT));
        curl_close($curl_handle);

        if ($error != "") {
            throw new baidubce_exception_BceRuntimeException(sprintf("errno = %d, error = %s", $errno, $error));
        }

        $http_headers = $this->parseHttpHeaders($this->http_response_headers);
        if ($response === '' || $response === true) {
            // $response === true means the response body was handled by $output_stream.
            // $response === '' means there is no response body.
            $body = array();
        } else if (!is_null($content_type) && strpos($content_type, 'application/json') === 0) {
            $body = json_decode($response, true);
            if (is_null($body)) {
                throw new baidubce_exception_BceRuntimeException(sprintf("MalformedJSON (%s)", $response));
            }
        } else {
            $body = $response;
        }

        return array(
            'status' => $status,
            'http_headers' => $http_headers,
            'body' => $body,
        );
    }

    /**
     * @param resource $_1 Then opened curl handle.
     * @param string $str The header line
     *
     * @return number
     */
    private function responseHeaderCallback($_1, $str) {
        array_push($this->http_response_headers, $str);
        return strlen($str);
    }

    /**
     * @param resource $_1 The opened curl handle.
     * @param string $str The response body data chunk.
     *
     * @return number
     */
    private function writeCallback($_1, $str) {
        if (is_resource($this->output_stream)) {
            return fwrite($this->output_stream, $str);
        }
        else if (method_exists($this->output_stream, 'write')) {
            return $this->output_stream->write($str);
        }

        // EOF
        return false;
    }

    /**
     * @param resource $_1 The opend curl handle
     * @param resource $_2 The opend file passed by CURLOPT_INFILE option
     * @param number size The required data length.
     *
     * @return number The actual readed data length, empty string means reach the end.
     */
    private function readCallback($_1, $_2, $size) {
        if (is_resource($this->input_stream)) { 
            return fread($this->input_stream, $size);
        }
        else if (method_exists($this->input_stream, 'read')) {
            return $this->input_stream->read($size);
        }

        // EOF
        return '';
    }

    /**
     * @param array $raw_headers The http response headers.
     *
     * @return array The normalized http response headers, empty value header
     *   was omited.
     */
    private function parseHttpHeaders($raw_headers) {
        $headers = array();
        foreach ($raw_headers as $i => $h) {
            if ($i > 0) {
                $h = explode(':', $h, 2);
                if (isset($h[1])) {
                    if ($h[0] === 'ETag') {
                        $h[1] = str_replace("\"", "", $h[1]);
                    }
                    $headers[$h[0]] = trim($h[1]);
                }
            }
        }

        return $headers;
    }
}
