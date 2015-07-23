$(document).ready(function() {
    $('.tabBt').click(function() {
        var $this = $(this);
		$this.parent().children('.tabBt').removeClass('selected');
		$this.addClass('selected');
        var tabToDisplay = $this.data('display');
        var $tabbedPane = $this.parents('.tabbedPane');
        var $tabs = $tabbedPane.find('.tab');
        $tabs.css('display', 'none');
        $tabbedPane.find('#' + tabToDisplay).fadeIn('fast');
    });
});


