<?php
namespace Modular;

use Modular\Exceptions\Exception;

/**
 * Simple trait to send an email via a 'notify' method.
 *
 * @package Modular
 */
trait emailer {

	/**
	 * @return Debugger
	 */
	abstract public function debugger($level = Debugger::LevelFromEnv, $source = '');

	/**
	 * @param string|int|\Member
	 * @param string|int|array|\Member $recipientAddressesOrMemberIDs
	 * @param string                   $subjectOrLangKey          subject for email or a language key to look subject up by
	 * @param string                   $messageOrBodyIfNoTemplate if no template matches then this will be the body, it will be provided as $Message in a found
	 *                                                            template
	 * @param string|array             $templatesOrBodyLangKey    name(s) of template to use (or list of alternatives), or a language key to lookup the body content with
	 *                                                            template body
	 * @param array                    $data
	 * @return boolean
	 */
	public function emailer_send($senderAddressOrMemberID, $recipientAddressesOrMemberIDs, $subjectOrLangKey, $messageOrBodyIfNoTemplate, $templatesOrBodyLangKey = '', $data = []) {
		$member = null;
		$sent = false;
		$className = get_called_class();

		$template = is_array($templatesOrBodyLangKey) ? implode(',', $templatesOrBodyLangKey) : $templatesOrBodyLangKey;

		$this->debug_trace("Notify (raw info): subject '$subjectOrLangKey' template '$template'");

		// try the language files for translated classname eg Approveable.Yes.Subject
		if (!$subject = _t("$className.$subjectOrLangKey", '', $data)) {
			$subject = _t('Should.Fail', $subjectOrLangKey, $data);
		}

		// take a copy with uppercase keys to make matching easier later for CC, BCC, REPLYTO etc
		$directives = array_change_key_case($data, CASE_UPPER);

		$senderAddress = '';
		if ($senderAddressOrMemberID instanceof \Member) {
			$senderAddress = $senderAddressOrMemberID->Email;
		} else if (is_int($senderAddressOrMemberID)) {
			if ($member = \Member::get()->byID($senderAddressOrMemberID)) {
				$senderAddress = $member->Email;
			}
		} else {
			$senderAddress = $senderAddressOrMemberID;
		}
		if (!$senderAddress) {
			$this->debug_error("Failed to send email to Member with ID '$senderAddressOrMemberID' as they don't exist");
		}

		// try these sources, a match against first tupple is considered 'ok' of second tupple value is true
		// otherwise the match will indicate a failure to identify a valid email address, but email will still
		// be sent with subject prefixed by 'Problem sending notification email:' type message
		$sources = array_filter([
			'provided'      => [$recipientAddressesOrMemberIDs, true],
			'data'          => isset($directives['RECIPIENT']) ? [$directives['RECIPIENT'], true] : [],
			'admin_email'   => [\Email::config()->get('admin_email'), false],
			'default_admin' => [\Member::default_admin(), false],
		]);
		$recipients = [];

		foreach ($sources as $source => list($emailsMembersOrIDs, $isOKRecipient)) {
			if (!is_array($emailsMembersOrIDs) && (!$emailsMembersOrIDs instanceof \Traversable)) {
				// normalise to an array
				$emailsMembersOrIDs = [$emailsMembersOrIDs];
			}
			foreach ($emailsMembersOrIDs as $emailMemberOrID) {
				$member = null;
				$recipientAddress = null;

				// find the member by ID or email address if not already a member
				if ($emailMemberOrID instanceof \Member) {
					// all good
					$member = $emailMemberOrID;

				} else if ($emailMemberOrID instanceof \DataObject && $emailMemberOrID->Email) {
					// has an email field so can be used
					$recipientAddress = $emailMemberOrID;

				} else if (is_array($emailMemberOrID) && array_key_exists('Email', $emailMemberOrID)) {

					$member = \Member::get()->filter([
						'Email' => $emailMemberOrID['Email'],
					])->first();

				} else if (is_numeric($emailMemberOrID)) {
					// presume a member ID
					$member = \Member::get()->byID($emailMemberOrID);
				} else {
					// presume an email address
					$member = \Member::get()->filter('Email', $emailMemberOrID)->first();
				}
				// we will either have a Member or a string email address by now
				if ($member) {

					$recipients[] = $member->Email;
					break;

				} else if (is_string($recipientAddress) && strpos($recipientAddress, '@')) {
					// not strict checking false return from strpos is ok as first place '@' is not valid email address
					$recipients[] = $recipientAddress;

				} else {
					$this->debug_warn("Failed to decode a recipient address from '$emailMemberOrID'");
				}
			}
		}
		if ($recipients && $senderAddress) {
			// find a template that exists, if not found then the 'no template' body will be used
			$templatesOrBodyLangKey = array_filter(is_array($templatesOrBodyLangKey)
				? $templatesOrBodyLangKey
				: [$templatesOrBodyLangKey]);

			$template = '';
			foreach ($templatesOrBodyLangKey as $template) {
				if (\SSViewer::hasTemplate($template)) {
					break;
				}
				// reset to nothing for when we fall out of loop
				$template = '';
			}

			// replace tokens and make the no template body available in the template
			if ($messageOrBodyIfNoTemplate) {
				if (!$template) {
					// try using the first template as a lang key instead
					$messageOrBodyIfNoTemplate = _t(
						reset($templatesOrBodyLangKey),
						$messageOrBodyIfNoTemplate,
						$data
					);
				}
			}
			$messageOrBodyIfNoTemplate = $messageOrBodyIfNoTemplate ?: _t('Notifications.EmptyBody', "This message has no content", $data);

			$data = array_merge(
				$data,
				[
					'ORIGINALSUBJECT'  => $subjectOrLangKey,
					'ORIGINALTEMPLATE' => $template,
					'ORIGINALMESSAGE'  => $messageOrBodyIfNoTemplate,
					'SENDER'           => $senderAddress,
					'SUBJECT'          => $subject,
					'MESSAGE'          => $messageOrBodyIfNoTemplate,
				]
			);

			foreach ($recipients as $recipientAddress) {
				$email = \Email::create();

				$data['RECIPIENT'] = $recipientAddress;

				if ($template) {
					$email->setTemplate($template);
					$email->populateTemplate($data);
				} else {
					// use lang key to lookup body or use noTemplateBody if no lang key found
					$email->setBody($messageOrBodyIfNoTemplate);
				}

				// replace tokens in subject with data
				$email->setSubject(_t('Should.Fail', $subject, $data));

				$email->setTo($recipientAddress);

				if (isset($directives['REPLYTO'])) {
					$email->replyTo($directives['REPLYTO']);
				}
				if (isset($directives['CC'])) {
					$email->setCc($directives['CC']);
				}
				if (isset($directives['BCC'])) {
					$email->setBcc($directives['BCC']);
				}

				$this->debug_trace("Sending email to '$recipientAddress' with subject '$subject'");
				if ($template && !isset($directives['SENDPLAIN'])) {
					$sent = $email->send();
				} else {
					$sent = $email->sendPlain();
				}

				if ($sent) {
					$this->debug_trace("Sent email to '$recipientAddress' with subject '$subject'");
				} else {
					$this->debug_error("Failed to send email to '$recipientAddress' with subject '$subject'");
				}
				unset($email);
				$sent = true;
			}
		} else if (!$senderAddress) {

			$this->debug_error("Failed to find a sender email address to send email from with subject '$subject' using template '$template'");

		} else if (!$recipients) {

			$this->debug_error("Failed to find at least one recipient email address to send email to with subject '$subject' using template '$template'");

		}
		return $sent;
	}
}