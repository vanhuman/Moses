/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function register_and_install_object() {
    this.parse = function(object) {
        var object_html = $('<div class="object">');
        var properties = $('<dl class="properties">');
        if (object.type && object.type.toString().toLowerCase() === 'object') {
            var reference_id = object.id ? object.id.toString().replace(/.*?-(\d+)$/, '$1') : null;
            object_html.data({
                    id : object.id,
                    file : object.file
            }).attr('data-reference_id', reference_id).append($('<div class="class">').html(object.class));
            var properties_count = object.properties ? object.properties.length : 0;
            for (var p = 0; p < properties_count; p++) {
                    add_property_to_dl(properties, object.properties[p].field, object.properties[p].value, object.properties);
            }
            if (object.ancestor && object.ancestor.id) {
                object_html.append($('<div class="ancestor">').append(parse_content_from_result(object.ancestor)));
            }
        } else {
            if (object.id) {
                    // object can be loaded when clicking on it
                    var new_button = $('<button data-id="' + object.id + '">').html('laad').on('click load_data', loadfromfile);
                    return $('<div>').append(new_button);
            }
            // else its an unkown
            for (var key in object) {
                    properties.append($('<dt>').append(parse_simple_from_result(key)))
                                    .append($('<dd>').append(parse_content_from_result(object[key])));
            }
        }
        object_html.append(properties);
        return object_html;
    }
    
    function add_property_to_dl(dl, field, value, all) {
        if (field === 'file' && typeof value === 'string' && value.indexOf('/') !== -1) {
            value_html = get_file_link(value, all);
        } else {
            value_html = parse_content_from_result(value);
        }
        dl.append($('<dt>').append(parse_content_from_result(field)))
                        .append($('<dd>').append(value_html));
        
    }
    
    function get_file_link(file, all) {
        line = '';
        for (var p = 0; p < all.length; p++) {
            if (all[p].field === 'line') {
                line = ':' + all[p].value;
                break;
            }
        }
        return $('<a>').attr({
            href : '/api.php/open_netbeans/?file=' + encodeURIComponent(file + line),
            class : "open_netbeans_link"
        }).html(parse_content_from_result(file));
    }
    
    var loadedfiles = {};
    function loadfromfile( ev ) {
            var button = $(ev.target);
            var id = button.data('id');
            if (loadedfiles[id]) {
                    loadfilesuccess(loadedfiles[id]);
                    return;
            }
            $.get('/api.php/reference/' +  id, '', function (response) {
                    if ( ! response.data || ! response.data.id) {
                            button.html('mislukt');
                            return;
                    }

                    var content = parse_content_from_result(response.data);
                    button.replaceWith(content);
                    var maxwidth = 0;
                    content.find('dt').each(function(i, item){
                            var width = $(item).width();
                            if (width > maxwidth) {
                                    maxwidth = width;
                            }
                    }).width(maxwidth);
            }, 'json');
    }
}

Object_parser = new register_and_install_object();


