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


$(document).ready(function () {
  var $path = $('#podcatcher-path');
  $path.on('click', function () {
    OC.dialogs.filepicker(
      t('podcatcher', 'Select top folder for podcasts'),
      function (path) {
        if ($path.val() !== path) {
          $path.val(path);
          $.post(OC.generateUrl('apps/podcatcher/userpath'), { path: path }, function (data) {
            if (!data.success) {
              OC.Notification.showTemporary(t('podcatcher', 'Invalid path!'));
            } else {
              OC.Notification.showTemporary(t('podcatcher', 'Top directory saved'));
            }
          });
        }
      },
      false,
      'httpd/unix-directory',
      true, 
      OC.dialogs.FILEPICKER_TYPE_CHOOSE
    );
  });

  var $interval = $('#podcatcher-interval');
  $interval.on('blur', function () {
    $.ajax({
      type: 'POST',
      url: OC.generateUrl('apps/podcatcher/interval'),
      data: {
        'interval': $interval.val()
      },
      success: function (data) {
        if (!data.success) {
          OC.Notification.showTemporary(t('podcatcher', 'Couldn\'t save interval!'));
        } else {
          OC.Notification.showTemporary(t('podcatcher', 'Interval saved'));
        }
      }
    });
  });

  $('#podcatcher-clear').on('click', function () {
    $('#podcatcher-url').val('');
    $('#podcatcher-subfolder').val('');
    $('#podcatcher-url').focus();
  });

  $('#podcatcher-add').on('click', function () {
    $.ajax({
      type: 'POST',
      url: OC.generateUrl('apps/podcatcher/addpodcast'),
      data: {
        'url': $("#podcatcher-url").val(), 
        'subfolder': $("#podcatcher-subfolder").val()
      },
      success: function (data) {
        if (!data.success) {
          OC.Notification.showTemporary(t('podcatcher', 'Didn\'t save podcast!'));
        } else {
          OC.Notification.showTemporary(t('podcatcher', 'Podcast saved'));
          location.reload();
        }
      }
    });
  });

  $('.podcatcher-update').on('click', function () {
    var $row = $(this).parent().parent();
    $row.find('*').attr('disabled', true);
    $.ajax({
      type: 'POST',
      url: OC.generateUrl('apps/podcatcher/updatepodcast'),
      context: $row, 
      data: {
        'id': $row.attr('data-id'), 
        'url': $row.find(".podcatcher-url").val(), 
        'subfolder': $row.find(".podcatcher-subfolder").val()
      },
      success: function (data) {
        if (!data.success) {
          OC.Notification.showTemporary(t('podcatcher', 'Podcast not updated!'));
        } else {
          OC.Notification.showTemporary(t('podcatcher', 'Podcast updated'));
        }
        $(this).find('*').attr('disabled', false);
      }
    });
  });

  $('.podcatcher-remove').on('click', function () {
    var $row = $(this).parent().parent();
    $row.hide();
    $.ajax({
      type: 'POST',
      url: OC.generateUrl('apps/podcatcher/removepodcast'),
      context: $row, 
      data: {
        'id': $row.attr('data-id')
      },
      success: function (data) {
        if (!data.success) {
          OC.Notification.showTemporary(t('podcatcher', 'Podcast not removed!'));
          $(this).show();
        } else {
          OC.Notification.showTemporary(t('podcatcher', 'Podcast removed'));
          $(this).remove();
        }
      }
    });
  });

  $('#podcatcher-fetchnow').on('click', function () {
    $("#podcatcher-fetchnow").hide();
    $("#podcatcher-fetching").show();
    $.ajax({
      type: 'POST',
      url: OC.generateUrl('apps/podcatcher/fetch'),
      data: { },
      success: function (data) {
        if (!data.success) {
          OC.Notification.showTemporary(t('podcatcher', 'Podcasts not fetched!'));
          location.reload();
        } else {
          OC.Notification.showTemporary(t('podcatcher', 'Podcasts fetched'));
          $("#podcatcher-fetchnow").show();
          $("#podcatcher-fetching").hide();
        }
      }
    });
  });
});