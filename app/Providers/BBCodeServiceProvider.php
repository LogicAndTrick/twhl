<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LogicAndTrick\WikiCodeParser\Parser;
use LogicAndTrick\WikiCodeParser\ParserConfiguration;

class BBCodeServiceProvider extends ServiceProvider {

    protected $defer = true;

    public function register()
    {
        $this->app->singleton('bbcode', function($app) {
            return new Parser($this->getConfig());
        });
    }

    public function provides()
    {
        return [
            'bbcode'
        ];
    }

    private function getConfig() : ParserConfiguration {
        return ParserConfiguration::Twhl();
    }
}
