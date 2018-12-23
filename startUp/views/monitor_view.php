<?php
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Monitor</title>
		<link href="css/monitor.css" type="text/css" rel="stylesheet"/>
		<script>
			var control,
				feedback;

			function get_controls()
			{
				if (control == null) {
					control = document.getElementById("controls");
				}
				return control;
			}

			function get_feedback()
			{
				if (feedback == null) {
					feedback = document.getElementById("feedback");
				}
				return feedback;
			}
		</script>
	</head>
	<body>
		<div id="main">
			<div id="controls">
				<button id="start">Start</button>
				<button id="stop">stop</button>
				<button id="clear">clear</button>
			</div>
			<div id="feedback"></div>
		</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<!--<script src="js/monitor_all_type.js"></script>-->
		<!--<script src="js/monitor_container_type.js"></script>-->
		<!--<script src="js/monitor_array_type.js"></script>-->
		<script src="js/monitor_object_type.js"></script>
		<script src="js/get_diff.js"></script>
		<script src="js/load_all_data.js"></script>
		<script src="js/sort.js"></script>
		<!--<script src="js/monitor_simple_types.js"></script>-->
		<script>
			(function(){
				var api_get = null;
				function start(){
					if (api_get) {
                                            return;
					}
                                        api_get = true;
					$.get('/api.php/record/').always(function(){
						call_get_every_so_much_milliseconds(5000);
					});
				}

				function call_get_every_so_much_milliseconds(milliseconds) {
                                    if (!api_get) {
                                        return;
                                    }
					let cycle_done = false;
					let timeout_done = false;
					setTimeout(function(){
							if (cycle_done) {
								call_get_every_so_much_milliseconds(milliseconds);
							}
							timeout_done = true;						
					}, milliseconds);
					get().then(function(){						
						cycle_done = true;
						if (timeout_done) {
							call_get_every_so_much_milliseconds(milliseconds);
						}
					});
				}

				var delete_old_files_now = 60;
				var get = function(){
					var delete_now = delete_old_files_now === 60 ? '1' : '0';
					var promise = new Promise(function(done, fail){
						$.get('/api.php/get_latest/', {
							delete_old : delete_now
						}, success, 'json').always(function(){
							done();
						});
						delete_old_files_now--;
						if (delete_old_files_now < 1) {
							delete_old_files_now = 60;
						}
					});
					return promise;
				};
				function success(result){
					html_result = create_html_result(result);
					if (! html_result ) {
						return;
					}

					$('body #feedback').append(html_result).css('border', 'solid 1px red');
					setTimeout(function(){
						$('#feedback').css('border', 'none');
					}, 300);
					process_new_result();
				};
				$('#start').on('click', function(){
					start();
				});
				$('#stop').on('click', function(){
					if (api_get) {
						api_get = false
						$.get('/api.php/stop/');
					}
				});
				$('#clear').on('click', function(){
					$('#feedback').empty();
					$.get('/api.php/delete_all/', '');
				});

				function create_html_result( result ) {

					if (! result || ! (result instanceof Array) && typeof result !== "string" ) {
						return false;
					}

					var result_array = result;
					if (typeof result === "string") {
						result_array = JSON.parse(result);
					}
					if (!result_array || result_array.length == 0) {
						return false;
					}

					var second = $('<div class="second">');
					var pairs = [];
					var add = 2;
					for (var ra = 0; ra < result_array.length; ra = ra + add) {
						if (result_array[ra] !== undefined && result_array[ra].tags && result_array[ra + 1] !== undefined) {
                            add = 2;
							pairs.push({
                                metaData : result_array[ra],
                                data : result_array[ra + 1]
                            });
                        } else {
							add = 1;
							pairs.push({
								metaData : {
									pid : 'unkown',
                                    index : 0,
                                },
								data : result_array[ra]
							});
                        }
					}
					pairs.sort(function(firstPair, secondPair) {
						return firstPair.metaData.index < secondPair.metaData.index ? -1 : 1;
                    });

					pairs.forEach(function(pair) {
						var tag = parse_tag_from_result(pair.metaData.tags);
						var content = parse_content_from_result(pair.data, tag);
						var pidColor = getPidColor(pair.metaData.pid);
						var span = $('<div data-pid="'+pair.metaData.pid+'">').css({
                            'border-left' : '5px solid rgb(' + pidColor.join(', ') + ')',
                            'padding-left' : '5px'
                        }).addClass('pid-part');
						span.append(content)
						second.append(span);
                    });

					return second;
				}

				var pidColorCache = {};
				function getPidColor(pid) {
					function getColorPart() {
						return Math.ceil(Math.random() * 255);
                    }
					if (!pidColorCache[pid]) {
						pidColorCache[pid] = [getColorPart(), getColorPart(), getColorPart()];
                    }
                    return pidColorCache[pid];
                }

				function parse_content_from_result(content, tag) {
					var content_html;
					if (content instanceof Array) {
						content_html = parse_array_from_result(content);
					} else if (content instanceof Object) {
						content_html = parse_object_from_result(content);
					} else {
						content_html = parse_simple_from_result(content);
					}
					if (tag) {
						content_html.prepend(tag);
					}
					return content_html;
				}
				window.parse_content_from_result = parse_content_from_result;

				function parse_tag_from_result( tag ) {
					return $('<div class="tags">').html(tag instanceof Array ? tag.join(', ') : tag);
				}

				function parse_array_from_result(array, type) {
					var array_html = $('<dl class="array real">');
					for (var i = 0; i < array.length; i++) {
						array_html.append($('<dt>').append(parse_simple_from_result(i)))
								.append($('<dd>').append(parse_content_from_result(array[i])));
					}
					return array_html;
				}

				function parse_array_from_result_object(object) {
					var array_html = $('<dl class="array">');
					for (var v = 0; v < object.value.length; v++) {
						var dt = $('<dt>').append(parse_content_from_result(object.value[v].key));
						array_html.append(dt)
								.append($('<dd>').append(parse_content_from_result(object.value[v].value)));

					}
					return array_html;
				}

				function parse_object_from_result (object) {
					if (object.type && object.type.toString().toLowerCase() === 'array') {
						return parse_array_from_result_object(object);
					}
					object_html = Object_parser.parse(object);
					return object_html;
				}

				

				function parse_simple_from_result (simple) {
					if (simple == null) {
						return $('<div class="null">').append($('<div class="content">').text("null"));
					} else if (typeof simple === "string") {
						return $('<div class="string">').append($('<div class="content">').html(
										simple.replace(/\\n/g, '<br>')
												.replace(/<(?!br>)/, '&lt;')
									)
								);
					} else if (typeof simple === "number") {
						return $('<div class="number">').append($('<div class="content">').text(simple));
					} else if (typeof simple === "boolean") {
						return $('<div class="boolean">').append($('<div class="content">').text(simple ? 'true' : 'false'));
					} else {
						return $('<div class="unkown">').append($('<div class="content">').text(simple.toString()));
					}
				}
				window.parse_simple_from_result = parse_simple_from_result;

				//function replace_new_lines

				var process_new_result = function(){
					$('#feedback>div:not(.processed)').each(process_new_result_div);
				};

				var process_new_result_div = function( index, item ) {
					var self = $(item);

					if (self.hasClass('error')) {
						create_foldable( self );
					} else {
						self.addClass('processed');
					}
					self.find('.error, .object, .array').each(function (i, c){
						create_foldable( $(c) );
					});
				};

				var create_foldable = function( item ) {
					var container = $('<div class="container processed">');
					var controls = $('<div class="controls">').append($('<button>').html('flip').on('click', function(){
						$(this).closest('.container').find('>.contents').toggle();
					}));
					item.replaceWith(container.append(controls).append(item.addClass('contents').clone(true).hide()));
				};

				$(document).on('click', '.open_netbeans_link', function(event){
					event.preventDefault();
					if (event.currentTarget.href) {
						$.get(event.currentTarget.href);
					}
				});
			})();
		</script>
	</body>
</html>

