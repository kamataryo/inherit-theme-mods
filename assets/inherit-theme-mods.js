//window.ajax is localized.

jQuery(document).ready(function($){

    var sync = false;

    var postFor = function(action){
        return function(){
            // disable if updating
            if (sync) { return; };
            sync = true;
            // add notification
            var notifier;
            notifier = $('<span class="ITM-status-notifier ITM-aside"><i class="fa fa-spinner fa-spin fa-wf"></i><span class="ITM-aside ITM-status-notifier-text">' + ajax.status['updating..'] + '</span></span>')
                .hide()
                .appendTo($('#ITM-title'))
                .fadeIn(100);
            // ajax
            $.post(ajax.endpoint, {
                action: ajax.actions[action],
                nonce: ajax.nonce

            }, function(data){
                console.log(data);
                sync = false;
                // view update
                $('#ITM-Content>table.wp-list-table>tbody').fadeOut(200, function(){
                    replaceITMcontent(data, function(){
                        $('#ITM-Content>table.wp-list-table>tbody').fadeIn(300);

                        // update notification
                        notifier.fadeOut(50, function(){
                            notifier.remove();
                            notifier = $('<span class="ITM-status-notifier ITM-aside"><i class="fa fa-check fa-wf"></i><span class="ITM-aside  ITM-status-notifier-text">' + ajax.status['finished!'] + '</span></span>')
                                .hide()
                                .appendTo($('#ITM-title'))
                                .fadeIn(50);
                            setTimeout(function(){
                                notifier.fadeOut(200, function(){
                                    notifier.remove();
                                    $('.ITM-visit-site').css('display', 'block');
                                });
                            },2000);
                        });
                    });
                });
            });
        };
    };

    //match json from ajax and table elements, then exchange them.
    var replaceITMcontent = function(data, callback){
        $('#ITM-Content>table.wp-list-table>tbody>tr')
            .each(function(i, tr){
                $(tr).children('td:not(.column-key)').each(function(i, td){
                    var $span = $(td).children('span.ITM-list-data');
                    var key = $span.data('key');
                    var col = $span.data('col');
                    var cols = (data.filter(function(item, index){return item.native_key === key; }))[0];
                    $(td).html(cols[col]);
                });
            });
        if ( typeof callback === 'function' ) {
            callback();
        }
    };



    $('#ITM-inherit').click(postFor('inherit'));
    $('#ITM-restore').click(postFor('restore'));

});
