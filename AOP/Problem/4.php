<?php
/**
 * Created by PhpStorm.
 * User: adamkopec
 * Date: 29.01.14
 * Time: 22:39
 */
class Controller {

    public function theAction() {
        $writer = new BusinessWriter(new SecureLoggingFileWriter('logfile'));
        $writer->doYourJob();
        return $this->redirect(ELSEWHERE);
    }

}

class BusinessWriter {

    /** @var  Writer */
    protected $writer;

    public function __construct(Writer $writer) {
        $this->writer = $writer;
    }

    protected function _getFileName() {
        return '/dev/null';
    }

    public function doYourJob() {
        $results = $this->_doJobInternal();
        $this->writer->write($results, $this->_getFileName());
    }

    private function _doJobInternal() {
        return 1;
    }
}

interface Writer {
    public function write($what, $where);
}

class FileWriter implements Writer {

    public function write($what, $where) {
        file_put_contents($where, $what);
    }

}

class SecureFileWriter extends FileWriter {

    public function write($what, $where) {
        if (!isset($_SESSION['user'])) {
            throw new Exception("You bastard! You're not allowed to do this!");
        }
        parent::write($what, $where);
    }
}

class LoggingFileWriter extends FileWriter {

    private $logfile;

    public function __construct($logfile) {
        $this->logfile = $logfile;
    }

    public function write($what, $where) {
        parent::write($what, $where);
        log('OMG, he did that!', $this->logfile);
    }
}

class SecureLoggingFileWriter extends FileWriter { //hmm...
    private $logfile;

    public function __construct($logfile) {
        $this->logfile = $logfile;
    }

    public function write($what, $where) {
        if (!isset($_SESSION['user'])) {
            throw new Exception("You bastard! You're not allowed to do this!");
        }
        parent::write($what, $where);
        log('OMG, he did that!', $this->logfile);
    }
}