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

    static function GetRenderTime()
    {
        $adv = Egg::$words['adverbs'];
        $adj = Egg::$words['adjectives'];
        $uni = Egg::$words['units'];
        $time = floor(2 + pow(mt_rand() / mt_getrandmax(), 2.5) * 1998);
        return 'Processed in ' . $adv[array_rand($adv)] . ' ' . $adj[array_rand($adj)] . ' ' . $time . ' ' . $uni[array_rand($uni)] . '.';
    }

    static $words = [
        'adverbs' => [
            'a blindingly',
            'a reasonably',
            'a decently',
            'a ploddingly',
            'a horribly',
            'an awfully',
            'an astoundingly',
            'a painfully',
            'a frustratingly',
            'an excessively',
            'an annoyingly',
            'an excessively',
            'a uniquely',
            'a terrifyingly',
            'a seductively',
            'a begrudgingly',
            'an excruciatingly',
            'a ridiculously',
            'an agreeably',
            'a bewilderingly',
            'a coincidentally',
            'a dangerously',
            'a respectable',
            'an ethically',
            'a fatally',
            'a genuinely',
            'a hauntingly',
            'an infinitely',
            'a not very',
            'a paradoxically',
            'a majestically',
            'an oddly',
            'an offensively',
            'a potentially',
            'a recklessly',
            'a somewhat',
            'a substantially',
            'an unfortunately',
            'a vaguely',
            'a woefully',
        ],
        'adjectives' => [
            'fast',
            'speedy',
            'quick',
            'decent',
            'slow',
            'lousy',
            'dire',
            'hungry',
            'cloistered',
            'forgetful',
            'massive',
            'tiny',
            'tragic',
            'luxurious',
            'tantalising',
            'extravagant',
            'reticent',
            'ambiguous',
            'mesmerising',
            'opaque',
            'terrific',
            'ominous',
            'unfortunate',
            'delicious',
            'relevant',
            'redundant',
            'cheesy',
            'jocular',
            'questionable',
            'unethical',
            'aromatic',
            'biodegradable',
            'cute',
            'colossal',
            'clumsy',
            'dismal',
            'dynamic',
            'dependable',
            'exciting',
            'flamboyant',
            'greedy',
            'hairy',
            'healthy',
            'historic',
            'impressive',
            'kosher',
            'lumpy',
            'mediocre',
            'monumental',
            'mysterious',
            'outrageous',
            'pitiful',
            'quaint',
            'reckless',
            'sarcastic',
            'shabby',
            'stimulating',
            'tempting',
            'traumatic',
            'unlucky',
            'useless',
            'vicious',
            'whimsical',
            'worthless'
        ],
        'units' => [
            // Heavy milliseconds stacking
            'milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds',
            'milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds',
            'milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds',
            'milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds','milliseconds',
            'nanoseconds',
            'seconds',
            'hours',
            'cubits',
            'leap years',
            'parsecs',
            'ticks',
            'generations',
            'Planck units',
            'half-lives',
            'jiffies',
            'epochs'
        ]
    ];
}