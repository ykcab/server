<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Files_Sharing\BackgroundJob;

use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\DailyJob;
use OCP\IDBConnection;
use \OCP\Notification\IManager as NotificationManager;

class ExiprationNotificationJob extends DailyJob {
	/** @var NotificationManager */
	private $notificationManager;
	/** @var IDBConnection */
	private $connection;

	public function __construct(ITimeFactory $time,
								NotificationManager $notificationManager,
								IDBConnection $connection) {
		parent::__construct($time);

		// Run twice a day
		$this->setInterval(12*60*60);
		$this->notificationManager = $notificationManager;
		$this->connection = $connection;
	}

	protected function run($argument) {
		//Current time
		$minTime = $this->time->getDateTime();
		$minTime->add(new \DateInterval('P1D'));
		$minTime->setTime(0,0,0);

		$maxTime = clone $minTime;
		$maxTime->setTime(23, 59, 59);

		/*
		 * Expire file link shares only (for now)
		 */
		$qb = $this->connection->getQueryBuilder();
		$qb->select('id', 'file_source', 'uid_owner', 'uid_initiator', 'item_type')
			->from('share')
			->where(
				$qb->expr()->andX(
					$qb->expr()->gte('expiration', $qb->expr()->literal($minTime->getTimestamp())),
					$qb->expr()->lte('expiration', $qb->expr()->literal($maxTime->getTimestamp())),
					$qb->expr()->orX(
						$qb->expr()->eq('item_type', $qb->expr()->literal('file')),
						$qb->expr()->eq('item_type', $qb->expr()->literal('folder'))
					)
				)
			);

		$shares = $qb->execute();
		$now = $this->time->getDateTime();
		while($share = $shares->fetch()) {
			$notification = $this->notificationManager->createNotification();
			$notification->setApp('files_sharing')
				->setDateTime($now)
				->setObject('share', (string)$share['id'])
				->setSubject('expiresTomorrow');

			// Only send to initiator for now
			$notification->setUser($share['uid_initiator']);
			$this->notificationManager->notify($notification);
		}
		$shares->closeCursor();
	}


}
