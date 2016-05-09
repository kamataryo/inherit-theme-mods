# **notice**
$ = jQuery # Don't complile me with {bare:true}

updateInstantNotifier = (classes) ->
    deferred = $.Deferred()

    $container= $ '#ITM-instant-notifier'
    if $container.children().length is 0
        msecToApper = 0
    else
        msecToApper = 50

    $container.fadeOut msecToApper, ->
        $(this).empty()
        $(this).fadeIn 50, ->
            $ "<i class=\"#{classes.join ' '}\"></i>"
                .hide()
                .appendTo $(this)
                .fadeIn 50, ->
                    deferred.resolve()

    return  deferred.promise()


clearInstantNotifier = ->
    deferred = $.Deferred()

    $('#ITM-instant-notifier').fadeOut 50, ->
        $(this).empty()

    return  deferred.promise()



updateNotifier = (type, innerHTML) ->
    deferred = $.Deferred()

    $('#ITM-notifier')
        .removeClass()
        .addClass 'notice'
        .addClass "notice-#{type}"
        .empty()
        .append $ "<p>#{innerHTML}</p>"
        .fadeIn 50, ->
            deferred.resolve()

    return  deferred.promise()


clearNotifier = ->
    deferred = $.Deferred()

    $ '#ITM-notifier'
        .fadeOut 50, ->
            $(this).empty()
            deferred.resolve()

    return  deferred.promise()


makeRequest = (action) ->
    deferred = $.Deferred()

    $.post ajax.endpoint, {action, nonce: ajax.nonce}, ({success, data}) ->
        if success
            deferred.resolve(data)
        else
            deferred.reject(data)

    return deferred.promise()


updateTable = (data) ->
    deferred = $.Deferred()

    $table = $('#ITM-Content>table.wp-list-table>tbody')
    $table.fadeOut 100, ->
        $table.children('tr').each (i, tr) ->
            $(tr).children('td:not(.column-key)').each (i, td) ->
                $span = $(td).children 'span.ITM-list-data'
                [key, col] = [$span.data('key'), $span.data('col')]
                cols = data.filter (item, index) -> item.native_key is key
                $(td).html cols[0][col]

        $table.fadeIn 200, ->
            deferred.resolve()

    return deferred.promise()



$(document).ready ($) ->
    sync = false # prevent multiple request
    [timerId1, timerId2] = []

    $('.ITM-button').click ->
        if sync then return else sync = true

        clearTimeout timerId1
        clearTimeout timerId2
        clearNotifier()
        clearInstantNotifier()

        $.when.apply null, [
            makeRequest $(this).data 'action'
            updateInstantNotifier ['fa', 'fa-spinner', 'fa-spin', 'fa-wf']
        ]
            .done updateTable
            .done ->
                updateNotifier 'success', ajax.status.success
                updateInstantNotifier ['fa', 'fa-check', 'fa-wf']
                    .done ->
                        timerId1 = setTimeout ->
                            clearInstantNotifier()
                        , 2000

            .fail (message) ->
                updateNotifier 'error', message
                updateInstantNotifier ['fa', 'fa-warning', 'fa-wf']
                    .done ->
                        timerId2 = setTimeout ->
                            clearInstantNotifier()
                        , 2000
            .always -> sync = false
