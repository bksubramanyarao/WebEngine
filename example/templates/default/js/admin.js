
var App = (function(){
	var state = {
		currentTableName: '',
		fields: [],
		records: [],
		selectedId: 0
	};
	
	var init = function() {
		$('select[name=table]').on('change', function() {
			getTable($(this).val());
		});
	}
	
	var getTable = function(table) {
		if (table != "") {
			$.ajax({
				url: '/admin/api/' + table + '/',
				dataType: 'json',
				success: function(json) {
					state.currentTableName = table;
					state.fields = json.fields;
					state.records = json.records;
					render();
				}
			});
		} else {
			state.currentTableName = '';
			state.fields = [];
			state.records = [];	
			state.selectedId = 0;	
			render();
		}
	}
	
	var getFormHtml = function(fieldname, fieldtype) {
		var html = '';
		switch (fieldtype) {
			case "INTEGER" :
				html += '<input type="number" name="' + fieldname + '" />';
				break;
			case "TEXT" :
				html += '<input type="text" name="' + fieldname + '" />';
				break;
			case "DEFAULT" :
				
		}
		return html;
	}
	
	var render = function() {
		// render #tablecontent
		$('.editBtn').off('click');
		$('.deleteBtn').off('click');
		
		if (state.selectedId == 0) {
			var html = '';
			html += '<table>';
			html += '	<thead>';
			html += '		<tr>';
			for (var field in state.fields) {
				html += '<th>' + field + '</th>';
			}
			html += '			<th>actions</th>';
			html += '		</tr>';
			html += '	</thead>';
			html += '	<tbody>';
			for (var record in state.records) {
				html += '<tr data-id="' + state.records[record].id + '">';
				for (var field in state.fields) {
					html += '<td>' + state.records[record][field] + '</td>';
				}
				html += '	<td><button class="btn editBtn">edit</button><button class="btn deleteBtn">delete</button></td>';
				html += '</tr>';
			}
			html += '	</tbody>';
			html += '</table>';
			$('#tablecontent').html(html).show();
			
			$('.editBtn').on('click', function() {
				state.selectedId = $(this).closest('tr').data('id');
				render(['table']);
			});
			
			$('.deleteBtn').on('click', function() {
				
			});
		} else {
			$('#tablecontent').html('').hide();
		}
		
		// render #breadcrumb
		$('.goToTableBtn').off('click');
		
		if (state.selectedId == 0) {
			$('#breadcrumb').html('').hide();
		} else {
			var html = '';
			html += '<button class="goToTableBtn">' + state.currentTableName + '</button>';
			html += ' / ' + state.selectedId;
			$('#breadcrumb').html(html).show();
			
			$('.goToTableBtn').on('click', function() {
				state.selectedId = 0;
				render();
			});
		}
		
		// render #detailform
		$('#detailform form').off('submit');
	
		if (state.selectedId == 0) {
			$('#detailform').html('').hide();
		} else {
			var html = '';
			html += '<form action="#" method="post">';
			for (var field in state.fields) {
				html += '<div class="formrow">';
				html += '	<div class="formlabel">' + field + '</div>';
				html += '	<div class="formfield">' + getFormHtml(field, state.fields[field]) + '</div>';
				html += '</div>';
			}
			html += '	<button>submit</button>';
			html += '</form>';
			$('#detailform').html(html).show();
			
			// populate fields
			var record = state.records[state.selectedId];
			for (var field in state.fields) {
				$('#detailform input[name=' + field + ']').val(record[field]);
			}
			$('#detailform input[name=id]').attr('disabled', 'disabled');
			
			$('#detailform form').on('submit', function(evt) {
				evt.preventDefault();
				
				// obtain a new data object
				var data = {};
				var arr = $(this).serializeArray();
				for (var i = 0; i < arr.length; i++) {
					data[arr[i].name] = arr[i].value;
				}
				
				$.ajax({
					url: '/admin/api/' + state.currentTableName + '/' + state.selectedId + '/',
					type: 'post',
					data: data,
					success: function(json) {
						for (var field in state.fields) {
							if (field != 'id') {
								state.records[state.selectedId][field] = data[field];
							}
						}
						state.selectedId = 0;
						render();
					},
					error: function() {
						alert('something went wrong!');
					}
				});
			});
		}		
		
	}
	
	init();
})();