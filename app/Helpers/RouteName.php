<?php

namespace App\Helpers;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RouteName {
    public function __construct(public string $name) {}
}
