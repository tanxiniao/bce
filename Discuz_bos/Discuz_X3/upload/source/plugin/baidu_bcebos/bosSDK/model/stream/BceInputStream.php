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

require_once dirname(__FILE__) . "/BceBaseStream.php";
require_once dirname(__FILE__) . "/../../exception/BceRuntimeException.php";

class baidubce_model_stream_BceInputStream extends baidubce_model_stream_BceBaseStream {
    public function read($size) {
        throw new baidubce_exception_BceRuntimeException("BceInputStream", "read");
    }

    public function readAll() {
        throw new baidubce_exception_BceRuntimeException("BceInputStream", "readAll");
    }

    public function getSize() {
        throw new baidubce_exception_BceRuntimeException("BceInputStream", "getSize");
    }

    public function seek($pos) {
        throw new baidubce_exception_BceRuntimeException("BceInputStream", "seek");
    }

    public function getPosition() {
        throw new baidubce_exception_BceRuntimeException("BceInputStream", "getPosition");
    }
} 
