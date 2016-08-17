<?php

//echo "Begin Test<br/>";

//File containing structure for our REST requests
require "RestRequest.php";

//File containing login information for Zuora
require "config.php";

//Sandbox URL for generating signature for IFrame
$url = 'https://apisandbox-api.zuora.com/rest/v1/rsa-signatures';

//Set required params for generating signature of HPM 2.0 page
$data = array(
	'uri' => $appUrl,
	'method' => "POST",
	'pageId' => $pageId
);

//JSON encode the params
$dataEncode = json_encode($data);

//Create Rest Request object for processing Rest call
$restResult = new RestRequest($url, 'POST', $dataEncode);

//echo "<br/>Before Execute:<br/>";

//Process Rest call
$restResult->execute();

$requestBody = $restResult->getRequestBody();
$resultBody = $restResult->getResponseBody();
$resultInfo = $restResult->getResponseInfo();

//echo "<br/>After Execute:<br/>";
//echo "<br/>Request:<pre> ".$requestBody."</pre><br/>";
//echo "<br/>Result:<pre> ".print_r($resultBody,true)."</pre><br/>";

$result = json_decode($resultBody);

//Check success of signature generation call
if ($result->success) {
	//echo "<br/>Call was a success<br/>";

	$signature = $result->signature;
	$token = $result->token;
	$key = $result->key;

	if ($signature !== null && $token !== null && $key !== null) {
		//echo "<br/>All parameters returned successfully<br/>";
	} else {
		echo "<br/>Not all parameters returned<br/>";
	}
} else {
	echo "<br/>Call failed<br/>";
}

//echo "<br/>End Test<br/>";
?>

<!doctype html>

<html>
	<head>
		<!-- Zuora Public javascript library, Sandbox version -->
		<script type="text/javascript" src="https://apisandboxstatic.zuora.com/Resources/libs/hosted/1.3.0/zuora-min.js"></script>
		 
		<script>
				
			//Fields to pre-populate into IFrame. This is where you would set "field_accountId" for an existing Zuora Account	
			var prepopulateFields = {
				email: "test@test.com",
				creditCardAddress1: "123 Main St"
			};

			//IFrame-specific parameters for Z.Render function
			//tenantId retrieved from config.php
			//id retrieved from config.php (page id)
			//token retrieved from signature generation call above
			//signature retrieved from signature generation call babove
			//style: inline or overlay
			//key retrieved from signature generation call above
			//submitEnabled: whether to show Submit button inside IFrame
			//locale
			//url: retrieved from config.php (url for hosted pages)
			//paymentGateway: optional param for setting Payment Gateway to use for authorizing card
			var params = {
				tenantId:"<?php echo $tenantId; ?>", 
				id:"<?php echo $pageId; ?>",
				token:"<?php echo $token; ?>", 
				signature:'<?php echo $signature; ?>',
				style:"inline", 
				key:"<?php echo $key; ?>",
				submitEnabled:"true",
				locale:"en_US",
				url:"<?php echo $appUrl; ?>",
				//paymentGateway: "Test Gateway"
			};
			
			//Callback function called after IFrame submits
			function callback(response) {
				// console.log("submitted!");

				if (response != null) {
					// console.log('response not null: '+JSON.stringify(response));

					if (response.success) {
						// console.log('Successful Response!!!');
						// console.log('RefId: '+response.refId);
						// console.log('Token: '+response.token);
						// console.log('PHP Token: <?php echo $token; ?>');
						alert("Payment Method created! \nRefid: "+response.refId);
					} else {
						// console.log('Response failure');
						alert("Response failure: "+response.errorCode+response.errorMessage);
					}
				} else {
					// console.log('response null');
				}
			}
			
			//Method called upon page load. Call this when you want to render the IFrame (calls Z.render function from Zuora JS Library)
			function loadHostedPage() {
				Z.render(
					params,
					prepopulateFields,
					callback
				);
			}				  
		</script>
	</head>

	<body onload="loadHostedPage()"> 
		<!--div used for rendering IFrame -->
		<div id="zuora_payment"></div>
	</body>
</html>
