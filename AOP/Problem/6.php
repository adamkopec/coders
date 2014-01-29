<?php
/**
 * Created by PhpStorm.
 * User: adamkopec
 * Date: 29.01.14
 * Time: 23:14
 */
class Controller {

    public function theAction() {
        $writer = new BusinessSecureLoggingProxy(
            new BusinessWriter()
        );
        $writer->setLogfile('logfile');
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

class BusinessSecureLoggingProxy implements Business {
    /** @var  Business */
    protected $business;

    private $logfile;

    /**
     * @param mixed $logfile
     */
    public function setLogfile($logfile)
    {
        $this->logfile = $logfile;
    }

    /**
     * @return mixed
     */
    public function getLogfile()
    {
        return $this->logfile;
    }

    public function __construct(Business $business) {
        $this->business = $business;
    }

    public function doYourJob()
    {
        if (!isset($_SESSION['user'])) {
            throw new Exception("You bastard! You're not allowed to do this!");
        }
        $this->business->doYourJob();
        log('OMG, he did that!', $this->logfile);
    }
}