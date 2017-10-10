<?php

use Phalcon\Paginator\Adapter\Model as Paginator;

class IndexController extends ControllerBase
{

    /**
     * Вывод вопросов и ответов
     */
    public function indexAction()
    {
        $questions = Questions::find();

        $paginator = new Paginator([
            'data' => $questions,
            'limit' => 100,
            'page' => 1
        ]);

        $this->view->page = $paginator->getPaginate();
    }

}

