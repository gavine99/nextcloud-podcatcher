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

namespace OCA\Podcatcher\Controller;

require("PodcastRetriever.php");

use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\IDbConnection;
use OCP\Files\IRootFolder;
use OCP\IConfig;

class SettingController extends Controller {
	private $userId;
	private $db;
  private $rootFolder;
  private $config;

	public function __construct(
      $AppName,
      IRequest $request,
      $UserId,
      IDbConnection $db,
      IRootFolder $rootFolder,
      IConfig $config
  ){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->db = $db;
    $this->rootFolder = $rootFolder;
    $this->config = $config;
	}

  /**
   * @NoAdminRequired
   * @param $path
   * @return JSONResponse
   * @throws
   */
  public function userPath($path) {
    try {
      $this->rootFolder->getUserFolder($this->userId)->get($path);
    } catch (\OCP\Files\NotFoundException $e) {
      return new JSONResponse(array('success' => false));
    }

    if ($path[0] !== '/')
      $path = '/' . $path;
    if ($path[strlen($path) - 1] !== '/')
      $path .= '/';

    $this->config->setUserValue($this->userId, $this->appName, 'path', $path);
    return new JSONResponse(array('success' => true));
  }

  /**
   * @NoAdminRequired
   * @param $interval
   * @return JSONResponse
   * @throws \OCP\PreConditionNotMetException
   */
	public function interval($interval) {
    $this->config->setUserValue($this->userId, $this->appName, 'interval', $interval);
		return new JSONResponse(array('success' => 'true'));
	}

  /**
   * @NoAdminRequired
   * @param $id
   * @param $url
   * @param $subfolder
   * @return JSONResponse
   * @throws \Doctrine\DBAL\DBALException
   */
	public function updatePodcast($id, $url, $subfolder) {
    $stmt = $this->db->prepare('UPDATE `*PREFIX*podcatcher` SET `url` = ?, `subfolder` = ?, `last_err_timestamp` = 0, `last_err` = NULL WHERE `user_id` = ? AND id = ?');
    $rows = $stmt->execute( [ $url, $subfolder, $this->userId, $id ] );
		return new JSONResponse(array('success' => ($rows > 0)));
  }

  /**
   * @NoAdminRequired
   * @param $id
   * @return JSONResponse
   * @throws \Doctrine\DBAL\DBALException
   */
	public function removePodcast($id) {
    $stmt = $this->db->prepare('DELETE FROM `*PREFIX*podcatcher` WHERE `user_id` = ? AND id = ?');
		return new JSONResponse(array('success' => ($stmt->execute( [ $this->userId, $id ] ) === true)));
  }

  /**
   * @NoAdminRequired
   * @param $url
   * @param $subfolder
   * @return JSONResponse
   * @throws \Doctrine\DBAL\DBALException
   */
	public function addPodcast($url, $subfolder) {
    $stmt = $this->db->prepare('INSERT INTO `*PREFIX*podcatcher` (`user_id`, `url`, `subfolder`) VALUES (?, ?, ?)');
    $rows = $stmt->execute( [ $this->userId, $url, $subfolder ] );
		return new JSONResponse(array('success' => ($rows > 0)));
  }

  /**
   * @NoAdminRequired
   * @return JSONResponse
   * @throws \Doctrine\DBAL\DBALException
   */
	public function fetch() {
    $retVal = true;
    try {
      (new PodcastRetriever($this->config, $this->rootFolder, $this->db))->getPodcasts($this->userId);
    }
    catch (Exception $e) {
      $retVal = false;
    }

    return new JSONResponse(array('success' => ($retVal === true)));
  }
}
