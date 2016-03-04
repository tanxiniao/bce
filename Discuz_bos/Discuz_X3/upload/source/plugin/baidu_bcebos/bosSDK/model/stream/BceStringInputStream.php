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
require_once dirname(__FILE__) . "/BceInputStream.php";
require_once dirname(__FILE__) . "/../../exception/BceStreamException.php";

class baidubce_model_stream_BceStringInputStream extends baidubce_model_stream_BceInputStream {
    function __construct($data) {
        $this->data = $data;
        $this->size = strlen($data);
        $this->pos = 0;
    }

    public function read($size) {
        if ($size + $this->pos > $this->size) {
            $size = $this->size - $this->pos;
        }

        $result = substr($this->data, $this->pos, $size);
        $this->pos += $size;

        return $result;
    }

    public function getSize() {
        return $this->size;
    }

    public function seek($pos) {
        if ($pos > $this->size || $pos < 0) {
            throw new baidubce_exception_BceStreamException("seek across end of string stream");
        }

        return $this->pos = $pos;

    }

    public function getPosition() {
        return $this->pos;
    }

    public function readAll() {
        return $this->data;
    }

    private $data;
    private $size;
    private $pos;
} 
