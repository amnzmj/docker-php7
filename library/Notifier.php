<?php
	require_once('phpmailer/class.phpmailer.php');
	require_once('phpmailer/class.smtp.php');
	require_once("Zend/Config/Ini.php");
	

	class Notifier{
		private $recipients;
		private $template;
		private $data;
		private $subject;
		private $email;
		
		
		public function __construct( $recipients = array(), $data = array(), $subject = "", $template = "" ){
			if( !is_array($recipients) || !count($recipients) ){
				throw new Exception("Recipients should be an array, with email addresses");
			}
			$this->recipients = $recipients;
			if($template == ""){
				$this->template = "Notifier/templates/default.phtml";
			}else{
				$this->template = $template;
			}
			$this->data = $data;
			$this->subject = $subject;
			$this->buildEmail();
			$this->sendEmail();
		}
		
		private function buildEmail(){
			$data = $this->data;
			ob_start();
			require($this->template);
			$content = ob_get_clean();
			
			$this->email = $content;
		}
		
		private function sendEmail(){

			$ini = Zend_Registry::get('config');

			$mail = new PHPMailer();
			
			$mail ->CharSet = 'UTF-8';
			$mail->Mailer = true;

			$mail->IsSMTP();
			$mail->SMTPOptions = array( 
				'ssl' => array( 
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true 
				) 
			);
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = 'tls';
			$mail->SMTPKeepAlive = true;

			$mail->Host			= $ini->email->connection->host;
			$mail->Port 		= $ini->email->connection->port;
			$mail->Username		= $ini->email->connection->username;  
			$mail->Password		= $ini->email->connection->password;

			$mail->From       	= $ini->email->fromAddress;
			$mail->FromName   	= $ini->email->fromName;
			$mail->Subject	    = $this->subject;
			$mail->AltBody      = $this->email;
			$mail->MsgHTML( $this->email );
			
			foreach($this->recipients as $r){
				$mail->AddAddress($r);
			}

			if (!$mail->send()) {
				throw new Exception("Error while sending email: '". $mail->ErrorInfo ."'");
			}

		}
	}
	
