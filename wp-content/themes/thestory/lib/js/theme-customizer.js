
(function($){


	PexetoCustomizer = function(options){
		
		this.options = options;
		this.init();
	};

	PexetoCustomizer.prototype = {
		init : function(){
			var option;

			this.$styles = [];
			this.$header = $('header');

			for(var i in this.options){
				option = this.options[i];

				if(option['type']=='color'){
					this.setColorOption(option);
				}
			}
		},

		getElementsObj : function(rules){
			var $elements,
				elements = [];

			for(var i in rules){
				//cache the elements
				$elements = $(rules[i]);

				if($elements.length){
					elements.push({
						obj : $elements,
						sel : rules[i],
						key : i
					});
				}
			}

			return elements;
		},

		setColorOption : function(option){

			var rules = option.rules,
				elements,
				self = this;

			if(!rules){
				return;
			}

			elements = this.getElementsObj(rules);

			wp.customize( option['id'], function( value ) {
				value.bind( function( newval ) {
					var $style = $('<style />'),
						styleVal = '';


					for(var i in elements){
						styleVal += elements[i].sel+'{'+ elements[i].key+':'+newval+';}';
					}

					if(self.$styles[option['id']]){
						self.$styles[option['id']].remove();
					}

					$style.append(styleVal);
					self.$header.append($style);
					self.$styles[option['id']] = $style;

				} );
			} );

		}
	};


}(jQuery));
