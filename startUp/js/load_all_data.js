(function($){
    var controls = get_controls(),
        feedback = get_feedback(),
        i,
        currently_loading = false,
        checkbox_name = "load_all_data",
        current_checkboxes = [],
        load_button_observer = new MutationObserver(function(mutations){
            mutations.forEach(function(mutation){
                if (diff_button.classList.contains("set")) {
                    for (i = 0; i < mutation.addedNodes.length; i++) {
                        var new_node = mutation.addedNodes.item(i);
                        if (new_node.querySelector && new_node.querySelector("button") !== null) {
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
    diff_button.setAttribute("id", "load_all_data_button");
    diff_button.textContent = "Load Data";
    diff_button.diff_elements = new Map;
    diff_button.addEventListener("click", function(event){
        if ( diff_button.classList.contains("set") ) {
            diff_button.classList.remove("set");
            remove_diff_checkboxes();
            diff_button.classList.add("loading");
            currently_loading = true;
            load_all_data(diff_button.diff_elements);
        } else if ( diff_button.classList.contains("loading") ) {
            diff_button.classList.remove("loading");
            currently_loading = false;
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
    
    function load_all_data( elements ) {
        if ( ! elements instanceof Map) {
            return;
        }
        
        elements.forEach(function(element)
        {
            load_all_element_data(element);
        });
    }
    
    function load_all_element_data (element) {
        var buttons = $(element).find('button[data-id]');
        if (buttons.length) {
            buttons.click();
            if (currently_loading) {
                setTimeout(function(){
                    load_all_element_data(element);
                }, 1000);                
            }
        }
    }
    
    
    })(jQuery);