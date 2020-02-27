<?php

require('../vendor/autoload.php');
$faker = Faker\Factory::create();

list($fileName, $tableName, $numOfRecord) = $argv;

$hostname = getenv('DB_HOST') || 'localhost';
$username = getenv('DB_USER') || 'root';
$password = getenv('DB_PASS');
$database = getenv('DB_NAME');
$port = getenv('DB_PORT');


try {
    $pdo = new PDO("pgsql:host='localhost';port=5433;dbname='database_name';user='postgres';password='Pass123!'");

} catch (Exception $exception) {
    echo "-- DATABASE CONNECTION ERROR --";
    print_r($exception->getMessage());
}

$result = $pdo->prepare('SELECT column_name, data_type, column_default FROM information_schema.COLUMNS WHERE TABLE_NAME = :tableName');
$result->bindParam(':tableName', $tableName);
$result->execute();

$schemas = $result->fetchAll(PDO::FETCH_ASSOC);

$columnData = [];
$columnNames = '(';

$pre_value = " VALUES(";

$columnValues = [];

foreach ($schemas as $schema) {
    $columnInfo['name'] = $schema['column_name'];
    $columnInfo['type'] = explode(' ', $schema['data_type'])[0];
    $columnInfo['autogen'] = strpos($schema['column_default'], 'nextval') === 0;
    if (!$columnInfo['autogen']) {
        if ($columnInfo['type'] != 'timestamp') {
            $columnNames .= $columnInfo['name'].','  ;
            $pre_value   .= '?, ';

            $columnData[] = $columnInfo;
        }
    }

}

$columnNames = rtrim($columnNames,",");
$pre_value = rtrim($pre_value," ,");


$columnNames .= ')';
$pre_value   .= ')';


for($i = 0; $i < $numOfRecord; $i++) {
    $values = [];

    foreach ($columnData as $key=>$data) {
        if ($data['type'] == 'integer') {
            $values[$data['name']] = mt_rand(0, (int) '2147483647');
        } elseif ($data['type'] == 'character') {
            $values[$data['name']] = $faker->sentence(2);
        } elseif ($data['type'] == 'numeric') {
            $values[$data['name']] = $faker->randomNumber(5);
        } elseif ($data['type'] == 'smallint') {
            $values[$data['name']] = mt_rand(0, 65535);
        } elseif ($data['type'] == 'float') {
            $values[$data['name']] = mt_rand(0, (int)'4294967295') / mt_rand(1, (int)'4294967295');
        } elseif ($data['type'] == 'bigint') {
            $values[$data['name']] = mt_rand(0, (int) '18446744073709551615');
        } elseif ($data['type'] == 'decimal') {
            $values[$data['name']] = mt_rand (2*5, 5*5) / 10;
        } elseif ($data['type'] == 'string') {
            $values[$data['name']] = $faker->sentence(3);
        } elseif ($data['type'] == 'text') {
            $values[$data['name']] = $faker->paragraph(5);
        } elseif ($data['type'] == 'date' || $data['datetime'] || $data['datetimetz']) {
            $values[$data['name']] = date('Y-m-d', strtotime( '+'.mt_rand(0,30).' days'));
        }  elseif ($data['type'] == 'date' || $data['datetime'] || $data['datetimetz']) {
            $values[$data['name']] = date('Y-m-d', strtotime( '+'.mt_rand(0,30).' days'));
        }  elseif ($data['type'] == 'boolean') {
            $values[$data['name']] = rand(0,1) == 1 ? 1 : 0;
        }
    }
//    print_r($values);exit();

    $params = implode(', ',array_values($values));

    $new_data = array_values($values) ;

    try {

        $sql = "INSERT into $tableName $columnNames $pre_value ";
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        $result = $pdo->prepare($sql);
        $result->execute($new_data);

     //   print_r($result->errorInfo());

    } catch (PDOException $exception) {
        echo('--- INSERTING ERROR ---');

        print_r($exception->getMessage());
    }
}

