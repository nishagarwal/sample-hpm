<?php



class RestRequest
{
	protected $url;
	protected $verb;
	protected $requestBody;
	protected $username;
	protected $password;
	protected $acceptType;
	protected $contentType;
	protected $responseBody;
	protected $responseInfo;

	public function __construct ($url = null, $verb = 'GET', $requestBody = null)
	{
		$this->url				= $url;
		$this->verb				= $verb;
		$this->requestBody		= $requestBody;
		$this->username			= null;
		$this->password			= null;
		$this->acceptType		= 'application/json';
		$this->contentType 		= 'application/json';
		$this->responseBody		= null;
		$this->responseInfo		= null;

		if ($this->requestBody !== null)
		{
			$this->buildPostBody();
		}
	}

	public function flush ()
	{
		$this->requestBody		= null;
		$this->verb				= 'GET';
		$this->responseBody		= null;
		$this->responseInfo		= null;
	}

	public function execute ()
	{
		$ch = curl_init();
		// $this->setAuth($ch);

		try
		{
			switch (strtoupper($this->verb))
			{
				case 'GET':
					$this->executeGet($ch);
					break;
				case 'POST':
					$this->executePost($ch);
					break;
				case 'PUT':
					$this->executePut($ch);
					break;
				case 'DELETE':
					$this->executeDelete($ch);
					break;
				default:
					throw new InvalidArgumentException('Current verb (' . $this->verb . ') is an invalid REST verb.');
			}
		}
		catch (InvalidArgumentException $e)
		{
			curl_close($ch);
			throw $e;
		}
		catch (Exception $e)
		{
			curl_close($ch);
			throw $e;
		}

	}

	// Accepts array or json data
	public function buildPostBody ($data = null)
	{
		$data = ($data !== null) ? $data : $this->requestBody;

		if (strtoupper($this->verb) === 'POST') {

			// If data is an array, convert to JSON
			if (is_array($data))
			{
				$data = json_encode($data);
			}

			$this->requestBody = $data;

		} else if (strtoupper($this->verb) === 'GET') {

			// If data is JSON, convert to array
			if (!is_array($data))
			{
				$data = json_decode($data, true);
			}

			$data = http_build_query($data, '', '&');

			$this->url = $this->url . '?' . $data;
		} 		
	}

	protected function executeGet ($ch)
	{
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		
		$this->doExecute($ch);
	}

	protected function executePost ($ch)
	{
		error_log('executePost()');
		if ($this->requestBody !== null && !is_string($this->requestBody))
		{
			error_log('buildPostBody()');
			$this->buildPostBody();
		}

		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
		curl_setopt($ch, CURLOPT_POST, 1);

		error_log('doExecute()');
		$this->doExecute($ch);
	}

	protected function executePut ($ch)
	{
		if ($this->requestBody !== null && !is_string($this->requestBody))
		{
			$this->buildPostBody();
		}


		error_log($this->requestBody);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);

		$this->doExecute($ch);
	}

	protected function executeDelete ($ch)
	{
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

		$this->doExecute($ch);
	}

	protected function doExecute (&$curlHandle)
	{
		error_log('Starting doExecute()');
		$this->setCurlOpts($curlHandle);
		$this->responseBody = curl_exec($curlHandle);
		$this->responseInfo	= curl_getinfo($curlHandle);
		error_log('Done doExecute()');
		curl_close($curlHandle);
	}

	protected function setCurlOpts (&$curlHandle)
	{
		include ('config.php');

		curl_setopt($curlHandle, CURLOPT_URL, $this->url);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);  
		curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array ('Accept: ' . $this->acceptType,'apiAccessKeyId:'.$username,'apiSecretAccessKey:'.$password, 'Content-Type: '. $this->contentType)); 
		curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, false);
	}

	/** GETTERS **/

	public function getUrl() {
		return $this->url;
	}

	public function getVerb() {
		return $this->verb;
	}

	public function getRequestBody() {
		return $this->requestBody;
	}

	public function getAcceptType() {
		return $this->acceptType;
	}

	public function getContentType() {
		return $this->contentType;
	}

	public function getResponseBody() {
		return $this->responseBody;
	}

	public function getResponseInfo() {
		return $this->responseInfo;
	}



	/** SETTERS **/

	public function setUrl($newUrl) {
		$this->url = $newUrl;
	}

	public function setVerb($newVerb) {
		$this->verb = $newVerb;
	}

	public function setRequestBody($newRequestBody) {
		$this->requestBody = $newRequestBody;
	}

	public function setAcceptType($newAcceptType) {
		$this->acceptType = $newAcceptType;
	}

	public function setContentType($newContentType) {
		$this->contentType = $newContentType;
	}
}

?>