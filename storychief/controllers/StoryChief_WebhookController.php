<?php namespace Craft;

class StoryChief_WebhookController extends BaseController {

	protected $allowAnonymous = true;

	protected $settings = null;
	protected $event = null;
	protected $payload = null;


	public function __construct($id, $module = null) {
		parent::__construct($id, $module);
		$this->settings = craft()->plugins->getPlugin('storyChief')->getSettings();
	}

	public function actionCallback() {
		$body = @file_get_contents('php://input');
		$this->payload = json_decode($body, true);

		if (!$this->validateCallback()) {
			http_response_code(400);
			$this->returnErrorJson('Callback failed validation');
		}
		$this->event = $this->payload['meta']['event'];

		switch ($this->event) {
			case 'publish':
				$response = $this->handlePublishEventType();
				break;
			case 'update':
				$response = $this->handleUpdateEventType();
				break;
			case 'delete':
				$response = $this->handleDeleteEventType();
				break;
			case 'test':
				$response = $this->handleTestEventType();
				break;
			default:
				$response = $this->handleMissingEventType();
		}

		$this->returnJson($response);
	}

	protected function validateCallback() {
		$json = $this->payload;
		if (!is_array($json)) {
			return false;
		}

		$key = $this->settings->getAttribute('key');
		$givenMac = $json['meta']['mac'];
		unset($json['meta']['mac']);
		$calcMac = hash_hmac('sha256', json_encode($json), $key);

		return hash_equals($givenMac, $calcMac);
	}

	protected function handlePublishEventType() {
		$section = $this->settings->getAttribute('section');
		$entry_type = $this->settings->getAttribute('entry_type');

		$entry = new EntryModel();
		$entry->setAttribute('sectionId', $section);
		$entry->setAttribute('typeId', $entry_type);

		// Set language
		if (craft()->isLocalized() && isset($this->payload['data']['language']) && $this->payload['data']['language']) {
			$entry->setAttribute('locale', $this->payload['data']['language']);
		}

		$entry = $this->_map($entry);

		craft()->entries->saveEntry($entry);

		return $this->_appendMac([
			'id'        => $entry->id,
			'permalink' => $entry->getUrl(),
		]);
	}

	protected function handleUpdateEventType() {
		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->id = $this->payload['data']['external_id'];
		if (craft()->isLocalized() && isset($this->payload['data']['language']) && $this->payload['data']['language']) {
			$criteria->locale = $this->payload['data']['language'];
		}

		$entry = $criteria->first();

		$entry = $this->_map($entry);

		craft()->entries->saveEntry($entry);

		return $this->_appendMac([
			'id'        => $entry->id,
			'permalink' => $entry->getUrl(),
		]);
	}

	protected function handleDeleteEventType() {
		craft()->entries->deleteEntryById($this->payload['data']['external_id']);

		return '';
	}

	protected function handleTestEventType() {
		$storyChiefPlugin = craft()->plugins->getPlugin('storyChief');
		if (isset($this->payload['data']['custom_fields']['data'])) {
			craft()->plugins->savePluginSettings($storyChiefPlugin, [
				'custom_field_definitions' => $this->payload['data']['custom_fields']['data'],
			]);
		} else {
			craft()->plugins->savePluginSettings($storyChiefPlugin, [
				'custom_field_definitions' => [],
			]);
		}

		return '';
	}

	protected function handleMissingEventType() {
		return '';
	}

