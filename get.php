<?php

use ModbusTcpClient\Composer\Read\ReadRegistersBuilder;
use ModbusTcpClient\Composer\Address;
use ModbusTcpClient\Network\NonBlockingClient;

require './vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


$address = $_ENV['TARGET'];

$unitID = $_ENV['SLAVE_ID']; // also known as 'slave ID'

$fc3 = ReadRegistersBuilder::newReadHoldingRegisters($address, $unitID)
    // ->bit(256, 15, 'pump2_feedbackalarm_do')
    // will be split into 2 requests as 1 request can return only range of 124 registers max
    ->uint16(0, 'counter_1') // 40001
    ->uint16(1, 'counter_2') // 40002
    // will be another request as uri is different for subsequent int16 register
    // ->useUri('tcp://127.0.0.1:5023')
    // ->string(
    //     669,
    //     10,
    //     'username_plc2',
    //     function ($value, $address, $response) {
    //         return 'prefix_' . $value; // optional: transform value after extraction
    //     },
    //     function (\Exception $exception, Address $address, $response) {
    //         // optional: callback called then extraction failed with an error
    //         return $address->getType() === Address::TYPE_STRING ? '' : null; // does not make sense but gives you an idea
    //     }
    // )
    ->build(); // returns array of 3 ReadHoldingRegistersRequest requests

// this will use PHP non-blocking stream io to recieve responses
$responseContainer = (new NonBlockingClient(['readTimeoutSec' => 0.2]))->sendRequests($fc3);
$data = $responseContainer->getData();

print_r($data); // array of assoc. arrays (keyed by address name)

$result = $data['counter_1'] + ($data['counter_2'] << 16);

$target = 21464143;


var_dump([$result, $target === $result ? "Match" : "Fail"]);

print_r($responseContainer->getErrors());
