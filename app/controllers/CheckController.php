<?php


class CheckController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $result_answer = [];
        $verification = [];
        $user_arr = [];
        $result = [];

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "index",
                'action' => 'index'
            ]);

            return;
        }

        //Получаем все пользовательские ответы из POST
        //Валидацию не делал
        $answers_users = $this->request->getPost("answers");
        if (!$answers_users) {
            $this->dispatcher->forward([
                'controller' => "index",
                'action' => 'index'
            ]);

            return;
        }

        //Получаем все вопросы с правильными ответами из базы
        //Здесь надо бы прибиндить условие, но почему -то не отработало
        //Обработаю в цикле и создам массив проверки $verification
        $questions = Questions::find();

        foreach ($questions as $question) {
            foreach ($question->answers as $answer) {
                if ($answer->good == 1) {
                    $verification[$question->id] = $answer->id;
                    $result_answer[$answer->id] = $answer->name;
                }
            }
        }

        //Основная обработка
        foreach ($answers_users as $answer) {
            $user_arr = explode("-", $answer);
            //Если у нас уже существует ответ на данный вопрос, то ошибка
            if (isset($result[$user_arr[0]])) {
                $result[$user_arr[0]] = "Дано больше 1 ответа";
                continue;
            }
            //Формируем пары пользовательских значений для проверки
            $result[$user_arr[0]] = $user_arr[1];
        }

        //Проверка на правильность
        foreach ($verification as $key => $val) {
            //Пользователь не дал ответ
            if (!isset($result[$key])) {
                $result[$key] = "На данный вопрос не дан ответ. Верный ответ - " . $result_answer[$val];
                continue;
            }

            if (isset($result[$key]) && $result[$key] == $val) {
                $result[$key] = "Ответ верный!";
                continue;
            }

            //Дано больше 1 ответа
            if (isset($result[$key]) && $result[$key] == "Дано больше 1 ответа") {
                $result[$key] = "Дано больше 1 ответа. Верный ответ - " . $result_answer[$val];
                continue;
            }

            //Ответ неверный
            if (isset($result[$key]) && $result[$key] != $val) {
                $result[$key] = "Ответ неверный. Верный ответ - " . $result_answer[$val];
                continue;
            }
        }


        $questions->rewind();

        $this->view->questions = $questions;
        $this->view->result = $result;
    }

}

