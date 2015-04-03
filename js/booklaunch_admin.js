(function($){
	$(function() {
		$('#page_template').change(function() {
			$('#booklaunch_options').toggle($(this).val() == 'page-booklaunch.php');
			$('#postdivrich').toggle($(this).val() != 'page-booklaunch.php');
		}).change();
	});
})(jQuery);