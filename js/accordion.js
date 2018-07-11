(function($) {
  var drupal_counselling_accordion = drupal_counselling_accordion || {};

  Drupal.behaviors.counsellingAccordion = {
    attach: function(context, settings) {

        $('.appointment-table').each( function() {
           if($(this).hasClass('active')) {
               $(this).accordion({
                   collapsible: true,
                   heightStyle: "content"
               });
           }
           else {
               $(this).accordion({
                   active: false,
                   collapsible: true,
                   heightStyle: "content"
               });
           }
        });
    }
  };


})(jQuery);