	// map data to entry
	private function _map(EntryModel $entry) {
		$mapping = $this->settings->getAttribute('mapping');

		// Set author
		if (isset($mapping['author']) && $mapping['author']) {
			// find author
			$user = craft()->users->getUserByEmail($this->payload['data']['author']['data']['email']);
			if (is_null($user) && $mapping['author'] === 'create') {
				$authorData = $this->payload['data']['author']['data'];

				$user = new UserModel();
				$user->username = strtolower($authorData['first_name'] . '.' . $authorData['last_name']);
				$user->firstName = $authorData['first_name'];
				$user->lastName = $authorData['last_name'];
				$user->email = $authorData['email'];

				$success = craft()->users->saveUser($user);
				if (!$success) {
					StoryChiefPlugin::log('Unable to create a user', LogLevel::Error);
					$user = null;
				} else {
					if (isset($authorData['profile_picture']['data']['url'])) {
						$imageInfo = pathinfo($authorData['profile_picture']['data']['url']);
						$tempPath = CRAFT_STORAGE_PATH . 'runtime/temp/' . $imageInfo['basename'];
						file_put_contents($tempPath, fopen($authorData['profile_picture']['data']['url'], 'r'));

						$profile_picture = new Image();
						$profile_picture->loadImage($tempPath);
						craft()->users->saveUserPhoto($authorData['profile_picture']['data']['name'], $profile_picture, $user);
					}
				}
			}
			if (!is_null($user)) {
				$entry->setAttribute('authorId', $user->id);
			}
		}
		unset($mapping['author']);

		// Set slug
		if(isset($this->payload['data']['seo_slug']) && !empty($this->payload['data']['seo_slug'])){
			$entry->slug = $this->payload['data']['seo_slug'];
		}

		// Set title
		$entry->getContent()->title = $this->payload['data']['title'];

		// map other fields
		foreach ($mapping as $fieldHandle => $scHandle) {
			if(!empty($scHandle)){
				$field = craft()->fields->getFieldByHandle($fieldHandle);
				$class = str_replace(array('Craft\\', 'FieldType'), array('\\Craft\\', 'StoryChiefFieldType'), get_class($field->getFieldType()));
				if (class_exists($class)) {
					$value = $this->_filterPayloadData($scHandle);
					if ($value) {
						$scField = new $class();
						if ($scField instanceof StoryChiefFieldTypeInterface) {
							$entry->getContent()->$fieldHandle = $scField->prepFieldData($field, $value);
						}
					}
				}
			}
		}

		return $entry;
	}

	// append MAC to a response
	private function _appendMac($response) {
		$key = $this->settings->getAttribute('key');
		$response['mac'] = hash_hmac('sha256', json_encode($response), $key);

		return $response;
	}

	// returns string value or array of strings values (select or checkboxes)
	private function _filterPayloadData($scHandle) {
		switch ($scHandle) {
			case 'featured_image':
				// returns image url
				if (isset($this->payload['data'][$scHandle]['data']['url'])) {
					return $this->payload['data'][$scHandle]['data']['url'];
				}

				return null;
			case 'categories':
			case 'tags':
				// returns array of values
				if (isset($this->payload['data'][$scHandle]['data'])) {
					return array_map(function ($v) {
						return $v['name'];
					}, $this->payload['data'][$scHandle]['data']);
				}

				return null;
			case 'title':
			case 'content':
			case 'excerpt':
			case 'seo_title':
			case 'seo_description':
				// returns value
				if (isset($this->payload['data'][$scHandle])) {
					return $this->payload['data'][$scHandle];
				}

				return null;

			default:
				// returns value or array of values
				return $this->_filterPayloadCustomData($scHandle);
		}
	}

	// returns string value or array of strings values (select or checkboxes)
	private function _filterPayloadCustomData($scHandle) {
		$cfd = $this->settings['custom_field_definitions'];
		$found_cfd_key = array_search($scHandle, array_column($cfd, 'name'));
		$found_value_key = array_search($scHandle, array_column($this->payload['data']['custom_fields'], 'key'));
		if ($found_cfd_key === false || $found_value_key === false) return null;

		switch ($cfd[$found_cfd_key]['type']) {
			case 'select':
			case 'checkbox':
				return explode(',', $this->payload['data']['custom_fields'][$found_value_key]['value']);
			default:
				return $this->payload['data']['custom_fields'][$found_value_key]['value'];
		}
	}
}