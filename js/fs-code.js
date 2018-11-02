
var fsCode = {

	options: {
		'templates': {
			'loader': '<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba( 255, 255, 255, 0.4 ); z-index: 999999999999999 !important;"><div class="loader"><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div></div></div>',

			'loaderModal': function()
			{
				return '<div style="position: absolute; width: 100%; height: 100%; background: rgba( 0, 0, 0, 0.2 ); z-index: 999;"></div>';
			},

			'modal': '<div class="modal"><div class="modal-content" style="{width}">{body}</div></div>',

			'alert': '<div id="confirmModal" class="modal"><div class="modal-content" style="width: 450px; height: 250px;"><div class="ws_color_{type}" style="padding-top: 45px; text-align: center; font-size: 50px;"><i class="fa fa-exclamation-triangle"></i></div><div style="text-align: center; font-size: 18px; padding: 30px; font-weight: 600; color: #999; line-height: 28px; overflow: auto; max-height: 155px;">{text}</div><button class="ws_btn ws_bg_{type}" type="button" style="position: absolute; bottom: 0; left: 0; width: 100%; height: 40px; border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;" data-modal-close="true">CLOSE</button></div></div>',
			'toast': '<div id="pro_toaster">Some text some message..</div>'
		}
	},

	modalsCount: 0,

	confirm: function ( text , bg , fnOkButton , afterClose )
	{
		var t = this;

		okButton = typeof okButton != 'undefined' ? okButton : 'Ok';
		cancelButton = typeof cancelButton != 'undefined' ? cancelButton : 'Cancel';
		afterClose = typeof afterClose != 'undefined' ? afterClose : true;

		var modalNumber = fsCode.modal( '<div style="padding-top: 35px;"><div style="width: 115px; height: 115px; line-height: 110px; font-weight: 300; color: #FFF; text-align: center; font-size: 50px; border: 1px solid #FFF; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; margin: auto;">!</div> </div><div style="color: #FFF; text-align: center; font-size: 18px; padding: 30px; font-weight: 300; line-height: 24px;">'+text+'</div><div style="text-align: center;"><button class="ws_btn ws_bg_'+bg+'" type="button" style="width: 150px;" data-modal-close="true">CANCEL</button><button class="ws_btn ws_color_'+bg+' yes_btn" type="button" style="width: 150px; -webkit-border-radius: 0;-moz-border-radius: 0;border-radius: 0;">YES , DELETE</button></div>' );

		$( modalNumber[2] + ' .modal-content' ).addClass('ws_bg_' + bg);

		$( modalNumber[2] + ' .yes_btn' ).click(function( )
		{
			fnOkButton( $( modalNumber[2] ) );

			if( afterClose )
			{
				t.modalHide( $( modalNumber[2] ) );
			}
		});
	},

	modalHide: function(modal)
	{
		modal.fadeOut(200 , function ()
		{
			$(this).trigger('modal-hide');
		});
	},

	modal: function ( body, options )
	{
		var t = this;

		body = typeof body == 'function' ? body() : body ;

		options = typeof options !== 'object' ? {} : options;

		var modalWidth = 'width' in options ? 'width: ' + (options['width'].toString().match(/(%|px)/)==null ? options['width'] + "%" : options['width']) + ' !important;' : '';

		t.modalsCount++;

		var modalTpl = t.options.templates.modal
			.replace( '{width}' , modalWidth )
			.replace( '{body}' , body );

		var el = t.parseHTML( modalTpl ),
			newId = 'proModal' + t.modalsCount;

		el.firstChild.id = newId;

		$("body").append(el);

		$("#" + newId).fadeIn(300).css('display', 'flex').on("modal-hide", function()
		{
			$( this ).remove( );
		});

		return [ newId , t.modalsCount , '#' + newId ];
	},

	modalWidth: function( _mn , width )
	{
		$("#proModal" + _mn + '>.modal-content' ).attr("style", "width: " + width + "% !important");
	},

	loadModal: function ( url , postParams, modalOptions )
	{
		var t = this,
			newModal = t.modal( '' , modalOptions );

		postParams['action'] = 'modal_' + url;

		postParams = typeof postParams != 'undefined' ? postParams : {};
		postParams['_mn'] = newModal[1];
		postParams['_token'] = $("meta[name=csrf-token]").attr('content');

		t.loading( 1 );

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: postParams,
			success: function ( result )
			{
				t.loading( 0 );

				result = t.jsonResjult( result );
				if( result['status'] == 'ok' && typeof result['html'] != 'undefined' )
				{
					$( "#" + newModal[0] ).find(".modal-content").html( '<div class="modal-pre-loader"></div>' + t.htmlspecialchars_decode( result['html'] ) );
					$( "#" + newModal[0] ).find(".modal-content>.modal-pre-loader").show().fadeOut(500 , function()
					{
						$(this).remove();
					});
				}
				else if( result['status'] == 'error' )
				{

				}
			},
			error: function (jqXHR, exception)
			{
				t.loading( 0 );
				var msg = '';
				if (jqXHR.status === 0) {
					msg = 'Not connect.';
				} else if (jqXHR.status == 404) {
					msg = 'Requested page not found. [404]';
				} else if (jqXHR.status == 500) {
					msg = 'Internal Server Error [500].';
				} else if (exception === 'parsererror') {
					msg = 'Requested JSON parse failed.';
				} else if (exception === 'timeout') {
					msg = 'Time out error.';
				} else if (exception === 'abort') {
					msg = 'Ajax request aborted.';
				} else {
					msg = 'Uncaught Error.';
				}
				t.alert( msg , 'danger' );
			}
		});
	},

	parseHTML: function ( html )
	{
		var range = document.createRange();
		var documentFragment = range.createContextualFragment( html );
		return documentFragment;
	},

	loading: function ( onOff )
	{
		if( typeof onOff == 'undefined' || onOff )
		{
			var tpl = this.parseHTML(this.options.templates.loader);
			tpl.firstChild.setAttribute('id' , 'pro-loading-element261272');
			document.body.insertBefore( tpl , document.body.lastChild);
		}
		else if( document.getElementById( 'pro-loading-element261272' ) !== null )
		{
			var prnt = document.getElementById( 'pro-loading-element261272' );
			prnt.parentNode.removeChild(prnt);
		}
	},

	jsonResjult: function ( json )
	{
		if( typeof json == 'object' )
		{
			return json;
		}

		var result;
		try
		{
			result = JSON.parse( json );
		}
		catch(e)
		{
			result = {
				'status': 'parse-error',
				'error': e
			};
		}
		return result;
	},

	htmlspecialchars_decode: function (string, quote_style)
	{
		var optTemp = 0,
			i = 0,
			noquotes = false;
		if(typeof quote_style==='undefined')
		{
			quote_style = 2;
		}
		string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
		var OPTS ={
			'ENT_NOQUOTES': 0,
			'ENT_HTML_QUOTE_SINGLE': 1,
			'ENT_HTML_QUOTE_DOUBLE': 2,
			'ENT_COMPAT': 2,
			'ENT_QUOTES': 3,
			'ENT_IGNORE': 4
		};
		if(quote_style===0)
		{
			noquotes = true;
		}
		if(typeof quote_style !== 'number')
		{
			quote_style = [].concat(quote_style);
			for (i = 0; i < quote_style.length; i++){
				if(OPTS[quote_style[i]]===0){
					noquotes = true;
				} else if(OPTS[quote_style[i]]){
					optTemp = optTemp | OPTS[quote_style[i]];
				}
			}
			quote_style = optTemp;
		}
		if(quote_style & OPTS.ENT_HTML_QUOTE_SINGLE)
		{
			string = string.replace(/&#0*39;/g, "'");
		}
		if(!noquotes){
			string = string.replace(/&quot;/g, '"');
		}
		string = string.replace(/&amp;/g, '&');
		return string;
	},

	htmlspecialchars: function ( string, quote_style, charset, double_encode )
	{
		var optTemp = 0,
			i = 0,
			noquotes = false;
		if(typeof quote_style==='undefined' || quote_style===null)
		{
			quote_style = 2;
		}
		string = typeof string != 'string' ? '' : string;

		string = string.toString();
		if(double_encode !== false){
			string = string.replace(/&/g, '&amp;');
		}
		string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');
		var OPTS = {
			'ENT_NOQUOTES': 0,
			'ENT_HTML_QUOTE_SINGLE': 1,
			'ENT_HTML_QUOTE_DOUBLE': 2,
			'ENT_COMPAT': 2,
			'ENT_QUOTES': 3,
			'ENT_IGNORE': 4
		};
		if(quote_style===0)
		{
			noquotes = true;
		}
		if(typeof quote_style !== 'number')
		{
			quote_style = [].concat(quote_style);
			for (i = 0; i < quote_style.length; i++)
			{
				if(OPTS[quote_style[i]]===0)
				{
					noquotes = true;
				}
				else if(OPTS[quote_style[i]])
				{
					optTemp = optTemp | OPTS[quote_style[i]];
				}
			}
			quote_style = optTemp;
		}
		if(quote_style & OPTS.ENT_HTML_QUOTE_SINGLE)
		{
			string = string.replace(/'/g, '&#039;');
		}
		if(!noquotes)
		{
			string = string.replace(/"/g, '&quot;');
		}
		return string;
	},

	alert: function( message , type )
	{
		type = type || 'danger';

		var tpl = this.options.templates.alert.replace('{text}' , message).replace('{type}' , type).replace('{type}' , type);

		$("body").append(tpl);
		$('body>.modal:eq(-1)').fadeIn(200).css('display' , 'flex');
	},

    ajaxResultCheck: function (res)
    {

        if( typeof res != 'object' )
        {
	        try
	        {
		        res = JSON.parse(res);
	        }
	        catch(e)
	        {
		        this.alert( 'Error!' );
		        return false;
	        }
        }

        if( typeof res['status'] == 'undefined' )
        {
            this.alert( 'Error!' );
            return false;
        }

        if( res['status'] == 'error' )
        {
            this.alert( typeof res['error_msg'] == 'undefined' ? 'Error!' : res['error_msg'] );
            return false;
        }

        if( res['status'] == 'ok' )
            return true;

        // else

        this.alert( 'Error!' );
        return false;
    },

    ajax: function ( action , params , func , noLoading )
    {
	    noLoadin = typeof noLoading == 'undefined' ? false : noLoading;

        var t = this;
        if( !noLoading )
	        t.loading(true);
	    params['action'] = action;

        $.post( ajaxurl , params , function( result )
        {
        	if( !noLoading )
	            t.loading(false);
            if( fsCode.ajaxResultCheck( result ) )
            {
	            try
	            {
		            result = JSON.parse(result);
	            }
	            catch(e)
	            {

	            }
	            if( typeof func == 'function' )
	                func( result );
            }
        });
    },

    zeroPad: function(n)
	{
		return n > 9 ? n : '0' + n;
	},

	spintax: function( text )
	{
		var matches, options, random;

		var regEx = new RegExp(/{([^{}]+?)}/);

		while((matches = regEx.exec(text)) !== null) {
			options = matches[1].split("|");
			random = Math.floor(Math.random() * options.length);
			text = text.replace(matches[0], options[random]);
		}

		return text;
	},

	toastTimer: 0,
	toast: function(text , type , duration , icon)
	{
		$("#pro_toaster").remove();
		if( this.toastTimer )
			clearTimeout(this.toastTimer);

		$("body").append(this.options.templates.toast);

		type = typeof type == 'undefined' ? 'success' : type;

		$("#pro_toaster").addClass('ws_bg_' + type).text(text).click(function()
		{
			$(this).fadeOut(200 , function()
			{
				$(this).remove();
			});
		});

		icon = typeof icon == 'undefined' ? 'check' : icon;

		$("#pro_toaster").prepend('<i class="fa fa-'+icon+'"></i> ');

		this.toastTimer = setTimeout(function()
		{
			$("#pro_toaster").fadeOut(200 , function()
			{
				$(this).remove();
			});
		} , typeof duration != 'undefined' ? duration : 4000);
	},

	serialize: function (data)
	{
		var res = {};
		data = data.serializeArray();

		$.each(data, function ()
		{
			if (res[this.name])
			{
				if (!res[this.name].push)
				{
					res[this.name] = [res[this.name]];
				}

				res[this.name].push(this.value || '');
			}
			else
			{
				res[this.name] = this.value || '';
			}
		});
		return res;
	}
}

if( typeof $ == 'undefined' ) var $ = typeof jQuery == 'undefined' ? null : jQuery;

jQuery(document).ready(function()
{
	if( typeof $ == 'undefined' ) $ = jQuery;

	$("body").on('click' , '[data-modal-id]' , function()
	{
		var modalId = $(this).attr('data-modal-id');

		$('#' + modalId).fadeIn(300);
	}).on('click' , '[data-load-modal]' , function()
	{
		var modal = $(this).attr('data-load-modal'),
			parameters = {},
			attrs = $(this)[0].attributes;

		for(var i = 0; i < attrs.length; i++)
		{
			var attrKey = attrs[i].nodeName;

			if( attrKey.indexOf('data-parameter-') == 0 )
			{
				parameters[attrKey.substr(15)] = attrs[i].nodeValue;
			}
		}

		fsCode.loadModal( modal  , parameters );
	}).on('click' , '.modal [data-modal-close=true]' , function ()
	{
		fsCode.modalHide( $(this).closest('.modal') );
	});

	try
	{
		if( typeof $('body').tooltip == 'function' )
		{
			$('body').tooltip({
				items: ".ws_tooltip2",
				content: function ()
				{
					return $(this).attr("title");
				}
			});
		}
	}
	catch (e) {}

});