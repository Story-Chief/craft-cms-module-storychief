<?php namespace Craft;

class AssetsStoryChiefFieldType implements StoryChiefFieldTypeInterface {

	public function supportedStorychiefFieldTypes() {
		return [
			'featured_image',
			'image',
		];
	}

	public function prepFieldData(FieldModel $field, $fieldData) {
		$folderId = $field->getFieldType()->resolveSourcePath();
		$preppedData = [];

		// get remote image and store in temp path
		$imageInfo = pathinfo($fieldData);
		$tempPath = CRAFT_STORAGE_PATH . 'runtime/temp/' . $imageInfo['basename'];
		file_put_contents($tempPath, fopen($fieldData, 'r'));

		// insert the file into assets
		$response = craft()->assets->insertFileByLocalPath(
			$tempPath,
			$imageInfo['basename'],
			$folderId, // notice, this is the id of the folder you want to upload to
			AssetConflictResolution::KeepBoth
		);

		// if the response is a success, get the file id
		if ($response && $response->isSuccess()) {
			$preppedData[] = $response->getDataItem('fileId');
		}

		return $preppedData;
	}
}