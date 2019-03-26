--TEST--
File::rewind()
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.inc.php';

var_dump(PEAR::isError(File::rewind('test.txt', FILE_MODE_WRITE)));
var_dump(PEAR::isError(File::rewind('test.txt', FILE_MODE_READ)));
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.inc.php';
?>
--EXPECT--
bool(false)
bool(false)