//window.ajax is localized.

jQuery(document).ready(function($){

    var nonce = $('input[name="' + ajax.nonceField + '"]').val();


    var postFor = function(action){
        return function(){
            $.post(ajax.endpoint, {
                action: ajax.actions[action],
                nonce: nonce
            }, function(res){
                replaceITMcontent(res);
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
