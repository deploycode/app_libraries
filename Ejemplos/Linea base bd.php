<?php
use Doctrine\Common\ClassLoader;

require $_SERVER['DOCUMENT_ROOT'].'\app_libraries\includes\doctrine\Doctrine\Common\ClassLoader.php';

$classLoader = new ClassLoader('Doctrine', $_SERVER['DOCUMENT_ROOT'].'\app_libraries\includes\doctrine');
$classLoader->register();
//
$config = new \Doctrine\DBAL\Configuration();
//..
$connectionParams = array(
    'dbname' => 'famisana',
    'user' => 'operador',
    'password' => 'op3r4d0r',
    'host' => '192.168.88.203',
    'port' => '1522',
    'driver' => 'oci8',
);

$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$sql = 'select table_name from user_tables';
$stmt = $conn->query($sql);

    echo '<pre>';
while ($row = $stmt->fetch()) 
{
    print_r($row);
    //echo $row['tpr_nombre'];
}
    echo '</pre>';
    
    echo '------------------------------------------------------------------------------';
$connectionParams = array(
    'dbname' => 'db_general',
    'user' => 'root',
    'password' => '',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
);

$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$sql = 'select * from tarea_programada';
$stmt = $conn->query($sql);

    echo '<pre>';
while ($row = $stmt->fetch()) 
{
    print_r($row);
    //echo $row['tpr_nombre'];
}
    echo '</pre>';
?>