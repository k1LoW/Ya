<?php

/**
 * InactiveControllerException.
 */
class InactiveControllerException extends CakeException
{
    public function __construct($message = null, $code = 302)
    {
        if (empty($message)) {
            $message = __('Exception Error.');
        }
        parent::__construct($message, $code);
    }
}
