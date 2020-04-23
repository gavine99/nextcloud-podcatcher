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

namespace OCA\podcatcher\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IURLGenerator;
use OCP\Settings\ISettings;
use OCP\IConfig;
use OCP\IDbConnection;

class Personal implements ISettings
{
  private $userId;
  private $urlGenerator;
  private $configManager;
  private $appName = "podcatcher";

  public function __construct(
      $userId,
      IConfig $configManager,
      IURLGenerator $urlGenerator,
      IDbConnection $db)
  {
      $this->userId = $userId;
      $this->configManager = $configManager;
      $this->urlGenerator = $urlGenerator;
      $this->db = $db;
  }

  public function getForm() {
    // get user-level options
    $params = [
        'path' => $this->configManager->getUserValue($this->userId, $this->appName, 'path'),
        'interval' => $this->configManager->getUserValue($this->userId, $this->appName, 'interval')
    ];

    // select data for all podcasts for the user
    $stmt = $this->db->prepare('SELECT `id`, `url`, `subfolder`, `last_err_timestamp`, `last_err` FROM `*PREFIX*podcatcher` WHERE `user_id` = ? ORDER BY id');
    $stmt->execute( [ $this->userId ] );
    $params['podcasts'] = \array_map(function($podcast) {
        if ($podcast['last_err_timestamp'] !== null)
          $podcast['last_err_time_text'] = $this->timeElapsed('@' . $podcast['last_err_timestamp']) . ': ';
        return $podcast;
      },
      $stmt->fetchAll()
    );

    // delete errors now that they've been shown to the user
    $this->db->prepare('UPDATE `*PREFIX*podcatcher` SET `last_err` = null, `last_err_timestamp` = null WHERE `user_id` = ? ORDER BY id')->execute( [ $this->userId ] );

    return new TemplateResponse($this->appName, 'Settings/personalsettings', $params, '');
  }

  public function getPanel() {
    return $this->getForm();
  }

  public function getSection() {
    return $this->appName;
  }

  public function getSectionID() {
    return $this->appName;
  }

  public function getPriority() {
    return 10;
  }

  protected function timeElapsed($datetime, $full = false) {
    // method from https://stackoverflow.com/questions/1416697/converting-timestamp-to-time-ago-in-php-e-g-1-day-ago-2-days-ago
    $now = new \DateTime;
    $ago = new \DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = \floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full)
      $string = \array_slice($string, 0, 1);

    return $string ? \implode(', ', $string) . ' ago' : 'just now';
  }
}
