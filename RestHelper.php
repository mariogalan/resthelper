<?php
/**
 * User: Mario Galan
 * Date: 10/29/13
 * Time: 5:48 PM
 */

class RestHelper {
  /**
   * Default response format. Usually will be XML or JSON
   */
  const DEFAULT_RESPONSE_FORMAT = 'XML';

  /**
   *
   */
  const HTTP_METHOD_GET = 'GET';
  /**
   *
   */
  const HTTP_METHOD_POST = 'POST';
  /**
   *
   */
  const HTTP_METHOD_PUT = 'PUT';
  /**
   *
   */
  const HTTP_METHOD_DELETE = 'DELETE';

  /**
   *
   */
  const HTTP_METHOD_PATCH = 'PATCH';

  /**
   * Port to use on the call
   */
  const DEFAULT_PORT = 80;

  /**
   * Set to TRUE to make curl output all the debug info
   */
  const DEBUG_MODE = FALSE;

  /**
   * @var string
   */
  private $response_format;
  /**
   * @var
   */
  private $url;
  /**
   * @var
   */
  private $endpoint;
  /**
   * @var int
   */
  private $port;

  /**
   * @param $url
   * @param $endpoint
   * @param string $response_format
   * @param int $port
   */
  public function __construct($url, $endpoint, $response_format = self::DEFAULT_RESPONSE_FORMAT, $port = self::DEFAULT_PORT) {
    $this->url = $url;
    $this->endpoint = $endpoint;
    $this->response_format = $response_format;
    $this->port = $port;
  }

  /**
   * Converts a raw response to an array of strings.
   *
   * @param $raw_response
   *
   * @internal param string $desired_format
   *
   * @return object
   */
  private function transformResponse($raw_response) {
    $output_response = '';

    switch ($this->response_format) {
      case 'XML':
        // This hack is needed for converting the format returned by simplexml_load_string to an object with
        // properties
        $output_response = json_decode(json_encode(simplexml_load_string($raw_response)));
        break;
      case 'JSON':
        $output_response = json_decode($raw_response);
        break;
    }

    return $output_response;
  }

  /**
   * Make a GET call.
   *
   * @param array $resource
   * @param array $url_parameters
   *
   * @return mixed|SimpleXMLElement|string
   */
  public function get(array $resource, array $url_parameters = array()) {
    return $this->makeCall($resource, self::HTTP_METHOD_GET, $url_parameters);
  }

  /**
   * Make a POST call.
   *
   * @param array $resource
   * @param array $url_parameters
   * @param array $post_data
   *
   * @return mixed|SimpleXMLElement|string
   */
  public function post(array $resource, array $url_parameters = array(), array $post_data = array()) {
    return $this->makeCall($resource, self::HTTP_METHOD_POST, $url_parameters, $post_data);
  }

  /**
   * Make a PUT call.
   * @param array $resource
   * @param array $url_parameters
   * @param array $post_data
   *
   * @return mixed|SimpleXMLElement|string
   */
  public function put(array $resource, array $url_parameters = array(), array $post_data = array()) {
    return $this->makeCall($resource, self::HTTP_METHOD_PUT, $url_parameters, $post_data);
  }

  /**
   * Make a DELETE call.
   *
   * @param array $resource
   * @param array $url_parameters
   * @param array $post_data
   *
   * @return mixed|SimpleXMLElement|string
   */
  public function delete(array $resource, array $url_parameters = array(), array $post_data = array()) {
    return $this->makeCall($resource, self::HTTP_METHOD_DELETE, $url_parameters, $post_data);
  }

  /**
   * Make a PATCH call.
   *
   * @param array $resource
   * @param array $url_parameters
   * @param array $post_data
   *
   * @return mixed|SimpleXMLElement|string
   */
  public function patch(array $resource, array $url_parameters = array(), array $post_data = array()) {
    return $this->makeCall($resource, self::HTTP_METHOD_PATCH, $url_parameters, $post_data);
  }


  /**
   * Make a generic call.
   *
   * @param $resource
   * @param $http_verb
   * @param array $url_parameters
   * @param array $post_data
   *
   * @internal param $desired_format
   *
   * @return mixed|SimpleXMLElement|string
   */
  private function makeCall(array $resource, $http_verb, array $url_parameters = array(), array $post_data = array()) {
    $resource = implode('/', $resource);
    $complete_url = $this->url . '/' . $this->endpoint . '/' . $resource . '?' . http_build_query($url_parameters);

    $ch = curl_init($complete_url);

    // curl output to a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // We establish the port to work
    curl_setopt($ch, CURLOPT_PORT, $this->port);

    /*
     * Note:
     * Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data, while passing a URL-encoded
     * string will encode the data as application/x-www-form-urlencoded.
     */
    $post_data = http_build_query($post_data);

    if (self::DEBUG_MODE) {
      curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
    }

    switch ($http_verb) {
      case self::HTTP_METHOD_GET:
        break;

      case self::HTTP_METHOD_POST:
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        break;

      case self::HTTP_METHOD_PUT:
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        break;

      case self::HTTP_METHOD_DELETE:
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        break;

      case self::HTTP_METHOD_PATCH:
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        break;
    }

    $response = curl_exec($ch);
    curl_close($ch);

    $response = self::transformResponse($response);

    return $response;
  }
}