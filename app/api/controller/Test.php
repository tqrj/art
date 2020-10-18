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
        $statement = $pdo->prepare('SELECT * FROM test');
        if (!$statement) {
            throw new RuntimeException('Prepare failed');
        }
        $result = $statement->execute([$a, $b]);
        if (!$result) {
            throw new RuntimeException('Execute failed');
        }
        $result = $statement->fetchAll();
        print_r($result);
        $this->pdoPool->put($pdo);
        art_assign(200,json_encode($params));

    }
}