<?php
class DataCopier {
    public function __construct(PDO $database1, PDO $database2) { }
}
$dice = new Dice();
//A rule for the default PDO object
$rule = new DiceRule;
$rule->shared = true;
$rule->constructParams = array('mysql:host=127.0.0.1;dbname=mydb', 'username', 'password');
$dice->addRule('PDO', $rule);

//And a rule for the second database
$secondDBRule = new DiceRule;
$secondDBRule->shared = true;
$secondDBRule->constructParams = array('mysql:host=example.com;dbname=externaldatabase', 'foo', 'bar');

//Set it to an instance of PDO
$secondDBRule->instanceOf = 'PDO';

//Add named instance called $Database2
$dice->addRule('$Database2', $secondDBRule);
//Now set DataCopier to use the two different databases:
$dataCopierRule = new DiceRule;
//Set the constructor parameters to the two database instances.
$dataCopierRule->constructParams = array(new DiceInstance('PDO'), new DiceInstance('$Database2'));
$dice->addRule('DataCopier', $dataCopierRule);
$dataCopier = $dice->create('DataCopier');

