
var App = (function(){
	var state = {
		fields: [],
		records: []
	};
	
	var init = function() {
		$('select[name=table]').on('change', function() {
			getTable($(this).val());
		});
	}
	
	var getTable = function(table) {
		$.ajax({
			url: '/admin/api/' + table + '/',
			dataType: 'json',
			success: function(json) {
				state.fields = json.fields;
				state.records = json.records;
				render(["table"]);
			}
		});
	}
	
	var render = function(section) {
		if (section.indexOf("table") > -1) {
			// render table
			var html = '';
			html += '<table>';
			html += '	<thead>';
			html += '		<tr>';
			for (var field in state.fields) {
				html += '<th>' + field + '</th>';
			}
			html += '			<th></th>';
			html += '		</tr>';
			html += '	</thead>';
			html += '	<tbody>';
			for (var record in state.records) {
				html += '<tr>';
				for (var field in state.fields) {
					html += '<td>' + state.records[record][field] + '</td>';
				}
				html += '</tr>';
			}
			html += '	</tbody>';
			html += '</table>';
			$('#tablecontent').html(html);
		}
	}
	
	init();
})();