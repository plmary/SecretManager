(function($){
	/* Template */
	$.fn.notif = function(options){
		var settings = {
			html: '<div class="notification animated fadeIn {{cls}}">\
			<div class="left">\
			{{#icon}}\
				<img class="iconnotification no-border" src="{{icon}}">\
			{{/icon}}\
			{{#img}}\
				<div class="img" style="background-image:url({{img}})">\
				</div>\
			{{/img}}\
			</div>\
			<div class="right">\
				<h2>{{title}}</h2>\
				<p>{{content}}</p>\
			</div>\
		</div>', 
		/* Image par défault img: 'img/evolys.jpg', */
		timeout: false
		}
		if (options.cls == 'error')
			settings.icon ="../../Images/cross.png";
		if (options.cls == 'success')
			settings.icon = "../../Images/valide.png";
		var options = $.extend(settings, options);

		// Création ou création à la suite
		return this.each(function(){
			var $this = $(this);
			var $notifs = $('> .notifications', this);
			var $notif = $(Mustache.render(options.html,
				options));
			// Vérification de l'existence des notifications
			if($notifs.length == 0){
				$notifs = $('<div class="notifications animated fadeIn"/>'
					);
				$this.append($notifs);
			}
			/* On ajoute la dernière la notification à l'ensemble de nos notifications */
			$notifs.append($notif);
			if(options.timeout){
				setTimeout(function(){
					$notif.trigger('click');
				}, options.timeout)
			}
			// Masque les notifications quand on clique
			$notif.click(function(event){
				event.preventDefault();
				// Effet supprime au bout de 300ms
				$notif.addClass('fadeOut').delay(500).slideUp(300, function(){
					// Supprimer les notifications filles
					if($notif.siblings().length==0){
						$notifs = $('<div class="notifications animated fadeIn/>"');
					}
					$notif.remove();
				});
			})
		})
	}

	/* Ajout notification quand clique */
	$('.add').click(function(event){
		event.preventDefault();
		$('body').notif({title:'Mon titre',
			content: 'Mon contenu',
			timeout: 3000});
	})

	$('.addsuccess').blur(function(event){
		$('body').notif({title:'Mon titre',
			content: 'Mon contenu',
			cls: 'success'});
	//		timeout: 2000});
	})

	$('.adderror').blur(function(event){
		$('body').notif({title:'Mon titre',
		content: 'Mon contenu',
		cls: 'error',
		timeout: 2000});
	})
})(jQuery);