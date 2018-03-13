<?php

namespace Modular\Traits;

use ArrayData;
use ClassInfo;
use Session;
/**
 * Add the ability to get and format messages from localisation files depending on context.
 *
 * @package Modular\Traits
 */
trait messages {

	/**
	 *
	 * Set session messages
	 *
	 */
	public function setSessionMessage($message, $type = 'success') {
		Session::set("Page.message", $message);
		Session::set("Page.messageType", $type);
	}

	/**
	 * Return Session message and type, if clear is true  (default behaviour) then also clear it, subsequent calls will return nothing.
	 *
	 * @param bool $clear
	 *
	 * @return \ArrayData|bool
	 */
	public function getSessionMessage($clear = true) {
		if (Session::get('Page.message')) {
			$msg = new ArrayData([
				'Message' => Session::get('Page.message'),
				'Type' => Session::get('Page.messageType'),
			]);
		} else {
			$msg = false;
		}

		return $msg;
	}

	/**
	 *
	 * Clear all session messages
	 *
	 */
	public function ClearMessage() {
		Session::clear('Page.message');
		Session::clear('Page.messageType');
	}

	/**
	 * Try and find a language yml entry with a composite key of extended model name and the provided key
	 * e.g. 'Member.Confirmed', then key of extension class name, provided key and model class e.g.
	 * 'ConfirmableExtension.Confirmed.Member' and then just 'ConfirmableExtension.Confirmed'.
	 *
	 * Tokens are replaced in the message using {token} syntax, default tokens are provided:
	 *
	 * {singular}   = extended model singular name
	 * {plural}     = extended model plural name
	 * {class}      = extended model class name
	 * {action}     = extension action, e.g. 'confirm', 'edit'
	 * {relcode}    = extension relationship code e.g. 'CFM', 'EDT'
	 * {<field>}    = value of extended model field e.g. {title} = the models Title field
	 *
	 * @param string \DataObject $modelOrClassName  use this model instead of the extended model
	 * @param string $actionOrKey                   e.g. 'confirm', 'edit'
	 * @param string $subKey                        e.g 'SuccessMessage' if empty then this extension action will be used, e.g. 'confirm' or 'edit'
	 *                                              if key contains a '.' then it will be used as is with no further mangling
	 * @param array  $tokens                        e.g. ['name' => 'John Smith' ] will replace {name} in message with John Smith.
	 * @param string $default                       message to use if no message found in language yml. If null then {action} will be used.
	 *                                              if empty string then no default will be returned.
	 *
	 * @return string
	 */
	public function actionMessage($modelOrClassName, $actionOrKey, $subKey = '', $default = null, $tokens = []) {
		$model = is_object($modelOrClassName)
		? $modelOrClassName
		: (ClassInfo::exists($modelOrClassName) ?
			singleton($modelOrClassName)
			: $modelOrClassName
		);

		if (is_object($modelOrClassName)) {
			$modelClass = Reflection::derive_class_name(
				$model,
				true
			);
			/** @var \DataObject $modelClass */
			$tokens = array_merge(
				[
					'ModelNiceName' => $model->i18n_singular_name(),
					'PluralNiceName' => $model->i18n_plural_name(),
					'ModelClass' => $model->ClassName,
					'ModelID' => $model->ID ?: '[none]',
					'Action' => $actionOrKey,
					'SubKey' => $subKey,
				],
				$model->toMap(),
				$tokens
			);
			$subKey = $subKey ?: $actionOrKey;
			$default = is_null($default) ? "{Action}" : $default;
			if (false !== strpos($subKey, '.')) {
				// use key verbatim as contains a '.'
				$message = _t($subKey, $default, $tokens);
			} else {
				$extensionClass = get_class($this);
				// lang yml keys in order they are tried before default is returned if not found
				$key1 = "$modelClass.$subKey"; // Member.Confirmed or Member.confirm
				$key2 = "$extensionClass.$subKey.$modelClass"; // ConfirmableExtension.Confirmed.Member or ConfirmableExtension.confirm.Member
				$key3 = "$extensionClass.$subKey"; // ConfirmableExtension.Confirmed or ConfirmableExtension.confirm
				if (!$message = _t($key1, '', $tokens)) {
					if (!$message = _t($key2, '', $tokens)) {
						$message = _t($key3, $default, $tokens);
					}
				}
			}
		} else {
			// no model use the global message key 'Application'
			$message = _t(
				implode('.', array_filter(['Application', $modelOrClassName, $actionOrKey, $subKey])),
				$default,
				[
					'ModelClass' => ($modelOrClassName && is_string($modelOrClassName)) ? $modelOrClassName : 'Thing',
					'Action' => $actionOrKey,
					'SubKey' => $subKey,
				]
			);
		}

		return $message;
	}
}