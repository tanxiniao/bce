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

namespace baidubce\model\stream;

use baidubce\exception\BceRuntimeException;

require_once dirname(__FILE__) . "/BceInputStream.php";

class BceFileInputStream extends BceInputStream {
    private $file_handle;
    private $file_size;

    function __construct($file_name, $start = 0, $length = -1) {
        $this->file_handle = fopen($file_name, "rb");
        fseek($this->file_handle, $start);
        if ($length >= 0) {
            $this->file_size = $start + $length;
        } else {
            $this->file_size = filesize($file_name);
        }
    }

    function __destruct() {
        fclose($this->file_handle);
    }

    public function read($size) {
        return fread($this->file_handle, $size);
    }

    public function readAll() {
        $this->seek(0);
        return fread($this->file_handle, $this->getSize());
    }

    public function getSize() {
        return $this->file_size;
    }

    public function seek($pos) {
        if ($pos > $this->file_size || $pos < 0) {
            throw new BceRuntimeException("Seek to a illegal position");
        }

        fseek($this->file_handle, $pos, SEEK_SET);
    }

    public function getPosition() {
        return ftell($this->file_handle);
    }
}
