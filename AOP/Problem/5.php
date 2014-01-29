<?php
/**
 * Created by PhpStorm.
 * User: adamkopec
 * Date: 29.01.14
 * Time: 22:42
 */
class Controller {

    public function theAction() {
        $writer = new BusinessLoggingDecorator(
                    new BusinessSecurityDecorator(
                        new BusinessWriter()
                    ), 'logfile');
        $writer->doYourJob();
        return $this->redirect(ELSEWHERE);
    }

}

interface Business {
    public function doYourJob();
}

class BusinessWriter implements Business{

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

abstract class Decorator implements Business {

    protected $business;

    public function __construct(Business $business) {
        $this->business = $business;
    }
}


class BusinessSecurityDecorator extends Decorator {

    public function doYourJob() {
        if (!isset($_SESSION['user'])) {
            throw new Exception("You bastard! You're not allowed to do this!");
        }
        return $this->business->doYourJob();
    }
}

class BusinessLoggingDecorator extends Decorator {

    private $logfile;

    public function __construct(Business $business, $logfile) {
        $this->business = $business;
        $this->logfile = $logfile;
    }

    public function doYourJob() {
        $result = $this->business->doYourJob();
        log('OMG, he did that!', $this->logfile);
        return $result;
    }
}