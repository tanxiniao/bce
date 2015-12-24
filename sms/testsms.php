<?php
//include 'BaiduBce.phar';

$data = '{
             "templateId":"smsTpl:e7476122a1c24e37b3b0de19d04ae904",
             "receiver":["18600883371"],
             "contentVar":"{\"code\":\"123456\"}"
          }';

date_default_timezone_set('UTC');

$AK = "";
        $timestamp = date("Y-m-d")."T".date("H:i:s")."Z";
        //$timestamp = new DateTime();
        //$timestamp->setTimezone(DateUtils::$UTC_TIMEZONE);


        print $timestamp."\n";
        $expirationPeriodInSeconds = "3600";
        $SK="";
       
       // $authStringPrefix = "bce-auth-v1/".$AK."/".$timestamp."/".$expirationPeriodInSeconds;

        $authStringPrefix = "bce-auth-v1"."/".$AK."/".$timestamp."/".$expirationPeriodInSeconds;
        print $authStringPrefix."\n";

        $SigningKey=hash_hmac('SHA256',$authStringPrefix,$SK);
        print $SigningKey."\n";

        $CanonicalHeaders1 = "host;"."x-bce-date";//
        $CanonicalHeaders2 = "host:sms.bj.baidubce.com\n"."x-bce-date:".urlencode($timestamp);//

        //$CanonicalHeaders2 = "host:bj.bcebos.com\n"."x-bce-date:".urlencode($timestamp);
        $CanonicalString = "";

        $CanonicalURI = "/v1/message"; 
        $CanonicalRequest = "POST\n".$CanonicalURI."\n".$CanonicalString."\n".$CanonicalHeaders2;	//第二步
        print $CanonicalRequest."\n";

        $Signature = hash_hmac('SHA256',$CanonicalRequest,$SigningKey);
        print $Signature."\n";

        $Authorization = "bce-auth-v1/{$AK}/".$timestamp."/{$expirationPeriodInSeconds}/{$CanonicalHeaders1}/{$Signature}";
        //$Authorization="bce-auth-v1/97659fdf3ab547e2a9f71dfcd6659a8b/2015-08-28T05:03:28Z/3600/host;x-bce-date/722b421d5cd9973fb8cbb308e5968af52a90f73e0ce28fb8f1c95c81483127c2";
        print $Authorization."\n";
        //$Authorization:
        //bce-auth-v1/97659fdf3ab547e2a9f71dfcd6659a8b2015-08-27T03:50:33Z/3600/content-length;host;x-bce-date;/5290e5669befd7f44dd362e00c2a4cc5edbee0bc925bceb15e8a9fcb91389201
<<<<<<< HEAD
<<<<<<< HEAD
        $url = "http://sms.bj.baidubce.com/v1/message";
        $data['templateId']= "smsTpl:e7476122a1c24e37b3b0de19d04ae901";
        $data['receiver']= "['15221095850']";
        $data['contentVar']= "{\"key1\" : \"val1\", \"key2\" : \"val2\"}";
        $head =  array("Content-type: application/json;charset=utf-8","Authorization:{$Authorization}","x-bce-date:{$timestamp}","x-bce-content-sha256:{$SigningKey}");
     
         $curlp = curl_init();
		 curl_setopt($curlp, CURLOPT_URL, $url);
		 curl_setopt($curlp, CURLOPT_HTTPHEADER,$head); 
		 curl_setopt($curlp, CURLOPT_SSL_VERIFYPEER, FALSE);
		 curl_setopt($curlp, CURLOPT_SSL_VERIFYHOST, FALSE);
		 if(!empty($data)){
		   curl_setopt($curlp, CURLOPT_POST, 1);
		   curl_setopt($curlp, CURLOPT_POSTFIELDS, $data);
		 }
		 curl_setopt($curlp, CURLOPT_RETURNTRANSFER, 1);
		 $output = curl_exec($curlp);
		 curl_close($curlp);
		 echo  $output;
=======
      //   $url = "http://sms.bj.baidubce.com/v1/message";
=======
      $url = "http://sms.bj.baidubce.com/v1/message";
>>>>>>> 3ed54023727e1a12b6c538ef4d9bb2afaa62dbb2
      // //   $data['templateId']= "smsTpl:e7476122a1c24e37b3b0de19d04ae904";
      // //   $data['receiver']= "['18600883371']";
      // //   $data['contentVar']= "{\"key1\" : \"val1\", \"key2\" : \"val2\"}";
        $head =  array("Content-type: application/json","Authorization:{$Authorization}","x-bce-date:{$timestamp}","x-bce-content-sha256:{$SigningKey}");
     
<<<<<<< HEAD
      //   $curlp = curl_init();
		    // curl_setopt($curlp, CURLOPT_URL, $url);
		    // curl_setopt($curlp, CURLOPT_HTTPHEADER,$head); 
		    // curl_setopt($curlp, CURLOPT_SSL_VERIFYPEER, FALSE);
		    // curl_setopt($curlp, CURLOPT_SSL_VERIFYHOST, FALSE);
		    // if(!empty($data)){
		    //   curl_setopt($curlp, CURLOPT_POST, 1);
		    //   curl_setopt($curlp, CURLOPT_POSTFIELDS, $data);
		    // }
		    // curl_setopt($curlp, CURLOPT_RETURNTRANSFER, 1);
		    // $output = curl_exec($curlp);
		    // curl_close($curlp);
		    // echo  $output;
>>>>>>> 8c500df051e2734b177b69e3f4111c9613c9dd22
?>
=======
        $curlp = curl_init();
		    curl_setopt($curlp, CURLOPT_URL, $url);
		    curl_setopt($curlp, CURLOPT_HTTPHEADER,$head); 
		    curl_setopt($curlp, CURLOPT_SSL_VERIFYPEER, FALSE);
		    curl_setopt($curlp, CURLOPT_SSL_VERIFYHOST, FALSE);
		    if(!empty($data)){
		      curl_setopt($curlp, CURLOPT_POST, 1);
		      curl_setopt($curlp, CURLOPT_POSTFIELDS, $data);
		    }
		    curl_setopt($curlp, CURLOPT_RETURNTRANSFER, 1);
		    $output = curl_exec($curlp);
		    curl_close($curlp);
		    echo  $output;
?>
>>>>>>> 3ed54023727e1a12b6c538ef4d9bb2afaa62dbb2
