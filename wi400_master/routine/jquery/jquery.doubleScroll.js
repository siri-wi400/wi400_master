/*
 * @name DoubleScroll
 * @desc displays scroll bar on top and on the bottom of the div
 * @requires jQuery, jQueryUI
 *
 * @author Pawel Suwala - http://suwala.eu/
 * @version 0.3 (12-03-2014)
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 */
 
(function($){
    $.widget("suwala.doubleScroll", {
		options: {
            contentElement: undefined, // Widest element, if not specified first child element will be used
            useParent: undefined,
			topScrollBarMarkup: '<div class="doubleScroll-wrapper" style="height: 15px; width: 100%;"><div class="suwala-doubleScroll-scroll" style="height: 15px;"></div></div>',
			topScrollBarInnerSelector: '.suwala-doubleScroll-scroll',			
			scrollCss: {                
				'overflow-x': 'scroll',
				'overflow-y':'hidden'
            },
			contentCss: {
				'overflow-x': 'scroll',
				'overflow-y':'hidden'
			}
        },		
        _create : function() {
            var self = this;
			var contentElement;

            // add div that will act as an upper scroll
			var topScrollBar = $($(self.options.topScrollBarMarkup));
            self.element.before(topScrollBar);

            // find the content element (should be the widest one)
            if (self.options.useParent !== undefined) {
            	contentElement = self.element;
            }else if (self.options.contentElement !== undefined && self.element.find(self.options.contentElement).length !== 0) {
                contentElement = self.element.find(self.options.contentElement);
            }else {
                contentElement = self.element.find('>:first-child');
            }

            // bind upper scroll to bottom scroll
            topScrollBar.scroll(function(){
                self.element.scrollLeft(topScrollBar.scrollLeft());
            });
			
            // bind bottom scroll to upper scroll
            self.element.scroll(function(){
                topScrollBar.scrollLeft(self.element.scrollLeft());
            });

            // apply css
            topScrollBar.css(self.options.scrollCss);
            self.element.css(self.options.contentCss);

            // set the width of the wrappers
			topScrollBar.width(self.element[0].clientWidth);
			$(self.options.topScrollBarInnerSelector, topScrollBar).width(contentElement[0].scrollWidth);
        },
        refresh: function(){
            // this should be called if the content of the inner element changed.
            // i.e. After AJAX data load
            var self = this;
			var contentElement;
            var topScrollBar = self.element.parent().find('.doubleScroll-wrapper');
            topScrollBar.css({"width": "100%", "display": "block"});
            $(self.options.topScrollBarInnerSelector, topScrollBar).css("width", "0");

            // find the content element (should be the widest one)
            if (self.options.useParent !== undefined) {
            	contentElement = self.element;
            }else if (self.options.contentElement !== undefined && self.element.find(self.options.contentElement).length !== 0) {
                contentElement = self.element.find(self.options.contentElement);
            }else {
                contentElement = self.element.find('>:first-child');
            }

            // set the width of the wrappers
        	topScrollBar.width(self.element[0].clientWidth);
            $(self.options.topScrollBarInnerSelector, topScrollBar).width(contentElement[0].scrollWidth);
        }
    });
})(jQuery);
