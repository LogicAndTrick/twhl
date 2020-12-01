<?php namespace App\Helpers;

class TwhlHeader
{
    static function IsChristmas() {
        return gmdate('m') == '12';
    }

    static function HeaderInfo() {
        $conditional = [
            'christmas' => TwhlHeader::IsChristmas()
        ];
        $headers = \Illuminate\Support\Arr::where(TwhlHeader::$headers, function ($header) use ($conditional) {
            return !array_key_exists('conditional', $header) || $conditional[$header['conditional']] === true;
        });
        return $headers[array_rand($headers)];
    }

    static $headers = [
        [ 'image' => 'bridgethegap2.jpg'    , 'type' => 'map' , 'name' => 'Bridge the Gap 2.0', 'name_link' => 'https://twhl.info/vault/view/6453'              , 'author' => 'UrbaNebula'    , 'author_link' => 'https://twhl.info/user/view/1127' ],
        [ 'image' => 'cenodrome.jpg'        , 'type' => 'map' , 'name' => 'Cenodrome'         , 'name_link' => 'https://twhl.info/vault/view/6300'              , 'author' => 'Unq'           , 'author_link' => 'https://twhl.info/user/view/5225' ],
        [ 'image' => 'dm_hydroelectric.jpg' , 'type' => 'map' , 'name' => 'dm_hydroelectric'  , 'name_link' => 'https://twhl.info/vault/view/6173'              , 'author' => 'Jessie'        , 'author_link' => 'https://twhl.info/user/view/3643' ],
        [ 'image' => 'dm_perthowned_01.jpg' , 'type' => 'map' , 'name' => 'dm_perthowned'     , 'name_link' => 'https://twhl.info/vault/view/6391'              , 'author' => 'Silvertongue'  , 'author_link' => 'https://twhl.info/user/view/6544' ],
        [ 'image' => 'dm_perthowned_02.jpg' , 'type' => 'map' , 'name' => 'dm_perthowned'     , 'name_link' => 'https://twhl.info/vault/view/6391'              , 'author' => 'Silvertongue'  , 'author_link' => 'https://twhl.info/user/view/6544' ],
        [ 'image' => 'dm_razorback.jpg'     , 'type' => 'map' , 'name' => 'Razorback'         , 'name_link' => 'https://twhl.info/vault/view/6395'              , 'author' => 'Windawz'       , 'author_link' => 'https://twhl.info/user/view/6880' ],
        [ 'image' => 'dm_undergroundlab.jpg', 'type' => 'map' , 'name' => 'dm_undergroundlab' , 'name_link' => 'https://twhl.info/vault/view/5313'              , 'author' => 'The Mad Carrot', 'author_link' => 'https://twhl.info/user/view/469'  ],
        [ 'image' => 'header-1.jpg'         , 'type' => 'game', 'name' => 'Half-Life'         , 'name_link' => 'https://store.steampowered.com/app/70/HalfLife/', 'author' => 'Valve Software', 'author_link' => 'https://www.valvesoftware.com/' ],
        [ 'image' => 'header-2.jpg'         , 'type' => 'game', 'name' => 'Half-Life'         , 'name_link' => 'https://store.steampowered.com/app/70/HalfLife/', 'author' => 'Valve Software', 'author_link' => 'https://www.valvesoftware.com/' ],
        [ 'image' => 'header-3.jpg'         , 'type' => 'game', 'name' => 'Half-Life'         , 'name_link' => 'https://store.steampowered.com/app/70/HalfLife/', 'author' => 'Valve Software', 'author_link' => 'https://www.valvesoftware.com/' ],
        [ 'image' => 'header-4.jpg'         , 'type' => 'game', 'name' => 'Half-Life'         , 'name_link' => 'https://store.steampowered.com/app/70/HalfLife/', 'author' => 'Valve Software', 'author_link' => 'https://www.valvesoftware.com/' ],
        [ 'image' => 'header-5.jpg'         , 'type' => 'game', 'name' => 'Half-Life'         , 'name_link' => 'https://store.steampowered.com/app/70/HalfLife/', 'author' => 'Valve Software', 'author_link' => 'https://www.valvesoftware.com/' ],
        [ 'image' => 'header-6.jpg'         , 'type' => 'game', 'name' => 'Half-Life'         , 'name_link' => 'https://store.steampowered.com/app/70/HalfLife/', 'author' => 'Valve Software', 'author_link' => 'https://www.valvesoftware.com/' ],
        [ 'image' => 'header-7.jpg'         , 'type' => 'game', 'name' => 'Half-Life'         , 'name_link' => 'https://store.steampowered.com/app/70/HalfLife/', 'author' => 'Valve Software', 'author_link' => 'https://www.valvesoftware.com/' ],
        [ 'image' => 'header-8.jpg'         , 'type' => 'game', 'name' => 'Half-Life'         , 'name_link' => 'https://store.steampowered.com/app/70/HalfLife/', 'author' => 'Valve Software', 'author_link' => 'https://www.valvesoftware.com/' ],
        [ 'image' => 'tower1_01.jpg'        , 'type' => 'mod' , 'name' => 'TWHL Tower'        , 'name_link' => 'https://twhl.info/vault/view/6082'              , 'author' => 'TWHL members'  , 'author_link' => 'https://twhl.info/' ],
        [ 'image' => 'tower1_02.jpg'        , 'type' => 'mod' , 'name' => 'TWHL Tower'        , 'name_link' => 'https://twhl.info/vault/view/6082'              , 'author' => 'TWHL members'  , 'author_link' => 'https://twhl.info/' ],
        [ 'image' => 'tower1_03.jpg'        , 'type' => 'mod' , 'name' => 'TWHL Tower'        , 'name_link' => 'https://twhl.info/vault/view/6082'              , 'author' => 'TWHL members'  , 'author_link' => 'https://twhl.info/' ],
        [ 'image' => 'tower2_01.jpg'        , 'type' => 'mod' , 'name' => 'TWHL Tower 2'      , 'name_link' => 'https://twhl.info/vault/view/6456'              , 'author' => 'TWHL members'  , 'author_link' => 'https://twhl.info/' ],
        [ 'image' => 'tower2_02.jpg'        , 'type' => 'mod' , 'name' => 'TWHL Tower 2'      , 'name_link' => 'https://twhl.info/vault/view/6456'              , 'author' => 'TWHL members'  , 'author_link' => 'https://twhl.info/' ],
        [ 'image' => 'tower2_03.jpg'        , 'type' => 'mod' , 'name' => 'TWHL Tower 2'      , 'name_link' => 'https://twhl.info/vault/view/6456'              , 'author' => 'TWHL members'  , 'author_link' => 'https://twhl.info/' ],
        // Conditional
        [ 'image' => 'santasrevenge_01.jpg' , 'type' => 'mod' , 'name' => 'Santa\'s Revenge'  , 'name_link' => 'https://twhl.info/vault/view/4332'              , 'author' => 'hlife_hotdog'  , 'author_link' => 'https://twhl.info/user/view/3749', 'conditional' => 'christmas' ],
        [ 'image' => 'santasrevenge_02.jpg' , 'type' => 'mod' , 'name' => 'Santa\'s Revenge'  , 'name_link' => 'https://twhl.info/vault/view/4332'              , 'author' => 'hlife_hotdog'  , 'author_link' => 'https://twhl.info/user/view/3749', 'conditional' => 'christmas' ],
    ];
}

?>