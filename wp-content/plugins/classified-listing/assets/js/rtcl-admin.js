(function ($) {
  'use restrict';

  $.fn.getType = function () {
    return this[0].tagName == "INPUT" ? this[0].type.toLowerCase() : this[0].tagName.toLowerCase();
  };

  var spinner = '<div class="rtcl-spinner block"><span class="rtcl-icon-spinner animate-spin"></span></div>';
  $(document.body).on('rtcl_add_error_tip', function (e, element, error_type) {
    var offset = element.position();

    if (element.parent().find('.rtcl_error_tip').length === 0) {
      element.after('<div class="rtcl_error_tip ' + error_type + '">' + rtcl[error_type] + '</div>');
      element.parent().find('.rtcl_error_tip').css('left', offset.left + element.width() - element.width() / 2 - $('.rtcl_error_tip').width() / 2).css('top', offset.top + element.height()).fadeIn('100');
    }
  }).on('rtcl_remove_error_tip', function (e, element, error_type) {
    element.parent().find('.rtcl_error_tip.' + error_type).fadeOut('100', function () {
      $(this).remove();
    });
  }).on('click', function () {
    $('.rtcl_error_tip').fadeOut('100', function () {
      $(this).remove();
    });
  }).on('blur', '#rtcl-price[type=text],#rtcl-pricing-price[type=text]', function () {
    $('.rtcl_error_tip').fadeOut('100', function () {
      $(this).remove();
    });
  }).on('keyup', '#rtcl-price[type=text],#rtcl-pricing-price[type=text]', function () {
    var id = $(this).attr('id'),
        decimal_point = rtcl.decimal_point,
        error = 'i18n_mon_decimal_error';

    if (id === 'rtcl-pricing-price') {
      decimal_point = rtcl.pricing_decimal_point;
      error = 'i18n_mon_pricing_decimal_error';
    }

    var regex = new RegExp('[^\-0-9\%\\' + decimal_point + ']+', 'gi');
    var value = $(this).val();
    var newvalue = value.replace(regex, '');

    if (value !== newvalue) {
      $(document.body).triggerHandler('rtcl_add_error_tip', [$(this), error]);
    } else {
      $(document.body).triggerHandler('rtcl_remove_error_tip', [$(this), error]);
    }
  }).on('change', '#rtcl-price[type=text],#rtcl-pricing-price[type=text]', function () {
    var id = $(this).attr('id'),
        decimal_point = id === 'rtcl-pricing-price' ? rtcl.pricing_decimal_point : rtcl.decimal_point;
    var regex = new RegExp('[^\-0-9\%\\' + decimal_point + ']+', 'gi'),
        value = $(this).val(),
        newvalue = value.replace(regex, '');

    if (value !== newvalue) {
      $(this).val(newvalue);
    }
  }).on('rtcl_price_type_changed', function (e, element) {
    if (element.value == "on_call" || element.value == "free" || element.value == "no_price") {
      $('#rtcl-price').attr("required", "false").val('');
      $('#rtcl-price-row').hide();
    } else {
      $('#rtcl-price').attr("required", "true");
      $('#rtcl-price-row').show();
    }
  }).on('change', '#rtcl-price-type', function () {
    $(document.body).trigger('rtcl_price_type_changed', [this]);
  });
  /**
   * Update price unit depends on category
   * @param cat_id
   */

  function load_price_units(cat_id) {
    var $target = $('#rtcl-price-row'),
        price_wrap = $("#rtcl-price-wrap"),
        units_wrap = $("#rtcl-price-unit-wrap", $target),
        has_units = units_wrap.length,
        data = {
      'action': 'rtcl_get_price_units_ajax',
      'term_id': cat_id || 0
    };
    $.ajax({
      url: ajaxurl,
      data: data,
      type: "POST",
      dataType: 'json',
      beforeSend: function beforeSend() {},
      success: function success(data) {
        if (data.html) {
          price_wrap.removeClass('col-md-12').addClass('col-md-6');

          if (has_units) {
            units_wrap.remove();
          }

          $target.append(data.html);
        } else {
          price_wrap.removeClass('col-md-6').addClass('col-md-12');
          units_wrap.remove();
        }
      },
      error: function error() {}
    });
  }

  var RtclTimeStamp = function RtclTimeStamp($target) {
    this._target = $target;
    this.timestampdiv = $('.rtcl-timestamp-div', this._target);
    this.timestamp = $('.rtcl-timestamp', this._target);
    this.stamp = this.timestamp.html();
    this.timestampwrap = this.timestampdiv.find('.timestamp-wrap');
    this.edittimestamp = this.timestampdiv.siblings('a.edit-timestamp');

    this.init = function () {
      var that = this;
      this.edittimestamp.on('click', function (event) {
        if (that.timestampdiv.is(':hidden')) {
          // Slide down the form and set focus on the first field.
          that.timestampdiv.slideDown('fast', function () {
            $('input, select', that.timestampwrap).first().focus();
          });
          $(this).hide();
        }

        event.preventDefault();
      });
      this.timestampdiv.find('.cancel-timestamp').on('click', function (event) {
        // Move focus back to the Edit link.
        that.edittimestamp.show().focus();
        that.timestampdiv.slideUp('fast');
        that.timestampdiv.find('.rtcl-mm').val(that.timestampdiv.find('.rtcl-hidden_mm').val());
        that.timestampdiv.find('.rtcl-jj').val(that.timestampdiv.find('.rtcl-hidden_jj').val());
        that.timestampdiv.find('.rtcl-aa').val(that.timestampdiv.find('.rtcl-hidden_aa').val());
        that.timestampdiv.find('.rtcl-hh').val(that.timestampdiv.find('.rtcl-hidden_hh').val());
        that.timestampdiv.find('.rtcl-mn').val(that.timestampdiv.find('.rtcl-hidden_mn').val());
        that.timestamp.html(that.stamp);
        event.preventDefault();
      });
      this.timestampdiv.find('.save-timestamp').on('click', function (event) {
        // crazyhorse - multiple ok cancels
        var aa = that.timestampdiv.find('.rtcl-aa').val(),
            mm = that.timestampdiv.find('.rtcl-mm').val(),
            jj = that.timestampdiv.find('.rtcl-jj').val(),
            hh = that.timestampdiv.find('.rtcl-hh').val(),
            mn = that.timestampdiv.find('.rtcl-mn').val(),
            newD = new Date(aa, mm - 1, jj, hh, mn);
        event.preventDefault();

        if (newD.getFullYear() != aa || 1 + newD.getMonth() != mm || newD.getDate() != jj || newD.getMinutes() != mn) {
          that.timestampwrap.addClass('form-invalid');
          return;
        } else {
          that.timestampwrap.removeClass('form-invalid');
        }

        that.timestamp.html(rtcl.expiredOn + ' <b>' + rtcl.dateFormat.replace('%1$s', $('option[value="' + mm + '"]', '#mm').attr('data-text')).replace('%2$s', parseInt(jj, 10)).replace('%3$s', aa).replace('%4$s', ('00' + hh).slice(-2)).replace('%5$s', ('00' + mn).slice(-2)) + '</b> '); // Move focus back to the Edit link.

        that.edittimestamp.show().focus();
        that.timestampdiv.slideUp('fast');
      });
    };

    this.init();
  };

  $.fn.rtcl_time_stamp = function () {
    $(this).each(function () {
      console.log($(this));
      return new RtclTimeStamp($(this));
    });
  };

  $('.rtcl-timestamp-wrapper').rtcl_time_stamp();
  $(".misc-pub-rtcl-never-expires").on('click', 'input', function () {
    if ($(this).is(':checked')) {
      $(".misc-pub-rtcl-expiration-time").hide();
    } else {
      $(".misc-pub-rtcl-expiration-time").show();
    }
  });
  $('#rtcl-ad-type').on('change', function () {
    var self = $(this),
        type = self.val(),
        target = $("#rtcl-price-row");
    target.find('.price-label .rtcl-per-unit').remove();

    if (type == 'to_let') {
      var unit = target.find('label').attr("data-per-unit");
      target.find('.price-label').append('<span class="rtcl-per-unit"> / ' + unit + '</span>');
    }

    if (type == 'job' || type == '') {
      $("#rtcl-form-price-wrap").slideUp(250);
    } else {
      $("#rtcl-form-price-wrap").slideDown(250);
    }

    if (type) {
      var data = {
        'action': 'rtcl_get_one_level_category_select_list_by_type',
        'type': type
      };
      $.ajax({
        url: ajaxurl,
        data: data,
        type: "POST",
        beforeSend: function beforeSend() {
          $(spinner).insertAfter(self);
          $('#rtcl-custom-fields-list').html('');
          $('#rtcl_sub_category').html('');
          $('#sub-cat-row').addClass("rtcl-hide");
        },
        success: function success(response) {
          console.log(response);
          self.next('.rtcl-spinner').remove();

          if (response.success) {
            $('#rtcl_category').html(response.cats);
          } else {
            $('#rtcl_category').html('');
          }
        },
        error: function error(e) {
          self.next('.rtcl-spinner').remove();
          console.log(e.responseText);
        }
      });
    } else {
      $('#rtcl-custom-fields-list').html('');
      $('#rtcl_category').html('');
      $('#rtcl_sub_category').html('');
      $('#sub-cat-row').addClass("rtcl-hide");
    }
  });
  $('#rtcl_category').on('change', function () {
    var self = $(this),
        term_id = $(this).val() || 0,
        data = {
      'action': 'rtcl_custom_fields_listings',
      'post_id': $('#rtcl-custom-fields-list').data('post_id'),
      'term_id': term_id,
      'is_admin': rtcl.is_admin
    };
    $.ajax({
      url: ajaxurl,
      data: data,
      type: "POST",
      beforeSend: function beforeSend() {
        $(spinner).insertAfter(self);
        $('#rtcl-custom-fields-list').html(spinner);
      },
      success: function success(data) {
        self.next('.rtcl-spinner').remove();
        $('#rtcl-custom-fields-list').html(data.custom_fields);
        $('#rtcl_sub_category').html(data.child_cats);

        if (data.child_cats) {
          $('#sub-cat-row').removeClass("rtcl-hide");
        } else {
          $('#sub-cat-row').addClass("rtcl-hide");
        }
      },
      error: function error() {
        self.next('.rtcl-spinner').remove();
      }
    });
    load_price_units(term_id);
  });
  $('#rtcl_sub_category').on('change', function () {
    var self = $(this),
        term_id = $(this).val() || $('#rtcl_category').val(),
        data = {
      'action': 'rtcl_custom_fields_listings',
      'post_id': $('#rtcl-custom-fields-list').data('post_id'),
      'term_id': term_id
    };
    $.ajax({
      url: ajaxurl,
      data: data,
      type: "POST",
      beforeSend: function beforeSend() {
        $(spinner).insertAfter(self);
        $('#rtcl-custom-fields-list').html(spinner);
      },
      success: function success(data) {
        self.next('.rtcl-spinner').remove();
        $('#rtcl-custom-fields-list').html(data.custom_fields);
      },
      error: function error() {
        self.next('.rtcl-spinner').remove();
      }
    });
    load_price_units(term_id);
  });

  if ($.fn.validate) {
    // Listing validation
    $(".post-type-rtcl_listing #post").validate(); // Pricing validation

    $(".post-type-rtcl_pricing #post").validate();
  }

  $("#send-email-to-user").on('click', function (e) {
    e.preventDefault();
    var dialog = $('<div style="display:none;height:450px;" id="user-message"><div class="form-group"><textarea class="rtcl-form-control" rows="6"></textarea></div><a class="message-send button button-primary button-large">Send</a></div>').appendTo('body');
    dialog.dialog({
      close: function close(event, ui) {
        dialog.remove();
      },
      open: function open(event, ui) {
        $('#user-message').parent('.ui-dialog').css('zIndex', 9999).nextAll('.ui-widget-overlay').css('zIndex', 9998);
      },
      closeText: false,
      modal: true,
      maxWidth: 850,
      zIndex: 9999,
      maxHeight: .9 * $(window).height(),
      title: "Message",
      position: {
        my: "center top+50",
        at: "center top",
        of: window
      }
    });
    dialog.on('click', 'a.message-send', function (e) {
      e.preventDefault();
      var it = $(this),
          post_id = $("#post_ID").val(),
          message = dialog.find("textarea").val();

      if (post_id && message) {
        var data = {
          action: "rtcl_send_email_to_user_by_moderator",
          post_id: post_id,
          message: message,
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };
        $.ajax({
          type: "POST",
          url: rtcl.ajaxurl,
          data: data,
          beforeSend: function beforeSend() {
            $(".rtcl-flash-messages").remove();
            it.addClass('disabled').attr('disabled', "disabled");
            $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(it);
          },
          success: function success(data) {
            it.removeClass('disabled');
            $('.rtcl-icon-spinner', dialog).remove();
            var flash = $("<div class='rtcl-flash-messages'>" + data.message + "</div>");
            flash.insertAfter(it);
            flash.addClass(data["class"]);

            if (!data.error) {
              setTimeout(function () {
                dialog.dialog('close');
              }, 1000);
            } else {
              it.removeAttr("disabled");
            }
          },
          error: function error(jqXHR, exception) {
            $('.rtcl-icon-spinner', dialog).remove();
            dialog.dialog('close');
            it.removeAttr("disabled");

            if (jqXHR.status === 0) {
              alert('Not connect.\n Verify Network.');
            } else if (jqXHR.status == 404) {
              alert('Requested page not found. [404]');
            } else if (jqXHR.status == 500) {
              alert('Internal Server Error [500].');
            } else if (exception === 'parsererror') {
              alert('Requested JSON parse failed.');
            } else if (exception === 'timeout') {
              alert('Time out error.');
            } else if (exception === 'abort') {
              alert('Ajax request aborted.');
            } else {
              alert('Uncaught Error.\n' + jqXHR.responseText);
            }
          }
        });
      } else {
        alert("Please add some message!!");
      }

      return false;
    });
    return false;
  });
  /* Ready function */

  $(function () {
    $("#rtcl-price-type").trigger("change");

    if ($.fn.select2) {
      $('.rtcl-select2').select2({
        dropdownAutoWidth: true,
        width: '100%'
      });
    }

    if ($("#expiry-date").length) {
      $('#expiry-date').datetimepicker();
    } // First level


    $('#rtcl-location').on('change', function () {
      var self = $(this),
          data = {
        'action': 'rtcl_get_sub_location_options',
        'term_id': $(this).val(),
        'blank': true
      };
      $.ajax({
        url: ajaxurl,
        data: data,
        type: 'POST',
        beforeSend: function beforeSend() {
          $(spinner).insertAfter(self);
        },
        success: function success(data) {
          self.next('.rtcl-spinner').remove();
          $('#rtcl-sub-location').html(data.locations);
          $('#rtcl-sub-sub-location').html('').addClass('rtcl-hide');

          if (data.locations) {
            $('#sub-location-row').removeClass('rtcl-hide');
          } else {
            $('#sub-location-row').addClass('rtcl-hide');
          }
        },
        error: function error() {
          self.next('.rtcl-spinner').remove();
        }
      });
    }); // Second level

    $('#rtcl-sub-location').on('change', function () {
      var self = $(this),
          data = {
        'action': 'rtcl_get_sub_location_options',
        'term_id': $(this).val(),
        'blank': true
      };
      $.ajax({
        url: ajaxurl,
        data: data,
        type: 'POST',
        beforeSend: function beforeSend() {
          $(spinner).insertAfter(self);
        },
        success: function success(data) {
          self.next('.rtcl-spinner').remove();
          $('#rtcl-sub-sub-location').html(data.locations);

          if (data.locations) {
            $('#sub-sub-location-row').removeClass('rtcl-hide');
          } else {
            $('#sub-sub-location-row').addClass('rtcl-hide');
          }
        },
        error: function error() {
          self.next('.rtcl-spinner').remove();
        }
      });
    });
  });
})(jQuery);

