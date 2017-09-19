<?php namespace Craft;

interface StoryChiefFieldTypeInterface {

	public function supportedStorychiefFieldTypes();

	public function prepFieldData(FieldModel $field, $fieldData);
}