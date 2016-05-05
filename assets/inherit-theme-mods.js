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
            notifier = $('<span class="ITM-status-notifier"><i class="fa fa-spinner fa-spin fa-wf"></i><span class="ITM-status-notifier-text">' + ajax.status['updating..'] + '</span></span>')
                .hide()
                .appendTo($('#ITM-title'))
                .fadeIn(100);
            // ajax
            $.post(ajax.endpoint, {
                action: ajax.actions[action],
                nonce: ajax.nonce
            }, function(res){
                sync = false;
                // view update
                replaceITMcontent(res);

                // update notification
                notifier.fadeOut(50, function(){
                    notifier.remove();
                    notifier = $('<span class="ITM-status-notifier"><i class="fa fa-check fa-wf"></i><span class="ITM-status-notifier-text">' + ajax.status['finished!'] + '</span></span>')
                        .hide()
                        .appendTo($('#ITM-title'))
                        .fadeIn(50);
                    setTimeout(function(){
                        notifier.fadeOut(100, function(){
                            notifier.remove();
                        });
                    },1500);
                });
            });
        };
    };

    var replaceITMcontent = function(html){
        $('#ITM-Content')
            .after($(html))
            .remove();
        $('#ITM-inherit').click(postFor('inherit'));
        $('#ITM-restore').click(postFor('restore'));
    };

    $('#ITM-inherit').click(postFor('inherit'));
    $('#ITM-restore').click(postFor('restore'));

});
