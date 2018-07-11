/**
 * @file contains the JS file for Scheduled product price.
 */

(function ($) {
    Drupal.behaviors.tablePager = {
        attach: function (context, settings) {

            $.each( settings.appointmentTables, function( type, table ) {
                var wrapper = table + '-wrapper';
                var pagerLink = $(wrapper).siblings('.item-list').find('ul.pager li a');
                var headerLink = $(wrapper).find('th a');
                var accordionSelected = $(wrapper).closest('.appointment-table');
                accordionSelected.addClass('active');
                accordionSelected.accordion();

                pagerLink.click(function(e) {
                    var url = this.href + table;
                    $.ajax({
                        url: url,
                        dataType: 'html',
                        success: function(data) {

                            var newTable = $(data).find(table);
                            var newPager = $(data).find(wrapper).siblings('.item-list').find('ul.pager');

                            $(wrapper).html(newTable);
                            $(wrapper).siblings('.item-list').html(newPager);

                            Drupal.attachBehaviors($(wrapper).siblings('.item-list'));

                            accordionSelected.addClass('active');
                            accordionSelected.accordion();
                        }

                    });
                    return false;
                });

                headerLink.click(function(e) {
                    var url = this.href + table;
                    $.ajax({
                        url: url,
                        dataType: 'html',
                        success: function(data) {

                            var newTable = $(data).find(table);
                            var newPager = $(data).find(wrapper).siblings('.item-list').find('ul.pager');

                            $(wrapper).html(newTable);
                            $(wrapper).siblings('.item-list').html(newPager);

                            Drupal.attachBehaviors($(wrapper).find('th a'));

                            accordionSelected.addClass('active');
                            accordionSelected.accordion();
                        }

                    });
                    return false;

                });

            });

        }
    };
})(jQuery);