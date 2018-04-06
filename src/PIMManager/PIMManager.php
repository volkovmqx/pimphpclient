<?php

/**
 * Basic Class for dealing with PIM requests
 * Author : Lotfi
 * Mucona Labs 2017
 */

namespace PIMManager;

class PIMManager
{

  private $client_id;
  private $secret;
  private $pim_url;
  private $pim_port;
  private $username;
  private $password;
  public function __construct($client_id,$secret,$pim_url,$pim_port,$username,$password) {
    $this->client_id = $client_id;
    $this->secret = $secret;
    $this->pim_url = $pim_url;
    $this->pim_port = $pim_port;
    $this->username = $username;
    $this->password = $password;
  }

  /**
   * Request handler for all the outgoing requests toward the PIM
   * @param  sorting $path                ressource path
   * @param  string $token                access token
   * @param  string $httpmethod           GET,POST..
   * @param  string $authorizationmethod  Basic or Bearer
   * @param  string $postfields
   * @return ResponseArray
   */
    private function sendRequest($path, $token, $httpmethod, $authorizationmethod,$postfields = NULL)
    {

      $curl = curl_init();
      $options = array(
      CURLOPT_PORT => $this->pim_port,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $httpmethod,
      CURLOPT_HTTPHEADER => array(
        "authorization: ".$authorizationmethod." ".$token,
        "cache-control: no-cache",
        "content-type: application/json"
      ));
      if(($postfields) != NULL)
        $options[CURLOPT_POSTFIELDS] = $postfields;
      if($authorizationmethod == 'Basic')
        $options[CURLOPT_URL] = $this->pim_url.'/api/oauth/v1/'.$path;
      else
        $options[CURLOPT_URL] = $this->pim_url.'/api/rest/v1/'.$path;
      curl_setopt_array($curl, $options);
        //echo('$response');
      $response = json_decode(curl_exec($curl));
      $err = curl_error($curl);

      curl_close($curl);
      if ($err)
        return false;
      else
        return $response;
    }


    /**
     * Get all the ressources
     * @param  string  $type       type of the ressource eg: product
     * @param  int  $page       number of the page for the pagination
     * @param  int  $limit      limit of the item
     * @param  boolean $with_count return count of the items
     * @param  string  $search     search terms to filter the results
     * @param  string  $locales    filter the results upon their language
     * @return RessourcesArrayOfArrays
     */
    public function getRessources($type)
    {
      return $this->sendRequest(
        $type,
        $this->generateToken(),
        'GET',
        'Bearer',
        ''
      );
    }

    /**
     * Get One ressource based on its identifier
     * @param  string  $type       type of the ressource eg: product
     * @param  string $identifier Ressource identifier
     * @return RessourceArray
     */
    public function getRessource($type,$identifier)
    {
      return $this->sendRequest(
        $type.'/'.$identifier,
        $this->generateToken(),
        'GET',
        'Bearer'
      );
    }
    /**
     * Update one Ressource
     * @param  string  $type       type of the ressource eg: product
     * @param string $identifier Ressource identifier
     * @param RessourceObject $ressource
     * @return Boolean Updating Status
     */
    public function setRessource($type, $identifier, $ressource)
    {
        return true;
    }
    /**
     * Add new ressource
     * @param  string  $type       type of the ressource eg: product
     * @param RessourceObject $ressource
     * @return Boolean Saving Status
     */
    public function addRessource($type, $ressource)
    {
        return $this->sendRequest(
          $type,
          $this->generateToken(),
          'POST',
          'Bearer',
          json_encode($ressource)
        );
    }
    /**
     * Delete Ressource
     * @param  string  $type       type of the ressource eg: product
     * @param  string $identifier Ressource identifier
     * @return Boolean             Delete Status
     */
    public function deleteRessource($type, $identifier)
    {
        return true;
    }

    /**
     * Generate new access token and update the Database
     * @return string new generated access token
     */
    private function generateToken()
    {
      $res = $this->sendRequest(
        'token',
        base64_encode($this->client_id.":".$this->secret),
        'POST',
        'Basic',
        "{\n  \"grant_type\": \"password\",\n  \"username\": \"".$this->username."\",\n  \"password\": \"".$this->password."\"\n}"
      );
      if($res != false) {
          return $res->access_token;
      }
    }
}
