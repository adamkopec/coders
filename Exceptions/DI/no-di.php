<?php
class SessionStorage {
    public function __construct($cookieName = 'PHP_SESS_ID') {
        session_name($cookieName);
        session_start();
    }

    public function get($key) {
        return $_SESSION[$key];
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
}

class User {
    protected $storage;

    public function __construct(Storage $sessionStorage) {
        $this->storage = $sessionStorage;
    }

    public function setStorage(Storage $storage) {
        $this->storage = $storage;
    }

    public function setLanguage($language) {
        $this->storage->set('language', $language);
    }

    public function getLanguage() {
        return $this->storage->get('language');
    }
}

$user = new User();

$storage = new Storage('MOJ_KLUCZ');
$user = new User($storage);