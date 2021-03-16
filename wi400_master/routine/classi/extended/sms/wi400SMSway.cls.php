<?php

/**
 * @name wi400SMSway
 * @desc Classe generica Invio SMS
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 13/09/2016
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400SMSway extends wi400Sms {

   public function send() {
   	   
   	   // Componi XML
   	  $xml=" 
   	<SendEx><username>{$this->user}</username>
   	<password>{$this->password}</password>
   	<priority>{$this->priority}</priority>
   	<sender>{$this->sender}</sender>
   	<address>{$this->address}</address>
   	<body>{$this->body}</body>
   	<valperiod>{$this->valperiod}</valperiod>
   	<encoding>{$this->encoding}</encoding>
   	<smsclass>{$this->classe}</smsclass>
   	<timetosend>{$this->senddate}</timetosend>
   	<messageid>{$this->keyid}</messageid>
   	</SendEx>";
   }
}
?>