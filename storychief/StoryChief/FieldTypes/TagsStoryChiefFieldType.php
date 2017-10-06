<?php namespace Craft;

class TagsStoryChiefFieldType implements StoryChiefFieldTypeInterface {

	public function supportedStorychiefFieldTypes() {
		return [
			'tags',
			'select',
			'checkbox'
		];
	}

	public function prepFieldData(FieldModel $field, $fieldData) {
		$preppedData = [];

		if (empty($fieldData)) return $preppedData;
		if (!is_array($fieldData)) $fieldData = array($fieldData);

		$settings = $field->getFieldType()->getSettings();

		// Get tag group id
		$source = $settings->getAttribute('source');
		list($type, $groupId) = explode(':', $source);

		// Find existing
		foreach ($fieldData as $tagName) {
			$criteria = craft()->elements->getCriteria(ElementType::Tag);
			$criteria->status = null;
			$criteria->groupId = $groupId;
			$criteria->title = DbHelper::escapeParam($tagName);

			$elements = $criteria->ids();

			$preppedData = array_merge($preppedData, $elements);

			// Create the elements if not found
			if (count($elements) == 0) {
				$element = new TagModel();
				$element->getContent()->title = $tagName;
				$element->groupId = $groupId;

				// Save tag
				if (craft()->tags->saveTag($element)) {
					$preppedData[] = $element->id;
				}
			}
		}

		return $preppedData;
	}
}