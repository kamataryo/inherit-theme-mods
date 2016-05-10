(function() {
  var $, clearInstantNotifier, clearNotifier, makeRequest, updateInstantNotifier, updateNotifier, updateTable;

  $ = jQuery;

  updateInstantNotifier = function(classes) {
    var $container, deferred, msecToApper;
    deferred = $.Deferred();
    $container = $('#ITM-instant-notifier');
    if ($container.children().length === 0) {
      msecToApper = 0;
    } else {
      msecToApper = 50;
    }
    $container.fadeOut(msecToApper, function() {
      $(this).empty();
      return $(this).fadeIn(50, function() {
        return $("<i class=\"" + (classes.join(' ')) + "\"></i>").hide().appendTo($(this)).fadeIn(50, function() {
          return deferred.resolve();
        });
      });
    });
    return deferred.promise();
  };

  clearInstantNotifier = function() {
    var deferred;
    deferred = $.Deferred();
    $('#ITM-instant-notifier').fadeOut(50, function() {
      return $(this).empty();
    });
    return deferred.promise();
  };

  updateNotifier = function(type, innerHTML) {
    var deferred;
    deferred = $.Deferred();
    $('#ITM-notifier').removeClass().addClass('notice').addClass("notice-" + type).empty().append($("<p>" + innerHTML + "</p>")).fadeIn(50, function() {
      return deferred.resolve();
    });
    return deferred.promise();
  };

  clearNotifier = function() {
    var deferred;
    deferred = $.Deferred();
    $('#ITM-notifier').fadeOut(50, function() {
      $(this).empty();
      return deferred.resolve();
    });
    return deferred.promise();
  };

  makeRequest = function(action) {
    var deferred;
    deferred = $.Deferred();
    $.post(ajax.endpoint, {
      action: action,
      nonce: ajax.nonce
    }, function(arg) {
      var data, success;
      console.log(arg);
      success = arg.success, data = arg.data;
      if (success == null) {
        deferred.reject();
      }
      if (success) {
        return deferred.resolve(data);
      } else {
        return deferred.reject(data);
      }
    });
    return deferred.promise();
  };

  updateTable = function(data) {
    var $table, deferred;
    deferred = $.Deferred();
    $table = $('#ITM-Content>table.wp-list-table>tbody');
    $table.fadeOut(100, function() {
      $table.children('tr').each(function(i, tr) {
        var foundValues;
        foundValues = false;
        $(tr).children('td:not(.column-key)').each(function(i, td) {
          var $span, col, cols, key, ref;
          $span = $(td).children('span.ITM-list-data');
          ref = [$span.data('key'), $span.data('col')], key = ref[0], col = ref[1];
          cols = data.filter(function(item, index) {
            return item.native_key === key;
          });
          if (cols.length > 0 && (cols[0][col] != null)) {
            foundValues = foundValues || true;
            if ($(td).html !== cols[0][col]) {
              return $(td).html(cols[0][col]);
            }
          }
        });
        if (foundValues) {
          return $(tr).show();
        } else {
          return $(tr).hide();
        }
      });
      return $table.fadeIn(200, function() {
        return deferred.resolve();
      });
    });
    return deferred.promise();
  };

  $(document).ready(function($) {
    var ref, sync, timerId1, timerId2;
    sync = false;
    ref = [], timerId1 = ref[0], timerId2 = ref[1];
    return $('.ITM-button').click(function() {
      if (sync) {
        return;
      } else {
        sync = true;
      }
      $('body').css('cursor', 'wait');
      clearTimeout(timerId1);
      clearTimeout(timerId2);
      clearNotifier();
      clearInstantNotifier();
      return $.when.apply(null, [makeRequest($(this).data('action')), updateInstantNotifier(['fa', 'fa-spinner', 'fa-spin', 'fa-wf'])]).done(updateTable).done(function() {
        updateNotifier('success', ajax.status.success);
        return updateInstantNotifier(['fa', 'fa-check', 'fa-wf']).done(function() {
          return timerId1 = setTimeout(function() {
            return clearInstantNotifier();
          }, 2000);
        });
      }).fail(function(message) {
        updateNotifier('error', message != null ? message : ajax.status.unknownError);
        return updateInstantNotifier(['fa', 'fa-warning', 'fa-wf']).done(function() {
          return timerId2 = setTimeout(function() {
            return clearInstantNotifier();
          }, 2000);
        });
      }).always(function() {
        sync = false;
        return $('body').css('cursor', 'default');
      });
    });
  });

}).call(this);
