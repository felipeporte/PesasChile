function countdownproduct() {
	$( ".time_count_down" ).each(function( index ) {
		
		var date_y = $(this).find('.time_countdown').attr('data-date-y');
		var date_m = $(this).find('.time_countdown').attr('data-date-m');
		var date_d = $(this).find('.time_countdown').attr('data-date-d');
		var date_h = $(this).find('.time_countdown').attr('data-date-h');
		var date_mi = $(this).find('.time_countdown').attr('data-date-mi');
		var date_s = $(this).find('.time_countdown').attr('data-date-s');
		//console.log(date_y +'---'+date_m+'------'+date_d+'----'+date_h+'---'+date_mi+'-----'+date_s);
		$(this).children().countdown({
			until: new Date(date_y,date_m-1,date_d,date_h,date_mi,date_s),
		});

	});
}

$(document).ready(function () { 
		countdownproduct()
});
