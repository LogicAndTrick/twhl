<?php

return [
    'tags' => [
        // Standard inline
        [ 'class' => 'App\Helpers\BBCode\Tags\Tag',      'scopes' => [ ], 'token' => 'b',      'element' => 'strong'                                   ],
        [ 'class' => 'App\Helpers\BBCode\Tags\Tag',      'scopes' => [ ], 'token' => 'i',      'element' => 'em'                                       ],
        [ 'class' => 'App\Helpers\BBCode\Tags\Tag',      'scopes' => [ ], 'token' => 'u',      'element' => 'span', 'element_class' => 'underline'     ],
        [ 'class' => 'App\Helpers\BBCode\Tags\Tag',      'scopes' => [ ], 'token' => 's',      'element' => 'span', 'element_class' => 'strikethrough' ],

        [ 'class' => 'App\Helpers\BBCode\Tags\Tag',      'scopes' => [ ], 'token' => 'green',  'element' => 'span', 'element_class' => 'green'         ],
        [ 'class' => 'App\Helpers\BBCode\Tags\Tag',      'scopes' => [ ], 'token' => 'blue',   'element' => 'span', 'element_class' => 'blue'          ],
        [ 'class' => 'App\Helpers\BBCode\Tags\Tag',      'scopes' => [ ], 'token' => 'purple', 'element' => 'span', 'element_class' => 'purple'        ],

        // Standard block
        [ 'class' => 'App\Helpers\BBCode\Tags\Tag',      'scopes' => [ ], 'token' => 'pre',    'element' => 'pre', 'block' => true ],

        // Links
        [ 'class' => 'App\Helpers\BBCode\Tags\LinkTag',      'scopes' => [ ], 'token' => 'url' ],
        [ 'class' => 'App\Helpers\BBCode\Tags\LinkTag',      'scopes' => [ ], 'token' => 'email' ],
        [ 'class' => 'App\Helpers\BBCode\Tags\QuickLinkTag', 'scopes' => [ ] ],
        [ 'class' => 'App\Helpers\BBCode\Tags\WikiLinkTag',  'scopes' => [ ] ],

        // Embedded
        [ 'class' => 'App\Helpers\BBCode\Tags\ImageTag',     'scopes' => [ ], 'token' => 'img' ],
        [ 'class' => 'App\Helpers\BBCode\Tags\ImageTag',     'scopes' => [ ], 'token' => 'simg' ],
        [ 'class' => 'App\Helpers\BBCode\Tags\WIkiImageTag', 'scopes' => [ ] ],

        //[ 'class' => 'App\Helpers\BBCode\Tags\YoutubeTag', 'scopes' => [ ] ],

        // Custom
        [ 'class' => 'App\Helpers\BBCode\Tags\QuoteTag', 'scopes' => [ ] ],
        [ 'class' => 'App\Helpers\BBCode\Tags\FontTag',  'scopes' => [ ] ],
    ],
    'elements' => [
        [ 'class' => 'App\Helpers\BBCode\Elements\MdHeadingElement', 'scopes' => [] ],
        [ 'class' => 'App\Helpers\BBCode\Elements\MdLineElement',    'scopes' => [] ],
        [ 'class' => 'App\Helpers\BBCode\Elements\MdQuoteElement',   'scopes' => [] ],
        [ 'class' => 'App\Helpers\BBCode\Elements\MdListElement',    'scopes' => [] ],
        [ 'class' => 'App\Helpers\BBCode\Elements\MdCodeElement',    'scopes' => [] ],
        [ 'class' => 'App\Helpers\BBCode\Elements\MdTableElement',   'scopes' => [] ],
    ],
    'processors' => [
        [ 'class' => 'App\Helpers\BBCode\Processors\AutoLinkingProcessor', 'scopes' => [] ],
        [ 'class' => 'App\Helpers\BBCode\Processors\SmiliesProcessor',     'scopes' => [] ],
        [ 'class' => 'App\Helpers\BBCode\Processors\NewLineProcessor',     'scopes' => [] ],
    ]
];
