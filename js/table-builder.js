jQuery(document).ready(function ($) {
	var field_name_prefix = 'table_values';

	function get_effective(curr, index) {
		if(!index)
			return curr;

		if(curr == index[0])
			return index[1];
		else if(curr == index[1])
			return index[0];
		else
			return curr;
	}

	function get_curr_row_count(){
		return parseInt($('.hmg-rows').val());
	}

	function get_curr_col_count(){
		return parseInt($('.hmg-cols').val());
	}
	
	function get_curr_sub_array(){
		return $('.hmg-subs').val();
	}

	function is_valid_num(x){
		if(parseInt(x) == x && x > 0)
			return true;
		return false;
	}

	function is_valid_row(x){
		if(is_valid_num(x) && parseInt(x) <= get_curr_row_count())
			return true;
		return false;
	}
	
	function is_valid_col(x){
		if(is_valid_num(x) && parseInt(x) <= get_curr_col_count())
			return true;
		return false;
	}

	function in_array(value, array) {
    for(var i=0; i<array.length; i++){
        if(value == array[i]) return true;
    }
    return false;
}

	function rebuildTable(switch_rows, switch_cols){
		switch_rows 	= (typeof switch_rows !== 'undefined') ? switch_rows : false;
		switch_cols 	= (typeof switch_cols !== 'undefined') ? switch_cols : false;
		var row_count 	= $('.hmg-rows').val();
		var col_count 	= $('.hmg-cols').val();
		var sub_array 	= $('.hmg-subs').val();
		sub_array = sub_array.split( ',' );
		
		var table 		= $('.hmg-comptable');
		var effective_row, effective_col;

		var table_html = '<thead><tr>';
		for(var i = 1; i <= col_count; i++){
			effective_col = get_effective(i, switch_cols);
			var selector = 'input[name="'+field_name_prefix+'[0]['+effective_col+']"]';
			var curr_val = ( $(selector).val() ) ? $(selector).val() : '';
			table_html += '<th><input name="'+field_name_prefix+'[0]['+i+']" value="'+curr_val+'" placeholder="'+hmg_js_strings.placeholder+'" /></th>';
		}
		table_html += '</tr></thead><tbody>';
		
		for(var i = 1; i <= row_count; i++){
			
			table_html += in_array(i, sub_array) ? '<tr class="subheader">' : '<tr>';
			
				for(var j = 1; j <= col_count; j++){

					effective_row = get_effective(i, switch_rows);
					effective_col = get_effective(j, switch_cols);

					var selector = 'textarea[name="'+field_name_prefix+'['+effective_row+']['+effective_col+']"]';
					var curr_val = ( $(selector).val() ) ? $(selector).val() : '';
					var col_span  = col_count;
					
					table_html += in_array(i, sub_array) ? '<td colspan="'+col_span+'">' : '<td>';
					if (j == 1) {
						table_html += '<span class="num_row_hmg_table">'+i+'</span>';
					}
					table_html += '<textarea name="'+field_name_prefix+'['+i+']['+j+']" placeholder="'+hmg_js_strings.placeholder+'">'+curr_val+'</textarea></td>';
					if(in_array(i, sub_array)) break;
				}
				table_html += '</tr>';
			
		}
		table_html += '</tbody>';
		table.html(table_html);
	}

	//Table resize dialog

	$('#hmg-comptable-resize-btn').click(function(e) {
		$( "#hmg-comptable-resize-dialog" ).dialog({
			modal:true,
			draggable: false,
			open: function(e, ui) {
				$(this).children('.hmg-row-count').val(get_curr_row_count());
				$(this).children('.hmg-col-count').val(get_curr_col_count());
				$(this).children('.hmg-sub-array').val(get_curr_sub_array());
				$(this).children('.hmg-dialog-error').hide();
			}
		});
	});

	$('#hmg-comptable-resize-dialog button').click(function(e) {
		var row_count 	= $(this).siblings('.hmg-row-count').val();
		var col_count 	= $(this).siblings('.hmg-col-count').val();
		var sub_array 	= $(this).siblings('.hmg-sub-array').val();
		var error_cont 	= $(this).siblings('.hmg-dialog-error').first();
		if(is_valid_num(row_count) && is_valid_num(col_count)){
			error_cont.hide();
			$('.hmg-rows').val(row_count);
			$('.hmg-cols').val(col_count);
			$('.hmg-subs').val(sub_array);
			rebuildTable();
			$('#hmg-comptable-resize-dialog').dialog("close");
		}
		else{
			error_cont.html(hmg_js_strings.resize_error).show().effect( "bounce" );
		}
	});

	//Switch Rows Dialog

	$('#hmg-row-switcher-btn').click(function(e) {
		$( "#hmg-row-switcher-dialog" ).dialog({
			modal:true,
			draggable: false,
			open: function(e, ui) {
				$(this).children('input[type="text"]').val('');
				$(this).children('.hmg-dialog-error').hide();
			}
		});
	});

	$( "#hmg-row-switcher-dialog button" ).click(function(e) {
		var row_1 		= $(this).siblings('.hmg-row1').val();
		var row_2 		= $(this).siblings('.hmg-row2').val();
		var error_cont 	= $(this).siblings('.hmg-dialog-error').first();
		if(is_valid_row(row_1) && is_valid_row(row_2)){
			error_cont.hide();
			rebuildTable([row_1, row_2], false);
			$( "#hmg-row-switcher-dialog" ).dialog("close");
		}
		else{
			error_cont.html(hmg_js_strings.switch_error + ' ' + get_curr_row_count()).show().effect( "bounce" );
		}
	});

	//Switch Cols Dialog

	$('#hmg-col-switcher-btn').click(function(e) {
		$( "#hmg-col-switcher-dialog" ).dialog({
			modal:true,
			draggable: false,
			open: function(e, ui) {
				$(this).children('input[type="text"]').val('');
				$(this).children('.hmg-dialog-error').hide();
			}
		});
	});

	$( "#hmg-col-switcher-dialog button" ).click(function(e) {
		var col_1 		= $(this).siblings('.hmg-col1').val();
		var col_2 		= $(this).siblings('.hmg-col2').val();
		var error_cont 	= $(this).siblings('.hmg-dialog-error').first();
		if(is_valid_col(col_1) && is_valid_col(col_2)){
			error_cont.hide();
			rebuildTable(false, [col_1, col_2]);
			$( "#hmg-col-switcher-dialog" ).dialog("close");
		}
		else{
			error_cont.html(hmg_js_strings.switch_error + ' ' + get_curr_col_count()).show().effect( "bounce" );
		}
	});

	//Table add empty dialog

	$('#hmg-comptable-addnew-btn').click(function(e) {
		$( "#hmg-comptable-addnew-dialog" ).dialog({
			modal:true,
			draggable: false,
			open: function(e, ui) {
				$(this).children('input[type="text"]').val('');
				$(this).children('.hmg-dialog-error').hide();
			}
		});
	});

	$('#hmg-comptable-addnew-dialog button').click(function(e) {
		var row_after 	= $(this).siblings('.hmg-row-after').val();	
		var col_after 	= $(this).siblings('.hmg-col-after').val();		
		var col_count 	= get_curr_col_count();		
		var row_count 	= get_curr_row_count();
		var sub_array 	= $('.hmg-subs').val();
		sub_array = sub_array.split( ',' );			
		var error_cont 	= $(this).siblings('.hmg-dialog-error').first();
		
		if(is_valid_col(col_after) || is_valid_row(row_after)){
			error_cont.hide();

			if (row_after && col_after) {
				error_cont.html(hmg_js_strings.only_one).show().effect( "bounce" );
				return;
			}

			if (row_after) {
				if (row_after < row_count) {
					// Increment indexes of all rows after row_after
					var row_index = row_insert_index = parseInt(row_after) + 1;
					for(row_index; row_index <= row_count; row_index++){
						var row_tr_index = $('.hmg-comptable tr:eq('+row_index+') textarea');
						row_increment = parseInt(row_index) + 1;
						row_tr_index.each(function(i){
							i++;
							$(this).attr('name', field_name_prefix+'['+row_increment+']['+i+']');
						});					
					}	
					// Add empty row
					table_html = '<tr>';
					for(var j = 1; j <= col_count; j++){	
						table_html += '<td><textarea name="'+field_name_prefix+'['+row_insert_index+']['+j+']" placeholder="'+hmg_js_strings.placeholder+'"></textarea></td>';
					}	
					table_html += '</tr>';	
					$('.hmg-comptable tr:eq('+row_insert_index+')').before(table_html);	
					// Increment hidden input value
					$('.hmg-rows').val(row_count + 1);
					// Rebuild sub headers
					var subheaderarray = [];
					$.each(sub_array, function(index, value) {
						if (parseInt(value) >= row_insert_index) {
							subheaderarray.push(parseInt(value) + 1);
						}
						else {
							subheaderarray.push(parseInt(value));
						}		    
					});		
					subheaderarray = subheaderarray.join(',');
					$('.hmg-subs').val(subheaderarray);
					// Close popup
					$('#hmg-comptable-addnew-dialog').dialog("close");
					// Rebuild values
					$('.num_row_hmg_table').remove();
					for (var num_index = 1; num_index <= row_count+1; num_index++) {
						$('.hmg-comptable tr:eq('+num_index+') > td:nth-child(1)').prepend('<span class="num_row_hmg_table">'+num_index+'</span>');
					}

				}
				else{
					error_cont.html(hmg_js_strings.insert_error_row).show().effect( "bounce" );
				}				
			} 	

			if (col_after) {
				if (col_after < col_count) {					
					for(var j = 1; j <= row_count; j++){
						// Increment indexes of all cols after col_after
						var col_index = col_insert_index = parseInt(col_after) + 1;
						for(col_index; col_index <= col_count; col_index++){
							var col_td_index = $('.hmg-comptable tr:eq('+j+') > td:nth-child('+col_index+') textarea');
							col_increment = parseInt(col_index) + 1;
							col_td_index.each(function(){
								$(this).attr('name', field_name_prefix+'['+j+']['+col_increment+']');
							});													
							$('.hmg-comptable tr > th:nth-child('+col_index+') input').attr('name', field_name_prefix+'[0]['+col_increment+']');				
						}

						var col_td_foreach = $('.hmg-comptable tr:eq('+j+') > td:nth-child('+col_insert_index+')');
						table_html = '<td><textarea name="'+field_name_prefix+'['+j+']['+col_insert_index+']" placeholder="'+hmg_js_strings.placeholder+'"></textarea></td>';
						col_td_foreach.before(table_html);

					}
					// Add empty col
					$('.hmg-comptable tr > th:nth-child('+col_insert_index+')').before('<th><input name="'+field_name_prefix+'[0]['+col_insert_index+']" value="" placeholder="'+hmg_js_strings.placeholder+'" /></th>');					

					// Increment hidden input value and close
					$('.hmg-cols').val(col_count + 1);
					$('#hmg-comptable-addnew-dialog').dialog("close");
					$('.hmg-comptable tr.subheader').each(function(){
						$(this).find('td').attr('colspan', col_count + 1)
					});
				}
				else{
					error_cont.html(hmg_js_strings.insert_error_col).show().effect( "bounce" );
				}
			} 

		}
		else{
			error_cont.html(hmg_js_strings.resize_error).show().effect( "bounce" );
		}
	});	


	//Table remove dialog

	$('#hmg-comptable-remove-btn').click(function(e) {
		$( "#hmg-comptable-remove-dialog" ).dialog({
			modal:true,
			draggable: false,
			open: function(e, ui) {
				$(this).children('input[type="text"]').val('');
				$(this).children('.hmg-dialog-error').hide();
			}
		});
	});

	$('#hmg-comptable-remove-dialog button').click(function(e) {
		var row_remove 	= $(this).siblings('.hmg-row-remove').val();	
		var col_remove 	= $(this).siblings('.hmg-col-remove').val();			
		var col_count 	= get_curr_col_count();		
		var row_count 	= get_curr_row_count();
		var error_cont 	= $(this).siblings('.hmg-dialog-error').first();
		var sub_array 	= $('.hmg-subs').val();
		sub_array = sub_array.split( ',' );		
		
		if(is_valid_col(col_remove) || is_valid_row(row_remove)){
			error_cont.hide();

			if (row_remove && col_remove) {
				error_cont.html(hmg_js_strings.only_one).show().effect( "bounce" );
				return;
			}

			if (row_remove) {
				if (row_remove < row_count) {
					// Reduce indexes of all rows after row_remove
					var row_index = parseInt(row_remove) + 1;
					var row_remove_index = parseInt(row_remove);
					for(row_index; row_index <= row_count; row_index++){
						var row_tr_index = $('.hmg-comptable tr:eq('+row_index+') textarea');
						row_reduce = parseInt(row_index) - 1;
						row_tr_index.each(function(i){
							i++;
							$(this).attr('name', field_name_prefix+'['+row_reduce+']['+i+']');
						});					
					}	
					// Remove row	
					$('.hmg-comptable tr:eq('+row_remove_index+')').remove();	
					
					// Reduce hidden input value, recreate subheaders and close popup
					$('.hmg-rows').val(row_count - 1);

					var subheaderarray = [];
					$.each(sub_array, function(index, value) {
						if (parseInt(value) > row_remove_index) {
							subheaderarray.push(parseInt(value) - 1);
						}
						else if (parseInt(value) == row_remove_index) {
						}
						else {
							subheaderarray.push(parseInt(value));
						}		    
					});		
					subheaderarray = subheaderarray.join(',');
					$('.hmg-subs').val(subheaderarray);

					$('#hmg-comptable-remove-dialog').dialog("close");

					$('.num_row_hmg_table').remove();
					for (var num_index = 1; num_index <= row_count-1; num_index++) {
						$('.hmg-comptable tr:eq('+num_index+') > td:nth-child(1)').prepend('<span class="num_row_hmg_table">'+num_index+'</span>');
					}

				}
				else{ //if removed row is last
					var row_remove_index = parseInt(row_remove);
					$('.hmg-comptable tr:eq('+row_remove_index+')').remove();	
					$('.hmg-rows').val(row_count - 1);
					var subheaderarray = [];
					$.each(sub_array, function(index, value) {
						if (parseInt(value) > row_remove_index) {
							subheaderarray.push(parseInt(value) - 1);
						}
						else if (parseInt(value) == row_remove_index) {
						}
						else {
							subheaderarray.push(parseInt(value));
						}			    
					});		
					subheaderarray = subheaderarray.join(',');
					$('.hmg-subs').val(subheaderarray);
					$('#hmg-comptable-remove-dialog').dialog("close");
					$('.num_row_hmg_table').remove();
					for (var num_index = 1; num_index <= row_count-1; num_index++) {
						$('.hmg-comptable tr:eq('+num_index+') > td:nth-child(1)').prepend('<span class="num_row_hmg_table">'+num_index+'</span>');
					}					
				}				
			} 	

			if (col_remove) {
				if (col_remove < col_count) {					
					for(var j = 1; j <= row_count; j++){
						// Reduce indexes of all cols after col_remove
						var col_index = parseInt(col_remove) + 1;
						var col_remove_index = parseInt(col_remove);
						for(col_index; col_index <= col_count; col_index++){
							var col_td_index = $('.hmg-comptable tr:eq('+j+') > td:nth-child('+col_index+') textarea');
							col_reduce = parseInt(col_index) - 1;
							col_td_index.each(function(){
								$(this).attr('name', field_name_prefix+'['+j+']['+col_reduce+']');
							});													
							$('.hmg-comptable tr > th:nth-child('+col_index+') input').attr('name', field_name_prefix+'[0]['+col_reduce+']');				
						}

						var col_td_foreach = $('.hmg-comptable tr:eq('+j+'):not(.subheader) > td:nth-child('+col_remove_index+')');						
						col_td_foreach.remove();

					}
					// Remove col in header
					$('.hmg-comptable tr > th:nth-child('+col_remove_index+')').remove();					

					// Reduce hidden input value and close
					$('.hmg-cols').val(col_count - 1);
					$('#hmg-comptable-remove-dialog').dialog("close");
					$('.hmg-comptable tr.subheader').each(function(){
						$(this).find('td').attr('colspan', col_count - 1)
					});
					$('.num_row_hmg_table').remove();
					for (var num_index = 1; num_index <= row_count; num_index++) {
						$('.hmg-comptable tr:eq('+num_index+') > td:nth-child(1)').prepend('<span class="num_row_hmg_table">'+num_index+'</span>');
					}					
				}
				else{
					var col_remove_index = parseInt(col_remove);
					for(var j = 1; j <= row_count; j++){
						var col_td_foreach = $('.hmg-comptable tr:eq('+j+'):not(.subheader) > td:nth-child('+col_remove_index+')');						
						col_td_foreach.remove();						
					}
					$('.hmg-comptable tr > th:nth-child('+col_remove_index+')').remove();
					$('.hmg-cols').val(col_count - 1);
					$('#hmg-comptable-remove-dialog').dialog("close");
					$('.hmg-comptable tr.subheader').each(function(){
						$(this).find('td').attr('colspan', col_count - 1)
					});										
				}
			} 

		}
		else{
			error_cont.html(hmg_js_strings.resize_error).show().effect( "bounce" );
		}
	});	


	//Shortcode helper

	$('#hmg_first_col_hover_check').click(function() {
		if ($(this).is(':checked')) {
			$('#hmg_comp_shortcode_firsthover').html('hover-col1 ');
		}	
		else {
			$('#hmg_comp_shortcode_firsthover').html('');
		}	
		var shortcode_strip_tags = $('.hmg_comptable_shortcode_hidden').text();
		$('.hmg_comptable_shortcode_echo').html(shortcode_strip_tags);	
	});

	$('#hmg_calign_check').click(function() {
		if ($(this).is(':checked')) {
			$('#hmg_comp_shortcode_calign').html('center-table-align');
		}	
		else {
			$('#hmg_comp_shortcode_calign').html('');
		}	
		var shortcode_strip_tags = $('.hmg_comptable_shortcode_hidden').text();
		$('.hmg_comptable_shortcode_echo').html(shortcode_strip_tags);	
	});	

	//Image helper	
	var imageFrame;jQuery(".hmg_table_helper_upload_image_button").click(function(e){e.preventDefault();return $self=jQuery(e.target),$div=$self.closest("div.hmg_table_helper_image"),imageFrame?void imageFrame.open():(imageFrame=wp.media({title:"Choose Image",multiple:!1,library:{type:"image"},button:{text:"Use This Image"}}),imageFrame.on("select",function(){selection=imageFrame.state().get("selection"),selection&&selection.each(function(e){console.log(e);{var t=e.attributes.sizes.full.url;e.id}$div.find(".hmg_table_helper_preview_image").attr("src",t),$div.find(".hmg_table_helper_upload_image").val('<img src ="'+t+'" alt="" />')})}),void imageFrame.open())}),jQuery(".hmg_table_helper_clear_image_button").click(function(){var e='';return jQuery(this).parent().siblings(".hmg_table_helper_upload_image").val(""),jQuery(this).parent().siblings(".hmg_table_helper_preview_image").attr("src",e),!1});


});