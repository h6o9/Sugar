(function ($) {
  "use strict";

  // Spinner
  var spinner = function () {
    setTimeout(function () {
      if ($("#spinner").length > 0) {
        $("#spinner").removeClass("show");
      }
    }, 1);
  };
  spinner();

//   // Initiate the wowjs
//   new WOW().init();

  // Sticky Navbar
  $(window).scroll(function () {
    if ($(this).scrollTop() > 45) {
      $(".navbar").addClass("sticky-top shadow-sm");
    } else {
      $(".navbar").removeClass("sticky-top shadow-sm");
    }
  });

  // Dropdown on mouse hover
  const $dropdown = $(".dropdown");
  const $dropdownToggle = $(".dropdown-toggle");
  const $dropdownMenu = $(".dropdown-menu");
  const showClass = "show";

  $(window).on("load resize", function () {
    if (this.matchMedia("(min-width: 992px)").matches) {
      $dropdown.hover(
        function () {
          const $this = $(this);
          $this.addClass(showClass);
          $this.find($dropdownToggle).attr("aria-expanded", "true");
          $this.find($dropdownMenu).addClass(showClass);
        },
        function () {
          const $this = $(this);
          $this.removeClass(showClass);
          $this.find($dropdownToggle).attr("aria-expanded", "false");
          $this.find($dropdownMenu).removeClass(showClass);
        }
      );
    } else {
      $dropdown.off("mouseenter mouseleave");
    }
  });

  // Back to top button
  $(window).scroll(function () {
    if ($(this).scrollTop() > 300) {
      $(".back-to-top").fadeIn("slow");
    } else {
      $(".back-to-top").fadeOut("slow");
    }
  });
  $(".back-to-top").click(function () {
    $("html, body").animate({ scrollTop: 0 }, 1500, "easeInOutExpo");
    return false;
  });

  // Facts counter
  $('[data-toggle="counter-up"]').counterUp({
    delay: 10,
    time: 2000,
  });

  // Modal Video
  $(document).ready(function () {
    var $videoSrc;
    $(".btn-play").click(function () {
      $videoSrc = $(this).data("src");
    });
    console.log($videoSrc);

    $("#videoModal").on("shown.bs.modal", function (e) {
      $("#video").attr(
        "src",
        $videoSrc + "?autoplay=1&amp;modestbranding=1&amp;showinfo=0"
      );
    });

    $("#videoModal").on("hide.bs.modal", function (e) {
      $("#video").attr("src", ""); // Set the src attribute to an empty string
    });

    var $banner = $('.app-download-banner');

    function updateBannerVisibility() {
      var scrollTop = $(window).scrollTop();
      var windowHeight = $(window).height();
      var documentHeight = $(document).height();

      // Page bottom check
      if (scrollTop + windowHeight >= documentHeight) {
        $banner.addClass('d-none'); // hide banner
      } else {
        $banner.removeClass('d-none'); // show banner
      }
    }

    $(window).on('scroll resize', updateBannerVisibility);

    // Also run immediately on load (e.g., after refresh when user may already be at bottom)
    updateBannerVisibility();
  });

  // Gallery carousel
  $(".gallery-carousel").owlCarousel({
    autoplay: false,
    smartSpeed: 1000,
    center: false,
    margin: 0,
    dots: false,
    nav: true, // Enable navigation arrows
    navText: [
      "<span class='fa fa-chevron-left arrow-icon me-2'></span>",
      "<span class='fa fa-chevron-right arrow-icon'></span>",
    ], // Customize arrow icons
    loop: true,
    responsive: {
      0: {
        items: 1,
      },
      768: {
        items: 2,
      },
      992: {
        items: 4,
      },
    },
  });

  // Home Banner Questions Block
  var headingsBlock = $(".headings-block .heading-question");
  var currentIndex = 0;

  function rotateHeadings() {
    headingsBlock.eq(currentIndex).removeClass("active");
    currentIndex = (currentIndex + 1) % headingsBlock.length;
    headingsBlock.eq(currentIndex).addClass("active");
  }

  headingsBlock.eq(currentIndex).addClass("active");
  setInterval(rotateHeadings, 3000); // Adjust the rotation interval as needed

  // When a clickable image is clicked
  $(".clickable-image").click(function () {
    // Get the image source and alt text
    var imgSrc = $(this).attr("src");
    var imgAlt = $(this).attr("alt");

    // Set the popup image source and alt text
    $("#popupImage").attr("src", imgSrc);
    $("#popupImage").attr("alt", imgAlt);

    // Show the image popup
    $("#imagePopup").fadeIn();
  });

  $("#closeImagePopup").click(function () {
    // Close the image popup
    $("#imagePopup").fadeOut();
  });

  // Close the image popup if the overlay is clicked
  $("#imagePopup").click(function (event) {
    if (event.target == this) {
      $(this).fadeOut();
    }
  });

  // Sidebar
  $(document).on("click", ".navbar-toggler", function () {
    $(".sidebar").removeClass("hidden");
    $("body").addClass("nav-open");
  });

  $(document).mouseup(function (e) {
    var container = $(".sidebar", this);

    // If the target of the click isn't the container
    if (!container.is(e.target) && container.has(e.target).length === 0) {
      container.addClass("hidden");
      $("body").removeClass("nav-open");
    }
  });

  // Overlay
  $(document).on("click", ".open-btn", function () {
    $("#myOverlay").show();
  });

  $(document).on("click", ".close-btn", function () {
    $("#myOverlay").hide();
  });

  // Toggle Password
  $(document).on("click", ".toggle-password", function () {
    $(this).toggleClass("fa-eye fa-eye-slash");
    var input = $($(this).attr("toggle"));
    if (input.attr("type") == "password") {
      input.attr("type", "text");
    } else {
      input.attr("type", "password");
    }
  });

    $('#jumpToMenu').on('click', function(e) {
        e.preventDefault();

        var headerHeight = $('.navbar').outerHeight(); // header ki height
        var target = $('#menuContainer');
        var targetPos = target.offset().top;

        $('html, body').animate({
            scrollTop: targetPos - headerHeight - 8 // -8 chhota sa gap
        }, 600); // 600ms = smooth scroll duration
    });
})(jQuery);
