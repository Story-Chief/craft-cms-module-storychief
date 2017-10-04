<?php namespace Craft;

class EntriesStoryChiefFieldType implements StoryChiefFieldTypeInterface {

	public function supportedStorychiefFieldTypes() {
		return [
			'select'
		];
	}

	public function prepFieldData(FieldModel $field, $fieldData) {
		$preppedData = [];

		if (empty($fieldData)) return $preppedData;
		if (!is_array($fieldData)) $fieldData = array($fieldData);

		$settings = $field->getFieldType()->getSettings();

		// Get source id's for connecting
		$sectionIds = array();
		$sources = $settings->sources;

		if (is_array($sources)) {
			foreach ($sources as $source) {
				// When singles is selected as the only option to search in, it doesn't contain any ids...
				if ($source == 'singles') {
					foreach (craft()->sections->getAllSections() as $section) {
						$sectionIds[] = ($section->type == 'single') ? $section->id : '';
					}
				} else {
					list($type, $id) = explode(':', $source);
					$sectionIds[] = $id;
				}
			}
		} else if ($sources === '*') {
			$sectionIds = '*';
		}

		// Find existing
		foreach ($fieldData as $entry) {
			$criteria = craft()->elements->getCriteria(ElementType::Entry);
			$criteria->status = null;
			$criteria->sectionId = $sectionIds;
			$criteria->limit = $settings->limit;
			$criteria->id = DbHelper::escapeParam($entry);
			$elements = $criteria->ids();

			$preppedData = array_merge($preppedData, $elements);
		}

		// Check for field limit - only return the specified amount
		if ($preppedData) {
			if ($field->settings['limit']) {
				$preppedData = array_chunk($preppedData, $field->settings['limit']);
				$preppedData = $preppedData[0];
			}
		}

		// Check if we've got any data for the fields in this element
		if (isset($fieldData['fields'])) {
			$this->_populateElementFields($preppedData, $fieldData['fields']);
		}

		return $preppedData;
	}
}