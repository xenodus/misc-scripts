<?php
    require('vendor/autoload.php');
    use Aws\Ec2\Ec2Client;
    use Aws\Ec2\Exception\Ec2Exception;
    date_default_timezone_set('UTC');
    
    /************************
     *  Settings
     ************************/	
    
    // True for testing - If "DryRunOperation" exception is encountered, it will be successful on production
    // False for production 
    $dry_run = false;
    
    // Instance IDs
    $backup_instances = array(
        'i-12345' => 'Hello-World',
        'i-54321' => 'Name-123',
    );
    
    // Regions to create images
    // Refer to http://docs.aws.amazon.com/general/latest/gr/rande.html for region list
    // Asia Pacific (Singapore) => 'ap-southeast-1'
    $regions = array(
        'us-west-1'
    );
    
    // Recipient of email notification
    if($dry_run) {
        $recipient[] = 'alvin.yeoh@gumi.sg';
    }
    else {
        $recipient[] = 'live@gumi.sg';
    }
    
    /************************
     *  Logic - Shouldn't need to touch anything here
     ************************/	
    
    // Configs
    $ec2Client = Ec2Client::factory(array(
        'region' => 'us-west-1'
    ));
    
    // Create AMI
    $success = array();
    $failures = array();
    $successCount = 0;
    $failureCount = 0;
    
    foreach($regions as $region) {
    
    	foreach($backup_instances as $id => $name) {
    		
    		$ami_name = $name.'_'.date('d-m-Y');
    		
    		try {
            	$ec2Client->createImage(array(
                    // Set to true for testing
                    'DryRun' => $dry_run,
                    // InstanceId is required
                    'InstanceId' => $id,
                    // Name is required
                    'Name' => $ami_name,
                    'NoReboot' => true,
                    'region' => $region
            	));
            	
            	$success[$region][$id] = $ami_name;
            	$successCount++;
    		}
    		catch(Ec2Exception $e) {
    			//http://docs.aws.amazon.com/AWSEC2/latest/APIReference/errors-overview.html
    			$failures[$region][$name.' / '.$id] = $e->getExceptionCode(); 
    			$failureCount++;
    		}
    		
    		// Pausing 2s
    		sleep(2);
    	}
    }
    
    // Send report
    $subject = 'Automated AMI Creation Report - '.date('d M Y');
    
    $message = 'AMI creation success(es): '.$successCount;
    $message .= '<br/>';
    $message .= 'AMI creation failure(es): '.$failureCount;
    
    if(count($success)) {
    
    	$message .= '<br/><br/>';		
    	$message .= "AMI(s) created: <br/>";
    	
    	foreach($success as $region => $data) {
    		foreach($data as $id => $name) {
    			$message .= $region.' => '.$id.' => '.$name.'<br/>';
    		}
    	}
    }
    
    if(count($failures)) {
    
        $message .= '<br/><br/>';
        $message .= "AMI(s) creation failures: <br/>";
    
        foreach($failures as $region => $data) {
    	    foreach($data as $id => $status) {
    	        $message .= $region.' => '.$id.' => '.$status.'<br/>';
    	    }
        }
    }
    
    sendmail($recipient, $message, $subject);
    
    /************************
     *  Functions
     ************************/	
    
    function sendmail($recipient, $message='', $subject='', $alt_message='', $cc=array()) {
        $mail = new PHPMailer;
        
        //$mail->SMTPDebug = 3;                               	// Enable verbose debug output
        
        $mail->isSMTP();                                      	// Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  						// Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               	// Enable SMTP authentication
        $mail->Username = '..ToDo..';                           // SMTP username
        $mail->Password = '..ToDo..';                           // SMTP password
        $mail->SMTPSecure = 'tls';                            	// Enable TLS encryption, `ssl` also accepted
        $mail->Port = 25;                                    	// TCP port to connect to
        
        $mail->From = '..ToDo..';
        $mail->FromName = 'GumiLive Tools';
        
        foreach($recipient as $email)
            $mail->addAddress($email);
        
        $mail->addReplyTo('..ToDo..');
        
        if( count($cc) ) {
            foreach($cc as $email => $name) {
                if($name)
                    $mail->addCC($email, $name);
                else
                    $mail->addCC($email);
            }
        }
        
        //$mail->addBCC('bcc@example.com');
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $mail->isHTML(true);                                  // Set email format to HTML
        
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = $alt_message;
        
        return $mail->send();
    }
?>