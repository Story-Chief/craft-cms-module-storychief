<?php namespace Craft;

class StoryChiefVariable {

	public function getStoryChiefSections() {
		$sections = [];
		foreach (craft()->sections->getAllSections() as $section) {
			if ($section->type === 'channel') {
				$sections[] = [
					'label' => $section->name,
					'value' => $section->id,
				];
			}
		}

		return $sections;
	}

	public function getAllStoryChiefFields() {
		$default_fields = [
			[
				'label' => 'Content',
				'name'  => 'content',
				'type'  => 'richtext',
			],
			[
				'label' => 'Excerpt',
				'name'  => 'excerpt',
				'type'  => 'textarea',
			],
			[
				'label' => 'Featured image',
				'name'  => 'featured_image',
				'type'  => 'image',
			],
			[
				'label' => 'Tags',
				'name'  => 'tags',
				'type'  => 'tags',
			],
			[
				'label' => 'Categories',
				'name'  => 'categories',
				'type'  => 'categories',
			],
			[
				'label' => 'SEO Title',
				'name'  => 'seo_title',
				'type'  => 'text',
			],
			[
				'label' => 'SEO Description',
				'name'  => 'seo_description',
				'type'  => 'textarea',
			],
		];
		$settings = craft()->plugins->getPlugin('storyChief')->getSettings();
		$custom_fields = $settings->getAttribute('custom_field_definitions');

		return array_merge($default_fields, $custom_fields);
	}

	public function getStoryChiefFieldOptions($fieldHandle) {
		$field = craft()->fields->getFieldByHandle($fieldHandle);
		$class = str_replace(array('Craft\\', 'FieldType'), array('\\Craft\\', 'StoryChiefFieldType'), get_class($field->getFieldType()));
		$allFields = $this->getAllStoryChiefFields();
		$options = [];
		if (class_exists($class)) {
			$field = new $class();
			if ($field instanceof StoryChiefFieldTypeInterface) {
				$supportedTypes = $field->supportedStorychiefFieldTypes();
				foreach ($allFields as $item) {
					if (in_array($item['type'], $supportedTypes)) {
						$options[] = [
							'label' => $item['label'],
							'value' => $item['name'],
						];
					}
				}
			}
		}

		return empty($options) ? null : $options;
	}

	public function getStoryChiefAuthorOptions() {
		return [
			[
				'label' => 'Don\'t import',
				'value' => '',
			],
			[
				'label' => 'Import',
				'value' => 'import',
			],
			[
				'label' => 'Import or create new',
				'value' => 'create',
			]
		];
	}

	public function getStoryChiefEntryTypes($sectionID) {
		$entryTypes = [];
		foreach (craft()->sections->getEntryTypesBySectionId($sectionID) as $entryType) {
			$entryTypes[] = [
				'label' => $entryType->name,
				'value' => $entryType->id,
			];
		}

		return $entryTypes;
	}

	public function getStoryChiefContentFields($entryTypeID) {
		$fieldDefinitions = [];

		$entryType = craft()->sections->getEntryTypeById($entryTypeID);
		$fields = craft()->fields->getLayoutById($entryType->getAttribute('fieldLayoutId'))->getFields();

		foreach ($fields as $field) {
			$fieldDefinition = $field->getField()->getAttributes(['id', 'type', 'name', 'handle']);
			$fieldDefinition['required'] = $field->getAttribute('required') === '1';
			$fieldDefinitions[] = $fieldDefinition;
		}

		return $fieldDefinitions;
	}
}
