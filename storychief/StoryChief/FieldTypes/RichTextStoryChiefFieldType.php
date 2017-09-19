<?php namespace Craft;

class RichTextStoryChiefFieldType implements StoryChiefFieldTypeInterface {

	public function supportedStorychiefFieldTypes() {
		return [
			'richtext'
		];
	}

	public function prepFieldData(FieldModel $field, $fieldData) {
		$preppedData = $fieldData;
		return $preppedData;
	}
}