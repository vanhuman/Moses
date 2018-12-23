(function(){
    var controls = get_controls(),
        feedback = get_feedback(),
        i,
        checkbox_name = "diff_input",
        current_checkboxes = [],
        load_button_observer = new MutationObserver(function(mutations){
            mutations.forEach(function(mutation){
                if (diff_button.classList.contains("set")) {
                    for (i = 0; i < mutation.addedNodes.length; i++) {
                        var new_node = mutation.addedNodes.item(i);
                        if (new_node.querySelector && new_node.querySelector("dl") !== null) {
                            add_diff_checkboxes(new_node);                         
                        }
                    }
                }
            });
        });
    
    /*
     * INITIATION
     */
    var diff_button = document.createElement("button");
    diff_button.setAttribute("id", "diff_button");
    diff_button.textContent = "Diff";
    diff_button.diff_elements = new Map;
    diff_button.addEventListener("click", function(event){
        if ( diff_button.classList.contains("set") ) {
            diff_button.classList.remove("set");
            remove_diff_checkboxes();
            compaire_elements(diff_button.diff_elements);
        } else {
            add_diff_checkboxes(feedback);
            diff_button.classList.add("set");
        }
    });        
    feedback.addEventListener("change", function(event){
        if (event.target.getAttribute("name") === checkbox_name) {
            if (event.target.checked === true) {
                add_node_to_diff(event.target.parentNode);
            } else {
                remove_node_from_diff(event.target.parentNode);
            }
        }
    });   
    controls.appendChild(diff_button);
    load_button_observer.observe(feedback, {
        childList : true,
        subtree : true
    });
    
    
    function add_diff_checkboxes( to_element )
    {
        var objects = to_element.getElementsByClassName('object'),
            object, checkbox;
    
        if (to_element.classList.contains('object')) {
            checkbox = get_new_diff_checkbox();
            to_element.insertBefore(checkbox, to_element.firstChild);
        }
        for (i = 0; i < objects.length; i++) {
            object = objects.item(i);
            checkbox = get_new_diff_checkbox();
            object.insertBefore(checkbox, object.firstChild);
        }
    }
    
    function get_new_diff_checkbox()
    {
        var checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.name = checkbox_name;
        current_checkboxes.push(checkbox);
        return checkbox;
    }
    
    function add_node_to_diff(Node)
    {
        diff_button.diff_elements.set(Node, Node);
    }
    
    function remove_node_from_diff(Node)
    {
        diff_butten.diff_elements.delete(Node);
    }
    
    function remove_diff_checkboxes()
    {
        for (i = 0; i < current_checkboxes.length; i++) {
            current_checkboxes[i].remove();
        }
    }
    
    function compaire_elements( elements )
    {
        if ( ! elements instanceof Map) {
            return;
        }
        
        var diff = document.createElement('div'),
            title = document.createElement('div'),
            result = document.createElement('div');
        
        diff.classList.add("diff_result");
        
        title.classList.add("tags");
        title.textContent = "Diff result";
        diff.appendChild(title);
        
        result.classList.add("result");
        diff.appendChild(result);
        
        var compairing = [],
            set_diff_holder = new diff_holder();
        
        elements.forEach(function(element)
        {            
            for (i = 0; i < compairing.length; i++) {
                complement_diff_with_difference(element, compairing[i], set_diff_holder);
            }
            compairing.push(element);
        });
        
        result.innerHTML = set_diff_holder.implode();        
        
        feedback.appendChild(diff);
    }
    
    function diff_holder()
    {
        var childern = [];
        
        this.add_child = function(diff) {
            if (this.node && this.node.nodeName.toLowerCase() === 'dl' 
                    && ( ! diff.node || (diff.node.nodeName.toLowerCase() !== 'dt' && diff.node.nodeName.toLowerCase() !== 'dd'))) {
                        diff.owntext_start = '<dt>diff content</dt><dd>' + diff.owntext_start;
                        diff.owntext_end += '</dd>';
            }
            childern.push(diff);
        };
        
        this.owntext_start = '';
        this.owntext_end = '';
        this.content = '';
        this.node;
        
        this.get_all = function()
        {
            if (this.node) {
                this.content = escapeHtml(this.node.outerHTML);
            }
        }
        
        this.implode = function()
        {
            for (var c = 0; c < childern.length; c++) {
                this.content += childern[c].implode();
            }
            
            if (this.content !== '') {
                return this.owntext_start + this.content + this.owntext_end;
            }
            return '';
        };
        
        this.fill_in_start_and_end = function( node )
        {
            var own_text = node.outerHTML.split('>' + node.innerHTML + '<');
            this.owntext_start = own_text[0] ? own_text[0] + '>' : '';
            this.owntext_end = own_text[1] ? '<' + own_text[1] : '';
            this.node = node;
        };
    }
    
    function diff_text_holder()
    {
        this.content_one = '';
        this.content_two = '';
        this.implode = function()
        {
            return '<div>'
                    + '<div class="tags">' + this.content_one + '</div>'
                    + '<div class="tags">' + this.content_two + '</div>'
                    + '</div>';
        };
    }
    
    function complement_diff_with_difference(first, second, parent_diff_holder)
    {
        var first_diff,
            second_diff,
            text_node;
        
        if (first instanceof diff_holder) {
            first_diff = first;
        } else {
            first_diff = get_diff_holder(first);
        }
        
        if (second instanceof diff_holder) {
            second_diff = second;
        } else {
            second_diff = get_diff_holder(second);
        }
        
        if (first_diff.owntext_start !== second_diff.owntext_start) {
            var base_diff = new diff_text_holder();
            base_diff.content_one = escapeHtml(first_diff.owntext_start);
            base_diff.content_two = escapeHtml(second_diff.owntext_start);
            parent_diff_holder.add_child(base_diff);
        }
        
        if (first_diff.content !== second_diff.content) {
            var content_diff = new diff_text_holder();
            content_diff.content_one = escapeHtml(first_diff.content);
            content_diff.content_two = escapeHtml(second_diff.content);
            parent_diff_holder.add_child(content_diff);
        } else {
            first_diff.content = '';
            second_diff.content = '';
        }
        
        var first_childs = get_childs_array(first_diff),
            second_childs = get_childs_array(second_diff),
            pairs = [], lonies = [], ci, cj;
        
        var found_dt = false;
        loop_first_childs:
            for (ci in first_childs) {
                if (first_childs[ci].node && first_childs[ci].node.nodeName.toLowerCase() === 'dt') {
                    found_dt = first_childs[ci].node.textContent.toString().trim();
                }
                for (cj in second_childs) {
                    if (typeof found_dt === "string" && second_childs[cj].node
                            && second_childs[cj].node.nodeName.toLowerCase() === 'dt'
                            && second_childs[cj].node.textContent.toString().trim() === found_dt) {
                        pairs.push([first_childs[ci], second_childs[cj]]);
                        found_dt = [found_dt, parseInt(cj) + 1];
                        delete first_childs[ci];
                        delete second_childs[cj];
                        continue loop_first_childs;                        
                    } else if (found_dt instanceof Array && cj === found_dt[1]) {
                        var prepend = '<dt>' + found_dt[0] + '</dt>';
                        first_childs[ci].owntext_start = prepend + first_childs[ci].owntext_start;
                        second_childs[cj].owntext_start = prepend + second_childs[cj].owntext_start;
                        pairs.push([first_childs[ci], second_childs[cj]]);
                        delete first_childs[ci];
                        delete second_childs[cj];
                        found_dt = false;
                        continue loop_first_childs;                        
                    }
                    if (is_interesting_diff_pair(first_childs[ci].node, second_childs[cj].node) === 2) {
                        pairs.push([first_childs[ci], second_childs[cj]]);
                        delete first_childs[ci];
                        delete second_childs[cj];
                        continue loop_first_childs;
                    }
                }
            }
        
        for (ci in first_childs) {
            for (cj in second_childs) {
                if (is_interesting_diff_pair(first_childs[ci].node, second_childs[cj].node) === 1) {
                    pairs.push([first_childs[ci], second_childs[cj]]);
                    delete first_childs[ci];
                    delete second_childs[cj];
                }
            }
        }
        
        for (ci in first_childs) {
            lonies.push(first_childs[ci]);
        }
        for (cj in second_childs) {
            lonies.push(second_childs[cj]);            
        }
        
        for (var p = 0; p < pairs.length; p++) {
            var parent = pairs[p][0];
            if (parent.node) {
                var nodename = parent.node.tagName.toLowerCase();                
                if (['button', 'input'].indexOf(nodename) !== false) {
                    parent = false;
                }
            } else {
                parent = false;
            }
            
            if (parent) {
                complement_diff_with_difference(pairs[p][0], pairs[p][1], parent);
                parent_diff_holder.add_child(parent);                                     
            } else {
                complement_diff_with_difference(pairs[p][0], pairs[p][1], parent_diff_holder);              
            }
        }
        
        for (var l = 0; l < lonies.length; l++) {
            lonies[l].get_all();
            parent_diff_holder.add_child(lonies[l]);
        }
    }
    
    function get_diff_holder( node )
    {
        first_diff = new diff_holder();
        if (node instanceof Text) {
            first_diff.content = node.wholeText;
        } else {
            first_diff.fill_in_start_and_end(node);
        }
        return first_diff;
    }
    
    function get_childs_array( diff )
    {
        var childs = {},
            childnodes, new_child;
        if (diff.node) {
            childnodes = diff.node.childNodes;
            for (var c = 0; c < childnodes.length; c++) {
                new_child = get_diff_holder(childnodes[c]);
                childs[c] = new_child;
            }            
        }
        return childs;
    }
    
    function is_interesting_diff_pair(first, second)
    {
        if ( !first && !second) {
            return 2;
        }
        if ( !first || !second) {
            return 0;
        }
        if (first.nodeName === second.nodeName && first.className === second.className) {
            return 2;
        }
        if (first.nodeName === second.nodeName) {
            return 1;
        }
        return 0;
    }
    
    function escapeHtml(text) {
        var map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        };

        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
      }
         
    
})();


