!(function ($) {
    "use strict";

    /*============================================
        Sticky header
    ============================================*/
    $(window).on("scroll", function () {
        var header = $(".header-area");
        // If window scroll down .is-sticky class will added to header
        if ($(window).scrollTop() >= 200) {
            header.addClass("is-sticky");
        } else {
            header.removeClass("is-sticky");
        }
    });


    /*============================================
        Mobile menu
    ============================================*/
    var mobileMenu = function () {
        // Variables
        var body = $("body"),
            mainNavbar = $(".main-navbar"),
            mobileNavbar = $(".mobile-menu"),
            cloneInto = $(".mobile-menu-wrapper"),
            cloneItem = $(".mobile-item"),
            menuToggler = $(".menu-toggler"),
            offCanvasMenu = $("#offcanvasMenu")

        menuToggler.on("click", function () {
            $(this).toggleClass("active");
            body.toggleClass("mobile-menu-active")
        })

        mainNavbar.find(cloneItem).clone(!0).appendTo(cloneInto);

        if (offCanvasMenu) {
            body.find(offCanvasMenu).clone(!0).appendTo(cloneInto);
        }

        mobileNavbar.find("li").each(function (index) {
            var toggleBtn = $(this).children(".toggle")
            toggleBtn.on("click", function (e) {
                $(this)
                    .parent("li")
                    .children("ul")
                    .stop(true, true)
                    .slideToggle(350);
                $(this).parent("li").toggleClass("show");
            })
        })

        // check browser width in real-time
        var checkBreakpoint = function () {
            var winWidth = window.innerWidth;
            if (winWidth <= 1199) {
                mainNavbar.hide();
                mobileNavbar.show()
            } else {
                mainNavbar.show();
                mobileNavbar.hide()
            }
        }
        checkBreakpoint();

        $(window).on('resize', function () {
            checkBreakpoint();
        });
    }
    mobileMenu();


    /*============================================
            Navlink active class
        ============================================*/
    var a = $("#mainMenu .nav-link"),
        c = window.location;

    for (var i = 0; i < a.length; i++) {
        const el = a[i];

        if (el.href == c) {
            el.classList.add("active");
        }
    }


    /*============================================
        Image to background image
    ============================================*/
    var bgImage = $(".bg-img")
    bgImage.each(function () {
        var el = $(this),
            src = el.attr("data-bg-image");

        el.css({
            "background-image": "url(" + src + ")",
            "display": "block",
            "background-repeat": "no-repeat"
        });
    });


    /*============================================
        Tabs mouse hover animation
    ============================================*/
    $("[data-hover='fancyHover']").mouseHover();


    /*============================================
        Booking Calender
    ============================================*/
    // $('.booking-calendar').pignoseCalendar({
    //     init: onInitBookingCalendar,
    //     select: onSelectBookingCalendar
    // });


    /*============================================
        Sliders
    ============================================*/
    // Category Slider all
    $(".category-slider").each(function () {
        var id = $(this).attr("id");
        var slidePerView = $(this).data("slides-per-view");
        var loops = $(this).data("swiper-loop");
        var sliderId = "#" + id;

        // console.log(slidePerView);

        var swiper = new Swiper(sliderId, {
            loop: loops,
            spaceBetween: 24,
            speed: 1000,
            autoplay: {
                delay: 3000,
            },
            slidesPerView: slidePerView,
            pagination: true,

            pagination: {
                el: sliderId + "-pagination",
                clickable: true,
            },

            // Navigation arrows
            navigation: {
                nextEl: sliderId + "-next",
                prevEl: sliderId + "-prev",
            },

            breakpoints: {
                320: {
                    slidesPerView: 1
                },
                576: {
                    slidesPerView: 2
                },
                992: {
                    slidesPerView: 3
                },
                1440: {
                    slidesPerView: slidePerView
                },
            }
        })
    })
    
    // category Slider 2
    let options = {
        speed: 1000,
        centeredSlides: true,
        initialSlide: 2,
        spaceBetween: 30,
        autoplay: true,
        autoplay: {
            delay: 3000,
        },

        pagination: {
            el: ".category-2-pagination",
            clickable: true,
        },

        breakpoints: {
            // when window width is >= 320px
            320: {
                slidesPerView: 1,
            },
            // when window width is >= 576px
            992: {
                slidesPerView: 4,
            },
            // when window width is >= 768px
            1200: {
                slidesPerView: 5
            },
        }
    }
    var categorySliderLength = $(".category-slider-2 .swiper-slide");
    switch (true) {
        case categorySliderLength.length == 4:
            options.initialSlide = 0,
            options.centeredSlides = false;

            $(".category-slider-2 .swiper-wrapper").css({"justify-content": "center"});
            $(".category-slider-2 .swiper-slide").css({"transform": "none"});
            break;
        case categorySliderLength.length <= 3:
            options.initialSlide = 1,
            options.centeredSlides = true,
            options.spaceBetween = 40;
            break;
        case categorySliderLength.length <= 5:
            options.pagination = false,
            options.allowSlideNext = false,
            options.allowSlidePrev = false;
            break;
    }
    var categorySlider2 = new Swiper(".category-slider-2", options);

    // Works slider
    var workSlider = new Swiper("#works-slider-1", {
        spaceBetween: 30,
        speed: 1000,
        autoplay: {
            delay: 3000,
        },
        slidesPerView: 2,
        pagination: true,

        pagination: {
            el: "#works-slider-1-pagination",
            clickable: true,
        },

        breakpoints: {
            320: {
                slidesPerView: 1
            },
            576: {
                slidesPerView: 2
            },
            1200: {
                slidesPerView: 2
            },
        }
    })

    // Product slider
    $(".product-slider").each(function () {
        var id = $(this).attr("id");
        var slidePerView = $(this).data("slides-per-view");
        var loops = $(this).data("swiper-loop");
        var sliderId = "#" + id;

        var swiper = new Swiper(sliderId, {
            loop: loops,
            spaceBetween: 24,
            speed: 1000,
            autoplay: {
                delay: 3000,
            },
            slidesPerView: slidePerView,
            pagination: true,

            pagination: {
                el: sliderId + "-pagination",
                clickable: true,
            },

            // Navigation arrows
            navigation: {
                nextEl: sliderId + "-next",
                prevEl: sliderId + "-prev",
            },

            breakpoints: {
                320: {
                    slidesPerView: 1
                },
                576: {
                    slidesPerView: 2
                },
                992: {
                    slidesPerView: 3
                },
                1440: {
                    slidesPerView: slidePerView
                },
            }
        })
    })
    // Product slider
    $(".product-inline-slider").each(function () {
        var id = $(this).attr("id");
        var slidePerView = $(this).data("slides-per-view");
        var loops = $(this).data("swiper-loop");
        var sliderId = "#" + id;

        var swiper = new Swiper(sliderId, {
            loop: loops,
            spaceBetween: 24,
            speed: 1000,
            autoplay: {
                delay: 3000,
            },
            slidesPerView: slidePerView,
            pagination: true,

            pagination: {
                el: sliderId + "-pagination",
                clickable: true,
            },

            // Navigation arrows
            navigation: {
                nextEl: sliderId + "-next",
                prevEl: sliderId + "-prev",
            },

            breakpoints: {
                320: {
                    slidesPerView: 1
                },
                768: {
                    slidesPerView: 2
                },
                1440: {
                    slidesPerView: slidePerView
                },
            }
        })
    })

    // Shop single slider
    var proSingleThumb = new Swiper(".slider-thumbnails", {
        loop: true,
        speed: 1000,
        spaceBetween: 20,
        slidesPerView: 4
    });
    var proSingleSlider = new Swiper(".product-single-slider", {
        loop: true,
        speed: 1000,
        autoplay: {
            delay: 3000
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        watchSlidesProgress: true,
        thumbs: {
            swiper: proSingleThumb,
        },

        // Navigation arrows
        navigation: {
            nextEl: "#product-single-btn-next",
            prevEl: "#product-single-btn-prev",
        },
    });

    // Testimonial Slider 1
    var testimonialSlider1 = new Swiper("#testimonial-slider-1", {
        speed: 1000,
        slidesPerView: 1,
        loop: true,
        grabCursor: true,
        effect: "creative",
        shadow: false,
        autoplay: {
            delay: 3000,
        },

        creativeEffect: {
            prev: {
                translate: [0, 0, -100],
            },
            next: {
                translate: ["-100%", 0, 0],
            },
        },

        // Pagination bullets
        pagination: {
            el: "#testimonial-slider-1-pagination",
            clickable: true,
        },
    });

    // Works slider
    var staffSlider = new Swiper(".staff-slider", {
        spaceBetween: 24,
        speed: 1000,
        loop: true,
        autoplay: {
            delay: 3000,
        },
        slidesPerView: 3,
        pagination: true,

        pagination: {
            el: "#staff-slider-pagination",
            clickable: true,
        },

        breakpoints: {
            320: {
                slidesPerView: 1
            },
            576: {
                slidesPerView: 2
            },
            992: {
                slidesPerView: 3
            },
        }
    })

    // Time slider
    var timeSlider = new Swiper(".booking-time-slider", {
        slidesPerView: 4,
        spaceBetween: 24,
        freeMode: true,
        scrollbar: {
            el: ".swiper-scrollbar",
        },
        mousewheel: true,
    });

    // Stop slider autoplay
    $(document).ready(function () {

        if ($(".swiper").length) {
            var mySwiper = document.querySelector(".swiper").swiper;

            $(".swiper").mouseenter(function () {
                mySwiper.autoplay.stop();
            });

            $(".swiper").mouseleave(function () {
                mySwiper.autoplay.start();
            });
        }
    });


    /*============================================
        Parallax image
    ============================================*/
    var parallax = $('.parallax');

    parallax.each(function () {
        $(this).mousemove(function (e) {
            var wx = $(window).width();
            var wy = $(window).height();
            var x = e.pageX - this.offsetLeft;
            var y = e.pageY - this.offsetTop;
            var newx = x - wx / 2;
            var newy = y - wy / 2;

            var parallaxChild = $(this).find('.parallax-img');
            parallaxChild.each(function () {
                var speed = $(this).attr('data-speed');
                if ($(this).attr('data-revert')) speed *= -.2;
                TweenMax.to($(this), 1, {
                    x: (1 - newx * speed),
                    y: (1 - newy * speed)
                });
            });
        });
    })


    /*============================================
        Bootstrap Stepper
    ============================================*/
    var bsStepper = function() {
        window.bookingStepper = new Stepper(document.querySelector('#booking-stepper'), {
            linear: true,
            animation: true
        });
    }
    // Reset stepper on close modal
    $(".booking-modal").on('hide.bs.modal', function() {
        bookingStepper.reset()
    })


    /*============================================
        Booking Calender
    ============================================*/
    function onInitBookingCalendar() {
        $('.booking-time .item').on("click", function() {
            bookingStepper.next();
        })
    }
    $('.booking-calendar').pignoseCalendar({
        init: onInitBookingCalendar
    });


    /*============================================
        Date-range Picker
    ============================================*/
    $('input[name="bookDate"]').daterangepicker({
        "showDropdowns": true,
        opens: 'left',
        "singleDatePicker": true,
        locale: {
            format: 'YYYY-MM-DD'
        }
    })
    $('input[name="bookTime"]').daterangepicker({
        opens: 'left',
        timePicker: true,
        "singleDatePicker": true,
        timePickerIncrement: 1,
        locale: {
            format: 'HH:mm'
        }
    }).on('show.daterangepicker', function (ev, picker) {
        picker.container.find(".calendar-table").hide();
    });


    /*============================================
        Price range
    ============================================*/
    var filterSliders = document.querySelector("[data-range-slider='filterPriceSlider']");
    var input0 = document.getElementById('min');
    var input1 = document.getElementById('max');
    var inputs = [input0, input1];

    // Filter frice slider
    if (filterSliders) {
        noUiSlider.create(filterSliders, {
            start: [200, 500],
            connect: !0,
            step: 10,
            margin: 10,
            range: {
                min: 0,
                max: 1000
            }
        }), filterSliders.noUiSlider.on("update", function (values, handle) {
            $("[data-range-value='filterPriceSliderValue']").text("$" + values.join(" - " + "$"));
            inputs[handle].value = values[handle];
        })
        inputs.forEach(function (input, handle) {
            if (input) {
                input.addEventListener('change', function () {
                    filterSliders.noUiSlider.setHandle(handle, this.value);
                });
            }
        });
    }


    /*============================================
        Quantity button
    ============================================*/
    $(document).on('click', '.quantity-down', function () {
        var numProduct = Number($(this).next().val());
        if (numProduct > 0) $(this).next().val(numProduct - 1);
    });
    $(document).on('click', '.quantity-up', function () {
        var numProduct = Number($(this).prev().val());
        $(this).prev().val(numProduct + 1);
    })


    /*============================================
        Read more toggle button
    ============================================*/
    $(".read-more-btn").on("click", function () {
        $(this).parent().toggleClass('show');
    })


    /*============================================
        Toggle List
    ============================================*/
    $("#toggleList").each(function (i) {
        var list = $(this).children();
        var listShow = $(this).data("toggle-show");
        var listShowBtn = $(this).next("[data-toggle-btn]");
        
        if (list.length > listShow) {
            listShowBtn.show()
            list.slice(listShow).toggle(300);
            
            listShowBtn.on("click", function () {
                list.slice(listShow).slideToggle(300);
                $(this).text($(this).text() === "Show Less" ? "Show More" : "Show Less")
            })
        } else {
            listShowBtn.hide()
        }
    })


    /*============================================
        Sidebar scroll
    ============================================*/
    $(document).ready(function () {
        $(".widget").each(function () {
            var child = $(this).find(".accordion-body.scroll-y");
            if (child.height() >= 245) {
                child.css({
                    "padding-inline-end": "10px",
                })
            }
        })
    })


    /*============================================
        Password icon toggle
    ============================================*/
    $(".show-password-field").on("click", function () {
        var showIcon = $(this).children(".show-icon");
        var passwordField = $(this).prev("input");
        showIcon.toggleClass("show");
        if (passwordField.attr("type") == "password") {
            passwordField.attr("type", "text")
        } else {
            passwordField.attr("type", "password");
        }
    })


    /*============================================
        Data tables
    ============================================*/
    var dataTable = function () {
        var dTable = $("#myTable");

        if (dTable.length) {
            dTable.DataTable()
        }
    }


    /*============================================
        Image upload
    ============================================*/
    var fileReader = function (input) {
        var regEx = new RegExp(/\.(gif|jpe?g|tiff?|png|webp|bmp)$/i);
        var errorMsg = $("#errorMsg");

        if (input.files && input.files[0] && regEx.test(input.value)) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#imagePreview').css('background-image', 'url(' + e.target.result + ')');
                $('#imagePreview').hide();
                $('#imagePreview').fadeIn(650);
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            errorMsg.html("Please upload a valid file type")
        }
    }
    $("#imageUpload").on("change", function () {
        fileReader(this);
    });


    /*============================================
        Youtube popup
    ============================================*/
    $(".youtube-popup").magnificPopup({
        disableOn: 300,
        type: "iframe",
        mainClass: "mfp-fade",
        removalDelay: 160,
        preloader: false,
        fixedContentPos: false,
    })


    /*============================================
        Product single popup
    ============================================*/
    $(".lightbox-single").magnificPopup({
        type: "image",
        mainClass: 'mfp-with-zoom',
        gallery: {
            enabled: true
        }
    });


    /*============================================
        Go to top
    ============================================*/
    $(".go-top").on("click", function (e) {
        $("html, body").animate({
            scrollTop: 0,
        }, 0);
    });


    /*============================================
        Lazyload image
    ============================================*/
    var lazyLoad = function () {
        window.lazySizesConfig = window.lazySizesConfig || {};
        window.lazySizesConfig.loadMode = 2;
        lazySizesConfig.preloadAfterLoad = true;

        var lazyContainer = $(".lazy-container");

        if (lazyContainer.children(".lazyloaded")) {
            lazyContainer.addClass("lazy-active")
        } else {
            lazyContainer.removeClass("lazy-active")
        }
    }


    /*============================================
        Nice select
    ============================================*/
    $(".niceselect").niceSelect();

    var selectList = $(".nice-select .list")
    $(".nice-select .list").each(function () {
        var list = $(this).children();
        if (list.length > 5) {
            $(this).css({
                "height": "160px",
                "overflow-y": "scroll"
            })
        }
    })


    /*============================================
        Footer date
    ============================================*/
    var date = new Date().getFullYear();
    $("#footerDate").text(date);


    /*============================================
        Document on ready
    ============================================*/
    $(document).ready(function () {
        lazyLoad();
        bsStepper();
    })
})(jQuery);

$(window).on("load", function () {
    const delay = 1000;
    /*============================================
        Preloader
    ============================================*/
    $("#preLoader").delay(delay).fadeOut();

    /*============================================
        Aos animation
    ============================================*/
    var aosAnimation = function () {
        AOS.init({
            easing: "ease",
            duration: 1200,
            once: true,
            offset: 60,
            disable: 'mobile'
        });
    }
    if ($("#preLoader")) {
        setTimeout(() => {
            aosAnimation()
        }, delay);
    } else {
        aosAnimation();
    }
})