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

namespace baidubce\services\bos\model\stream;

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/model/stream/BceInputStream.php";

use baidubce\model\stream\BceInputStream;

class BosFileInputStream extends BceInputStream {
    private $file_handle;
    private $file_size;
    private $offset;
    private $done;
    private $left_length;

    /**
     * @param resource $file_handle The opened file.
     * @param number $file_size The file size.
     * @param number $offset The start position.
     * @param number $length The part size.
     */
    function __construct($file_handle, $file_size, $offset = 0, $length = null) {
        $this->file_handle = $file_handle;
        $this->file_size = $file_size;
        $this->offset = $offset;
        $this->done = 0;
        $this->left_length = is_null($length) ? $file_size : $length;
    }

    /**
     * @param number $size
     *
     * @return string
     */
    public function read($size) {
        if ($this->left_length <= 0) {
            return '';
        }

        if ($this->getPosition() !== $this->offset + $this->done) {
            $this->seek($this->offset + $this->done);
        }

        if ($this->left_length < $size) {
            $content = fread($this->file_handle, $this->left_length);
        }
        else {
            $content = fread($this->file_handle, $size);
        }
        $content_length = strlen($content);

        $this->done += $content_length;
        $this->left_length -= $content_length;

        return $content;
    }

    /**
     * @return string
     */
    public function readAll() {
        $this->seek(0);
        return fread($this->file_handle, $this->getSize());
    }

    /**
     * @return number
     */
    public function getSize() {
        return $this->file_size;
    }

    /**
     * @param number $pos
     */
    public function seek($pos) {
        if ($pos > $this->file_size || $pos < 0) {
            throw new BceRuntimeException("Seek to a illegal position");
        }

        fseek($this->file_handle, $pos, SEEK_SET);
    }

    /**
     * @return number
     */
    public function getPosition() {
        return ftell($this->file_handle);
    }
}




/* vim: set ts=4 sw=4 sts=4 tw=120: */
