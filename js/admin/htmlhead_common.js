/* ----------------------------------- */
/**
 * @package WP Nutrition Facts
 * @link http://www.kilukrumedia.com
 * @copyright Copyright &copy; 2014, Kilukru Media
 * @version: 1.0.2
 */
/* ----------------------------------- */

jQuery(document).ready(function ($) {
	'use strict';

	var percentage = function(contains, reference) {
		return Math.round( contains / reference * 100 );
	}


	function write_nutrition_facts( e ){
		
		var index,
		    value;
		for (index in wpnf_nutrional_fields ) {

		    value = wpnf_nutrional_fields[index];

		    var input = $('#wpnf_' + index);
		    var input_val = $(input).val();
		    var item_label = $('#wpnf_label_' + index );
		    var item_row = $(item_label).parents('.item_row');

		    var item_label_percent = $('#wpnf_label_' + index + '_percent' );

		    $(item_label).text( input_val );

		    if( input_val == '' ){
		    	$(item_row).addClass('item_row_notactive');

		    	if( $(item_label_percent).length ){
		    		$(item_label_percent).text('');
		    	}

		    }else{
		    	$(item_row).removeClass('item_row_notactive');

		    	if( $(item_label_percent).length ){
		    		$(item_label_percent).text( percentage(input_val, wpnf_rda[index] ) + '%' );
		    	}

		    }

		    

		}

	}


	if( $('#wpnf-example-options').length ){


		$('#wpnf-example-options input').keyup(function(e){
			write_nutrition_facts( e );
		});

		$("#wpnf-example-options input.digit").numeric({ negative: false });

	}


	// Set Tooltip for elements
	$('.wpnf_tooltip').tooltip();


});