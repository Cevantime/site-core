var $ = require('jquery');

function parsePagination () {
	
	var $paginations = $('.pagination.paginationAjax:not(.parsed)');
	$paginations.addClass('parsed');
	$paginations.each(function(){
		var $pagination = $(this);
		var dataContainer = $pagination.data('container');
		if (dataContainer === 'undefined') {
			var container = $pagination.parent();
		} else {
			var container = $(dataContainer);
		}
		var linksPagination = $pagination.find('a');
		linksPagination.click(function (e) {
			e.preventDefault();
			var target_action = $(this).attr('href');
			$.ajax({
				url: target_action,
				success: function (html) {
					var height = container.height();
					container.replaceWith(html);
					if (dataContainer === 'undefined') {
						container = $pagination.parent();
					} else {
						container = $(dataContainer);
					}
					$('html,body').animate({
						scrollTop: $('html,body').scrollTop() + container.height() - height
					}, 'fast');
					parsePagination();
				}
			});
		})
		
	});
	
}

$(function(){
	parsePagination();
	
});

