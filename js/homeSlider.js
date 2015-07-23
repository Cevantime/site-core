(function($)
{
	$.fn.slider = function(params)
	{
		
		var defaults = {
			nbDisplayedSlides: 1,
			textPagination: true,
			auto: false,
			slideDuration: 500,
			pagination: 'buttons',
			swipe : false,
			wrapPagination : false,
			paginers : false,
			onSlide: function(index, slider){
				
			},
			onReady : function(slider){
				
			}
		};

		var currentIndex = 0;
		var $slider = null;
		var slideWidth = 0;
		var slideHeight = 0;
		var nbSlides = 0;
		$.extend(defaults, params);

		return this.each(function()
		{
			$slider = $(this);
			$slider.css('position', 'relative');

			var $slides = $slider.children('.slide');
			nbSlides = $slides.length;
			$slides.css('display', 'inline-block');
			$slides.each(function() {
				slideWidth = Math.max(slideWidth, $(this).outerWidth(true));
				slideHeight = Math.max(slideHeight, $(this).outerHeight(true));
				
			});
			$slides.outerWidth(slideWidth);
			$slides.height(slideHeight);
			$slides.css('float', 'left');
			
			$slider.width(nbSlides * slideWidth);
			var $sliderWrap = $('<div></div>');
			$sliderWrap.width(slideWidth * defaults.nbDisplayedSlides);
			$sliderWrap.addClass("sliderWrap");
			$sliderWrap.css('overflow', 'hidden');
			$slider.wrap($sliderWrap);

			if (defaults.pagination !== 'none' && defaults.nbDisplayedSlides < nbSlides) {
				if(defaults.wrapPagination){
					var $pagination = $('<div></div>');
					$pagination.addClass('paginationSlider');
					$pagination.insertBefore($slider.parent());
				} 
				else {
					var $pagination = $slider.parent().parent();
				}
				var $btLeft = $('<a class="btPaginationSlider linkLeft"></a>');
				$btLeft.click(function() {
					slideLeft();
				});
				if(defaults.pagination === 'both' || defaults.pagination === 'arrows'){
					$pagination.append($btLeft);
				}
				if(defaults.pagination === 'buttons' || defaults.pagination === 'both'){
					for (var i = 0; i < nbSlides; i++) {
						var $btSlide = $('<a class="btPaginationSlider" id="link' + i + '"></a>');
						$pagination.append($btSlide);
						$btSlide.click(function() {
							
							var index =  $('.btPaginationSlider').index($(this));
							slideTo(index);
						
						});
						if (defaults.textPagination) {
							$btSlide.text(i);
						}

					}
				}
				var $btRight = $('<a class="btPaginationSlider linkRight"></a>');
				$btRight.click(function() {
					slideRight();
				});
				if(defaults.pagination === 'both' || defaults.pagination === 'arrows'){
					$pagination.append($btRight);
				}
				if(defaults.textPagination){
					$btLeft.text('<');
					$btRight.text('>');
				}
			}
			if(defaults.paginers){
				var $paginers = $(defaults.paginers);
				$paginers.each(function(){
					$(this).click(function(){
						slideTo($paginers.index(this));
					});
				});
			}
			defaults.onReady($slider);
			slideTo(0);
			if (defaults.auto) {
				setInterval(function() {
					slideRight();
				}, 5000);
			}
			
			if(defaults.swipe){
				$slider.swipe({
					swipeLeft : function() {
						slideRight();
					},
					swipeRight : function() {
						slideLeft();
					},
					threshold:0 
				});
			}

		});

		function slideTo(index) {
			currentIndex = index;
			$slider.animate({
				right: currentIndex * slideWidth
			}, defaults.slideDuration);
			
			defaults.onSlide(index, $slider);
		}

		function slideLeft() {
			if (currentIndex > 0) {
				currentIndex--;
			}
			else {
				currentIndex = nbSlides  - defaults.nbDisplayedSlides;
			}
			slideTo(currentIndex);
		}

		function slideRight() {
			currentIndex = (currentIndex + 1) % (nbSlides - defaults.nbDisplayedSlides +1);
			slideTo(currentIndex);
		}
	};
	
})(jQuery);

