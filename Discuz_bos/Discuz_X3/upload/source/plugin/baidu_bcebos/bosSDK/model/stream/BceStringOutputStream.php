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
require_once dirname(__FILE__) . "/BceOutputStream.php";

class BceStringOutputStream extends  BceOutputStream {
    function __construct()
    {
        $this->data_array = array();
    }

    public function write($data) {
        array_push($this->data_array, $data);
        return strlen($data);
    }

    public function reserve($data) {
        return 0;
    }

    public function readAll() {
        return implode($this->data_array);
    }

    private $data_array;
} 