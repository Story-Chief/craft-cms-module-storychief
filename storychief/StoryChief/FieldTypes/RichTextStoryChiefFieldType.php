<?php namespace Craft;

class RichTextStoryChiefFieldType implements StoryChiefFieldTypeInterface {

	public function supportedStorychiefFieldTypes() {
		return [
			'richtext',
			'text',
			'textarea',
			'excerpt'
		];
	}

	public function prepFieldData(FieldModel $field, $fieldData) {
		$preppedData = $fieldData;
		return $preppedData;
	}
}