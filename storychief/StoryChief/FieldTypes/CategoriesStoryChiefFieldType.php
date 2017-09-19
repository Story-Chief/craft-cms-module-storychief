<?php namespace Craft;

class CategoriesStoryChiefFieldType implements StoryChiefFieldTypeInterface {

	public function supportedStorychiefFieldTypes() {
		return [
			'categories',
			'select',
			'checkbox'
		];
	}

	public function prepFieldData(FieldModel $field, $fieldData) {
		$preppedData = [];
		$categoryGroupID = str_replace('group:', '', $field->getAttribute('settings')['source']);

		$limit = count($fieldData);
		if (isset($field->settings['limit']) && $field->settings['limit']) {
			$limit = min($limit, $field->settings['limit']);
		}
		$i = 0;
		while ($i < $limit) {
			$categoryName = $fieldData[$i];

			$category = craft()->elements->getCriteria(ElementType::Category, [
				'groupId' => $categoryGroupID,
				'title'   => $categoryName,
			])->first();
			if (!$category) {
				$category = new CategoryModel();
				$category->groupId = $categoryGroupID;
				$category->getContent()->title = $categoryName;

				craft()->categories->saveCategory($category);
			}
			$preppedData[] = $category->id;

			$i++;
		}

		return $preppedData;
	}
}