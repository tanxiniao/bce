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
class baidubce_util_Coder {
    /**
     * @param string $str
     *
     * @return string
     */
    static function urlEncode($str) {
        return rawurlencode($str);
    }

    /**
     * Encode string except slash
     *
     * @param string $str
     *
     * @return string
     */
    static function urlEncodeExceptSlash($str) {
        return implode("/", array_map(rawurlencode, explode("/", $str)));
    }

    /**
     * @param resource $fp The opened file
     * @param number $offset The offset.
     * @param number $length Maximum number of characters to copy from
     *   $fp into the hashing context.
     * @param boolean $raw_output When TRUE, returns the digest in raw
     *   binary format with a length of 16
     *
     * @return string
     */
    static function md5FromStream($fp, $offset = 0, $length = -1, $raw_output = false) {
        $pos = ftell($fp);
        $ctx = hash_init('md5');
        fseek($fp, $offset, SEEK_SET);
        hash_update_stream($ctx, $fp, $length);
        // TODO(leeight) restore ftell value?
        if ($pos !== false) {
            fseek($fp, $pos, SEEK_SET);
        }
        return hash_final($ctx, $raw_output);
    }

    /**
     * Guess mime-type from file name extension. Return default mime-type if guess failed.
     *
     * @param string $file_name
     *
     * @return string
     */
    static function guessMimeType($file_name) {
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $map = require(dirname(__FILE__) . "/mime.types.php");

        return isset($map[$ext]) ? $map[$ext] : 'application/octet-stream';
    }
} 
