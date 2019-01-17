jQuery(document).ready(function($) {

	$(".tab_content").hide();
	$(".tab_content:first").show(); 

	$("ul.tabs_slider_inner li span").click(function() {
		$("ul.tabs_slider_inner li").removeClass("active");
		$(this).parent().addClass("active");
		$(".tab_content").hide();
		var activeTab = $(this).parent().attr("rel"); 
		$("#"+activeTab) .fadeIn().addClass("animatetab");  
	});
	
	if(POS_HOME_PRODUCTTAB_PAGINATION==null || POS_HOME_PRODUCTTAB_PAGINATION =="") {POS_HOME_PRODUCTTAB_PAGINATION = false} else { POS_HOME_PRODUCTTAB_PAGINATION = true}
	if(POS_HOME_PRODUCTTAB_NAV==null || POS_HOME_PRODUCTTAB_NAV =="") {POS_HOME_PRODUCTTAB_NAV = false} else {POS_HOME_PRODUCTTAB_NAV = true}
	var test=[0, 1 ,2, 3];
	test.forEach(function(j) {
		$(".productTabContent" + j).owlCarousel({
			items:POS_HOME_PRODUCTTAB_ITEMS,
			smartSpeed: POS_HOME_PRODUCTTAB_SPEED,
			dots :POS_HOME_PRODUCTTAB_PAGINATION,
			nav : POS_HOME_PRODUCTTAB_NAV,
			scrollPerPage: true,
			responsive:{
				0:{
					items:1,
				},
				480:{
					items:1,
				},
				768:{
					items:1,
				},
				992:{
					items:2,
				},
				1200:{
					items:5,
				},
				1200:{
					items:POS_HOME_PRODUCTTAB_ITEMS,
				}
			}
		});
		
	});
});
