<?php namespace Craft;

class DropdownStoryChiefFieldType implements StoryChiefFieldTypeInterface {

	public function supportedStorychiefFieldTypes() {
		return [
			'select'
		];
	}

	public function prepFieldData(FieldModel $field, $fieldData) {
		$preppedData = null;

		if (empty($fieldData) || empty($fieldData[0])) return null;

		$settings = $field->getFieldType()->getSettings();
		$options = $settings->getAttribute('options');

		foreach ($options as $option) {
			if ($fieldData[0] == $option['value']) {
				$preppedData = $option['value'];
				break;
			}
		}

		return $preppedData;
	}
}