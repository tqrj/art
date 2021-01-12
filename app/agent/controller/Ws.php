<?php


namespace app\agent\controller;


use art\context\Context;
use art\ws\ArtWs;

class Ws
{
    private $isWs = true;

    public function joinGroup()
    {
        $ws = Context::get('response');
        $authInfo = Context::get('authInfo');
        ArtWs::bindUid($ws->artWsId,'agent'.$authInfo['id']);
        ArtWs::joinGroup($ws->artWsId, $authInfo['id']);
    }
}