<?php

namespace App\Helpers;

class Errors
{
    /**
     * Check if an exception is a TypeError when calling a controller action.
     * This should be replaced with better protection on the routes once I move
     * to modern explicit routing.
     */
    public static function isControllerBindingTypeError(\Throwable $exception)
    {
        if (!($exception instanceof \TypeError)) return false;
        $trace = $exception->getTrace();
        $source = $trace[1] ?? [];
        return is_array($source)
            && isset($source['class']) && $source['class'] === 'Illuminate\Routing\Controller'
            && isset($source['type']) && $source['type'] === '->'
            && isset($source['function']) && $source['function'] === 'callAction';
    }
}
