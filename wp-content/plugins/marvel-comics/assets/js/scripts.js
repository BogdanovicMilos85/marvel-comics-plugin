(function ( $ ) {
	'use strict';

	$( document ).ready(
		function () {
			marvelComicsSearch.init();
		}
	);

	var marvelComicsSearch = {
		init: function () {
			this.holder = $( '.marvel-comics-wrapper' );

			if ( this.holder.length ) {
				var $searchField = this.holder.find( '#marvel-comics-search' ),
            $searchSubmit = this.holder.find( '#marvel-comics-submit');

            $searchSubmit.click( () => {
              marvelComicsSearch.getResults();
              $('body').addClass('body-no-scroll');
              $('.marvel-comics-search-results-wrapper').on("click", ".marvel-comics-close" , () => {
                $('.marvel-comics-search-results-wrapper').addClass('close');
                $('body').removeClass('body-no-scroll');
              });
            });
			}
		},
    getResults: function() {
      marvelComicsSearch.addSearchResultHTML();

      var results = $('body').find('.marvel-comics-search-results-wrapper'),
          searchField = this.holder.find( '#marvel-comics-search' );

      $.getJSON(marvelData.root_url + '/wp-json/wp/v2/comics?search=' + searchField.val(), comics => {
      results.html(`
        <h2 class="marvel-comics__section-title">Comics Information</h2>
        <ul class="marvel-comics-search-list">
          ${comics.map(item => `<li><a href="${item.link}">${item.title.rendered}</a></li>`).join('')}
        </ul>
        <button class="marvel-comics-close">Close</button>
        <p>Data provided by Marvel. © 2014 Marvel</p>
      `);
      });
      
    },
    addSearchResultHTML: function() {
      $('body').append('<div class="marvel-comics-search-results-wrapper"></div>');
    },
	};
})( jQuery );