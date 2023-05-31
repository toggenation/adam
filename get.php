<?php

use ModbusTcpClient\Composer\Read\ReadRegistersBuilder;
use ModbusTcpClient\Network\NonBlockingClient;

require './vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

$dotenv->load();

$address = $_ENV['TARGET'];

$unitID = $_ENV['SLAVE_ID']; // also known as 'slave ID'

$fc3 = ReadRegistersBuilder::newReadHoldingRegisters($address, $unitID)
    // ->bit(256, 15, 'pump2_feedbackalarm_do')
    // will be split into 2 requests as 1 request can return only range of 124 registers max
    ->uint16(0, 'counter_a') // 40001
    ->uint16(1, 'counter_b') // 40002
    ->uint32(0, 'counter_1') // DI-0 counter
    ->uint32(2, 'counter_2') // DI-1 counter 
    ->uint32(4, 'counter_3') // DI-2 counter
    ->uint32(6, 'counter_4') // DI-3 counter
    ->uint32(8, 'counter_5') // DI-4 counter
    ->uint32(10, 'counter_6') // DI-5 counter
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

try {
    $responseContainer = (new NonBlockingClient(['readTimeoutSec' => 0.2]))->sendRequests($fc3);
} catch (\Throwable $e) {
    echo PHP_EOL .  $e->getMessage() . str_repeat(PHP_EOL, 2);
    return;
}

$data = $responseContainer->getData();

// print_r($data); // array of assoc. arrays (keyed by address name)

// $result = $data['counter_a'] + ($data['counter_b'] << 16);

// $target = 21464143; // DI-0 counter read from the Adam .NET gui client

print_r([
    'data_from_adam_6251' => $data,
    // 'result' => $result,
    // 'target' => $target,
    // 'match' => $target === $result ? "Match" : "Fail",
    'errors' => $responseContainer->getErrors()
]);
