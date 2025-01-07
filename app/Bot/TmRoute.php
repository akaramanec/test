<?php

namespace App\Bot;

use App\Models\Logger;
use Exception;

class TmRoute
{
    private array $route;

    public function __construct($action, $actionData = null)
    {
        $this->route = config('tm-routes');
        try {
            if (isset($this->route[$action])) {
                $route = $this->route[$action];
            } else {
                return (new TmCommon)->unknown();
            }
            $object = new $route['class'];

            if (method_exists($object, $route['method'])) {
                if ($actionData) {
                    return $object->{$route['method']}($actionData);
                } else {
                    return $object->{$route['method']}();
                }
            }
            throw new Exception('No method exists: '.$route['method']);
        } catch (Exception $e) {
            Logger::commit([$e->getMessage(), $e->getTraceAsString()], __METHOD__.__LINE__);
            exit(__METHOD__.__LINE__);
        }
    }
}
