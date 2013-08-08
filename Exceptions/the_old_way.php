<?php
// kody odpowiedzi

define('TRUE', 0);
define('FALSE', 1);
define('PROBABLY', 0.5);

class ImportantThing {
    public function useIt($parameter) {
        if (wednesday) {
            $this->result = $this->_reallyUseThisGoddamnThing();
            return TRUE;
        } else if (thursday) {
            return FALSE;
        } else {
            if (rand(0,100) < 50) {
                $this->result = $this->_reallyUseThisGoddamnThing();
            }
            return PROBABLY;
        }
    }

    public function report() {
        return $this->result;
    }
}

class Client {
    public function call() {
        $thing = new ImportantThing();
        $thing->useIt(7);
        $thing->report();
    }

    public function saferCall() {
        $thing = new ImportantThing();
        if ($thing->useIt(7)) {
            //?
            $thing->report();
        }
    }
}

//zmienne stanu

class EvenMoreImportantThing {
    protected $error;

    public function useIt() {
        if (wednesday) {
            $this->result = $this->_reallyUseThisGoddamnThing();
            $this->error = null;
        } else if (thursday) {
            $this->error = 73;
        } else {
            $logicalExpression = rand(0, 100) < 50;
            if ($logicalExpression) {
                $this->result = $this->_reallyUseThisGoddamnThing();
            }
            $this->error = 5 * ($logicalExpression - 1);
        }
    }

    public function report() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }
}

class AngryClient {
    public function call() {
        $thing = new EvenMoreImportantThing();
        $thing->useIt(7);
        $thing->report();
    }

    public function saferCall() {
        $thing = new EvenMoreImportantThing();
        $thing->useIt(7);
        if (!$thing->getError())
        {
            //?
            $thing->report();
        }
    }
}

//globalne lokalizacje

class CriticalThing {
    public function useIt() {
        if (wednesday) {
            $this->result = $this->_reallyUseThisGoddamnThing();
        } else if (thursday) {
            report_critical_error(73);
        } else {
            $logicalExpression = rand(0, 100) < 50;
            if ($logicalExpression) {
                $this->result = $this->_reallyUseThisGoddamnThing();
            } else {
                report_critical_error(5);
            }
        }
    }

    public function report() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }
}

class ClientWhoIsLikelyToGoToCourt {
    public function call() {
        $thing = new CriticalThing();
        $thing->useIt(7);
        $thing->report();
    }

    public function saferCall() {
        $thing = new CriticalThing();
        $thing->useIt(7);
        if (!get_last_critical_error())
        {
            //?
            $thing->report();
        }
    }
}
