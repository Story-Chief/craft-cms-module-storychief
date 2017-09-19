<?php namespace Craft;

class PlainTextStoryChiefFieldType implements StoryChiefFieldTypeInterface {

	public function supportedStorychiefFieldTypes(){
		return [
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