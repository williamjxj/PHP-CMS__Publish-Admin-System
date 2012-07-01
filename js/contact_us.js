Contact = {
    url : 'contact_us.php',
	n1 : 0,
    count_deleted : function() {
      var n = $("input[name='deleted']:checked").length;
	  $('#div1_checked').text(n>0?(n+" checked!") : 'delete');
	  if(n>0) {
		if(Contact.n1==0) {
			$('#img_delete').wrap('<a href="javascript:void(0);"></a>');
			Contact.n1 ++;
	  	}
	  }
	  else {
		  //if($("img[title^=remove]").parent().is('a')) { //$('#').attr(tagName).toLowerCase()
		  if($('#img_delete').parent().is('a')) {
			$("#img_delete").unwrap();
			Contact.n1 = 0;
		  } 
		  // else { alert($("img[title^=remove]").parent().get(0).tagName); }
	  }
	  return true;
    },
	update_deleted : function() {
			$('#img_delete').parent().click(function() {
			var cids = $('input[name="deleted"]:checked').map(function() {
				 return $(this).val();
			 }).get().join(',');
			//alert(cids);
			$.ajax({
				type: "POST",
				url: Contact.url,
				data: {'delete':1,'cids':cids},
				success: function(data) {
					if(data.length>0 && /Y/.test(data)) {
						$("input[type=checkbox][name=deleted]:checked").each(function() {
							$(this).parents('tr:eq(0)').hide();
						});
					}
					return false;
				}
			});
			return false;
		});
	},
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
		$('#title1').show().fadeIn(500);
		$('#form1').hide().fadeOut(500);
	});
	$('#title1').bind('click', function() {
		$('#form1').show().fadeIn(500);
		$('#title1').hide().fadeOut(500);
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
		if(browserWidth <= 740){
			$('fieldset').addClass('fsWidth_fix');
		} else {
			$('fieldset').removeAttr('class');
		};
	});
});
