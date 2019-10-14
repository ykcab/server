<?php
/**
 * @copyright Copyright (c) 2019 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Files\Controller;


use Exception;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\DirectEditing\ICreateEmpty;
use OCP\DirectEditing\ICreateFromTemplate;
use OCP\DirectEditing\IEditor;
use OCP\DirectEditing\IManager;
use OCP\DirectEditing\RegisterDirectEditorEvent;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IRequest;
use OCP\IURLGenerator;

class DirectEditingController extends OCSController {

	/** @var IManager */
	private $directEditingManager;

	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct($appName, IRequest $request, $corsMethods, $corsAllowedHeaders, $corsMaxAge,
								IEventDispatcher $eventDispatcher, IURLGenerator $urlGenerator, IManager $manager) {
		parent::__construct($appName, $request, $corsMethods, $corsAllowedHeaders, $corsMaxAge);

		$this->directEditingManager = $manager;
		$this->urlGenerator = $urlGenerator;
		$eventDispatcher->dispatch(RegisterDirectEditorEvent::class, new RegisterDirectEditorEvent($this->directEditingManager));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function get(): DataResponse {
		$capabilities = [
			'editors' => [],
			'creators' => []
		];

		/**
		 * @var string $id
		 * @var IEditor $editor
		 */
		foreach ($this->directEditingManager->getEditors() as $id => $editor) {
			$capabilities['editors'][$id] = [
				'name' => $editor->getName(),
				'mimetypes' => $editor->getMimetypes(),
				'optionalMimetypes' => $editor->getMimetypesOptional(),
				'secure' => $editor->isSecure(),
			];
			/** @var ICreateEmpty|ICreateFromTemplate $creator */
			foreach ($editor->getCreators() as $creator) {
				$id = $creator->getId();
				$capabilities['creators'][$id] = [
					'id' => $id,
					'name' => $creator->getName(),
					'extension' => $creator->getExtension(),
					'templates' => false
				];
				if ($creator instanceof ICreateFromTemplate) {
					$capabilities['creators'][$id]['templates'] = true;
				}

			}
		}
		return new DataResponse($capabilities);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function create(string $path, string $editorId, string $creatorId, $templateId = null): DataResponse {
		try {
			$token = $this->directEditingManager->create($path, $editorId, $creatorId, $templateId);
			return new DataResponse([
				'url' => $this->urlGenerator->linkToRouteAbsolute('files.DirectEditingView.edit', ['token' => $token])
			]);
		} catch (Exception $e) {
			return new DataResponse('Failed to create file', Http::STATUS_FORBIDDEN);
		}
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function open(int $fileId, string $editorId = null): DataResponse {
		$token = $this->directEditingManager->open($fileId, $editorId);
		return new DataResponse([
			'url' => $this->urlGenerator->linkToRouteAbsolute('files.DirectEditingView.edit', ['token' => $token])
		]);
	}



	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function templates(string $editorId, string $creatorId): DataResponse {
		return new DataResponse($this->directEditingManager->getTemplates($editorId, $creatorId));
	}
}
