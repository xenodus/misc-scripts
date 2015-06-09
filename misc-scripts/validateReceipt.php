<?php
function verifyAndroidReceipt( $receiptdata, $signature, $googlePlayKey ) {

	$publicKey = "-----BEGIN PUBLIC KEY-----\n".chunk_split($googlePlayKey, 64, "\n").'-----END PUBLIC KEY-----';	
	$key = openssl_get_publickey( $publicKey );
	
	if( openssl_verify( $receiptdata, base64_decode( $signature ), $key, OPENSSL_ALGO_SHA1 ) == 1 )
		return array('result' => true);
	
	return array('result' => false);
}

function verifyiOSReceipt($receipt) {
	
	if($GLOBALS['environment']=='staging')
		$verification_url_production = 'https://sandbox.itunes.apple.com/verifyReceipt';
	else
		$verification_url_production = 'https://buy.itunes.apple.com/verifyReceipt';
	
	$receipt = '{ "receipt-data": "'.$receipt.'" }';
	$request = do_http_request($verification_url_production, 'post', $receipt);
	
	if(isset($request['body'])) {
		$result = json_decode($request['body'], true);
	
		if($result['status'] == 0)
			return array('result' => true, 'body' => $request['body'] );
	}
	
	return array('result' => false);
}

function verifyAmazonReceipt($amazonID, $receipt) {
	$amazon_secret = "..ToDo..";
	$verification_url_production = "https://appstore-sdk.amazon.com/version/2.0/verify/developer/";	
	$url = $verification_url_production.$amazon_secret."/user/".$amazonID."/purchaseToken/".$receipt;
	
	$request = do_http_request($url);
	
	if( isset($request['responseCode']) && $request['responseCode']=='200' )
		return array('result' => true, 'body' => $request['body'] );
	
	return array('result' => false);
}

function parseIOSreceipt($receipt) {
	$receipt = base64_decode($receipt);	
	$receipt = str_replace('" =','" :', $receipt);
	$receipt = str_replace('";','",', $receipt);
	$receipt = json_decode(str_replace(",\n}","\n}", $receipt), true);
	
	return $receipt;
}
?>