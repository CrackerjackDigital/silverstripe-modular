<?php
namespace Modular\Extensions\Model;

/**
 * SiteConfig extension adds AdminEmail and SystemAdminEmail fields to SiteConfig as used by Application and Debugger. These can be overridden in use
 * by Application.system_admin_field_name and Application.admin_field_name in case this extension is not being used to add them to SiteConfig.
 *
 * @package Modular\Extensions\Model
 */
class SiteConfig extends \DataExtension {
	const AdminFieldName = 'AdminEmail';
	const SystemAdminFieldName = 'SystemAdminEmail';

	private static $db = [
		self::AdminFieldName => 'Varchar(255)',
		self::SystemAdminFieldName => 'Varchar(255)'
	];
}