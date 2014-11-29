function page(action, container) {
    $.ajax({
        url: action,
        success: function(response) {
            $('#' + container).html(response);
			$('html, body').animate({
				scrollTop: $('#'+container).offset().top
			}, 500);
        }
    });
}


