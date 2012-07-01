// JavaScript Document

$(document).ready(function(){
						   
	$('.linkDrop').click(function(){
		$(this).find('.navDrop').show();		
	});

	$('.navDrop').mouseleave(function(){
		$('.navDrop').hide();		
	});

	
});