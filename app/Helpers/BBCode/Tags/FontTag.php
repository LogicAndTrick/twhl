<?php

namespace App\Helpers\BBCode\Tags;
 
class FontTag extends Tag {

    function __construct()
    {
        $this->token = 'font';
        $this->element = 'span';
        $this->main_option = 'color';
        $this->options = array('color', 'size');
    }

    public function FormatResult($result, $parser, $scope, $options, $text)
    {
        $str = '<' . $this->element;
        if ($this->element_class) $str .= ' class="' . $this->element_class . '"';
        if (array_key_exists('color', $options) || array_key_exists('colour', $options) || array_key_exists('size', $options)) {
            $str .= ' style="';
            if (array_key_exists('color', $options) && FontTag::IsValidColor($options['color'])) $str .= 'color: ' . $options['color'] . ';';
            else if (array_key_exists('colour', $options) && FontTag::IsValidColor($options['colour'])) $str .= 'color: ' . $options['colour'] . ';';
            if (array_key_exists('size', $options) && FontTag::IsValidSize($options['size'])) $str .= 'font-size: ' . $options['size'] . ';';
            $str .= '"';
        }
        $str .= '>';
        $str .= $parser->ParseBBCode($result, $text, $scope, $this->block ? 'block' : 'inline');
        $str .= '</' . $this->element . '>';
        return $str;
    }

    private static $color_names = array(
        'aliceblue', 'antiquewhite', 'aqua', 'aquamarine', 'azure', 'beige', 'bisque', 'black',
        'blanchedalmond', 'blue', 'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse',
        'chocolate', 'coral', 'cornflowerblue', 'cornsilk', 'crimson', 'cyan', 'darkblue', 'darkcyan',
        'darkgoldenrod', 'darkgray', 'darkgrey', 'darkgreen', 'darkkhaki', 'darkmagenta', 'darkolivegreen',
        'darkorange', 'darkorchid', 'darkred', 'darksalmon', 'darkseagreen', 'darkslateblue',
        'darkslategray', 'darkslategrey', 'darkturquoise', 'darkviolet', 'deeppink', 'deepskyblue',
        'dimgray', 'dimgrey', 'dodgerblue', 'firebrick', 'floralwhite', 'forestgreen', 'fuchsia',
        'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'gray', 'grey', 'green', 'greenyellow',
        'honeydew', 'hotpink', 'indianred', 'indigo', 'ivory', 'khaki', 'lavender', 'lavenderblush',
        'lawngreen', 'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan', 'lightgoldenrodyellow',
        'lightgray', 'lightgrey', 'lightgreen', 'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue',
        'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightyellow', 'lime', 'limegreen', 'linen',
        'magenta', 'maroon', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple',
        'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise', 'mediumvioletred',
        'midnightblue', 'mintcream', 'mistyrose', 'moccasin', 'navajowhite', 'navy', 'oldlace', 'olive',
        'olivedrab', 'orange', 'orangered', 'orchid', 'palegoldenrod', 'palegreen', 'paleturquoise',
        'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'purple', 'red',
        'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown', 'seagreen', 'seashell', 'sienna',
        'silver', 'skyblue', 'slateblue', 'slategray', 'slategrey', 'snow', 'springgreen', 'steelblue', 'tan',
        'teal', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'white', 'whitesmoke', 'yellow', 'yellowgreen');

    public static function IsValidColor($text)
    {
        if (preg_match('/^#(?:[0-9A-F]{3}){1,2}$/i', $text)) {
            return true;
        }
        return array_search($text, FontTag::$color_names) !== false;
    }

    public static function IsValidSize($text)
    {
        return is_numeric($text) && $text >= 6 && $text <= 40;
    }
}
