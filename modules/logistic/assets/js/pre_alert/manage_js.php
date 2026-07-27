<script>
(function($) {
"use strict";
	var alertServerParams = {
		"status": "[name='status[]']",
		"clients": "[name='clients[]']",
		"from_date": 'input[name="from_date"]',
        "to_date": 'input[name="to_date"]',
	    };


	var table_pre_alert = $('.table-pre_alert');    
	initDataTable('.table-pre_alert', window.location.href, [], [],
	        alertServerParams, [1, 'desc']);

	 $.each(alertServerParams, function(i, obj) {
        $('select' + obj).on('change', function() {  
            table_pre_alert.DataTable().ajax.reload()
                .columns.adjust();
        });
    });

	 $('input[name="from_date"]').on('change', function() {
        table_pre_alert.DataTable().ajax.reload()
                .columns.adjust();
    });
    $('input[name="to_date"]').on('change', function() {
        table_pre_alert.DataTable().ajax.reload()
                .columns.adjust();
    });

    set_hide_column('table-pre_alert', 'table-pre_alert_hide_column', false);

})(jQuery);

function set_hide_column(table_id, cookie_name, hide_fist_column){
    "use strict";
    var html = '';
    html += '<span class="sort-column">';
    html += '<a href="javascript:void(0)" class="selectBox" onclick="show_check_boxes(this)">';
    html += '<i class="fa fa-columns"></i>';
    html += '<div class="overSelect"></div>';
    html += '</a>';
    html += '<div id="list-checkboxes">';
    var list_tb_header = $('#'+table_id).find('tr th');
    for(let i = 0; i < list_tb_header.length; i++){
        if(hide_fist_column == true){
            if(i > 0){
                html += '<div class="checkbox-fade fade-in-primary">';
                html += '<label>';
                html += '<input type="checkbox" name="column['+i+']" id="column['+i+']" value="'+i+'" onchange="change_hidden_column(this,\''+table_id+'\',\''+cookie_name+'\')" checked>';
                html += '<span class="cr">';
                html += '<i class="cr-icon icofont icofont-ui-check txt-primary"></i>';
                html += '</span>';
                html += '<span>'+list_tb_header.eq(i).text()+'</span>';
                html += '</label>';
                html += '</div>';
            }
        }
        else{
            html += '<div class="checkbox-fade fade-in-primary">';
            html += '<label>';
            html += '<input type="checkbox" name="column['+i+']" id="column['+i+']" value="'+i+'" onchange="change_hidden_column(this,\''+table_id+'\',\''+cookie_name+'\')" checked>';
            html += '<span class="cr">';
            html += '<i class="cr-icon icofont icofont-ui-check txt-primary"></i>';
            html += '</span>';
            html += '<span>'+list_tb_header.eq(i).text()+'</span>';
            html += '</label>';
            html += '</div>';
        }
    }
    html += '</div>';
    html += '</span>';
    $('#'+table_id+'_wrapper').find('.dataTables_filter').append(html);
    set_hidden_column_from_ck(table_id, cookie_name, hide_fist_column);
}

function set_hidden_column_from_ck(table_id, cookie_name, hide_fist_column){
    "use strict";
    var table = $('#'+table_id).DataTable();
    if(hide_fist_column == true){
        table.column(0).visible( false, false );
    }
    var list_column_ck = getCookie(cookie_name);
    var id_list = list_column_ck.split(',');
    if(id_list.length > 0){
        var list_checkbox = $('#'+table_id+'_filter #list-checkboxes input[type="checkbox"]');
        for (let i = 0; i < list_checkbox.length; i++) {
            var obj = list_checkbox.eq(i);
            var obj_val = obj.val();
            let hide = 0;
            for (let j = 0; j < id_list.length; j++) {
                var index = id_list[j];
                if((index != '') && (index == obj_val)){
                    obj.prop('checked', false);
                    table.column(index).visible( false, false );
                    hide = 1;
                    break;
                }
            }
        }
    }
    table.columns.adjust().draw( false );
    document.getElementById(table_id).removeAttribute("style");
}

function getCookie(cname) {
    "use strict";
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function add_cookie(cname, cvalue, exdays) {
    "use strict";
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function show_check_boxes(el) {
    "use strict";
    var parent = $(el).closest('.sort-column');
    var dropdown = parent.find('#list-checkboxes');
    if (dropdown.hasClass('d-block')) {
        dropdown.removeClass('d-block');
    } else {
        dropdown.addClass('d-block');
    }
}

function change_hidden_column(el, table_id, cookie_name){
    "use strict";
    var table = $('#'+table_id).DataTable();
    var input = $(el);
    var value = input.val();
    var list_column_ck = getCookie(cookie_name);
    if(input.is(':checked')){
        var id_list = list_column_ck.split(',');
        list_column_ck = '';
        $.each(id_list, function(index, val) { 
            if((val != '') && (val != value)){
                list_column_ck += val+',';
            }
        });
        if(list_column_ck != ''){
            list_column_ck = rtrim(list_column_ck);
        }
        table.column(value).visible( true, true );
    }
    else{
        if(list_column_ck == ''){
            list_column_ck = value;
        }
        else{
            list_column_ck = list_column_ck+','+value;
        }
        table.column(value).visible( false, false );
    }

    table.columns.adjust().draw( false );
    document.getElementById(table_id).removeAttribute("style");
    add_cookie(cookie_name,list_column_ck,365);
}
function rtrim(str){
    "use strict";
    return str.replace(/\,+$/, '');
}
	
</script>