<?php

require_once 'vendor/autoload.php';

try{

    $client = new \MCS\CargoUnitedClient(
        '<GebruikerID>',
        '<APIkey>'
    );

    $result = $client->createShipment([
        'Type' => 'Standaard pakket',
        'Referentie' => 'Test shipment ' . time(),
        'Naam' => '<name>',
        'Straat' => '<address>',
        'Postcode' => '<postcode>',
        'Plaats' => '<city>',
        'AantalPakketten' => 1,
        'Gewicht' => 1
    ]);

}
catch(\Exception $e){
    echo $e->getMessage();    
}
