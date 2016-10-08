<?php
namespace Craft;

class NeoranContactFormExtensionPlugin extends BasePlugin
{
	public function getName()
	{
		return Craft::t('Neorans Contact Form Extension Plugin');
	}

	public function getVersion()
	{
		return '1.0.0';
	}

	public function getSchemaVersion()
	{
		return '1.0.0';
	}

	public function getDeveloper()
	{
		return 'Neoran';
	}

	public function getDeveloperUrl()
	{
		return 'https://github.com/FH3095';
	}

	public function getPluginUrl()
	{
		return 'https://github.com/FH3095/NeoranContactFormExtensionPlugin';
	}

	public static function contactFormBeforeSend(ContactFormEvent $event)
	{
		$captcha = craft()->request->getPost('g-recaptcha-response');
		$verified = craft()->recaptcha_verify->verify($captcha);
		if(!$verified)
		{
			$event->isValid = false;
			$errorMessage = 'Missing verification.';
			if(strcasecmp(craft()->getLanguage(),'de')==0)
			{
				$errorMessage = 'Verifizierung fehlt.';
			}
			$event->params['message']->addError('captcha', $errorMessage);
		}
		if(!isset($event->params['message']->messageFields['fromName']) || empty($event->params['message']->messageFields['fromName']))
		{
			$event->isValid = false;
			$event->params['message']->addError('fromName',Craft::t('{attribute} cannot be blank.',array('attribute' => 'Name'),'coreMessages'));
		}
		else
		{
			// When fromName from message parameters is set, copy it to the fromName field
			$event->params['message']->fromName = $event->params['message']->messageFields['fromName'];
		}
	}

	public static function contactFormBeforeMessageCompile(ContactFormMessageEvent $event)
	{
		if(empty($event->params['postedMessage']['body']))
		{
			return;
		}

		$messageFields = $event->messageFields = $event->params['postedMessage'];
		$message = '';

		foreach($messageFields AS $fieldName=>$fieldValue)
		{
			if('body' == $fieldName)
			{
				continue;
			}

			$message .= $fieldName . ': ';

			if(is_array($fieldValue))
			{
				$message .= implode(', ', $fieldValue);
			}
			else
			{
				$message .= $fieldValue;
			}
			$message .= "\n\n";
		}

		if(isset($messageFields['body']) && !empty($messageFields['body']))
		{
			$message .= $messageFields['body'] . "\n\n";
		}

		$event->message = $message;
		$event->htmlMessage = nl2br($message, false);
	}

	public function init()
	{
		craft()->on('contactForm.beforeSend', function(ContactFormEvent $event) {
			NeoranContactFormExtensionPlugin::contactFormBeforeSend($event);
		});
		craft()->on('contactForm.beforeMessageCompile', function(ContactFormMessageEvent $event) {
			NeoranContactFormExtensionPlugin::contactFormBeforeMessageCompile($event);
		});
	}

	/*public function getReleaseFeedUrl()
	{
		return 'https://github.com/FH3095/NeoranContactFormExtensionPlugin/master/releases.json';
	}*/
}
