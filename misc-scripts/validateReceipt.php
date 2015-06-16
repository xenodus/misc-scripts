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
			return array('result' => true, 'body' => $request['body']);
	}
	
	return array('result' => false);
}

function verifyAmazonReceipt($amazonID, $receipt) {
	$amazon_secret = "amazon...secret...";
	$verification_url_production = "https://appstore-sdk.amazon.com/version/2.0/verify/developer/";	
	$url = $verification_url_production.$amazon_secret."/user/".$amazonID."/purchaseToken/".$receipt;
	
	$request = do_http_request($url);
	
	if($request['responseCode'] == '200')
	    return array('result' => true, 'body' => $request['body']);
	else
	    return array('result' => false, 'status_code' => $request['responseCode']);
}

function verifyWindowsReceipt($receipt) {
    $verification_url_production = "https://lic.apps.microsoft.com/licensing/certificateserver/?";
    $result = false;
    $body = array();
    $error_msg = "";
    
    // Strip space
    $xml = $receipt;
    $xml = str_replace(array("\n","\t", "\r"), "", $xml);
    $xml = preg_replace('/\s+/', " ", $xml);
    $xml = str_replace("> <", "><", $xml);
    
    $doc = new DOMDocument();
    
    if(@$doc->loadXML($xml)) {
        
        $receipt = $doc->getElementsByTagName('Receipt')->item(0);
        $certificateId = $receipt->getAttribute('CertificateId');
        
        $url = $verification_url_production."cid=".$certificateId;
        $request = do_http_request($url);
        
        if($request['responseCode'] == "200") {
        
            $publicKey = $request['body'];
            $objXMLSecDSig = new XMLSecurityDSig();
            $objDSig = $objXMLSecDSig->locateSignature($doc);
        
            if($objDSig) {
        
                try {
                    $objXMLSecDSig->canonicalizeSignedInfo();
                    $retVal = $objXMLSecDSig->validateReference();
        
                    if (!$retVal) {
                        throw new Exception("Error Processing Request", 1);
                    }
                    $objKey = $objXMLSecDSig->locateKey();
        
                    if (!$objKey) {
                        throw new Exception("Error Processing Request", 1);
                    }
                    $key = NULL;
                    $objKeyInfo = XMLSecEnc::staticLocateKeyInfo($objKey, $objDSig);
        
                    if (! $objKeyInfo->key && empty($key)) {
                        $objKey->loadKey($publicKey);
                    }
        
                    if (!$objXMLSecDSig->verify($objKey)) {
                        throw new Exception("Error Processing Request", 1);
                    }
                    
                    $body['productReceipt'] = $doc->getElementsByTagName('ProductReceipt')->item(0);
                    $body['productId'] = $body['productReceipt']->getAttribute('ProductId');
                    $body['purchaseDate'] = $body['productReceipt']->getAttribute('PurchaseDate');
                    $body['bundleId'] = $body['productReceipt']->getAttribute('AppId');
                    $body['purchasePrice'] = $body['productReceipt']->getAttribute('PurchasePrice');
                    
                    $result = true; // success
        
                }
                catch (Exception $e) {
                    $error_msg = $e->getMessage();
                }
            }
        }
    }

    return array("result" => $result, "body" => $body, "error_msg" => $error_msg);
}

function parseIOSreceipt($receipt) {
	$receipt = base64_decode($receipt);	
	$receipt = str_replace('" =','" :', $receipt);
	$receipt = str_replace('";','",', $receipt);
	$receipt = json_decode(str_replace(",\n}","\n}", $receipt), true);
	
	return $receipt;
}
?>