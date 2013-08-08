<?php
class CodedException extends Exception {
    const KNOWN_ERROR = 0;
    const UNKNOWN_ERROR = 1;
    const SOMETHING_BAD = 2;
    const SOMETHING_EVEN_WORSE = 3;
}

function doNothing() {
    try {
        doNothingButThrowAnException();
    } catch(CodedException $e) {
        echo translate('error' . $e->getCode());
    }
}

//ale:

function react() {
    try {
        doNothingButThrowAnException();
    } catch (CodedException $e) {
        switch ($e->getCode()) {
            case CodedException::KNOWN_ERROR:
            case CodedException::UNKNOWN_ERROR:
                //these codes are not mine!
                throw $e;
                break;
            case CodedException::SOMETHING_BAD:
                //not that bad anyway
                break;
            case CodedException::SOMETHING_EVEN_WORSE:
                throw new UnexpectedException("Nobody expects the Spanish Inquisition!");
                break;
            default:
                throw new Exception("Incorrect exception"); //never happens ofc :p
        }
    }
}

//z innej beczki:

class MyAppException extends Exception {}
class MyProcessException extends MyAppException {}
class FatalProcessException extends MyProcessException {}


function reactOnClass() {
   try {
       throwItUp();
   } catch(FatalProcessException $e) {
       die(); //from shame
   } catch(MyProcessException $e) {
       log('blame John, he implemented it');
   } catch(MyAppException $e) {
       echo "We are very sorry but an error occurred during an operation conducted by third party classes. Your system will reboot, but it's not our fault.";
   } catch(Exception $e) {
       log('oh my goodness, php is soooo lame!');
   }
}