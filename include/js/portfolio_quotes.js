jQuery(document).ready(function($) {

	function sortTable(f,n){
		var rows = $('#mytable tbody tr').get();
        var count = 0;
		rows.sort(function(a, b) {
          var A = getVal(a);
          var B = getVal(b);
          if(A < B) {
            return -1*f;
          }
          if(A > B) {
            return 1*f;
          }
		  count = count +1;
          return 0;
        });

        function getVal(elm){
          var v = $(elm).children('td').eq(n).text().toUpperCase();
          if($.isNumeric(v)){
             v = parseInt(v,10);
          }
          return v;
        }

        $.each(rows, function(index, row) {
          $('#mytable').children('tbody').append(row);
        });
	}
	
	var f_col1 = 1; // flag to toggle the sorting order
	var f_col2 = 1; // flag to toggle the sorting order
	var f_col3 = 1; // flag to toggle the sorting order
	var f_col4 = 1; // flag to toggle the sorting order
	$("#col1").on("click", function(){
		f_col1 *= -1; // toggle the sorting order
		var n = $(this).prevAll().length;
		sortTable(f_col1,n);
	});
	$("#col2").on("click", function(){
		f_col2 *= -1; // toggle the sorting order
		var n = $(this).prevAll().length;
		sortTable(f_col2,n);
	});
	$("#col3").on("click", function(){
		f_col3 *= -1; // toggle the sorting order
		var n = $(this).prevAll().length;
		sortTable(f_col3,n);
	});	
	$("#col4").on("click", function(){
		f_col4 *= -1; // toggle the sorting order
		var n = $(this).prevAll().length;
		sortTable(f_col4,n);
	});	
	var url = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol="
	var tables = $(".diy_investor_stock_portfolio_table");
	var row = 0;
	$(".diy_investor_stock_portfolio_ticker").each(function(n){
		var stocks = $(this).text();
		get_stock_data(row, url, $(tables[0]).attr('id'), $("#diy_investor_stock_portfolio_id_color_" + $(tables[0]).attr('id')).val(), stocks, $("#diy_investor_stock_portfolio_id_apikey_" + $(tables[0]).attr('id')).val());
		row++;
		});

	function commaSeparateNumber(val){
	    while (/(\d+)(\d{3})/.test(val.toString())){
	      val = val.toString().replace(/(\d+)(\d{3})/, '$1'+','+'$2');
	    }
	    return val;
	}
	  
	function get_stock_data(q, url, table_id, color, stocks, apikey) {
		$.ajax({
			url: url + stocks + "&apikey=" + apikey,
			crossDomain: true,
			dataType: "json",
			success: function(data, textStatus, jqXHR) {
				data = JSON.stringify(data);
				data = data.replace("/ ^[\b \t \n \r \]*$", '');
	        	data = JSON.parse(unescape(data));
		        if (typeof(data) != "undefined" && data !== null) {
					var symbol = data["Meta Data"]["2. Symbol"];
					symbol = symbol.replace('.', '_');
					var eod = data["Time Series (Daily)"];
					for (var x in eod) {
						var last_price = parseFloat(eod[x]["4. close"]);
						break;
					}
				    var price = (Math.round(last_price * 100) / 100).toFixed(2);
					var cost = $(".diy_investor_stock_portfolio_costs" + q).text();
					var PNL = ((last_price - cost) / cost * 100).toFixed(2); 
					var PNLs = PNL.toString();
					if (PNL <= 0) {
				        if (color == 'change') {
							$(".diy_investor_stock_portfolio_quote_" + table_id + symbol).attr('style', 'border: none; color:red; text-align:right');
				        } else {
							$(".diy_investor_stock_portfolio_quote_" + table_id + symbol).attr('style', 'border: none; text-align:right');
				        }
						$(".diy_investor_stock_portfolio_change_" + symbol).attr('style', 'border: none; color:red; text-align:right');
						$(".diy_investor_stock_portfolio_change_p_" + symbol).attr('style', 'border: none;color:red; text-align:right');
						$(".diy_investor_stock_portfolio_change_pnl_" + symbol).attr('style', 'border: none;color:red; text-align:right');
				    } else {
				      	if (color == 'change') {
							$(".diy_investor_stock_portfolio_quote_" + table_id + symbol).attr('style', 'border: none;color:green; text-align:right');
				        } else {
							$(".diy_investor_stock_portfolio_quote_" + table_id + symbol).attr('style', 'border: none; text-align:right');
				        }
						$(".diy_investor_stock_portfolio_change_" + symbol).attr('style', 'border: none;color:green; text-align:right');
						$(".diy_investor_stock_portfolio_change_p_" + symbol).attr('style', 'border: none;color:green; text-align:right');
						$(".diy_investor_stock_portfolio_change_pnl_" + symbol).attr('style', 'border: none;color:green; text-align:right');
					}
				    $(".diy_investor_stock_portfolio_quote_" + table_id + symbol).text('$' + commaSeparateNumber(price));
					$(".diy_investor_stock_portfolio_change_pnl_" + symbol).text(PNLs+'%');
					if (last_price == 0) {
						if (color == 'change') {
							$(".diy_investor_stock_portfolio_quote_" + table_id + symbol).attr('style', 'border: none;color:red; text-align:right');
						} else {
							$(".diy_investor_stock_portfolio_quote_" + table_id + symbol).attr('style', 'border: none; text-align:right');
				        }
						$(".diy_investor_stock_portfolio_change_" + symbol).attr('style', 'border: none;color:red; text-align:right');
						$(".diy_investor_stock_portfolio_change_p_" + symbol).attr('style', 'border: none;color:red; text-align:right');
						$(".diy_investor_stock_portfolio_change_pnl_" + symbol).attr('style', 'border: none;color:red; text-align:right');
						$(".diy_investor_stock_portfolio_quote_" + table_id + symbol).text('Invalid');
						$(".diy_investor_stock_portfolio_change_" + symbol).text('Invalid');
					}
		        }
	        }
	    });
    }
})