(function ($) {
// this ensures jQuery is not breaking other things

	var EpisodePeople = {

		create_person_box: function(label, value) {
			var html = '<div class="person-box"><span class="label"><strong>'+label+'</strong></span> <a class="remove-person" href="#">Remove Person</a><input type="hidden" name="nexus-person[]" value="'+value+'" /></div>';
			return $(html);
		},

		delegate_remove_person: function() {
			$('#people-list').on('click', 'a.remove-person', function(event){
				var parent = $(this).parent('.person-box');
				parent.hide(function(){$(this).remove();});
				return false;
			});
		},

		episode_people_list_inflate: function() {
			var element = $('#people-list-inflate');
			if (!element.length) return false; 
			var peopleList = $("#people-list");
			var self = this;
			var json = element.html();
			var data = $.parseJSON(json);
			peopleList.empty();
			$.each(data, function(i, obj){
				peopleList.append(self.create_person_box(obj.label, obj.value));
			});
			peopleList.append($('<input type="hidden" name="nexus-person-commit" value="1" />'));
			return true;
		},

		setup: function() {
		    var element = $( "#nexus-episode-people-input" );
		    var peopleList = $("#people-list");
		    var self = this;
		    element.autocomplete({
		      source: ajaxurl + '?action=episode_people_search',
		      focus: function(event, ui) {return false;},
		      select: function(event, ui) {
		    	if (ui.item) {
		    		peopleList.append(self.create_person_box(ui.item.label, ui.item.value));
		    	}
		    	$(this).val('');
		    	return false;
		      }
		    });
		    // handle extraneous enter keypress events; prevents early form submission
		    element.bind('keypress', function(event){
		    	var enter = event.keyCode == 13;
		    	if (enter) {
		    		event.stopPropagation();
		    		return false;
		    	}
		    	return true;
		    });
		    this.delegate_remove_person();
		    this.episode_people_list_inflate();
	   	}

	};

	var Episode = {

		create: function(id, ui) {
			var html = '<input type="hidden" name="'+id+'-id" id="'+id+'-id" value="'+ui.item.value+'" />';
			return $(html);
		},

		setup: function() {
			var root = $( "#nexus-episode" );
			var parent = $( "#nexus-parent-episode" );
			var fringe = $( "#nexus-fringe-episode" );
			var self = this;
			var elements = [parent, fringe];
			$.each(elements, function(i, element){
				var id = element.attr('id');
				element.autocomplete({
					source: ajaxurl + '?action=episode_search&id='+id,
					focus: function(event, ui) {
						$(this).val(ui.item.label);
						return false;
					},
					change: function(event, ui) {
						if ( $(this).val() == '' ) {
							var element = $("#"+id+'-id');
							element.remove();
						}
					},
					select: function(event, ui) {
						$(this).val(ui.item.label);
						$("#"+id+'-id').remove();
						var el = self.create(id, ui);
						$(el).insertAfter(element);
						return false;
					}
				});
			    element.bind('keypress', function(event){
			    	var enter = event.keyCode == 13;
			    	if (enter) {
			    		event.stopPropagation();
			    		return false;
			    	}
			    	return true;
			    });
			});
		}

	};

	var TMCEditor = {

		setup: function() {
			var target = $('.wp-editor-area');
			if ( target.length <= 0 || !QTags ) return;
				QTags.addButton( 'links-section', 'Links Section', "<h3>Links</h3>\n<ul>\n\n\n\n</ul>", '', null, 'Links Section', 1 );
				QTags.addButton( 'no-links-section', 'No Links', "<h3>Links</h3>\nThere are no links for this episode.", '', null, 'No Links', 1 );
		}

	};

	var Reformat = {

		

	};

	var AlbumArtSelector = {

		setup: function() {

			var selectors = $('.set-series-featured-image');
			var _this = this;
			$.each(selectors, function(i, obj){

				_this.init($(obj));

			});


		},

		init: function(target) {

			var image = target.find('.image-preview');
			var input = target.find('.image-id');
			var button = target.find('.set-post-thumbnail');


			var _this = this;

			button.on('click', function(event){

				var el = this;

				event.preventDefault();

				if (_this.file_frame) {
					_this.file_frame.open();
					return;
				}

				_this.file_frame = wp.media.frames.file_frame = wp.media({

					title: 'Default Feature Image',
					button: {
						text: 'Select'
					},
					type: 'image',
					multiple: false

				});

				_this.file_frame.on('select', function(){

					attachment = _this.file_frame.state().get('selection').first().toJSON();

					if ( attachment !== null && typeof attachment === 'object' && ('id' in attachment) ) {
						
						input.val(attachment.id);

						if ( 'medium' in attachment.sizes ) {
							image.attr('src', attachment.sizes.medium.url);
						} else if ( 'thumbnail' in attachment.sizes ) {
							image.attr('src', attachment.sizes.thumbnail.url);
						}

					}

				});

				_this.file_frame.open();

			});

		}

	};

	var PersonSelector = {


		setup: function() {

			var selectors = $('.people-selector');
			var _this = this;
			$.each(selectors, function(i, obj){

				_this.init($(obj));

			});

		},

		init: function(target) {

			var input = target.find('.text-selector');
			var people_list = target.find('.people-list')
			var template_element = target.find('.people-template');
			var inflate_element = target.find('.people-list-inflate');

			/*
				TODO:
					fix this episode_people_search
			*/

			input.autocomplete({
		      source: ajaxurl + '?action=episode_people_search',
		      focus: function(event, ui) {return false;},
		      select: function(event, ui) {
		    	if (ui.item == false ) return false;

		    	var data = {label: ui.item.label, value: ui.item.value};

		    	var html = _.template(template_element.html(), data);

		    	people_list.append(html);

				$(this).val('');

		    	return false;
		      }
		    });

			people_list.on('click', 'a.remove-person', function(event){
				var parent = $(this).parent('.person-box');
				parent.hide(function(){$(this).remove();});
				return false;
			});

			people_list.removeClass('hidden');

		    target.bind('keypress', function(event){
		    	var enter = event.keyCode == 13;
		    	if (enter) {
		    		event.stopPropagation();
		    		return false;
		    	}
		    	return true;
		    });

		    this.inflate(inflate_element, people_list, template_element);

		},

		inflate: function(inflate_element, people_list, template_element) {

			var data = $.parseJSON(inflate_element.html());
			var _this = this;
			$.each(data, function(i, obj){

		    	var html = _.template(template_element.html(), obj);

		    	people_list.append(html);

			});

			people_list.append($('<input type="hidden" name="nexus-inflate" value="1" />'));

		}

	};

	$(document).ready(function() {
		EpisodePeople.setup();
		Episode.setup();
		TMCEditor.setup();
		AlbumArtSelector.setup();
		PersonSelector.setup();
	});

}(jQuery));