(function ($) {
  // on dialogopen
  $(document).on('dialogopen', '.ui-dialog', function (e, ui) {
    // normalize primary buttons
    $('button.button-primary, button.wpcf-ui-dialog-cancel').blur().addClass('button').removeClass('ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only');
  }); // resize

  var resizeTimeout;
  $(window).on('resize scroll', function () {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(dialogResize, 200);
  });

  function dialogResize() {
    $('.ui-dialog').each(function () {
      $(this).css({
        'maxWidth': '100%',
        'zIndex': 9999,
        'top': $(window).scrollTop() + 50 + 'px',
        'left': ($('body').innerWidth() - $(this).outerWidth()) / 2 + 'px'
      });
    });
  }
  /**
   * Payment Notes Panel
   */


  var rtcl_meta_boxes_payment_notes = {
    init: function init() {
      $('#rtcl-payment-notes').on('click', 'button.add-note', this.add_order_note).on('click', 'a.delete_note', this.delete_order_note);
    },
    add_order_note: function add_order_note() {
      if (!$('textarea#rtcl-add-payment-note').val()) {
        return;
      }

      var data = {
        action: 'rtcl_add_payment_note',
        post_id: parseInt($('#post_ID').val(), 10) || 0,
        note: $('textarea#rtcl-add-payment-note').val(),
        note_type: $('select#rtcl-payment-note-type').val(),
        __rtcl_wpnonce: rtcl.__rtcl_wpnonce
      };
      $.ajax({
        url: rtcl.ajaxurl,
        data: data,
        type: "POST",
        dataType: 'json',
        beforeSend: function beforeSend() {
          $('#rtcl-payment-notes').block({
            message: null,
            overlayCSS: {
              background: '#fff',
              opacity: 0.6
            }
          });
        },
        success: function success(response) {
          $('ul.rtcl_payment_notes').prepend(response.html);
          $('#rtcl-payment-notes').unblock();
          $('#rtcl-add-payment-note').val('');
        },
        error: function error() {
          $('#rtcl-payment-notes').unblock();
        }
      });
      return false;
    },
    delete_order_note: function delete_order_note() {
      if (window.confirm(rtcl.i18n_delete_note)) {
        var note = $(this).closest('li.note');
        var data = {
          action: 'rtcl_delete_payment_note',
          note_id: $(note).attr('rel'),
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: "POST",
          dataType: 'json',
          beforeSend: function beforeSend() {
            $(note).block({
              message: null,
              overlayCSS: {
                background: '#fff',
                opacity: 0.6
              }
            });
          },
          success: function success(res) {
            if (res.success) {
              $(note).remove();
            }
          }
        });
      }

      return false;
    }
  };
  rtcl_meta_boxes_payment_notes.init();
})(jQuery);
