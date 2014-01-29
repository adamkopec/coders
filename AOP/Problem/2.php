<?php
/**
 * Created by PhpStorm.
 * User: adamkopec
 * Date: 29.01.14
 * Time: 22:18
 */

class Controller {

    public function theAction() {
        $writer = new BusinessWriter(new FileWriter());
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