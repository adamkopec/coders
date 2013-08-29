<?php

class C { }
class B { function __construct(C $c) {} }
class A { function __construct(B $b) {} }

$a = Framework::create('A');
//($a->b instanceof B) === true
//($a->b->c instanceof C) === true


