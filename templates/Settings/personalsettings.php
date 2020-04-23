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

script('podcatcher', 'personalsettings');
style('podcatcher', 'personalsettings');
?>

<div class="section" id="podcatcher">
  <div>
    <input type="button" id="podcatcher-fetchnow" value="Fetch all podcasts now"/>
    <img src="/core/img/loading-dark.gif" id="podcatcher-fetching" style="display: none;"/>
  </div>

  <h1>
    General settings
  </h1>

  <div>
    <label for="podcatcher-path"><?php p($l->t('Top directory for podcasts')); ?>:</label>
    <input type="text" id="podcatcher-path" value="<?php p($_['path']); ?>"/>
  </div>

  <div>
    <label for="podcatcher-interval"><?php p($l->t('Hours between podcasts auto updating')); ?>:</label>
    <input type="text" id="podcatcher-interval" value="<?php p($_['interval']); ?>"/>
  </div>

  <h1>
    Podcast settings
  </h1>

  <div>
    <table>
      <thead>
      <th>Podcast URL</th>
      <th>Sub-folder</th>
      <th>Action</th>
      <th>Last error</th>
      </thead>
      <tbody>
        <?php foreach($_['podcasts'] as $podcast): ?>
        <tr class="podcast" data-id="<?php p($podcast['id']); ?>">
          <td><input type="text" class="podcatcher-url" value="<?php p($podcast['url']); ?>"/></td>
          <td><input type="text" class="podcatcher-subfolder" value="<?php p($podcast['subfolder']); ?>"/></td>
          <td><input type="button" class="podcatcher-update" value="Update"/> &nbsp;<input type="button" class="podcatcher-remove" value="Remove"/></td>
          <td class="last_err"><?php p($podcast['last_err_time_text'] . $podcast['last_err']); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
          <td><input type="text" id="podcatcher-url"/></td>
          <td><input type="text" id="podcatcher-subfolder"/></td>
          <td><input type="button" id="podcatcher-clear" value="Clear"/>&nbsp;<input type="button" id="podcatcher-add" value="Add"/></td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
