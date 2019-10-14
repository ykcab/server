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


use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Response;
use OCP\DirectEditing\IManager;
use OCP\DirectEditing\RegisterDirectEditorEvent;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IRequest;

class DirectEditingViewController extends Controller {

	/** @var IManager */
	private $directEditingManager;

	public function __construct($appName, IRequest $request, IEventDispatcher $eventDispatcher, IManager $manager) {
		parent::__construct($appName, $request);

		$this->directEditingManager = $manager;
		$eventDispatcher->dispatch(RegisterDirectEditorEvent::class, new RegisterDirectEditorEvent($this->directEditingManager));
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 */
	public function edit(string $token): Response {
		return $this->directEditingManager->edit($token);
	}
}
