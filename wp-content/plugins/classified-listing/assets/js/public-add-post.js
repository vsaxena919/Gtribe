;

(function ($) {
  'use restrict';

  $.fn.getType = function () {
    return this[0].tagName == "INPUT" ? this[0].type.toLowerCase() : this[0].tagName.toLowerCase();
  };

  var spinner = '<div class="rtcl-spinner block"><span class="rtcl-icon-spinner animate-spin"></span></div>';

  if ($.fn.validate) {
    var submitForm = $("form#rtcl-post-form");

    if (submitForm.length) {
      submitForm.validate({
        submitHandler: function submitHandler(form) {
          try {
            tinymce.triggerSave();
            var editor = tinymce.get("description");
            editor.save();
          } catch (e) {}

          if (rtcl.recaptcha_listing > 0) {
            var response = grecaptcha.getResponse(rtcl.recaptcha_responce['listing']);

            if (0 == response.length) {
              $('#rtcl-listing-g-recaptcha-message').addClass('text-danger').html(rtcl.recaptcha_invalid_message);
              grecaptcha.reset(rtcl.recaptcha_responce['listing']);
              return false;
            }
          }

          var target = submitForm.parent('.rtcl-post-form-wrap'),
              targetBtn = $(".rtcl-submit-btn", submitForm),
              msgHolder = $("<div class='alert rtcl-response'></div>");
          $.ajax({
            url: rtcl.ajaxurl,
            type: "POST",
            dataType: 'json',
            data: $(form).serialize() + "&action=rtcl_post_new_listing",
            beforeSend: function beforeSend() {
              submitForm.find('.alert.rtcl-response').remove();
              target.addClass('rtcl-loading');
              targetBtn.find(".rtcl-icon-spinner").remove();
              targetBtn.attr('disabled', true).append("<span class='rtcl-icon-spinner animate-spin'> </span>");
            },
            success: function success(response) {
              targetBtn.prop("disabled", false).find(".rtcl-icon-spinner").remove();
              target.removeClass("rtcl-loading");
              var msg = '';

              if (response.success) {
                submitForm[0].reset();

                if (response.success_message.length) {
                  response.success_message.map(function (message) {
                    msg += "<p>" + message + "</p>";
                  });
                }

                if (msg) {
                  msgHolder.removeClass('alert-danger').addClass('alert-success').html(msg).appendTo(submitForm);
                }

                if (response.redirect_url) {
                  window.location.href = response.redirect_url;
                }
              } else {
                if (response.error_message.length) {
                  response.error_message.map(function (message) {
                    msg += "<p>" + message + "</p>";
                  });

                  if (msg) {
                    msgHolder.removeClass('alert-success').addClass('alert-danger').html(msg).appendTo(submitForm);
                  }
                }
              }
            },
            error: function error(e) {
              console.log(e);
              msgHolder.removeClass('alert-success').addClass('alert-danger').html(e.responseText).appendTo(submitForm);
              targetBtn.prop("disabled", false).find(".rtcl-icon-spinner").remove();
              target.removeClass("rtcl-loading");
            }
          });
          return false;
        }
      });
    }
  }

  function rtcl_delete_on_unload() {
    var pId = parseInt($("#_post_id").val(), 10);

    if (!pId || pId === 0 || isNaN(pId)) {
      return;
    }

    var data = {
      action: 'rtcl_delete_temp_listing',
      __rtcl_wpnonce: rtcl.__rtcl_wpnonce,
      id: pId
    };
    $.ajax(rtcl.ajaxurl, {
      data: data,
      dataType: 'json',
      type: 'post',
      success: function success(response) {}
    });
  }

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
      url: rtcl.ajaxurl,
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

  $(document.body).on('rtcl_price_type_changed', function (e, element) {
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
  /* Ready */

  $(function () {
    $("#rtcl-price-type").trigger("change");

    if ($.fn.select2 && $('.rtcl-select2').length) {} //$('.rtcl-select2').select2();

    /* only free version */


    $('#rtcl-ad-type').on('change', function () {
      var self = $(this),
          type = self.val(),
          target = $("#rtcl-price-row");
      target.find('.price-label .rtcl-per-unit').remove();

      if (type == 'to_let') {
        var unit = target.find('label').attr("data-per-unit");
        target.find('.price-label').append('<span class="rtcl-per-unit"> / ' + unit + '</span>');
      }

      if (type == 'job') {
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
          url: rtcl.ajaxurl,
          data: data,
          type: "POST",
          dataType: 'json',
          beforeSend: function beforeSend() {
            $(spinner).insertAfter(self);
            $('#rtcl-custom-fields-list').html('');
            $('#rtcl-sub-category').html('');
            $('#sub-cat-row').addClass("rtcl-hide");
          },
          success: function success(response) {
            console.log(response);
            self.next('.rtcl-spinner').remove();

            if (response.success) {
              $('#rtcl-category').html(response.cats);
            } else {
              $('#rtcl-category').html('');
            }
          },
          error: function error(e) {
            self.next('.rtcl-spinner').remove();
            console.log(e.responseText);
          }
        });
      } else {
        $('#rtcl-custom-fields-list').html('');
        $('#rtcl-category').html('');
        $('#rtcl-sub-category').html('');
        $('#sub-cat-row').addClass("rtcl-hide");
      }
    });
    $('#rtcl-category').on('change', function () {
      var self = $(this),
          target = $('#rtcl-custom-fields-list'),
          term_id = $(this).val(),
          data = {
        'action': 'rtcl_custom_fields_listings',
        'post_id': target.data('post_id'),
        'term_id': term_id,
        'is_admin': rtcl.is_admin
      };
      $.ajax({
        url: rtcl.ajaxurl,
        data: data,
        type: "POST",
        dataType: 'json',
        beforeSend: function beforeSend() {
          target.html(spinner);
          $(spinner).insertAfter(self);
        },
        success: function success(data) {
          self.next('.rtcl-spinner').remove();
          target.html(data.custom_fields);

          if (term_id) {
            $('#rtcl-sub-category').html(data.child_cats);

            if (data.child_cats) {
              $('#sub-cat-row').removeClass("rtcl-hide");
            } else {
              $('#sub-cat-row').addClass("rtcl-hide");
            }
          } else {
            $('#sub-cat-row').addClass("rtcl-hide");
          }
        },
        error: function error() {
          target.html('');
          self.next('.rtcl-spinner').remove();
        }
      });
      load_price_units(term_id);
    });
    $('#rtcl-sub-category').on('change', function () {
      $('#rtcl-custom-fields-list').html(spinner);
      var self = $(this),
          target = $('#rtcl-custom-fields-list'),
          term_id = $(this).val() || $('#rtcl-category').val(),
          data = {
        'action': 'rtcl_custom_fields_listings',
        'post_id': target.data('post_id'),
        'term_id': term_id
      };
      $.ajax({
        url: rtcl.ajaxurl,
        data: data,
        type: "POST",
        dataType: 'json',
        beforeSend: function beforeSend() {
          target.html(spinner);
          $(spinner).insertAfter(self);
        },
        success: function success(data) {
          self.next('.rtcl-spinner').remove();
          target.html(data.custom_fields);
        },
        error: function error() {
          self.next('.rtcl-spinner').remove();
          target.html('');
        }
      });
      load_price_units(term_id);
    }); // First level

    $('#rtcl-location').on('change', function () {
      var self = $(this),
          subLocation = $('#rtcl-sub-location'),
          subLocationRow = subLocation.parents('#sub-location-row'),
          data = {
        'action': 'rtcl_get_sub_location_options',
        'term_id': $(this).val()
      };
      $.ajax({
        url: rtcl.ajaxurl,
        data: data,
        type: 'POST',
        dataType: 'json',
        beforeSend: function beforeSend() {
          $(spinner).insertAfter(self);
        },
        success: function success(data) {
          self.next('.rtcl-spinner').remove();
          subLocation.find('option').each(function () {
            if ($(this).val().trim()) {
              $(this).remove();
            }
          });
          subLocation.append(data.locations);

          if (data.locations) {
            subLocationRow.removeClass('rtcl-hide');
          } else {
            subLocationRow.addClass('rtcl-hide');
          }
        },
        error: function error() {
          self.next('.rtcl-spinner').remove();
        }
      });
    }); // Second level

    $('#rtcl-sub-location').on('change', function () {
      var self = $(this),
          subSubLocation = $('#rtcl-sub-sub-location'),
          subSubLocationRow = subSubLocation.parents('#sub-sub-location-row'),
          data = {
        'action': 'rtcl_get_sub_location_options',
        'term_id': $(this).val()
      };
      $.ajax({
        url: rtcl.ajaxurl,
        data: data,
        type: 'POST',
        dataType: 'json',
        beforeSend: function beforeSend() {
          $(spinner).insertAfter(self);
        },
        success: function success(data) {
          self.next('.rtcl-spinner').remove();
          subSubLocation.find('option').each(function () {
            if ($(this).val().trim()) {
              $(this).remove();
            }
          });
          subSubLocation.append(data.locations);

          if (data.locations) {
            subSubLocationRow.removeClass('rtcl-hide');
          } else {
            subSubLocationRow.addClass('rtcl-hide');
          }
        },
        error: function error() {
          self.next('.rtcl-spinner').remove();
        }
      });
    });
  });
  $(window).bind("beforeunload", rtcl_delete_on_unload);
})(jQuery);
