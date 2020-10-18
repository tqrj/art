<?php


namespace app\api\controller;


use app\BaseController;
use art\request\Request;
use http\Exception\RuntimeException;

class Test extends BaseController
{
    public function hello()
    {
        $params = Request::only(['pp','cc']);
        art_validate($params,[
            'cc'=>'require|mobile'
        ]);
        $pdo = $this->pdoPool->get();
        $statement = $pdo->prepare('SELECT ? + ?');
        if (!$statement) {
            throw new RuntimeException('Prepare failed');
        }
        $a = mt_rand(1, 100);
        $b = mt_rand(1, 100);
        $result = $statement->execute([$a, $b]);
        if (!$result) {
            throw new RuntimeException('Execute failed');
        }
        $result = $statement->fetchAll();
        if ($a + $b !== (int)$result[0][0]) {
            throw new RuntimeException('Bad result');
        }
        $this->pdoPool->put($pdo);
        art_assign(200,json_encode($params));

    }
}