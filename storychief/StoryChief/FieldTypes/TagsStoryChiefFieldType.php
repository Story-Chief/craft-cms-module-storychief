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
		foreach ($fieldData as $tagName) {
			$tag = craft()->elements->getCriteria(ElementType::Tag, [
				'groupId' => $field->getGroup()->id,
				'title'   => $tagName,
			])->first();
			if (!$tag) {
				$tag = new TagModel();
				$tag->groupId = $field->getGroup()->id;
				$tag->getContent()->title = $tagName;

				craft()->tags->saveTag($tag);
			}
			$preppedData[] = $tag->id;
		}

		return $preppedData;
	}
}