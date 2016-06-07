<?php 
namespace MCS;

use DateTime;
use DateTimeZone;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class CargoUnitedClient{
    
    const API_BASE_URL = 'http://www.cargo-united.nl/api/';
    
    const TIME_ZONE = 'Europe/Amsterdam';
    
    const DATE_FORMAT = 'Y-m-d h:i:s';
    
    private $GebruikerID = '';
    
    private $APIkey = '';
    
    private $required_shipment_parameters = [
        'Type', 
        'Referentie', 
        'Naam', 
        'Straat', 
        'Postcode',
        'Plaats', 
        'AantalPakketten',
        'Gewicht'
    ];
    
    /**
     * @param string $GebruikerId
     * @param string $APIkey
     */
    public function __construct($GebruikerID, $APIkey)
    {
        if ((!$GebruikerID) or (!$APIkey)) {
            throw new Exception('Either `GebruikerID` or `APIkey` not set!');    
        }
        
        $this->GebruikerID = $GebruikerID;
        $this->APIkey = $APIkey;
        $this->client = new Client();
    }
    
    /**
     * Calculate the request query string
     * @param  array        
     * @return string query
     */
    private function requestParameters($array = [])
    {
        $date = new DateTime('now', new DateTimeZone(self::TIME_ZONE));
        $date = $date->format(self::DATE_FORMAT);
        
        $string_to_hash = $this->GebruikerID . $date;
        
        if (isset($array['Referentie'])) {
            $add_to_hash = ['Postcode'];
        } else {
            $add_to_hash = ['Postcode', 'Nummer', 'Straat'];    
        }
        
        foreach ($add_to_hash as $key) {
            if (isset($array[$key])) {
                $string_to_hash .= $array[$key];
            }
        }
        
        $HmacSha256 = hash_hmac(
            'sha256', 
            $string_to_hash, 
            $this->APIkey
        );
        
        return http_build_query(array_merge($array, [
            'GebruikerId' => $this->GebruikerID,
            'Datum' => $date,
            'HmacSha256' => $HmacSha256
        ]));
    }
    
    /**
     * Request to the cargo-united webservice
     * @param  string $url                         
     * @param  array $additional_query_parameters
     * @return array response
     */
    private function request($url, $additional_query_parameters = [])
    {
        
        try{
            
            $client = new Client();   
            
            $response = $client->request(
                'GET', self::API_BASE_URL . $url . '?' . $this->requestParameters($additional_query_parameters)
            );
            
            $response = $response->getBody();
            
            $body = json_decode($response, true);
            
            if (is_array($body)) {
                return $body;    
            }
            
            return (string) $response;
            
        } catch(BadResponseException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $message = $response->getBody()->getContents();
            } else {
                $message = 'An error occured';    
            }
            throw new Exception($message);
        }  
        
    }
    
    /**
     * Validate api credentials
     * @return array response
     */
    public function validateApiKey()
    {
        return $this->request('validate_apikey.php')['valid'];
    }
    
    /**
     * Get all shipment types for your account
     * @return array response
     */
    public function getShipmentTypes()
    {
        return $this->request('type.php');
    }
    
    /**
     * Get all PostNL Pakjegemak locations
     * @param string $Postcode 
     * @param string $Straat   
     * @param string $Nummer   
     * @return array response
     */
    public function getUitreikLocatie($Postcode, $Straat, $Nummer)
    {
        return $this->request('uitreiklocatie.php', [
            'Postcode' => $Postcode,
            'Nummer' => $Nummer,
            'Straat' => $Straat
        ]);
    }
    
    /**
     * Submit a shipment
     * @param array $array
     * @return array response
     */
    public function createShipment(array $array)
    {
        foreach ($this->required_shipment_parameters as $required_parameter) {
            if (!isset($array[$required_parameter])) {
                throw new Exception('Required parameter `' . $required_parameter . '` not set');    
            }
        }
        
        $response = $this->request('zending.php', $array);
        $response['LabelUrl'] = str_replace('/api/', '', self::API_BASE_URL) . $response['LabelUrl'];
        return $response;
    }
    
}