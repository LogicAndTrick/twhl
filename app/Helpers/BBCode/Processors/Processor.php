<?php

namespace App\Helpers\BBCode\Processors;
 
abstract class Processor {

    public $scopes = array();

    abstract function Process($result, $text, $scope);

    public function InScope($scope)
    {
        return !$scope || count($this->scopes) == 0 || array_search($scope, $this->scopes) !== false;
    }
}

?> 