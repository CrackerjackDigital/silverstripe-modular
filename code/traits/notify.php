<?php
namespace Modular;

use Modular\Exceptions\Exception;

/**
 * Simple trait to send an email via a 'notify' method.
 * @package Modular
 */
trait notify
{
	
	/**
	 * @return Debugger
	 */
	abstract public function debugger();
	
	/**
	 * @param string|int   $recipientAddressOrMemberID
	 * @param string       $subject
	 * @param string|array $templatesOrLangKey
	 * @param string       $noTemplateBody
	 * @param array        $data
	 * @return boolean
	 */
	public function notify($recipientAddressOrMemberID, $subject, $templatesOrLangKey, $noTemplateBody, $data = [])
	{
		$member = null;
		$sent   = false;
		// take a copy with uppercase keys to make matching easier
		$directives = array_change_key_case($data, CASE_UPPER);
		
		$this->debugger()->trace("Notify: '$recipientAddressOrMemberID' subject '$subject'");
		
		// find the member by ID or email address
		if (is_numeric($recipientAddressOrMemberID)) {
			if (!$member = \Member::get()->byID($recipientAddressOrMemberID)) {
				$this->debugger()->warn("Failed to find member with ID '$recipientAddressOrMemberID'");
			}
		} else {
			if (!$member = \Member::get()->filter('Email', $recipientAddressOrMemberID)->first()) {
				$this->debugger()->info("Email address '$recipientAddressOrMemberID' of non-existant member being used");
			}
		}
		// try member if set in data
		if (!$member && isset( $directives['MEMBER'] )) {
			$member = $directives['MEMBER'];
		}
		
		if ($member) {
			$recipientAddressOrMemberID = is_array($member) ? $member['Email'] : $member->Email;
		} else {
			if (isset( $directives['TO'] )) {
				$recipientAddressOrMemberID = $directives['TO'];
			} else {
				$recipientAddressOrMemberID = \Email::config()->get('admin_email') ?: constant('SS_SEND_ALL_EMAILS_TO');
			}
		}
		if ($recipientAddressOrMemberID) {
			// find a template that exists, if not found then the 'no template' body will be used
			$templatesOrLangKey = array_filter(is_array($templatesOrLangKey)
				? $templatesOrLangKey
				: [ $templatesOrLangKey ]);
			
			$template = '';
			foreach ($templatesOrLangKey as $template) {
				if (\SSViewer::hasTemplate($template)) {
					break;
				}
				$template = '';
			}
			// make the member available in the template if not already set in data
			if ($member && !isset( $data['Member'] )) {
				$data['Member'] = $member;
			}
			
			// replace tokens and make the no template body available in the template
			if ($noTemplateBody) {
				$noTemplateBody = _t(current($templatesOrLangKey) ?: 'Should.Fail', $noTemplateBody, $data);
				$data['AltBody'] = $noTemplateBody;
			}
			
			$email = \Email::create();
			
			if ($template) {
				$email->setTemplate($template);
				$email->populateTemplate($data);
			} else {
				// use lang key to lookup body or use noTemplateBody if no lang key found
				$email->setBody($noTemplateBody);
			}
			
			// replace tokens in subject with data
			$email->setSubject(_t('Should.Fail', $subject, $data));
			
			// by now this should be an email address
			$email->setTo($recipientAddressOrMemberID);
			
			if (isset( $directives['REPLYTO'] )) {
				$email->replyTo($directives['REPLYTO']);
			}
			if (isset( $directives['CC'] )) {
				$email->setCc($directives['CC']);
			}
			if (isset( $directives['BCC'] )) {
				$email->setBcc($directives['BCC']);
			}
			
			$this->debugger()->trace("Sending email to '$recipientAddressOrMemberID' with subject '$subject'");
			if ($template && !isset( $directives['SENDPLAIN'] )) {
				$sent = $email->send();
			} else {
				$sent = $email->sendPlain();
			}
			
			if ($sent) {
				$this->debugger()->trace("Sent email to '$recipientAddressOrMemberID' with subject '$subject'");
			} else {
				$this->debugger()->error("Failed to send email to '$recipientAddressOrMemberID' with subject '$subject'");
			}
		} else {
			$this->debugger()->error("Failed to find an email address from input '$recipientAddressOrMemberID' to send email with subject '$subject'");
		}
		return $sent;
	}
}