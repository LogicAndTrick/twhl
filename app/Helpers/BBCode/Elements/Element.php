<?php

namespace App\Helpers\BBCode\Elements;

use App\Helpers\BBCode\Lines;
use App\Helpers\BBCode\Parser;

abstract class Element
{
    public $scopes = array();

    /**
     * @param $lines Lines
     * @return bool
     */
    abstract function Matches($lines);

    /**
     * @param $parser Parser
     * @param $lines Lines
     * @return bool
     */
    abstract function Consume($parser, $lines);

    abstract function Parse($result, $scope);

    public function InScope($scope)
    {
        return !$scope || count($this->scopes) == 0 || array_search($scope, $this->scopes) !== false;
    }
}
