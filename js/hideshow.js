// JavaScript Document

$(document).ready(function(){
						   
	$('.icon_plusminus').click(function(){
		if($('.icon_plusminus').hasClass('pm_deactive')) {
			$(this).removeClass('pm_deactive');
			$('.searchBox').slideDown();
			$('.icon_plusminus').removeClass('plusbg');
			$('.icon_plusminus').addClass('minusbg');
		} else {
			$(this).addClass('pm_deactive');
			$('.searchBox').slideUp();
			$('.icon_plusminus').removeClass('minusbg');
			$('.icon_plusminus').addClass('plusbg');
		}
	});
	
	//$('ul.menuList li a').hover(
//		function(){
//			$('ul.menuList li').removeAttr('class');
//			$(this).parent().addClass('activeMenu');
//			$('.activeMenu').find('.smWrap').show();
//		}
//	);
	
})