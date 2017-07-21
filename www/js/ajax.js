$(document).ready(function() {
    Sorting();
    Searching();

    // Karma vote
    $('button[id^="karma-vote"]').click(function() {
        url = $(this).data('url');
        vote = $(this).data('vote');
        hash = $(this).data('hash');
        $('#snippet--overview-karma').load(url+'/materials/overview/'+hash+'/?vote='+vote+' #snippet--overview-karma');
    });
});

var Sorting = function() {
    // Kategorie
    $('button[id^="materials_category"]').click(function() {
        data = $(this).data('cat');
        url = $(this).data('url');
        if (data == 'ALL') {
            $('#snippet--materials_list').load(url+' #snippet--materials_list');
        }
        else {
            $('#snippet--materials_list').load(url+data+' #snippet--materials_list');
        }
    });
};

var Searching = function() {
    $('#search_clear').click(function() {
        var si = $('#search_input');
        si.val('');
        si.focus();
        $('#snippet--materials_list').load(url+' #snippet--materials_list');
    });

    $('#search_input').keypress(function() {
        v = $(this).val();
        url = $(this).data('url');
        if (v == '') {
            $('#snippet--materials_list').load(url+' #snippet--materials_list');
        }
        else {
            $('#snippet--materials_list').load(url+'?search='+v+' #snippet--materials_list');
        }
    });
};