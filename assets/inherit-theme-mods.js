(function() {
  var $, clearNotifier, makeRequest, updateNotifier, updateTable;

  $ = jQuery;

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
    var sync;
    sync = false;
    return $('.ITM-button').click(function() {
      if (sync) {
        return;
      } else {
        sync = true;
      }
      $('body').css('cursor', 'wait');
      updateNotifier('success', ajax.status.updating);
      return makeRequest($(this).data('action')).done(updateTable).done(function() {
        return updateNotifier('success', ajax.status.success);
      }).fail(function(message) {
        return updateNotifier('error', message != null ? message : ajax.status.unknownError);
      }).always(function() {
        sync = false;
        return $('body').css('cursor', 'default');
      });
    });
  });

}).call(this);
