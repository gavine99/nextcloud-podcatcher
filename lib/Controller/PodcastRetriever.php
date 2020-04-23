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

class PodcastRetriever {
  private $config;
	private $rootFolder;
  private $db;

  private $mimetypes = array(
      'audio/mpeg' => 'mp3',
      'audio/mp4' => 'mp4',
      'audio/x-mpegurl' => 'm3u',
      'audio/ogg' => 'ogg',
      'audio/vorbis' => 'ogg',
      'video/mp4' => 'mp4',
      'video/3gpp' => '3gp',
      'video/quicktim' => 'mov',
      'video/x-msvideo' => 'avi',
      'video/x-ms-wmv' => 'wmv',
  );

  public function __construct($config, $rootFolder, $db) {
    $this->config = $config;
    $this->rootFolder = $rootFolder;
    $this->db = $db;
  }

  public function getPodcasts($userId) {
    $retVal = true;

    // get base path
    $basepath = $this->config->getUserValue($userId, 'podcatcher', 'path');

    // get all podcasts for given user in db
    $stmt = $this->db->prepare("SELECT `id`, `url`, `subfolder`, `last_item` FROM `*PREFIX*podcatcher` WHERE `user_id` = ? ORDER BY `id`");
    $stmt->execute( [ $userId ] );

    // iterate podcasts
    foreach ($stmt->fetchAll() as $podcast) {
      // load podcast feed xml
      if (!($xml = simplexml_load_file($podcast['url']))) {
        $this->logErr($podcast['id'], "The podcast URL ${podcast['url']} could not be loaded");
        $retVal = false;
        continue;
      }

      // iterate podcast items
      foreach ($xml->channel->item as $item) {
        // catch all exceptions
        try {
          $itemTime = strtotime((string)$item->pubDate);
          if ($itemTime <= $podcast['last_item'])
            continue;

          // build filename components
          $pubDate = date("Y-m-d-H-i-s", $itemTime);
          $title = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', (string)$item->title);

          // convert mime-type to a file extension - default to .mp3 in case mime type not found
          $extension = 'mp3';
          $mimetype = $item->enclosure['type'];
          foreach ($this->mimetypes as $mt=>$ext) {
            if ($mt == $mimetype) {
              $extension = $ext;
              break;
            }
          }

          $filename = "{$pubDate} {$title}.{$extension}";

          // ensure directory exists in file store
          $userRoot = $this->rootFolder->getUserFolder($userId);
          if ($userRoot->nodeExists("{$basepath}/{$podcast['subfolder']}") === false)
            $userRoot->newFolder("{$basepath}/{$podcast['subfolder']}");

          $podcastFolder = $userRoot->get("{$basepath}/{$podcast['subfolder']}");

          // if file exists, remove it in case of a previous bad download
          if ($podcastFolder->nodeExists($filename) === true)
            $podcastFolder->get($filename)->delete();

          // create new file
          $podcastFile = $podcastFolder->newFile($filename);

          // start http download
          $response = (new \GuzzleHttp\Client())->get((string)$item->enclosure['url']);

          // write http response contents to file
          $podcastFile->putContent($response->getBody()->getContents());

          // update time of last item downloaded
          $this->db->prepare("UPDATE `*PREFIX*podcatcher` SET `last_item` = ? WHERE `user_id` = ? AND `id` = ?")->execute([ $itemTime, $userId, $podcast['id'] ] );
        }
        catch (Exception $e) {
          $this->logErr($podcast['id'], $e->message);
          $retVal = false;
        }
      }
    }

    return $retVal;
  }

  function logErr($id, $errText) {
    $this->db->prepare("UPDATE `*PREFIX*podcatcher` SET `last_err_timestamp` = ?, `last_err` = ? WHERE id = ?")->execute( [ time(), $errText, $id ] );
  }
}