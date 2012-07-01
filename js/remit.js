Remit = {
    url : 'main.php',
	n1 : 0,
	n2 : 0,
	count_download : function() {
      var n = $("input[name=download]:checked").length;
	  $('#div_checked').text(n>0?(n+" checked") : 'download files');
	  if(n>0) {
		if(Remit.n1==0) {
			$("#img_download").wrap('<a href="get_rfiles.php" target="i_view"></a>');
			Remit.n1 ++;
		}
	  }
	  else {
		  if($("#img_download").parent().is('a')) {
		  $("#img_download").unwrap();
			Remit.n1 = 0;
		  } 
	  }
	  return true;
    },
    count_deleted : function() {
      var n = $("input[name='deleted']:checked").length;
	  $('#div1_checked').text(n>0?(n+" checked!") : 'delete files');
	  if(n>0) {
		if(Remit.n2==0) {
			$('#img_delete').wrap('<a href="javascript:void(0);"></a>');
			Remit.n2 ++;
	  	}
	  }
	  else {
		  //if($("img[title^=remove]").parent().is('a')) { //$('#').attr(tagName).toLowerCase()
		  if($('#img_delete').parent().is('a')) {
			$("#img_delete").unwrap();
			Remit.n2 = 0;
		  } 
		  // else { alert($("img[title^=remove]").parent().get(0).tagName); }
	  }
	  return true;
    },
	update_deleted : function() {
			$('#img_delete').parent().click(function() {
			var files = $('input[name="deleted"]:checked').map(function() {
				 return $(this).val();
			 }).get().join(';');
			//delete=1&files=payment_bc200_20110201044421.csv%2Cpayment_bc300_20110128055927.csv
			$.ajax({
				type: "POST",
				url: Remit.url,
				data: {'delete':1,'files':files},
				success: function(data) {
					// alert('['+data+'],'+data.length); // bugs: why 3, 4 instead of 1???
					if(data.length>0 && /Y/.test(data)) {
						$("input[name='deleted']:checked").each(function() {
							$(this).parents('tr:eq(0)').hide();
						});
					}
					return false;
				}
			});
			return false;
		});
	},
	update_download_all : function() {
		$('#img_download').parent().click(function() {
			var files = $('input[name="download"]:checked').map(function() {
				return $(this).val();
			}).get().join(',');			
			$.ajax({
				type: "POST",
				url: Remit.url,
				async:false,
				data: {'download':1,'files': files},
				success: function(data) { // document.location.href='get_rfiles.php'; //document.getElementById(sid).innerHTML = data; //return false; //}
					var t = data.split(';');
					$('.count_time').each(function(index) {
					document.getElementById(this.id).innerHTML = t.shift();
					});
					return true;
				}
			});
			return true;
		});
	},
	update_download_time : function(file, sid) {
		$.ajax({
				type: "POST",
				url: Remit.url,
				data: {'dfile': file},
				success: function(data) {
						document.getElementById(sid).innerHTML = data;
						return false;
				}
		});
		return false;
	}

};

$(document).ready(function() {
	var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
    $('#form1').submit(function() {
       var data = $('#form1').serialize() + '&search1=1';
        $.ajax({
            type: "POST",
            url: this.url,
            data: data,
            success: function(data) {
                if(data) {
                    $('#main1').html(data).show(200);
                } else {
                    alert('Error Here.');
                }
                return false;
            }
        });
	});
	$('#tab8').bind('click', function(event) {
		 event.preventDefault();
		if(/Hide/.test(this.innerHTML)){
			parent.document.getElementsByTagName('FRAMESET').item(1).cols = '1,*';
			this.innerHTML='&nbsp;&nbsp;&nbsp;Show Left Menu';
			$(this).removeClass('move2').addClass('move1');
		} else {
			parent.document.getElementsByTagName('FRAMESET').item(1).cols = '200,*';
			this.innerHTML='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hide Left Menu';
			$(this).removeClass('move1').addClass('move2');
		}
	});
	$('#legend1').bind('click', function() {
		$('#title1').fadeIn(500);
		$('#form1').fadeOut(500);
	});
	$('#title1').bind('click', function() {
		$('#form1').fadeIn(500);
		$('#title1').fadeOut(500);
	});
	
	$('input.btn-remit').hover(function(){
		$(this).removeClass('btn-norm');
		$(this).addClass('btn-hover');
	}, function() {
		$(this).removeClass('btn-hover');
		$(this).addClass('btn-norm');
	});
	$('input.btn-reset').hover(function(){
		$(this).removeClass('btn-norm');
		$(this).addClass('btn-hover');
	}, function() {
		$(this).removeClass('btn-hover');
		$(this).addClass('btn-norm');
	});
	$(window).resize(function(){
		var browserWidth = $(window).width();
		if(parseInt(browserWidth) <= 740){
			$('fieldset').addClass('fsWidth_fix');
		} else {
			$('fieldset').removeAttr('class');
		};
	});
});
