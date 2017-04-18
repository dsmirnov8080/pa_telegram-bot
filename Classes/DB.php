<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 17.04.2017
 * Time: 12:28
 */

//namespace pa;
use PDO;


class DB extends Logger
{

    /**
     * MySQL credentials
     *
     * @var array
     */
    static protected $mysql_credentials = [];
    /**
     * PDO object
     *
     * @var PDO
     */
    static protected $pdo;
    /**
     * Table prefix
     *
     * @var string
     */
    static protected $table_prefix;

    static protected $telegram;


    /**
     * Initialize
     *
     * @param array                         $credentials  Database connection details
     * @param \TelegramApi $telegram     Telegram object to connect with this object
     * @param string                        $table_prefix Table prefix
     * @param string                        $encoding     Database character encoding
     *
     * @return PDO PDO database object
     //* @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public static function initialize(
        array $credentials,
        TelegramApi $telegram,
        $table_prefix = null,
        $encoding = 'utf8mb4'
    ) {
        if (empty($credentials)) {
            //throw new TelegramException('MySQL credentials not provided!');
            echo('MySQL credentials not provided!');
            exit;
        }

        $dsn     = 'mysql:host=' . $credentials['host'] . ';dbname=' . $credentials['database'];
        $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $encoding];
        try {
            $pdo = new PDO($dsn, $credentials['user'], $credentials['password'], $options);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        //} catch (PDOException $e) {
        } catch (\mysqli_sql_exception $e) {
            //throw new TelegramException($e->getMessage());
            echo($e);
            exit;
        }
        self::$pdo               = $pdo;
        self::$telegram          = $telegram;
        self::$mysql_credentials = $credentials;
        self::$table_prefix      = $table_prefix;
        self::defineTables();
        return self::$pdo;
    }

    /**
     * Fetch update(s) from DB
     *
     * @param int $limit Limit the number of updates to fetch
     *
     * @return array|bool Fetched data or false if not connected
     //* @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public static function selectWeatherCurrentDay()
    {
        if (!self::isDbConnected()) {
            Logger::getLogger("messages_result")->log("DB not connected!");
            return false;
        }
        try {
            $sql = '
                SELECT *
                FROM `weather`
                ORDER BY `id` DESC
                LIMIT 1
            ';

            $sth = self::$pdo->prepare($sql);
            //$sth->bindParam(':limit', $limit, PDO::PARAM_INT);
            $sth->execute();
            Logger::getLogger("messages_result")->log("weather query executed");

            $res =$sth->fetchAll(PDO::FETCH_ASSOC);

            /*
             * todo
             * Вынести в отдельную функцию
             */
            $message = "Погода на: " .date("d.m.Y H:i", $res[0]["weather_date"]) ."
            " . "ночь t: " .$res[0]["temp_night"] . " ". "день t: " .$res[0]["temp_day"] ."
            " . "влажность %: " .$res[0]["humidity"] ."
            " . $res[0]["description"];

            return $message;
        } catch (\mysqli_sql_exception $e) {
            echo($e);
            Logger::getLogger("messages_result")->log($e);
            exit;
        }
    }

    /**
     * Define all the tables with the proper prefix
     */
    protected static function defineTables()
    {
        $tables = [
            'chats',
            'messages',
            'accounts',
            'weather',
        ];
        foreach ($tables as $table) {
            $table_name = 'PA_' . strtoupper($table);
            if (!defined($table_name)) {
                define($table_name, self::$table_prefix . $table);
            }
        }
    }

    /**
     * Check if database connection has been created
     *
     * @return bool
     */
    public static function isDbConnected()
    {
        return self::$pdo !== null;
    }

    /**
     * Get the PDO object of the connected database
     *
     * @return \PDO
     */
    public static function getPdo()
    {
        return self::$pdo;
    }

}