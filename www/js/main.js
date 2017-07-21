$(document).ready(function() {

    // Materials:Management:Remove
    var removeHash = '';
    var removeUrl = '';

    $('a[id^="askToRemove"]').click(function() {
        removeHash = $(this).data('dbHash');
        removeUrl = $(this).data('urlRemove');
    });

    $('#yesRemove').click(function() {
        console.log(removeHash);
        window.location.replace(removeUrl);
        removeHash = '';
    });

    // Management:Upload

    $("#fileupload").fileinput({
        language: 'cz'
    });

    // Other

    var clipboard = new Clipboard('#copy_button');
    clipboard.on('success', function(e) {
        console.info('Action:', e.action);
        console.info('Text:', e.text);
        console.info('Trigger:', e.trigger);

        e.clearSelection();
    });

    clipboard.on('error', function(e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    //graph();
});

//var graph = function() {
//    Morris.Donut({
//        element: 'graph',
//        data: JSON.parse($('#json_data').html()),
//        formatter: function (x) { return x }
//    }).on('click', function(i, row){
//        console.log(i, row);
//    });
//};

