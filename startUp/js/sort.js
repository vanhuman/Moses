(function($){
	var controls = $('#controls');
	var feedback = $('#feedback');
	function addSortButton() {
		var sortOnPid = $('<button>').html('per pid').on('click', onSortOnPid);
		controls.append(sortOnPid);
	}
	function onSortOnPid() {
		var seconds = feedback.children('.second');
		var pidParts = seconds.children();
		var perPid = {};
		pidParts.each(function(i ,pidElement) {
			var pid = pidElement.dataset.pid;
			if (pid) {
				if (! perPid[pid]) {
					perPid[pid] = [];
				}
				perPid[pid].push(pidElement);
			}
		});
		Object.getOwnPropertyNames(perPid).forEach(function(pid) {
			var pidContainer = feedback.children('.pid-container[pid=' + pid + ']')[0];
			if (!pidContainer) {
				pidContainer = getNewContainer(pid);
			}
			var partsContainer = pidContainer.children('.parts').get(0);

			perPid[pid].forEach(function(pidElement){
				partsContainer.append(pidElement);
			});
		});
	}

	function getNewContainer(pid) {
		var pidContainer = $('<div>').addClass('pid-container').data('pid', pid);
		var pidControls = $('<div>').addClass('pid-controlls');
		pidControls.prepend($('<button>').html('delete').on('click', function() {
			pidContainer.remove();
		}));
		var parts = $('<div>').addClass('parts');
		var name = $('<input>').on('change', function() {
			var val = this.value;
			if (!val || !val.trim()) {
				val = 'toggle';
			}
			toggle.html(val);
		});
		var toggle = $('<button>').html('toggle').on('click', function(){
			parts.toggle();
			name.toggle();
		});
		pidControls.append(toggle).append(name);
		pidContainer.append(pidControls).append(parts).insertAfter(feedback);
		return pidContainer;
	}



	addSortButton();
})(jQuery);