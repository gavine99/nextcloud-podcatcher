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

return [
  'routes' => [
    ['name' => 'setting#userpath', 'url' => '/userpath', 'verb' => 'POST'],
    ['name' => 'setting#interval', 'url' => '/interval', 'verb' => 'POST'],
    ['name' => 'setting#addpodcast', 'url' => '/addpodcast', 'verb' => 'POST'],
    ['name' => 'setting#updatepodcast', 'url' => '/updatepodcast', 'verb' => 'POST'],
    ['name' => 'setting#removepodcast', 'url' => '/removepodcast', 'verb' => 'POST'],
    ['name' => 'setting#fetch', 'url' => '/fetch', 'verb' => 'POST'],
  ]
];
