<?php
interface Storage {
    function get($key);9
    function set($key, $value);
}

class OtherStorage implements Storage {
    //...
}

class StorageImpl implements Storage {
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

class ServiceLocator {

    protected $storage;

    protected static $instance;

    private function __construct(Storage $storage) {
        $this->storage = $storage;
    }

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getSessionStorage() {
        return $this->storage;
    }

    public function setSessionStorage(Storage $storage) {
        $this->storage = $storage;
    }
}

class User {
    protected $storage;

    public function __construct() {
        $this->storage = ServiceLocator::getInstance()->getSessionStorage();
    }

    public function setLanguage($language) {
        $this->storage->set('language', $language);
    }

    public function getLanguage() {
        return $this->storage->get('language');
    }
}