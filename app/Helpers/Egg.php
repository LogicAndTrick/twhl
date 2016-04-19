<?php namespace App\Helpers;

class Egg
{
    static function GetEggClass()
    {
        $random = mt_rand(1, 13337);
        $leet = request('leet');
        $groups = [];
        $classes = [];
        foreach (Egg::$eggs as $key => $egg) {
            $match = null;
            if (isset($egg['leet']) && $leet == $egg['leet']) $match = true;
            else if ($egg['type'] == 'random') $match = Egg::random($random, $egg);
            else if ($egg['type'] == 'date') $match = Egg::date($egg);
            if (!$match) continue;

            foreach ($egg['groups'] as $group) {
                if (isset($groups[$group])) $match = false;
            }
            if (!$match) continue;

            foreach ($egg['groups'] as $group) {
                $groups[$group] = true;
            }
            if (isset($egg['classes'])) {
                $classes[] = 'egg-' . $egg['classes'][array_rand($egg['classes'])];
            } else {
                $classes[] = 'egg-' . $key;
            }
        }
        return implode(' ', $classes);
    }

    static function random($number, $egg) {
        return $egg['match'] == $number;
    }

    static function date($egg) {
        return gmdate($egg['format']) == $egg['match'];

    }

    static $eggs = [
        'ant' => [ // Ant's birthday Feb 29
            'groups' => ['background'],
            'type' => 'date',
            'format' => 'md',
            'match' => '0229',
            'leet' => 2
        ],
        'christmas' => [ // Christmas month
            'groups' => ['header'],
            'type' => 'date',
            'format' => 'm',
            'match' => '12',
            'classes' => [
                'christmas-1',
                'christmas-2',
                'christmas-3'
            ]
        ],
        'ninja' => [
            'groups' => ['background'],
            'type' => 'random',
            'match' => 10432,
            'leet' => 1
        ]
    ];
}