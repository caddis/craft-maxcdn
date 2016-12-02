<?php
namespace Craft;

class MaxCDNPlugin extends BasePlugin
{
	public function getName()
	{
		return 'MaxCDN';
	}

	public function getDescription()
	{
		return Craft::t('Manage and view stats on MaxCDN files.');
	}

	public function getVersion()
	{
		return '0.1.0';
	}

	public function getSchemaVersion()
	{
		return '1.0.0';
	}

	public function getDeveloper()
	{
		return 'Caddis';
	}

	public function getDeveloperUrl()
	{
		return 'https://www.caddis.co';
	}

	public function getDocumentationUrl()
	{
		return 'https://github.com/caddis/craft-maxcdn';
	}

	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/caddis/craft-maxcdn/master/releases.json';
	}

	public function init()
	{
		require CRAFT_PLUGINS_PATH . '/maxcdn/vendor/autoload.php';
	}

	protected function defineSettings()
	{
		return [
			'alias' => array(
				AttributeType::String,
				'default' => ''
			),
			'consumerKey' => array(
				AttributeType::String,
				'default' => ''
			),
			'consumerSecret' => array(
				AttributeType::String,
				'default' => ''
			)
		];
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('maxcdn/settings', array(
			'settings' => $this->getSettings()
		));
	}

	public function hasCpSection()
	{
		return true;
	}

	public function registerCpRoutes()
	{
		return array(
			'maxcdn' => array(
				'action' => 'MaxCDN/index'
			),
			'maxcdn/zones' => array(
				'action' => 'MaxCDN/zones'
			),
			'maxcdn/cache' => array(
				'action' => 'MaxCDN/cache'
			)
		);
	}
}