Pams = {
    url : 'main.php',
	n1 : 0,
	n2 : 0,
	countDownload : function() {
      var n = $("input[name=download]:checkbox:checked").length;
	  $('#div_checked').text(n>0?(n+" checked") : 'download files');
	  //$('#div_checked').text(n + " checked!");
	  if(n>0) {
		if(Pams.n1==0) {
			$("img[title^=download]").wrap('<a href="get_rfiles.php"></a>');
			Pams.n1 ++;
		}
	  }
	  else {
		  if($("img[title^=download]").parent().is('A')) {
		  $("img[title^=download]").unwrap();
			Pams.n1 = 0;
		  } 
	  }
    },
    countDeleted : function() {
      var n = $("input[name=deleted]:checked").length;
	  $('#div1_checked').text(n>0?(n+" checked!") : 'delete files');
	  if(n>0) {
		if(Pams.n2==0) {
		  	$("img[title^=remove]").wrap('<a href="javascript:void(0);"></a>');
			Pams.n2 ++;
		}
	  }
	  else {
		  if($("img[title^=remove]").parent().is('a')) { //$('#').attr(tagName).toLowerCase()
			$("img[title^=remove]").unwrap();
			Pams.n2 = 0;
		  } 
		  // else { alert($("img[title^=remove]").parent().get(0).tagName); }
	  }
	  return true;
    },
	add_delete : function() {
			$('#img_delete').parent().click(function() {
			var files = $('input[name="deleted"]:checked').map(function() {
				 return $(this).val();
			 }).get().join(',');
			//delete=1&files=payment_bc200_20110201044421.csv%2Cpayment_bc300_20110128055927.csv
			$.ajax({
				type: "POST",
				url: Pams.url,
				data: {'delete':1,'files':files},
				success: function(data) {
					// alert('['+data+'],'+data.length); // bugs: why 3, 4 instead of 1???
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
	add_download : function() {
		$('#img_download').parent().click(function() {
			var files = $('input[name="download"]:checkbox:checked').map(function() {
				return $(this).val();
			}).get().join(',');			
			$.ajax({
				type: "POST",
				url: Pams.url,
				async:false,
				data: {'download':1,'files': files}
				//success: function(data) { // document.location.href='get_rfiles.php'; //document.getElementById(sid).innerHTML = data; //return false; //}
			});
			return true;
		});
	},
	update_download_time : function(file, sid) {
		$.ajax({
				type: "POST",
				url: 'main.php',
				data: {'dfile': file},
				success: function(data) {
						document.getElementById(sid).innerHTML = data;
						return false;
				}
		});
		return false;
	}

	
};
