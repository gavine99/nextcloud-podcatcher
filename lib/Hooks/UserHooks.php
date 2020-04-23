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

namespace OCA\podcatcher\Hooks;

use OCP\IUserManager;
use OCP\IDbConnection;

class UserHooks {
  private $userManager;
  private $db;

  public function __construct(IUserManager $userManager, IDbConnection $db){
    $this->userManager = $userManager;
    $this->db = $db;
  }

  public function register() {
    $callback = function(\OC\User\User $user) {
      $this->db->prepare('DELETE FROM `*PREFIX*podcatcher` WHERE `user_id` = ?')->execute( [ $user->getUID() ] );
    };
    $this->userManager->listen('\OC\User', 'postDelete', $callback);
  }
}