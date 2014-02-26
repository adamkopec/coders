<?php
/**
 * Created by PhpStorm.
 * User: adamkopec
 * Date: 26.02.14
 * Time: 22:27
 */

require_once dirname(__FILE__) . '/../Source/Printer.php';

class PrinterTest extends PHPUnit_Framework_TestCase {

    /** @var  Printer */
    protected $_printer;

    public function setUp() {
        $this->_printer = new Printer();
    }

    public function testCanPrintEmptyString() {

        $this->assertEquals('', $this->_printer->printSomething());

    }
}
 