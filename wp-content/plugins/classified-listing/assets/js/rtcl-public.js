(function ($) {
  'use strict';

  window.rtcl_make_checkout_request = function (form, callback) {
    var $form = $(form),
        $submitBtn = $("input[type=submit]", $form),
        msgHolder = $("<div class='alert rtcl-response'></div>"),
        date = $form.serialize();
    $.ajax({
      url: rtcl.ajaxurl,
      data: date,
      type: "POST",
      dataType: 'JSON',
      beforeSend: function beforeSend() {
        $submitBtn.prop('disabled', true);
        $form.find('.alert.rtcl-response').remove();
        $submitBtn.find('.rtcl-icon-spinner').remove();
        $submitBtn.append("<span class='rtcl-icon-spinner animate-spin'></span>");
      },
      success: function success(response) {
        $submitBtn.prop('disabled', false);
        $submitBtn.find('.rtcl-icon-spinner').remove();
        var msg = '';

        if (response.success) {
          if (response.success_message.length) {
            response.success_message.map(function (message) {
              msg += "<p>" + message + "</p>";
            });
          }

          if (msg) {
            msgHolder.removeClass('alert-danger').addClass('alert-success').html(msg).appendTo($form);
          }
        } else {
          if (response.error_message.length) {
            response.error_message.map(function (message) {
              msg += "<p>" + message + "</p>";
            });
          }

          if (msg) {
            msgHolder.removeClass('alert-success').addClass('alert-danger').html(msg).appendTo($form);
          }

          if (typeof callback === 'function') {
            callback();
          }
        }

        setTimeout(function () {
          if (response.redirect_url) {
            window.location = response.redirect_url;
          }
        }, 1000);
      },
      error: function error(e) {
        $submitBtn.prop('disabled', false);
        $submitBtn.find('.rtcl-icon-spinner').remove();

        if (typeof callback === 'function') {
          callback();
        }
      }
    });
  };

  window.rtcl_on_recaptcha_load = function () {
    if ('' != rtcl.recaptcha_site_key) {
      // Add reCAPTCHA in registration form
      if ($("#rtcl-registration-g-recaptcha").length) {
        if ($.inArray("registration", rtcl.recaptchas) != -1) {
          rtcl.recaptcha_registration = 1;
          rtcl.recaptcha_responce['registration'] = grecaptcha.render('rtcl-registration-g-recaptcha', {
            'sitekey': rtcl.recaptcha_site_key
          });
          $("#rtcl-registration-g-recaptcha").addClass('mb-2');
        }
      } else {
        rtcl.recaptcha_registration = 0;
      } // Add reCAPTCHA in listing form


      if ($("#rtcl-listing-g-recaptcha").length) {
        if ($.inArray("listing", rtcl.recaptchas) != -1) {
          rtcl.recaptcha_listing = 1;
          rtcl.recaptcha_responce['listing'] = grecaptcha.render('rtcl-listing-g-recaptcha', {
            'sitekey': rtcl.recaptcha_site_key
          });
        }
      } else {
        rtcl.recaptcha_listing = 0;
      } // Add reCAPTCHA in contact form


      if ($("#rtcl-contact-g-recaptcha").length) {
        if ($.inArray("contact", rtcl.recaptchas) != -1) {
          rtcl.recaptcha_responce['contact'] = grecaptcha.render('rtcl-contact-g-recaptcha', {
            'sitekey': rtcl.recaptcha_site_key
          });
          rtcl.recaptcha_contact = 1;
        }
      } else {
        rtcl.recaptcha_contact = 0;
      } // Add reCAPTCHA in report abuse form


      if ($("#rtcl-report-abuse-g-recaptcha").length) {
        if ($.inArray("report_abuse", rtcl.recaptchas) != -1) {
          rtcl.recaptcha_responce['report_abuse'] = grecaptcha.render('rtcl-report-abuse-g-recaptcha', {
            'sitekey': rtcl.recaptcha_site_key
          });
          rtcl.recaptcha_report_abuse = 1;
        }
      } else {
        rtcl.recaptcha_report_abuse = 0;
      }
    }
  };

  function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
      sParameterName = sURLVariables[i].split('=');

      if (sParameterName[0] === sParam) {
        return sParameterName[1] === undefined ? true : sParameterName[1];
      }
    }
  }

  function equalHeight() {
    $(".rtcl-equal-height").each(function () {
      var $equalItemWrap = $(this),
          equalItems = $equalItemWrap.find('.equal-item');
      equalItems.height('auto');

      if ($(window).width() > 767) {
        var maxH = 0;
        equalItems.each(function () {
          var itemH = $(this).outerHeight();

          if (itemH > maxH) {
            maxH = itemH;
          }
        });
        equalItems.height(maxH + 'px');
      } else {
        equalItems.height('auto');
      }
    });
  } // On ready function


  $(function () {
    $(".rtcl-delete-listing").on('click', function (e) {
      e.preventDefault();

      if (confirm(rtcl.confirm_text)) {
        var _self = $(this),
            data = {
          action: 'rtcl_delete_listing',
          post_id: parseInt(_self.attr("data-id"), 10),
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };

        if (data.post_id) {
          $.ajax({
            url: rtcl.ajaxurl,
            data: data,
            type: "POST",
            beforeSend: function beforeSend() {
              $("<span class='rtcl-icon-spinner animate-spin'></span>").insertAfter(_self);
            },
            success: function success(data) {
              _self.next('.rtcl-icon-spinner').remove();

              if (data.success) {
                _self.parents(".rtcl-listing-item").animate({
                  height: 0,
                  opacity: 0
                }, 'slow', function () {
                  $(this).remove();
                });
              }
            },
            error: function error() {
              _self.next('.rtcl-icon-spinner').remove();
            }
          });
        }
      }

      return false;
    });
    $(".rtcl-delete-favourite-listing").on('click', function (e) {
      e.preventDefault();

      if (confirm(rtcl.confirm_text)) {
        var _self = $(this),
            data = {
          action: 'rtcl_public_add_remove_favorites',
          post_id: parseInt(_self.attr("data-id"), 10),
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };

        if (data.post_id) {
          $.ajax({
            url: rtcl.ajaxurl,
            data: data,
            type: "POST",
            beforeSend: function beforeSend() {
              $("<span class='rtcl-icon-spinner animate-spin'></span>").insertAfter(_self);
            },
            success: function success(data) {
              _self.next('.rtcl-icon-spinner').remove();

              if (data.success) {
                _self.parents(".rtcl-listing-item").animate({
                  height: 0,
                  opacity: 0
                }, 'slow', function () {
                  $(this).remove();
                });
              }
            },
            error: function error(e) {
              _self.next('.rtcl-icon-spinner').remove();
            }
          });
        }
      }

      return false;
    });
    $("#rtcl-checkout-form").on('click', 'input[name="pricing_id"]', function (e) {
      if ($(this).val() == 0) {
        $("#rtcl-payment-methods, #rtcl-checkout-submit-btn").slideUp(250);
      } else {
        $("#rtcl-payment-methods, #rtcl-checkout-submit-btn").slideDown(250);
      }
    });
    $("#rtcl-checkout-form").on('change', 'input[name="payment_method"]', function (e) {
      var target_payment_box = $('div.payment_box.payment_method_' + $(this).val());

      if ($(this).is(':checked') && !target_payment_box.is(':visible')) {
        $('#rtcl-checkout-form div.payment_box').filter(':visible').slideUp(250);

        if ($(this).is(':checked')) {
          target_payment_box.slideDown(250);
        }
      }
    }); // Toggle password fields in user account form

    $('#rtcl-change-password').on('change', function () {
      var $checked = $(this).is(":checked");

      if ($checked) {
        $('.rtcl-password-fields').show().find('input[type="password"]').attr("disabled", false);
      } else {
        $('.rtcl-password-fields').hide().find('input[type="password"]').attr("disabled", "disabled");
      }
    }).trigger('change'); // Report abuse [on modal closed]

    $('#rtcl-report-abuse-modal').on('hidden.bs.modal', function (e) {
      $('#rtcl-report-abuse-message').val('');
      $('#rtcl-report-abuse-message-display').html('');
      $(this).find('.modal-dialog').removeClass('modal-vertical-centered');
    });
    $('#rtcl-report-abuse-modal').on('shown.bs.modal', function () {
      $(this).find('.modal-dialog').addClass('modal-vertical-centered');
    }); // Alert users to login (only if applicable)

    $('.rtcl-require-login').on('click', function (e) {
      e.preventDefault();
      alert(rtcl.user_login_alert_message);
    });
    $('.rtcl-do-email').on('click', 'a', function (e) {
      e.preventDefault();

      var _self = $(this),
          wrap = _self.parents('.rtcl-do-email');

      $("#rtcl-contact-form", wrap).slideToggle("slow");
      return false;
    }); // Add or Remove from favourites

    $(document).on('click', 'a.rtcl-favourites', function (e) {
      e.preventDefault();

      var _self = $(this),
          data = {
        action: 'rtcl_public_add_remove_favorites',
        post_id: parseInt(_self.attr("data-id"), 10),
        __rtcl_wpnonce: rtcl.__rtcl_wpnonce
      };

      if (data.post_id) {
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: "POST",
          beforeSend: function beforeSend() {
            $("<span class='rtcl-icon-spinner animate-spin'></span>").insertAfter(_self);
          },
          success: function success(data) {
            _self.next('.rtcl-icon-spinner').remove();

            if (data.success) {
              _self.replaceWith(data.data);
            }
          },
          error: function error(e) {
            _self.next('.rtcl-icon-spinner').remove();
          }
        });
      }
    });
    /**
     * Slider Class.
     */

    var RtclSlider = function RtclSlider($target, args) {
      this.$target = $target;
      this.slider_enabled = $.isFunction($.fn.owlCarousel);
      this.options = this.$target.data('options') || {};

      this.initSlider = function () {
        if (!this.slider_enabled) {
          return;
        }

        this.$target.owlCarousel({
          responsive: {
            0: {
              items: 1
            },
            320: {
              items: this.options.mobile_items ? parseInt(this.options.mobile_items, 10) : 1
            },
            768: {
              items: this.options.tab_items ? parseInt(this.options.tab_items, 10) : 3
            },
            992: {
              items: this.options.items ? parseInt(this.options.items, 10) : 4
            }
          },
          margin: this.options.margin ? parseInt(this.options.margin, 10) : 0,
          rtl: !!parseInt(rtcl.is_rtl),
          nav: !!this.options.nav,
          dots: !!this.options.dots,
          autoplay: !!this.options.autoplay,
          smartSpeed: this.options.smart_speed ? parseInt(this.options.smart_speed, 10) : 250,
          autoplaySpeed: this.options.autoplay_speed ? this.options.autoplay_speed : false,
          navSpeed: this.options.nav_speed ? this.options.nav_speed : false,
          dotsSpeed: this.options.dots_speed ? this.options.dots_speed : false,
          navText: ['<i class="rtcl-icon-angle-left"></i>', '<i class="rtcl-icon-angle-right"></i>']
        });
      };

      this.imagesLoaded = function () {
        var that = this;

        if (!$.isFunction($.fn.imagesLoaded) || $.fn.imagesLoaded.done) {
          this.$target.trigger('rtcl_slider_loading', this);
          this.$target.trigger('rtcl_slider_loaded', this);
          return;
        }

        this.$target.imagesLoaded().progress(function (instance, image) {
          that.$target.trigger('rtcl_slider_loading', [that]);
        }).done(function (instance) {
          that.$target.trigger('rtcl_slider_loaded', [that]);
        });
      };

      this.start = function () {
        var that = this;
        this.$target.on('rtcl_slider_loaded', this.init.bind(this));
        setTimeout(function () {
          that.imagesLoaded();
        }, 1);
      };

      this.init = function () {
        this.initSlider();
      };

      this.start();
    };

    $.fn.rtcl_slider = function (args) {
      new RtclSlider(this, args);
      return this;
    };

    $('.rtcl-carousel-slider').each(function () {
      $(this).addClass("owl-carousel").rtcl_slider();
    }); // Populate child terms dropdown

    $('.rtcl-terms').on('change', 'select', function (e) {
      e.preventDefault();
      var $this = $(this),
          taxonomy = $this.data('taxonomy'),
          parent = $this.data('parent'),
          value = $this.val(),
          slug = $this.find(':selected').attr('data-slug') || '',
          classes = $this.attr('class'),
          form = $this.closest('form'),
          url = form.attr('data-action').replace(/\/+$/, ""),
          termHolder = $this.closest('.rtcl-terms').find('input.rtcl-term-hidden');
      termHolder.val(value).attr("data-slug", slug);
      $this.parent().find('div:first').remove();

      if (url) {
        var loc = form.find('input.rtcl-term-hidden.rtcl-term-rtcl_location').attr("data-slug"),
            cat = form.find('input.rtcl-term-hidden.rtcl-term-rtcl_category').attr("data-slug");

        if (cat && loc) {
          url = url + "/" + loc + "/" + cat;
          form.attr("action", url);
        } else if (cat && !loc) {
          url = url + "/category/" + cat;
        } else if (!cat && loc) {
          url = url + "/" + loc;
        }

        form.attr("action", url);
      }

      if (parent != value) {
        $this.parent().append('<div class="rtcl-spinner"><span class="rtcl-icon-spinner animate-spin"></span></div>');
        var data = {
          'action': 'rtcl_child_dropdown_terms',
          'taxonomy': taxonomy,
          'parent': value,
          'class': classes
        };
        $.post(rtcl.ajaxurl, data, function (response) {
          $this.parent().find('div:first').remove();
          $this.parent().append(response);
        });
      }
    });
    $("form.rtcl-search-form-inline").on('change', '.rtcl-location-search, .rtcl-category-search', function () {
      var $this = $(this),
          form = $this.closest('form'),
          url = form.attr('data-action').replace(/\/+$/, ""),
          loc = form.find('select.rtcl-location-search').val(),
          cat = form.find('select.rtcl-category-search').val();

      if (url) {
        if (cat && loc) {
          url = url + "/" + loc + "/" + cat;
        } else if (cat && !loc) {
          url = url + "/category/" + cat;
        } else if (!cat && loc) {
          url = url + "/" + loc;
        }

        form.attr("action", url);
      }
    });
    $(".rtcl-filter-form .filter-list").on("click", '.is-parent.has-sub .arrow', function (e) {
      e.preventDefault();
      var self = $(this),
          li = self.closest('li'),
          target = li.find('> ul.sub-list');

      if (li.hasClass('is-open')) {
        target.slideUp(function () {
          li.removeClass('is-open');
        });
      } else {
        target.slideDown();
        li.addClass('is-open');
      }
    });
    $(".rtcl-filter-form .ui-accordion-item").on('click', '.ui-accordion-title', function () {
      var self = $(this),
          holder = self.parents('.ui-accordion-item'),
          target = $(".ui-accordion-content", holder);

      if (holder.hasClass('is-open')) {
        target.slideUp(function () {
          holder.removeClass('is-open');
        });
      } else {
        target.slideDown();
        holder.addClass('is-open');
      }
    });
    $(".rtcl-filter-form").on("click", '.filter-submit-trigger', function (e) {
      var r,
          i,
          self = $(this),
          holder = self.parents('.ui-accordion-content'),
          type = holder.find('input.type').val();

      if (self.is(':checkbox')) {
        r = self;
        i = !r.prop("checked");
      } else {
        e.preventDefault();
        r = self.siblings("input");
        i = r.prop("checked");
      }

      if (type === 'radio' || type === 'select') {
        holder.find('input[type=checkbox]').prop("checked", false);
      }

      r.prop("checked", !i);
      self.closest('form').submit();
    });
    $("ul.filter-list.is-collapsed, ul.sub-list.is-collapsed, ul.ui-link-tree.is-collapsed").on('click', 'li.is-opener', function () {
      $(this).parent('ul').removeClass('is-collapsed').addClass('is-open');
    });
    /* REVEAL PHONE */

    $('.reveal-phone').on('click', function (e) {
      var $this = $(this),
          isMobile = $this.hasClass('rtcl-mobile');

      if (!$this.hasClass('revealed')) {
        e.preventDefault();
        var options = $this.data('options') || {};
        var $numbers = $this.find('.numbers');
        var aPhone = '';

        if (options.safe_phone && options.phone_hidden) {
          var purePhone = options.safe_phone.replace('XXX', options.phone_hidden);
          aPhone = $('<a href="#" />').attr('href', "tel:" + purePhone).text(purePhone);
          $this.attr('data-tel', 'tel:' + purePhone);
        }

        $numbers.html(aPhone).append(wPhone);
        $this.addClass('revealed');
      } else {
        if (isMobile) {
          var tel = $this.attr("data-tel");

          if (tel) {
            window.location = tel;
          }
        }
      }
    }); // parameter setting

    var option = getUrlParameter('option') || '',
        gateway = getUrlParameter('gateway') || '';

    if (option) {
      $("input[name='pricing_id'][value='" + option + "']").prop('checked', true);
    }

    if (gateway) {
      $("label[for='gateway-" + gateway + "']").trigger('click');
    }
  });

  if ($.fn.validate) {
    $('#rtcl-login-form, #rtcl-lost-password-form, #rtcl-password-reset-form, .rtcl-login-form').validate(); // Check out validation

    $("#rtcl-checkout-form").validate({
      submitHandler: function submitHandler(form) {
        rtcl_make_checkout_request(form);
        return false;
      }
    }); // Validate registration form

    $('#rtcl-register-form').validate({
      submitHandler: function submitHandler(form) {
        if (rtcl.recaptcha_registration > 0) {
          var response = grecaptcha.getResponse(rtcl.recaptcha_responce['registration']);

          if (0 == response.length) {
            $('#rtcl-registration-g-recaptcha-message').addClass('text-danger').html(rtcl.recaptcha_invalid_message);
            grecaptcha.reset(rtcl.recaptcha_responce['registration']);
            return false;
          }
        }

        form.submit();
      }
    }); // Validate report abuse form

    $('#rtcl-report-abuse-form').validate({
      submitHandler: function submitHandler(form) {
        if (rtcl.recaptcha_report_abuse > 0) {
          var response = grecaptcha.getResponse(rtcl.recaptcha_responce['report_abuse']);

          if (0 == response.length) {
            $('#rtcl-report-abuse-message-display').removeClass('text-success').addClass('text-danger').html(rtcl.recaptcha_invalid_message);
            grecaptcha.reset(rtcl.recaptcha_responce['report_abuse']);
            return false;
          }
        } // Post via AJAX


        var data = {
          'action': 'rtcl_public_report_abuse',
          'post_id': rtcl.post_id || 0,
          'message': $('#rtcl-report-abuse-message').val(),
          'g-recaptcha-response': response
        },
            targetBtn = $(form).find('.btn.btn-primary');
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: 'POST',
          beforeSend: function beforeSend() {
            $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(targetBtn);
          },
          success: function success(response) {
            targetBtn.next('.rtcl-icon-spinner').remove();

            if (response.error) {
              $('#rtcl-report-abuse-message-display').removeClass('text-success').addClass('text-danger').html(response.message);
            } else {
              $(form)[0].reset();
              $('#rtcl-report-abuse-message-display').removeClass('text-danger').addClass('text-success').html(response.message);
              setTimeout(function () {
                $('#rtcl-report-abuse-modal').modal('hide');
              }, 1500);
            }

            if (rtcl.recaptcha_contact > 0) {
              grecaptcha.reset(rtcl.recaptcha_responce['report_abuse']);
            }
          },
          error: function error(e) {
            $('#rtcl-report-abuse-message-display').removeClass('text-success').addClass('text-danger').html(e);
            targetBtn.next('.rtcl-icon-spinner').remove();
          }
        });
      }
    });
    $('#rtcl-contact-form').validate({
      submitHandler: function submitHandler(form) {
        var f = $(form);

        if (rtcl.recaptcha_contact > 0) {
          var response = grecaptcha.getResponse(rtcl.recaptcha_responce['contact']);

          if (0 == response.length) {
            $('#rtcl-contact-message-display').addClass('text-danger').html(rtcl.recaptcha_invalid_message);
            grecaptcha.reset(rtcl.recaptcha_responce['contact']);
            return false;
          }
        } // Post via AJAX


        var data = {
          'action': 'rtcl_public_send_contact_email',
          'post_id': rtcl.post_id || 0,
          'name': $('#rtcl-contact-name').val(),
          'email': $('#rtcl-contact-email').val(),
          'message': $('#rtcl-contact-message').val(),
          'g-recaptcha-response': response
        };
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: 'POST',
          beforeSend: function beforeSend() {
            $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(f.find('.btn'));
          },
          success: function success(response) {
            f.find('.btn').next('.rtcl-icon-spinner').remove();

            if (response.error) {
              $('#rtcl-contact-message-display').removeClass('text-success').addClass('text-danger').html(response.message);
            } else {
              f[0].reset();
              $('#rtcl-contact-message-display').removeClass('text-danger').addClass('text-success').html(response.message);
              setTimeout(function () {
                f.slideUp();
              }, 800);
            }

            if (rtcl.recaptcha_contact > 0) {
              grecaptcha.reset(rtcl.recaptcha_responce['contact']);
            }
          },
          error: function error(e) {
            $('#rtcl-contact-message-display').removeClass('text-success').addClass('text-danger').html(e);
            f.find('.btn').next('.rtcl-icon-spinner').remove();
          }
        });
      }
    }); // User account form

    $("#rtcl-user-account").validate({
      submitHandler: function submitHandler(form) {
        var $form = $(form),
            targetBtn = $form.find('input[type=submit]'),
            responseHolder = $form.find('.rtcl-response'),
            msgHolder = $("<div class='alert'></div>"),
            data = {
          action: "rtcl_update_user_account",
          first_name: $form.find("input[name='first_name']").val(),
          last_name: $form.find("input[name='last_name']").val(),
          email: $form.find("input[name='email']").val(),
          change_password: !!$form.find("input[name='change_password']").is(":checked"),
          pass1: $form.find("input[name='pass1']").val(),
          pass2: $form.find("input[name='pass2']").val(),
          phone: $form.find("input[name='phone']").val(),
          website: $form.find("input[name='website']").val(),
          zipcode: $form.find("input[name='zipcode']").val(),
          address: $form.find("textarea[name='address']").val(),
          latitude: $form.find("input[name='latitude']").val(),
          longitude: $form.find("input[name='longitude']").val(),
          location: $form.find("select[name='location']").val(),
          sub_location: $form.find("select[name='sub_location']").val(),
          sub_sub_location: $form.find("select[name='sub_sub_location']").val(),
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: 'POST',
          beforeSend: function beforeSend() {
            $form.addClass("rtcl-loading");
            targetBtn.prop("disabled", true);
            responseHolder.html('');
            $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(targetBtn);
          },
          success: function success(response) {
            targetBtn.prop("disabled", false).next('.rtcl-icon-spinner').remove();
            $form.removeClass("rtcl-loading");

            if (!response.error) {
              $form.find("input[name=pass1]").val('');
              $form.find("input[name=pass2]").val('');
              msgHolder.removeClass('alert-danger').addClass('alert-success').html(response.message).appendTo(responseHolder);
              setTimeout(function () {
                responseHolder.html('');
              }, 1000);
            } else {
              msgHolder.removeClass('alert-success').addClass('alert-danger').html(response.message).appendTo(responseHolder);
            }
          },
          error: function error(e) {
            msgHolder.removeClass('alert-success').addClass('alert-danger').html(e.responseText).appendTo(responseHolder);
            targetBtn.prop("disabled", false).next('.rtcl-icon-spinner').remove();
            $form.removeClass("rtcl-loading");
          }
        });
      }
    });
  } // on window load


  $(window).on('resize load', equalHeight);
})(jQuery);
