<?php

namespace PhpOnCouch\Exception;

class NoResponseException extends Exception {
    function __construct() {
        parent::__construct(array('status_message'=>'No response from server - '));
    }
}