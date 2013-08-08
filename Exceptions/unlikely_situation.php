<?php
class YeDataFetchingException extends Exception {}

class YeController {
    function yeImportantAction() {
        try {
            $this->_doThisRidiculouslyDangerousOperation();
        }
        catch(YeDataFetchingException $e) {
            echo "So do not fear, exception caught. Confess your sins: ";
            echo $e;
        }
        catch(Exception $e) {
            echo "Beg for mercy, The Management is coming...";
        }
    }

    /**
     * @throws YeDataFetchingException
     */
    protected function _doThisRidiculouslyDangerousOperation()
    {
        $data = $this->model->fetchData();
        $this->manipulateData($data);
    }
}

class YeModel {
    function fetchData() {
        if (rand(0,10) == 7) {
            throw new YeDataFetchingException("Nobody expects the Spanish Inquisition!");
        } else {
            return $this->data;
        }
    }
}