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

use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class PersonalSection implements IIconSection {
    private $urlGenerator;
    private $appName =  "podcatcher";

    public function __construct(IURLGenerator $urlGenerator) {
      $this->urlGenerator = $urlGenerator;
    }

    public function getIcon() {
      return $this->urlGenerator->imagePath($this->appName, 'app.svg');
    }

    public function getID() {
      return $this->appName;
    }

    public function getName() {
      return ucfirst($this->appName);
    }

    public function getPriority() {
      return 10;
    }
}