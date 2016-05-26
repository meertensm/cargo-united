<?php namespace MCS;

use DateTime;
use DateTimeZone;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class CargoUnitedClient{
    
    const API_BASE_URL = 'http://www.cargo-united.nl/api/';
    
    const TIME_ZONE = 'Europe/Amsterdam';
    
    private $GebruikerID = '';
    
    private $APIkey = '';
    
    public function __construct($GebruikerID, $APIkey)
    {
        if((!$GebruikerID) or (!$APIkey)){
            throw new Exception('Either `GebruikerID` or `APIkey` not set!');    
        }
        
        $this->GebruikerID = $GebruikerID;
        $this->APIkey = $APIkey;
        $this->client = new Client();
        
    }
    
    private function requestParameters($array = [])
    {
        $date = new DateTime('now', new DateTimeZone(self::TIME_ZONE));
        $date = $date->format('Y-m-d h:i:s');
        
        return http_build_query(array_merge($array, [
            'GebruikerId' => $this->GebruikerID,
            'Datum' => $date,
            'HmacSha256' => hash_hmac(
                'sha256', 
                $this->GebruikerID . $date, 
                $this->APIkey
            )
        ]));
    }
    
    private function request($url, $additional_parameters = [], $body = [])
    {
        $method = count($body) ? 'POST' : 'GET';
        
        try{
            
            $client = new Client();   
            
            $response = $client->request(
                $method, self::API_BASE_URL . $url . '?' . $this->requestParameters($additional_parameters), $body
            );
            
            $response = $response->getBody();
            
            $body = json_decode($response, true);
            
            if(is_array($body)){
                return $body;    
            }
            
            return (string) $response;
            
        }
        catch(BadResponseException $e){
            if ($e->hasResponse()){
                $message = $e->getResponse();
                $message = $message->getStatusCode() . ' - ' . $message->getReasonPhrase();
            }
            else{
                $message = 'An error occured';    
            }
            throw new Exception($message);
        }  
        
    }
    
    public function validateApiKey()
    {
        return $this->request('validate_apikey.php')['valid'];
    }
    
    public function getShipmentTypes()
    {
        return $this->request('type.php');
    }
    
    
    
    
}