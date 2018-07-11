(function($) {
  var drupal_calendar = drupal_calendar || {};

  Drupal.behaviors.calendar = {
    attach: function(context, settings) {

      var calendarWrapper = $('#calendar-wrapper');
      var calendarContainer = $('.counselling-calendar--container');
      var calendar = $('.counselling-calendar');
      var practitioner = $('.practitioner');
      var timesWrapper = $('#times-wrapper');
      timesWrapper.append('<div id="time-validator" style="height: 0;"></div>');
      var appointmentType = settings.counselling.appointmentType;

      var form = calendar.closest('form');
      var submit = form.find('#edit-submit');
      submit.attr('disabled', 'disabled');

      var events = settings.counselling.events;
      calendar.getCalendar(events);

      practitioner.click( function () {
         practitionerId = $(this).attr('practitioner-id');
         $('#practitioner-id').val(practitionerId);
         $(this).removeClass('disabled-practitioner').addClass('selected-practitioner');
         $(this).siblings('.practitioner').removeClass('selected-practitioner').addClass('disabled-practitioner');

         var practitionerEvents = settings.counselling['events_' + practitionerId];
         calendar.getCalendar(practitionerEvents, practitionerId);
         calendarWrapper.removeClass('disabled-wrapper').find('.help').slideUp(100);
         calendarContainer.slideDown(100);

         timesWrapper.find('.selected-time').removeClass('selected-time');
         timesWrapper.find('.times-container').hide();
         timesWrapper.find('.help').show();
         submit.attr('disabled', 'disabled');
         $(this).scrollTo('#calendar-wrapper', 500);
      });

      if(settings.counselling.selectedDate) {

          calendarWrapper.find('.help').hide();
          timesWrapper.find('.help').hide();

          var date = settings.counselling.selectedDate;
          var eventId = settings.counselling.eventId;
          var practitionerId = settings.counselling.practitionerId;

          if (practitionerId && appointmentType === 'counselling') {
            var practitionerEvents = settings.counselling['events_' + practitionerId];
            calendar.getCalendar(practitionerEvents);
          }
          else {
            calendar.getCalendar(events);
          }

          $('.' + 'practitioner-' + practitionerId).addClass('selected-practitioner');
          $('#' + date).show().find('.appointment-time[appointment_id="' + eventId + '"]').addClass('selected-time');

          calendarContainer.show();
          submit.removeAttr('disabled', 'disabled');
      }

      $('.appointment-time').click( function() {
          $('.appointment-time').removeClass('selected-time');
          $(this).addClass('selected-time');
          var appointmentId = $(this).attr('appointment_id');
          $('#appointment-id').val(appointmentId);
          $('#time-validator input').remove();
          //submit.removeAttr('disabled', 'disabled');
      });

    }
  };

    $.getCalendar = $.fn.getCalendar = function(events, practitionerId) {
        practitionerId = practitionerId || false;

        var form = this.closest('form');
        var timesWrapper = $('#times-wrapper');

        this.fullCalendar('destroy');
        this.fullCalendar({
            firstDay: 1,
            showNonCurrentDates: false,
            eventSources: [
                {
                    dayCount: 30,
                    events: events,
                    color: 'white',     // an option!
                    textColor: 'purple' // an option!
                }
            ],

            dayRender: function(date, cell){

              if (!cell.hasClass('fc-event-container') && !cell.hasClass('fc-disabled-day')) {
                  cell.html('<div class="empty-date">' + date.format('DD') + '</div>');
              }

            },

            eventClick: function(calEvent, jsEvent, view) {

                var alreadySelected = $(jsEvent.target).closest('.fc-event').hasClass('selected-date');

                if (!alreadySelected) {
                    if($('#time-validator').html() == '') {
                        var validatorInput = '<input type="text" class="form-text" value="" required oninvalid="setCustomValidity(\'Please select the time of the appointment\')"/>';
                        $('#time-validator').append(validatorInput);
                    }

                    $('.selected-time').removeClass('selected-time');
                    $(jsEvent.target).closest('.fc-event').addClass('selected-date');

                    $('#times-wrapper').removeClass('disabled-wrapper').find('.help').hide();

                    if(practitionerId) {
                        $('.times-container').find('.practitioner-' + practitionerId).siblings('.appointment-time').hide();
                        $('.times-container').find('.practitioner-' + practitionerId).show();
                    }

                    $('#' + calEvent.stringDate).show().siblings('.times-container').hide();
                    $('#appointment-id').val('');

                    form.find('#edit-submit').removeAttr('disabled', 'disabled');

                    $(this).scrollTo('#times-wrapper', 500);

                }
            }

        });
    }

    $.scrollTo = $.fn.scrollTo = function(target, time) {
        var time = time || 300;
        $('html, body').animate({
            scrollTop: $(target).offset().top
        }, time);
    }


})(jQuery);
