<?php
/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 17.10.2018
 * Time: 10:46
 */

namespace db;

use PDO;

/**
 * Class Db
 * @package db
 */
class Db
{
    /**
     * @var PDO
     */
    private $pdo;

    private $_dsn;
    private $_username;
    private $_password;

    public function __construct()
    {
        $this->_dsn = getenv("DB_DSN");
        $this->_username = getenv("DB_USERNAME");
        $this->_password = getenv("DB_PASSWORD");
    }

    /**
     * Получить сообщения
     * @return array
     */
    public function getMessages()
    {
        $this->open();
        $messages_on_page = getenv("MESSAGES_ON_PAGE");
        $stmt = $this->pdo->prepare("SELECT * FROM `messages` ORDER BY id DESC LIMIT :limit");
        $stmt->execute([':limit' => $messages_on_page]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->close();
        foreach ($data as &$one) {
            $one['time'] = date(getenv("TIME_FORMAT"), $one['time']);
        }

        return $data;
    }

    /**
     * Записать сообщение
     *
     * @param $user
     * @param $message
     */
    public function writeMessage($user, $message)
    {
        $this->open();
        $query = $this->pdo->prepare("INSERT INTO messages VALUES(0,:author,:text,:time)");
        $query->execute([':author' => $user, ':text' => $message, ':time' => time()]);
        $this->close();
    }

    /**
     * Сохранение данных пользователя в БД
     *
     * @param $username
     * @param $email
     * @param $password_hash
     */
    public function saveUser($username, $email, $password_hash)
    {
        $this->open();
        $query = $this->pdo->prepare("INSERT INTO users VALUES(0,:username,:email,:password_hash)");
        $query->execute([':username' => $username, ':email' => $email, ':password_hash' => $password_hash]);
        $this->close();
    }

    /**
     * @param $username
     *
     * @return bool|mixed
     */
    public function getUserByUsername($username)
    {
        $this->open();

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username=:username");
        if ($stmt->execute([':username' => $username])) {
            $this->close();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $this->close();
        return false;
    }

    /**
     * @param $email
     *
     * @return bool|mixed
     */
    public function getUserByEmail($email)
    {
        $this->open();
        $query = $this->pdo->prepare("SELECT * FROM users WHERE email=:email");
        $result = false;
        if ($query->execute([':email' => $email])) {
            $result = $query->fetch(PDO::FETCH_ASSOC);
        }
        $this->close();

        return $result;
    }

    /**
     * @return $this
     */
    private function open()
    {
        $this->pdo = new PDO($this->_dsn, $this->_username, $this->_password);

        return $this;
    }

    /**
     * @return $this
     */
    private function close()
    {
        $this->pdo = null;

        return $this;
    }
}