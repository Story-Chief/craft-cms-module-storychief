<?php namespace Craft;

class MultiSelectStoryChiefFieldType implements StoryChiefFieldTypeInterface {

	public function supportedStorychiefFieldTypes(){
		return [
			'select'
		];
	}

	public function prepFieldData(FieldModel $field, $fieldData) {
		$preppedData = [];

		if (empty($fieldData)) return $preppedData;
		if (!is_array($fieldData)) $fieldData = array($fieldData);

		$settings = $field->getFieldType()->getSettings();
		$options = $settings->getAttribute('options');

		foreach ($options as $option) {
			foreach ($fieldData as $dataValue) {
				if ($dataValue == $option['value']) {
					$preppedData[] = $option['value'];
				}
			}
		}

		return $preppedData;
	}
}