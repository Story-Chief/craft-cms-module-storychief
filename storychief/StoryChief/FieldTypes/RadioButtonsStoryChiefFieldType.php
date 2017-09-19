<?php namespace Craft;

class RadioButtonsStoryChiefFieldType implements StoryChiefFieldTypeInterface {

	public function supportedStorychiefFieldTypes() {
		return [
			'radio'
		];
	}

	public function prepFieldData(FieldModel $field, $fieldData) {
		$preppedData = null;

		if (empty($fieldData)) return null;

		$settings = $field->getFieldType()->getSettings();
		$options = $settings->getAttribute('options');

		foreach ($options as $option) {
			if ($fieldData == $option['value']) {
				$preppedData = $option['value'];
				break;
			}
		}

		return $preppedData;
	}
}