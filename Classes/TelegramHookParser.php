<?php

/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 02.04.2017
 * Time: 11:08
 */

/*
$updateId = $data->updateId;
$fromId = $data->message->from->id;
$fromFirstName = $data->message->from->first_name;
$fromLastName = $data->message->from->last_name;
$fromUserName = $data->message->from->username;
$chatId = $data->message->chat->id;
$messageDate = $data->message->date;
$messageText = $data->message->text;
 */

class TelegramHookParser extends Logger
{

    /*
     * Обработать весь массив на безопасность.
     */
    public $hookData;
    public $telegramApi;
    public $DB;
    private $chatId;
    private $messageText;


    public function initPreferences(){

        $this->chatId = $this->hookData->message->chat->id;
        $this->messageText = $this->hookData->message->text;
    }

    public function processRequest()
    {

        $result = true;
        $this->initPreferences();

        switch ($this->messageText) {
            case "/help":
                //sendMessage1("Какой-то список команд и прочая информация...");
                $this->processCommandHelp();
                break;
            case "/weather":
                $this->processCommandWeather();
                break;
            case 2:

                break;
            default:
                $this->sendResponse();
        }


        return $result;
    }

    private function processCommandRegister()
    {

        $result = true;


        return $result;
    }

    private function processCommandUnRegister()
    {

        $result = true;


        return $result;
    }

    private function processCommandHelp()
    {

        $message = "Команда1 - Описани1
        Команда2 - Описани2
        Команда3 - Описани3
        Команда4 - Описани4
        ";

        $this->sendResponse($message);
        return true;
    }

    private function processCommandWeather()
    {

        //$DB = new DB();
        $message = DB::selectWeatherCurrentDay();

        $this->sendResponse($message);
        return true;
    }

    private function processCommandBuy()
    {

        $result = true;


        return $result;
    }

    private function sendResponse($messageToUser = "Неизвестный запрос"){

        $res = $this->telegramApi->sendMessage($this->chatId, $messageToUser);
    }
}