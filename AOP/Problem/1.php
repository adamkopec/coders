<?php
/**
 * Created by PhpStorm.
 * User: adamkopec
 * Date: 29.01.14
 * Time: 22:32
 */
class Controller {

    public function theAction() {
        $writer = new BusinessWriter();
        $writer->doYourJob();
        return $this->redirect(ELSEWHERE);
    }

}

class BusinessWriter {

    protected function _getFileName() {
        return '/dev/null';
    }

    public function doYourJob() {
        $results = $this->_doJobInternal();
        file_put_contents($this->_getFileName(), $results);
    }

    private function _doJobInternal() {
        return 1;
    }
}