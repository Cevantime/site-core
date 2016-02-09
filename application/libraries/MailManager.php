<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class MailManager {
	
	private $_mail;
	private $_defaults;
	
	public function __construct() {
		$CI =& get_instance();
		$CI->load->library('email');
		$this->_mail = $CI->email;
		$CI->load->model('configuration');
		$this->_defaults = $CI->configuration->getValues(array(
			'mail_from' => 'thibaulttruffert@hotmail.com',
			'mail_cc' => 'thibaulttruffert@hotmail.com',
			'mail_bcc' => 'thibaulttruffert@hotmail.com',
			'mail_debug' => false
		));
	}
	
	public function sendMail($subject, $message, $to, $from = null, $cc = null, $bcc = null) {
		$this->_mail->from($from ? $from : $this->_defaults['mail_from']);
		$this->_mail->to($to);
		$this->_mail->subject($subject);
		$this->_mail->message($message);
		if ($cc) {
			$this->_mail->cc($cc);
		}
		if ($bcc) {
			$this->_mail->bcc($bcc);
		}

		$sent = $this->_mail->send();
		if(!$sent && $this->_defaults['mail_debug']) {
			echo $this->_mail->print_debugger();
		}
		return $sent;
	}
	
	public function sendMailWithCC($subject, $message,$to,$from = null, $cc = null, $bcc = null){
		$this->_mail->from($from ? $from : $this->_defaults['mail_from']);
		$this->_mail->to($to);
		$this->_mail->subject($subject);
		$this->_mail->message($message);
		$this->_mail->cc($cc ? $cc : $this->_defaults['mail_cc']);
		$this->_mail->bcc($bcc ? $bcc : $this->_defaults['mail_bcc']);
		
		$sent = $this->_mail->send();
		if(!$sent && $this->_defaults['mail_debug']) {
			echo $this->_mail->print_debugger();
		}
		return $sent;
		
	}
}