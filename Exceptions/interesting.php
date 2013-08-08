<?php
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");

/* Trigger exception */
strpos();

/**
Fatal error: Uncaught exception 'ErrorException' with message 'Wrong parameter count for strpos()' in /path/interesting.php:8
Stack trace:
#0 [internal function]: exception_error_handler(2, 'Wrong parameter...', '/path...', 8, Array)
#1 /path/interesting.php(8): strpos()
#2 {main}
thrown in /path/interesting.php on line 8
*/























//finally
function addRecord($record, $db) {
    try {
        $db->lock($record->table);
        $db->prepare($record->sql);
        $db->exec($record->params);
    }
    catch(Exception $e) {
        $db->rollback($record);
        if (!write_log($e->getMessage(), $e->getTraceAsString())) {
            throw new Exception('Unable to write to error log.');
        }
    }
    finally {
        $db->unlock($record->table);
    }
    return true;
}


//a tutaj co?
function test($var = false) {
    try {
        if (!$var) throw new Exception;
        return 1;
    }
    catch(Exception $e) {
        return 2;
    }
    finally {
        return 3;
    }
}


