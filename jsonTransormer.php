<?php
use NilPortugues\Api\Mapping\Mapping;
use NilPortugues\Api\Transformer\Json\JsonApiTransformer;
use NilPortugues\Api\Transformer\Json\JsonHalTransformer;
use NilPortugues\Api\Transformer\Json\JsonTransformer;
use NilPortugues\Serializer\Serializer;

include 'vendor/autoload.php';


$array = [];
for ($i = 1; $i <= 5; $i++) {
    $array[] = new DateTime("now +$i days");
}


$dateTimeMapping = new Mapping('DateTime', 'http://example.com/date-time/{timezone_type}', ['timezone_type']);
$dateTimeMapping->setHiddenProperties(['timezone_type']);
$dateTimeMapping->setPropertyNameAliases(['date' => 'fecha']);

$apiMappingCollection = [
    $dateTimeMapping->getClassName() => $dateTimeMapping
];


header('Content-Type: application/vnd.api+json; charset=utf-8');


echo PHP_EOL;
echo PHP_EOL;

echo '-------------------------------------------------------------';
echo 'JSON Format';
echo '-------------------------------------------------------------';
echo PHP_EOL;
echo PHP_EOL;
echo (new Serializer(new JsonTransformer()))->serialize($array);
echo PHP_EOL;
echo PHP_EOL;
echo '-------------------------------------------------------------';
echo 'JSON API Format';
echo '-------------------------------------------------------------';
echo PHP_EOL;
echo PHP_EOL;
$serializer = new JsonApiTransformer($apiMappingCollection);
$serializer->setApiVersion('1.0.1');
$serializer->setSelfUrl('http://example.com/date_time/');
$serializer->setNextUrl('http://example.com/date_time/?page=2&amount=20');
$serializer->addMeta(
    'author',
    [
        ['name' => 'Nil Portugués Calderó', 'email' => 'contact@nilportugues.com']
    ]
);

echo (new Serializer($serializer))->serialize($array);
echo PHP_EOL;
echo PHP_EOL;
echo '-------------------------------------------------------------';
echo 'JSON+HAL API Format';
echo '-------------------------------------------------------------';
echo PHP_EOL;
echo PHP_EOL;

$serializer = new JsonHalTransformer($apiMappingCollection);
$serializer->setSelfUrl('http://example.com/date_time/');
$serializer->setNextUrl('http://example.com/date_time/?page=2&amount=20');

echo (new Serializer($serializer))->serialize($array);