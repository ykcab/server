<?php
declare(strict_types=1);
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

namespace OCP\DirectEditing;

use OCP\AppFramework\Http\Response;
use OCP\Files\File;
use OCP\Files\NotPermittedException;
use RuntimeException;

interface IManager {

	/**
	 * Register a new editor
	 *
	 * @param IEditor $directEditor
	 */
	public function registerDirectEditor(IEditor $directEditor): void;

	/**
	 * Open the editing page for a provided token
	 *
	 * @param string $token
	 * @return Response
	 */
	public function edit(string $token): Response;

	/**
	 * @param string $path
	 * @param string $editorId
	 * @param string $creatorId
	 * @param null $templateId
	 * @return string
	 * @throws NotPermittedException
	 * @throws RuntimeException
	 */
	public function create(string $path, string $editorId, string $creatorId, $templateId = null): string;

	/**
	 * Get the token details for a given token
	 * @param string $token
	 * @return IToken
	 */
	public function getToken(string $token): IToken;

	/**
	 * Cleanup expired tokens
	 */
	public function cleanup(): void;

}

