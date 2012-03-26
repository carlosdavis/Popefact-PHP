$(function() {
	var watchFuncs = function(){
		$('.popefacts ul:first-child li.first').css("right", "100%").hide().animate({
				"opacity": "toggle",
				"right": "50%",
			},
			750
		); 
		$('.popefacts ul:first-child li.second').css("left", "100%").hide().animate({
				"opacity": "toggle",
				"left": "50%",
			},
			750
		); 
		$('#fact-input').blur(function() {
			var saveValue = $(this).val(); 
			$.ajax({
				url: "/save",
				global: true,
				type: "POST",
				data: {
					value: saveValue
				}, 
				success: function(result) {
					$('#fact-form').html(result).hide().fadeIn("slow");
					
					$.ajax({
							url: "/view",
							global: false,
							type: "POST", 
							data: {
								refresh: 1
							},
							success: function(result) {
								$('.popefacts').html(result); 
							}
					}); 
				}
			});
		}); 

	}
	
	$('a.help, #help a.close').click(function() {
		$('#help').animate({"opacity" : "toggle", "height" : "toggle"}, "medium"); 
	}); 


	watchFuncs();   
	$(window).bind("ajaxSuccess", watchFuncs); 
	
}); 