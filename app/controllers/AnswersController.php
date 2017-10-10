<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class AnswersController extends ControllerBase
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;
    }

    /**
     * Searches for answers
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Answers', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";

        $answers = Answers::find($parameters);
        if (count($answers) == 0) {
            $this->flash->notice("The search did not find any answers");

            $this->dispatcher->forward([
                "controller" => "answers",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $answers,
            'limit' => 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

    /**
     * Displays the creation form
     */
    public function newAction()
    {

    }

    /**
     * Edits a answer
     *
     * @param string $id
     */
    public function editAction($id)
    {
        if (!$this->request->isPost()) {

            $answer = Answers::findFirstByid($id);
            if (!$answer) {
                $this->flash->error("answer was not found");

                $this->dispatcher->forward([
                    'controller' => "answers",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $answer->id;

            $this->tag->setDefault("id", $answer->id);
            $this->tag->setDefault("name", $answer->name);
            $this->tag->setDefault("quest_id", $answer->quest_id);
            $this->tag->setDefault("good", $answer->good);

        }
    }

    /**
     * Creates a new answer
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "answers",
                'action' => 'index'
            ]);

            return;
        }

        $answer = new Answers();
        $answer->name = $this->request->getPost("name");
        $answer->questId = $this->request->getPost("quest_id");
        $answer->good = $this->request->getPost("good");

        //Только один правильный ответ может быть
        if ($answer->good) {
            $answers_good = Answers::find("quest_id = $answer->questId AND good = 1");

            if ($answers_good->count()) {
                $answers_good->rewind();
                $answer_good = $answers_good->current();
                $this->flash->error("Правильный ответ уже дан = " . $answer_good->name);
                $this->dispatcher->forward([
                    'controller' => "answers",
                    'action' => 'index'
                ]);

                return;

            }
        }


        if (!$answer->save()) {
            foreach ($answer->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "answers",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("answer was created successfully");

        $this->dispatcher->forward([
            'controller' => "answers",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a answer edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "answers",
                'action' => 'index'
            ]);

            return;
        }

        $id = $this->request->getPost("id");
        $answer = Answers::findFirstByid($id);

        if (!$answer) {
            $this->flash->error("answer does not exist " . $id);

            $this->dispatcher->forward([
                'controller' => "answers",
                'action' => 'index'
            ]);

            return;
        }

        $answer->name = $this->request->getPost("name");
        $answer->questId = $this->request->getPost("quest_id");
        $answer->good = $this->request->getPost("good");
        //Только один правильный ответ может быть
        if ($answer->good) {
            $answers_good = Answers::find("quest_id = $answer->questId AND good = 1");

            if ($answers_good->count()) {
                $answers_good->rewind();
                $answer_good = $answers_good->current();
                if ($id != $answer_good->id) {
                    $this->flash->error("Правильный ответ уже дан = " . $answer_good->name);

                    $this->dispatcher->forward([
                        'controller' => "answers",
                        'action' => 'index'
                    ]);

                    return;
                }
            }
        }


        if (!$answer->save()) {

            foreach ($answer->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "answers",
                'action' => 'edit',
                'params' => [$answer->id]
            ]);

            return;
        }

        $this->flash->success("answer was updated successfully");

        $this->dispatcher->forward([
            'controller' => "answers",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a answer
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $answer = Answers::findFirstByid($id);
        if (!$answer) {
            $this->flash->error("answer was not found");

            $this->dispatcher->forward([
                'controller' => "answers",
                'action' => 'index'
            ]);

            return;
        }

        if (!$answer->delete()) {

            foreach ($answer->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "answers",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("answer was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "answers",
            'action' => "index"
        ]);
    }

}
