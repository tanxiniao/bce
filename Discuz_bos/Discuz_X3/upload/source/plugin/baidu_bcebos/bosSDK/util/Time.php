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
class baidubce_util_Time {
    /**
     * @param number $bos_time The default value is 0, most of time this paramter
     *   only used in test cases.
     *
     * @return string
     */
    static function bceTimeNow($bos_time = 0) {
        if ($bos_time > 0) {
            return gmstrftime("%Y-%m-%dT%H:%M:%SZ", $bos_time);
        }
        return gmstrftime("%Y-%m-%dT%H:%M:%SZ", time());
    }
}
