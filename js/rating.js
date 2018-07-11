(function($) {
  var drupal_counselling_rating = drupal_counselling_rating || {};

  Drupal.behaviors.counsellingRating = {
    attach: function(context, settings) {
        // $('.counselling-score').barrating('show', {
        //     theme: 'bars-square',
        //     showValues: true,
        //     showSelectedRating: false
        // });
        $('.counselling-score').barrating({
            theme: 'css-stars',
            showSelectedRating: false
        });
    }
  };


})(jQuery);
