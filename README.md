# Cargo United Webservice
[![Latest Stable Version](https://poser.pugx.org/mcs/cargo-united/v/stable)](https://packagist.org/packages/mcs/cargo-united) [![Total Downloads](https://poser.pugx.org/mcs/cargo-united/downloads)](https://packagist.org/packages/mcs/cargo-united) [![Latest Unstable Version](https://poser.pugx.org/mcs/cargo-united/v/unstable)](https://packagist.org/packages/mcs/cargo-united) [![License](https://poser.pugx.org/mcs/cargo-united/license)](https://packagist.org/packages/mcs/cargo-united)

Installation:
```bash
$ composer require mcs/cargo-united
```

Features:
 * Submit a shipment to the Cargo United webservice and retrieve it's label and tracking information

Basic shipment usage:

```php
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

```