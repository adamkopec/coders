<?php
/**
 * Created by PhpStorm.
 * User: adamkopec
 * Date: 29.01.14
 * Time: 23:24
 */
class Controller {

    public function theAction() {
        $writer = new BusinessWriter();
        $writer->doYourJob();
        return $this->redirect(ELSEWHERE);
    }

}

class BusinessWriter {

    use Logging, Security;

    public function __construct() {
        $this->setLogfile('logfile');
    }

    protected function _getFileName() {
        return '/dev/null';
    }

    public function doYourJob() {
        $this->_log(function() {
            $this->_securely(function() {
                $results = $this->_doJobInternal();
                file_put_contents($this->_getFileName(), $results);
            });
        });
    }

    private function _doJobInternal() {
        return 1;
    }
}

trait Logging {

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

    function _log(Closure $what) {
        $what();
        log('OMG, he did that!', $this->logfile);
    }
}

trait Security {
    function _securely(Closure $what) {
        if (!isset($_SESSION['user'])) {
            throw new Exception("You bastard! You're not allowed to do this!");
        }
        $what();
    }
}