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
require_once dirname(__FILE__) . "/BceBaseException.php";

class baidubce_exception_BceServiceException extends baidubce_exception_BceBaseException {
    private $status_code;
    private $request_id;
    private $service_error_code;

    /**
     * @return string
     */
    public function getRequestId() {
        return $this->request_id;
    }

    /**
     * @return string
     */
    public function getServiceErrorCode() {
        return $this->service_error_code;
    }

    /**
     * @return number
     */
    public function getStatusCode() {
        return $this->status_code;
    }


    /**
     * @param string $request_id The x-bce-request-id value
     * @param string $service_error_code The server error code.
     * @param string $service_error_code The server error message.
     * @param number $status_code The http response status.
     */
    function __construct($request_id, $service_error_code, $service_error_message, $status_code) {
        $this->request_id = $request_id;
        $this->service_error_code = $service_error_code;
        $this->status_code = $status_code;
        parent::__construct($service_error_message);
    }

    /**
     * @return string
     */
    function getDebugMessage() {
        return sprintf("status_code:%d, service_error_code:%s, message:%s, request_id:%s",
            $this->status_code, $this->service_error_code, $this->getMessage(), $this->request_id);
    }
} 
