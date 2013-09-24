(function($){

  function fullscreenImage(el, options) {
    var self = this;

    // Defaults
    self.defaults = {
      imagePath: '',
      pathToPHP: 'fullscreenImage.php',
      rootPath: '/',
      widthBreakPoints: [
        100,
        300,
        400,
        500,
        700,
        1000,
        1500,
        2000,
        3000,
        5000,
        10000
      ],
      onReady: function() {},
      onSlideEnd: function() {}
    };

    // Extending options
    self.opts = $.extend({}, self.defaults, options);

    // Privates
    self.$wrapper = $(el);
    self.$image   = {};

    self.$window   = $(window);
    self.$document =  $(document);

    self.winWidth       = 0;
    self.winHeight      = 0;
    self.refreshTimeout = 0;
  }

  // Separate functionality from object creation
  fullscreenImage.prototype = {

    init: function() {
      var self = this;

      self.initVars();
      self.initWrapper();
      self.onResize();
      self.loadImage(function(imageData) {
        self.initImage(imageData);
      });
    },

    initWrapper: function() {
      var self = this;

      self.$wrapper
        .css({
          'position': 'fixed',
          'left': 0,
          'top': 0
        })
        .hide()
      ;

      self.resizeWrapper();
    },

    initVars: function() {
      var self = this;

      self.winWidth  = self.$window.width();
      self.winHeight = self.$window.height();
    },

    initImage: function(imageData) {
      var self = this;

      self.placeImage(imageData);
      self.resizeImage(imageData);

      self.$window.resize(function() {
        self.resizeImage(imageData);
      });

      self.$wrapper.fadeIn();
    },

    refreshImage: function() {
      var self = this;

      clearTimeout(self.refreshTimeout);

      self.refreshTimeout = setTimeout(function() {
        self.loadImage(function(imageData) {
          self.placeImage(imageData);
          self.resizeImage(imageData);
        });
      }, 200);
    },

    onResize: function() {
      var self = this;

      self.$window.resize(function() {
        self.initVars();
        self.resizeWrapper();
        self.refreshImage();
      });
    },

    resizeWrapper: function() {
      var self = this;

      self.$wrapper.css({
        'width': self.winWidth,
        'height': self.winHeight
      });
    },

    resizeImage: function(imageData) {
      var self = this;

      var imgWidth, imgHeight, widthRatio, heightRatio, newWidth, newHeight;

      imgWidth  = imageData.width;
      imgHeight = imageData.height;

      widthRatio  = self.winWidth / imgWidth;
      heightRatio = self.winHeight / imgHeight;

      newWidth  = imgWidth * heightRatio;
      newHeight = imgHeight * widthRatio;

      if(widthRatio >= heightRatio) {
        self.$image.css({
          'width': self.winWidth + 'px',
          'height': newHeight + 'px'
        });

        self.$image.css({
          'top': ((newHeight - self.winHeight)/2) * -1,
          'left': 0
        });
      } else {
        self.$image.css({
          'width': newWidth + 'px',
          'height': self.winHeight + 'px'
        });

        self.$image.css({
          'top': 0,
          'left': ((self.$image.width() - self.winWidth)/2) * -1
        });
      }
    },

    loadImage: function(callback) {
      var self = this;

      var postVars = {
        'winWidth': self.winWidth,
        'winHeight': self.winHeight,
        'action': 'getSizedImage',
        'imagePath': self.opts.imagePath,
        'widthBreakPoints': self.opts.widthBreakPoints,
        'rootPath': self.opts.rootPath
      };

      $.post(self.opts.pathToPHP, postVars, function(imageData) {
        callback(imageData);
      }, 'json');
    },

    placeImage: function(imageData) {
      var self = this;

      if(self.$image instanceof jQuery == false) {
        self.$image = $('<img src="' + imageData.path + '">');
      } else {
        // Copy image, add new one, delete old to prevent flickering in webkit
        $clonedImage = self.$image.clone();
        $clonedImage.appendTo(self.$wrapper);
        self.$image.attr('src', imageData.path);
        setTimeout(function() {
          $clonedImage.remove();
        }, 100);
      }

      self.$image
        .css({
          'position': 'absolute'
        })
        .appendTo(self.$wrapper)
      ;
    }
  };

  function lowerFirstLetter(string) {
    return string.charAt(0).toLowerCase() + string.slice(1);
  }

  /**
   * Loads automatically custom events of the plugin
   * @param  {object} obj the plugin object
   * @return {void}
   */
  function loadCustomEvents(obj) {
    var self = obj;

    for (var optionName in self.opts) {
      var
        optionValue = self.opts[optionName],
        optionType  = typeof(optionValue)
      ;

      if(optionType == 'function') {
        optionNames = optionName.split('on');
        eventName   = lowerFirstLetter(optionNames[1]);

        self.$wrapper.on(eventName, self.opts[optionName]);
      }
    }
  }

  // http://gistflow.com/posts/342-how-refresh-a-stored-jquery-selector-variable
  $.fn.refreshElement = function() {
    return $(this.selector);
  };

  // The actual plugin
  $.fn.fullscreenImage = function(options) {
    if(this.length) {
      this.each(function() {
        var rev = new fullscreenImage(this, options);
        rev.init();
        loadCustomEvents(rev);
        $(this).data('fullscreenImage', rev);
      });
    }
  };
})(jQuery);