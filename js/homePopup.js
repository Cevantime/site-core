/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function($)
{
	$.fn.popup = function(params)
	{
		
		var defaults = {
			innerWidth: "auto",
			wrapHeight: "100%",
			paddingTop: "20%",
			closeButton: '<span class="bt">OK</span>',
			trigger: false,
			auto: true,
			deleteOriginal: false,
			id: false,
			close : function(wrap, inner){
				wrap.css('display', 'none');
			},
			onClose: function(wrap) {
				
			},
			display: function(wrap, inner){
				wrap.css('display', 'block');
			}
		};

		$.extend(defaults, params);
		i = 0;
		return this.each(function()
		{
			var $popupWrap = null;
			var $popup = null;
			var $close = null;
			$popup = $(this).clone();
			
			$popup.css('display', 'block');
			$popupWrap = $('<div class="popup-wrap"></div>');
			$popupWrap.css('position','absolute');
			$popupWrap.css('width', '100%');
			$popupWrap.css('height', defaults.wrapHeight);
			$popupWrap.css('display', 'none');
			$popupWrap.css('top', 0);
			$popupWrap.css('paddingTop', defaults.paddingTop);
			$popupWrap.css('display', 'none');
			$popupWrap.css('text-align', 'center');
			$popupWrap.css('z-index', '1000');
			var id = '';
			if(defaults.id){
				id = defaults.id;
			}
			else if(typeof $popup.attr('id') !== 'undefined' && $popup.attr('id')){
				id = 'popup-'+$popup.attr('id');
			}
			else {
				id = "my-popup-"+(i++);
			}
			$popupWrap.attr('id',id);
			var $pop = $('<div class=popup></div>');
			$pop.append($popup);
			$pop.css('display','inline-block');
			$pop.css('position', 'relative');
			$pop.css('width',defaults.innerWidth);
//			$pop.css('margin','0 auto');
			$popupWrap.append($pop);
			if(defaults.closeButton){
				$close = $('<a href="#" class="close-popup"></a>');
				$pop.append($close);
				$close.html(defaults.closeButton);
			}
			$popupWrap.find('.close-popup').click(function(){
				close($popupWrap, $popup);
			});
			var $trigger = defaults.trigger ? defaults.trigger : $(this);
			$trigger.click(function(){
				defaults.display($popupWrap, $popup);
			});
			
			if(defaults.deleteOriginal){
				$(this).remove();
			}
			$('body').append($popupWrap);
			
			if(defaults.auto){
				defaults.display($popupWrap, $popup);
			}

		});
		
		function close(wrap, inner) {
			defaults.close(wrap, inner);
			defaults.onClose(wrap, inner);
		}
	};
	
})(jQuery);


