//window.ajax is localized.

jQuery(document).ready(function($){

    var sync = false;

    var postFor = function(action){
        return function(){
            if (sync) { return; };
            sync = true;
            $('#' + action + ' i.fa')
                .addClass('fa-spinner fa-pulse');

            $.post(ajax.endpoint, {
                action: ajax.actions[action],
                nonce: ajax.nonce
            }, function(res){
                replaceITMcontent(res);
                $('#' + action + ' i.fa')
                    .removeClass('fa-spinner fa-pulse')
                sync = false;
            });
        };
    };

    var replaceITMcontent = function(html){
        $('#ITMContent')
            .after($(html))
            .remove();
        $('#inherit').click(postFor('inherit'));
        $('#restore').click(postFor('restore'));
};


$('#inherit').click(postFor('inherit'));
$('#restore').click(postFor('restore'));


});
