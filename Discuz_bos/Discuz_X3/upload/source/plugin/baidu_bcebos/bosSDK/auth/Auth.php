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

require_once dirname(dirname(__FILE__)) . "/util/Time.php";
require_once dirname(dirname(__FILE__)) . "/util/Coder.php";


class baidubce_auth_Auth {
    private $access_key;
    private $access_key_secret;

    /**
     * The Auth constructor
     *
     * @param string $access_key The BCE Access Key
     * @param string $access_key_secret The BCE Access Key Secret.
     */
    function __construct($access_key, $access_key_secret) {
        $this->access_key = $access_key;
        $this->access_key_secret = $access_key_secret;
    }

    /**
     * Generate BCE Authorization Signature
     *
     * @param string $method HTTP Request Method.
     * @param string $resource The resource path.
     * @param array $params The query strings.
     * @param array $headers The extra http request headers.
     * @param number $timestamp The customized timestamp, if it's 0, the will use time() instead.
     * @param number $expiration_in_seconds The valid time in seconds.
     * @param array $headers_to_sign The extra http request headers will be included in the signature
     *
     * @return string
     */
    public function generateAuthorization(
        $method, $resource,
        $params = array(), $headers = array(),
        $timestamp = 0, $expiration_in_seconds = 1800, $headers_to_sign = null) {

        $raw_session_key = sprintf("bce-auth-v%s/%s/%s/%d",
            "1",
            $this->access_key,
            baidubce_util_Time::bceTimeNow($timestamp),
            $expiration_in_seconds
        );
        $session_key = hash_hmac("sha256", $raw_session_key, $this->access_key_secret, false);

        $canonical_uri = "/v1" . baidubce_util_Coder::urlEncodeExceptSlash($resource);
        $canonical_query_string = $this->queryStringCanonicalization($params);
        list($canonical_headers, $signed_headers) = $this->headersCanonicalization($headers, $headers_to_sign);

        $raw_signature = $method ."\n" . $canonical_uri . "\n" . $canonical_query_string . "\n" . $canonical_headers;
        $signature = hash_hmac("sha256", $raw_signature, $session_key, false);

        if (count($signed_headers) > 0) {
            return sprintf('%s/%s/%s', $raw_session_key, implode(';', $signed_headers), $signature);
        }

        return sprintf('%s//%s', $raw_session_key, $signature);
    }

    /**
     * Canonicalization the query string.
     *
     * @param string $query_string The original query string.
     *
     * @return string
     */
    public function queryStringCanonicalization($query_string) {
        $canonical_query_string = array();
        foreach($query_string as $key => $value) {
            if ($key != "authorization" && $key != "Authorization") {
                array_push($canonical_query_string,
                    $this->normalizeString(trim($key)) . "=" . $this->normalizeString(trim($value)));
            }
        }

        sort($canonical_query_string);
        return implode("&", $canonical_query_string);
    }

    /**
     * Canonicalization the request headers.
     *
     * @param array $headers The http request headers.
     * @param array $headers_to_sign The default is host, content-md5, content-length, content-type, x-bce-*
     *
     * @return array
     */
    public function headersCanonicalization($headers, $headers_to_sign = null) {
        if (is_null($headers_to_sign) || count($headers_to_sign) <= 0) {
            $headers_to_sign = array('host', 'content-md5', 'content-length', 'content-type');
        }

        $canonical_headers = array();
        foreach ($headers as $key => $value) {
            $key = trim(strtolower($key));
            $value = trim($value);

            if ($value == '') {
                continue;
            }

            if (strpos($key, 'x-bce-') === 0 || in_array($key, $headers_to_sign)) {
                array_push($canonical_headers,
                    sprintf("%s:%s", $this->normalizeString($key), $this->normalizeString($value)));
            }
        }

        sort($canonical_headers);

        $signed_headers = array();
        foreach ($canonical_headers as $item) {
            $xyz = explode(':', $item, 2);
            array_push($signed_headers, $xyz[0]);
        }
        return array(
            implode("\n", $canonical_headers),
            $signed_headers,
        );
    }

    /**
     * Canonicalization string
     *
     * @param string $data
     *
     * @return string
     */
    private function normalizeString($data) {
        return rawurlencode($data);
    }
}
