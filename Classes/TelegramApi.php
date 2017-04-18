<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 31.03.2017
 * Time: 22:41
 */

//namespace pa;
use PDO;

class TelegramApi
{
    public $token;
    public $DB;

    /**
     * PDO object
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * MySQL integration
     *
     * @var boolean
     */
    protected $mysql_enabled = false;

    const BASE_API_URL = 'https://api.telegram.org/bot';


    public function enableMySql(array $credential, $table_prefix = null, $encoding = 'utf8mb4')
    {
        $this->pdo = DB::initialize($credential, $this, $table_prefix, $encoding);
        //ConversationDB::initializeConversation();
        $this->mysql_enabled = true;
        return $this;
    }

    /**
     * @param String $hookUrl - адрес на нашем сервере, куда будут приходить обновления
     * @return mixed|null
     */
    public function setWebHook($hookUrl)
    {
        $this->sendPost('deleteWebhook', []);
        return $this->sendPost('setWebHook', ['url' => $hookUrl]);
    }

    /**
     * @return mixed
     */
    public function getUpdates()
    {
        $data = file_get_contents($this->buildUrl('getUpdates'));
        return json_decode($data, true);
    }

    /**
     * @param int $chatId - ID чата, в который отправляем сообщение
     * @param String $message - текст сообщения
     * @param array $params - дом.параметры (опционально)
     * @return mixed
     */
    public function sendMessage($chatId, $message, $params = [])
    {
        if (!is_array($params)) {
            $params = array();
        }

        $params['chat_id'] = $chatId;
        $params['text'] = strip_tags($message); // Telegram не понимает html-тегов

        $url = $this->buildUrl('sendMessage') . '?' . http_build_query($params);

        $data = file_get_contents($url);
        return json_decode($data, true);
    }

    /**
     * @param String $methodName - имя метода в API, который вызываем
     * @param array $data - параметры, которые передаем, необязательное поле
     * @return mixed|null
     */
    private function sendPost($methodName, $data = [])
    {
        $result = null;

        if (is_array($data)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->buildUrl($methodName));
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            $result = curl_exec($ch);
            curl_close($ch);
        }

        return $result;

    }

    /**
     * @param String $methodName - имя метода в API, который вызываем
     * @return string - Софрмированный URL для отправки запроса
     */
    private function buildUrl($methodName)
    {
        return self::BASE_API_URL . $this->token . '/' . $methodName;
    }


} // end class