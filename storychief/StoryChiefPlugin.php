<?php

namespace Craft;

class StoryChiefPlugin extends BasePlugin {
	function getName() {
		return Craft::t('Story Chief');
	}

	function getVersion() {
		return '1.0.0';
	}

	function getDeveloper() {
		return 'Story Chief';
	}

	function getDeveloperUrl() {
		return 'https://storychief.io';
	}

	protected function defineSettings() {
		return [
			'key'                      => [AttributeType::String, 'required' => true],
			'section'                  => [AttributeType::String, 'required' => true],
			'entry_type'               => [AttributeType::String, 'required' => true],
			'custom_field_definitions' => [AttributeType::Mixed, 'default' => []],
			'mapping'                  => [AttributeType::Mixed],
		];
	}

	public function getSettingsHtml() {
		return craft()->templates->render('storychief/settings', array(
			'settings' => $this->getSettings(),
			'redirect' => 'settings/plugins/storychief',
		));
	}

	public function registerSiteRoutes() {
		return array(
			'storychief/webhook' => array('action' => 'storyChief/webhook/callback'),
		);
	}

	public function init() {
		Craft::import('plugins.storychief.StoryChief.FieldTypes.*');
	}
}