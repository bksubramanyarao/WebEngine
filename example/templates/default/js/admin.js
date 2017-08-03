
var App = (function(){
	var init = function() {
		$('select[name=table]').on('change', function() {
			getTable($(this).val());
		});
	}
	
	var getTable = function(table) {
		$.ajax({
			url: '/api/' + table + '/',
			dataType: 'json',
			success: function(json) {
				console.log(json);
			}
		});
	}
	
	init();
})();