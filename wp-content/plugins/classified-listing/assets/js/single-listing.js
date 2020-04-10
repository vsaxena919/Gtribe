(function ($) {
  'user strict';
  /**
   * Listing gallery class.
   */

  var RtclListingGallery = function RtclListingGallery($target, args) {
    this.target = $target;
    this._slider = $('.rtcl-slider', $target);
    this._slider_nav = $('.rtcl-slider-nav', $target);
    this._slider_images = $('.rtcl-slider-item', this._slider);
    this.options = Object.assign({}, rtcl_single_listing_params.slider_options); //if rtl value was not passed and html is in rtl..enable it by default.

    if (typeof this.options.rtl == 'undefined' && $('html').attr('dir') === 'rtl') {
      this.options.rtl = true;
    } // Pick functionality to initialize...


    this.slider_enabled = $.isFunction($.fn.owlCarousel);

    if (1 === this._slider_images.length) {
      this.slider_enabled = false;
    }

    this.sliderChangeActive = function (sliderIndex) {
      this._slider_nav.find('.rtcl-slider-thumb-item.active').removeClass('active');

      this._slider_nav.find('.owl-item:eq(' + sliderIndex + ') .rtcl-slider-thumb-item').addClass('active');

      $(this).index();
    };

    this.initOwlCarousel = function () {
      if (!this.slider_enabled) {
        return;
      }

      var $slider = this._slider,
          $slider_nav = this._slider_nav,
          that = this;
      $slider.owlCarousel({
        items: 1,
        dots: false,
        autoHeight: true,
        rtl: !!this.options.rtl,
        nav: true,
        navText: ['<i class="rtcl-icon-angle-left"></i>', '<i class="rtcl-icon-angle-right"></i>']
      }).on('changed.owl.carousel', function (slider) {
        $slider_nav.trigger('to.owl.carousel', [slider.item.index, 300, true]);
        that.sliderChangeActive(slider.item.index);
      });
      $slider_nav.find('.rtcl-slider-thumb-item:first-child').addClass('active');
      $slider_nav.owlCarousel({
        responsive: {
          0: {
            items: 4
          },
          200: {
            items: 4
          },
          400: {
            items: 4
          },
          600: {
            items: 5
          }
        },
        margin: 5,
        rtl: !!this.options.rtl,
        nav: true,
        navText: ['<i class="rtcl-icon-angle-left"></i>', '<i class="rtcl-icon-angle-right"></i>'],
        onInitialized: function onInitialized() {
          var $stage = $('.rtcl-slider-thumb-item .owl-stage');
          $stage.css('width', $stage.width() + 1);
        },
        onResized: function onResized() {
          var $stage = $('.rtcl-slider-thumb-item .owl-stage');
          $stage.css('width', $stage.width() + 1);
        }
      }).on('click', '.owl-item', function () {
        $slider.trigger('to.owl.carousel', [$(this).index(), 300, true]);
        that.sliderChangeActive($(this).index());
      }).on('changed.owl.carousel', function (e) {
        $slider.trigger('to.owl.carousel', [e.item.index, 300, true]);
        that.sliderChangeActive(e.item.index);
      });
    };

    this.imagesLoaded = function () {
      var that = this;

      if ($.fn.imagesLoaded.done) {
        this.target.trigger('rtcl_gallery_loading', this);
        this.target.trigger('rtcl_gallery_loaded', this);
        return;
      }

      this.target.imagesLoaded().progress(function (instance, image) {
        that.target.trigger('rtcl_gallery_loading', [that]);
      }).done(function (instance) {
        that.target.trigger('rtcl_gallery_loaded', [that]);
      });
    };

    this.start = function () {
      var that = this;
      this.target.on('rtcl_gallery_loaded', this.init.bind(this));
      setTimeout(function () {
        that.imagesLoaded();
      }, 1);
    };

    this.init = function () {
      this.initOwlCarousel();
    };

    this.start();
  };

  $.fn.rtcl_listing_gallery = function (args) {
    new RtclListingGallery(this, args);
    return this;
  };
  /**
   * Initialize all galleries on page.
   */


  $('.rtcl-slider-wrapper').each(function () {
    $(this).rtcl_listing_gallery();
  });
})(jQuery);
