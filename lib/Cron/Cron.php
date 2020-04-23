<?php
/**
 * own/nextCloud - Podcatcher app
 *
 * Copyright 2020 Gavin E
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Gavin E <don't@email.me.please>
 **/

namespace OCA\podcatcher\Cron;

use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserManager;
use OCP\IDbConnection;
use OCP\Files\IRootFolder;
use OCP\IConfig;

class Cron extends TimedJob {
  private $userManager;
	private $db;
  private $rootFolder;
  private $config;

  public function __construct(
    ITimeFactory $time,
    IUserManager $userManager,
    IDbConnection $db,
    IRootFolder $rootFolder,
    IConfig $config
  ) {
    parent::__construct($time);
    $this->userManager = $userManager;
		$this->db = $db;
    $this->rootFolder = $rootFolder;
    $this->config = $config;

    // check for any users that need podcasts downloaded each hour
    // (one cron job for all users rather than cron-per-user)
    $this->setInterval(3600);
  }

  public function run($arguments) {
    // iterate all users
    $this->userManager->callForSeenUsers(function(\OC\User\User $user) {
      $now = time();

      // if user has not set an update interval, don't update
      $interval = $this->config->getUserValue($user->getUID(), 'podcatcher', 'interval');
      if (empty($interval) === true)
        return;

      // convert interval to seconds
      $interval *= (60 * 60);

      // if never updated before, set to do update now
      $lastUpdated = $this->config->getUserValue($user->getUID(), 'podcatcher', 'lastupdated');
      if (empty($lastUpdated) === true)
        $lastUpdated = ($now - $interval);

      // if not due to update for this user, don't update now
      if (($now - $lastUpdated) < $interval)
        return;

      (new \OCA\podcatcher\Controller\PodcastRetriever($this->config, $this->rootFolder, $this->db))->getPodcasts($user->getUID());

      $this->config->setUserValue($user->getUID(), 'podcatcher', 'lastupdated', $now);
    });
  }
}
