<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Sugar Pappi</title>
      <!-- Developed By Ranglerz -->
      <link rel="stylesheet" href="https://www.ranglerz.com/cost-to-make-a-web-ios-or-android-app-and-how-long-does-it-take.php">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon -->
    <link href="{{ asset('public/img/logo.png') }}" rel="icon">
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="{{ asset('https://fonts.googleapis.com') }}">
    <link rel="preconnect" href="{{ asset('https://fonts.gstatic.com') }}" crossorigin>
    <link
        href="{{ asset('https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap') }}"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css') }}"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css') }}"
        integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css') }}"
        rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('public/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/1.6.14/css/lightgallery.css" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('public/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Template Stylesheet -->
    <link href="{{ asset('public/css/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('public/css/common.css') }}" rel="stylesheet">
    <link href="{{ asset('public/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('public/css/sugarpappi-update.css') }}?v=20260831comp2" rel="stylesheet">
    @if(request()->is('/'))
        <link rel="preload" as="video" href="{{ asset('public/videos/hero.mp4') }}" type="video/mp4">
    @endif
    <link rel="stylesheet" href="{{ asset('public/admin/assets/toastr/css/toastr.css') }}">
    
    <!-- Google reCAPTCHA Script -->
    <script src="https://www.google.com/recaptcha/api.js?render=explicit" async defer></script>
    <script>
        function enableCaptchaSubmit() {
            // This function will be called when reCAPTCHA is successfully completed
            console.log('reCAPTCHA completed');
        }
    </script>
</head>

@php
    $hideSiteChrome = request()->is('login', 'getRegistor', 'forgot-password')
        || request()->routeIs('login', 'getRegistor', 'forgot-password');
@endphp
<body class="{{ $hideSiteChrome ? 'sp-auth-page' : '' }}">
    <div id="app" class="bg-white">
        <div class="main-wrapper main-wrapper-1">
            <div class="container-xxl bg-white p-0">
                @unless($hideSiteChrome)
                    @include('home.common.header')
                    @include('home.common.side_menu')
                    @if(!empty($businessHours) && empty($businessHours['is_open']))
                        <div class="sp-closed-bar">
                            {{ $businessHours['message'] }}
                            <form method="POST" action="{{ route('schedule.order') }}" class="d-inline ms-2">@csrf<button class="btn btn-sm btn-light">Schedule Order</button></form>
                        </div>
                    @endif
                @endunless
                <div class="main-content">
                    @yield('content')
                </div>
                @unless($hideSiteChrome)
                    @include('home.common.footer')
                @endunless
            </div>
        </div>
    </div>

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>if (window.spInitStorefront) window.spInitStorefront();</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/1.6.14/js/lightgallery-all.min.js"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    {{-- <script src="{{ asset('public/lib/wow/wow.min.js') }}"></script> --}}
    <script src="{{ asset('public/lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('public/lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('public/lib/counterup/counterup.min.js') }}"></script>
    <script src="{{ asset('public/lib/owlcarousel/owl.carousel.min.js') }}"></script>



    <!-- Template Javascript -->
    <script src="{{ asset('public/js/main.js') }}"></script>
    <script>
        $(document).on('click', '#closeSidebar', function () {
            $('.sidebar').addClass('hidden');
            $('body').removeClass('nav-open');
        });
        $(document).on('click', '#spMenuToggle', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var group = $(this).closest('.sp-menu-group');
            var open = group.toggleClass('open').hasClass('open');
            $(this).attr('aria-expanded', open ? 'true' : 'false');
            $('#spMenuCats').prop('hidden', !open);
        });
        function spSyncHeaderOffset() {
            var header = document.querySelector('.sp-site-header');
            if (header) {
                document.documentElement.style.setProperty('--sp-header-offset', header.offsetHeight + 'px');
            }
        }
        $(window).on('resize', spSyncHeaderOffset);
        $(spSyncHeaderOffset);
        function activateMenuTab(hash) {
            if (!hash) return;
            var id = hash.replace('#', '');
            var $btn = $();
            if (id === 'pappi-special') {
                $btn = $('[data-special="1"]').first();
            }
            if (!$btn.length) {
                $btn = $('[data-pane="' + id + '"]');
            }
            if (!$btn.length) {
                $btn = $('[data-bs-target="#' + id + '"]');
            }
            if ($btn.length) {
                $btn.trigger('click');
                if (typeof window.spShowMenuPane === 'function' && $btn.data('pane')) {
                    window.spShowMenuPane($btn.data('pane') || $btn.attr('data-pane'));
                }
                var el = document.getElementById(id) || document.getElementById('menuContainer');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return true;
            }
            var target = document.getElementById(id);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return true;
            }
            return false;
        }
        $(document).on('click', '.sidebar a.sp-nav-link', function (e) {
            var href = this.getAttribute('href') || '';
            var hashPos = href.indexOf('#');
            $('.sidebar').addClass('hidden');
            $('body').removeClass('nav-open');
            if (hashPos === -1) return;
            var hash = href.substring(hashPos);
            if (hash.indexOf('menuTab') === -1 && hash !== '#menuContainer' && hash !== '#pappi-special') return;
            if ($('#menuContainer').length) {
                e.preventDefault();
                if (hash === '#menuContainer' || hash === '#pappi-special') {
                    var target = document.querySelector(hash);
                    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return;
                }
                activateMenuTab(hash);
            }
        });
        $(function () {
            if (window.location.hash) {
                activateMenuTab(window.location.hash);
            }
        });
        $(document).on('click', '.select-store-btn', function () {
            var id = $(this).data('branch');
            $.post('{{ route("select.store") }}', {_token: '{{ csrf_token() }}', branch_id: id}, function (res) {
                // #region agent log
                fetch('http://127.0.0.1:7335/ingest/7b869b6d-0737-4031-abfa-725419739f94',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'1796d5'},body:JSON.stringify({sessionId:'1796d5',hypothesisId:'S2',location:'app.blade.php:select-store-btn',message:'client store redirect',data:{redirect:res && res.redirect},timestamp:Date.now()})}).catch(function(){});
                // #endregion
                if (res.redirect) { window.location = res.redirect; }
            }).fail(function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Store unavailable');
            });
        });
        (function () {
            function isMobileHeader() {
                return window.matchMedia('(max-width: 991.98px)').matches;
            }
            function closeHeaderDropdowns() {
                document.querySelectorAll('.sp-header-actions [data-bs-toggle="dropdown"]').forEach(function (el) {
                    var inst = bootstrap.Dropdown.getInstance(el);
                    if (inst) inst.hide();
                });
                document.body.classList.remove('sp-header-modal-open');
            }
            $(document).on('show.bs.dropdown', '.sp-header-actions .dropdown', function () {
                if (isMobileHeader()) document.body.classList.add('sp-header-modal-open');
            });
            $(document).on('hidden.bs.dropdown', '.sp-header-actions .dropdown', function () {
                if (!$('.sp-header-actions .dropdown-menu.show').length) {
                    document.body.classList.remove('sp-header-modal-open');
                }
            });
            $(document).on('click', '#spHeaderBackdrop, .sp-dropdown-close', function (e) {
                e.preventDefault();
                e.stopPropagation();
                closeHeaderDropdowns();
            });
        })();
        (function () {
            function formatMmSs(remain) {
                remain = Math.max(0, remain);
                if (remain >= 3600) {
                    var h = Math.floor(remain / 3600);
                    var hm = String(Math.floor((remain % 3600) / 60)).padStart(2, '0');
                    return h + 'h ' + hm + 'm';
                }
                var m = String(Math.floor(remain / 60)).padStart(2, '0');
                var s = String(remain % 60).padStart(2, '0');
                return m + ':' + s;
            }
            function hideWindowActions(el) {
                el.querySelectorAll('.js-window-actions, .sp-remove-item, .sp-edit-item, .sp-btn-pink, #spRemoveItemsBtn').forEach(function (n) {
                    n.style.display = 'none';
                });
            }
            function expireAndReload(el) {
                hideWindowActions(el);
                var label = el.querySelector('.timer-remain');
                if (label) label.textContent = '00:00';
                if (el.id === 'addToOrderTimer') {
                    var mins = el.getAttribute('data-minutes') || '10';
                    el.innerHTML = 'Your ' + mins + ' minutes are over. The order is placed as it is.';
                } else if (el.getAttribute('data-wholesale') === '1') {
                    var note = el.querySelector('.sp-timer-note');
                    if (note) note.textContent = 'You can no longer update this order.';
                }
                if (!window.__spReloadedTimer) {
                    window.__spReloadedTimer = true;
                    setTimeout(function () { window.location.reload(); }, 400);
                }
            }
            function tickCountdowns() {
                document.querySelectorAll('[data-sp-countdown]').forEach(function (el) {
                    var remain = parseInt(el.getAttribute('data-sp-countdown'), 10);
                    if (isNaN(remain)) return;
                    remain -= 1;
                    if (remain < 0) remain = 0;
                    el.setAttribute('data-sp-countdown', String(remain));
                    var label = el.querySelector('.timer-remain');
                    if (remain <= 0) {
                        expireAndReload(el);
                        return;
                    }
                    if (label) label.textContent = formatMmSs(remain);
                });
            }
            var timers = document.querySelectorAll('[data-sp-countdown]');
            if (timers.length) {
                timers.forEach(function (el) {
                    var remain = parseInt(el.getAttribute('data-sp-countdown'), 10);
                    if (!isNaN(remain) && remain <= 0) {
                        expireAndReload(el);
                    }
                });
                setInterval(tickCountdowns, 1000);
            }
        })();
        (function () {
            var addMins = {{ (int) (\App\Models\BusinessSetting::getValue('add_to_order_minutes', 10) ?: 10) }};
            function csrfToken() {
                var token = document.querySelector('meta[name="csrf-token"]');
                return token ? token.getAttribute('content') : '';
            }
            function postRemove(url, itemIds) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ item_ids: itemIds })
                }).then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); });
            }
            function afterRemove(out, isWholesale) {
                if (!out.ok || (out.data && out.data.success === false)) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', (out.data && out.data.message) || 'Could not remove item.', 'error');
                    } else {
                        alert((out.data && out.data.message) || 'Could not remove item.');
                    }
                    return;
                }
                var msg = (out.data && out.data.message) || (isWholesale
                    ? 'Product removed.'
                    : ('Product removed. The ' + addMins + '-minute timer has started again.'));
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Removed', msg, 'success').then(function () { window.location.reload(); });
                } else {
                    window.location.reload();
                }
            }
            function confirmRemove(url, itemId, name, count, isWholesale) {
                var onlyOne = parseInt(count, 10) === 1;
                var text;
                if (onlyOne) {
                    text = 'This is the only item. Removing it will empty the order.';
                } else if (isWholesale) {
                    text = (name || 'This product') + ' will be removed from this order.';
                } else {
                    text = (name || 'This product') + ' will be removed and the ' + addMins + '-minute timer will start again.';
                }
                if (typeof Swal === 'undefined') {
                    if (!window.confirm(text)) return;
                    postRemove(url, [itemId]).then(function (out) { afterRemove(out, isWholesale); });
                    return;
                }
                Swal.fire({
                    title: 'Remove this product?',
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ff2d87',
                    confirmButtonText: 'Yes, remove it'
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    postRemove(url, [itemId]).then(function (out) { afterRemove(out, isWholesale); });
                });
            }
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.sp-remove-item');
                if (!btn) return;
                e.preventDefault();
                var isWholesale = btn.getAttribute('data-wholesale') === '1'
                    || !!(btn.closest('[data-wholesale="1"]'));
                confirmRemove(
                    btn.getAttribute('data-remove-url'),
                    btn.getAttribute('data-item-id'),
                    btn.getAttribute('data-item-name'),
                    btn.getAttribute('data-item-count'),
                    isWholesale
                );
            });
            var bar = document.getElementById('addToOrderTimer');
            var btn = document.getElementById('spRemoveItemsBtn');
            if (!bar || !btn) return;
            var items = [];
            try { items = JSON.parse(bar.getAttribute('data-items') || '[]'); } catch (e) { items = []; }
            var url = bar.getAttribute('data-remove-url');
            btn.addEventListener('click', function () {
                if (!items.length) {
                    if (typeof Swal !== 'undefined') Swal.fire('No items', 'There is nothing to remove from this order.', 'info');
                    return;
                }
                if (items.length === 1) {
                    confirmRemove(url, items[0].id, items[0].name, 1);
                    return;
                }
                if (typeof Swal === 'undefined') return;
                var inputOptions = {};
                items.forEach(function (item) {
                    inputOptions[item.id] = item.name + ' (x' + item.qty + ')';
                });
                Swal.fire({
                    title: 'Please select the item you want to remove',
                    input: 'select',
                    inputOptions: inputOptions,
                    inputPlaceholder: 'Select an item',
                    showCancelButton: true,
                    confirmButtonColor: '#ff2d87',
                    confirmButtonText: 'Continue'
                }).then(function (picked) {
                    if (!picked.isConfirmed || !picked.value) return;
                    var chosen = items.filter(function (item) { return String(item.id) === String(picked.value); })[0];
                    confirmRemove(url, picked.value, chosen ? chosen.name : '', items.length);
                });
            });
        })();
        (function () {
            var banner = document.querySelector('.app-download-banner');
            var footer = document.querySelector('.footer');
            if (!banner || !footer) return;
            function syncBanner() {
                var rect = footer.getBoundingClientRect();
                var footerInView = rect.top < window.innerHeight && rect.bottom > 0;
                banner.classList.toggle('hide-for-footer', footerInView);
                banner.style.setProperty('display', footerInView ? 'none' : 'flex', 'important');
            }
            window.addEventListener('scroll', syncBanner, { passive: true });
            window.addEventListener('resize', syncBanner);
            if ('IntersectionObserver' in window) {
                new IntersectionObserver(function (entries) {
                    var hide = entries[0].isIntersecting;
                    banner.classList.toggle('hide-for-footer', hide);
                    banner.style.setProperty('display', hide ? 'none' : 'flex', 'important');
                }, { threshold: 0, rootMargin: '0px' }).observe(footer);
            }
            syncBanner();
        })();
    </script>
    @yield('css')
    @yield('js')
</body>
<script>
    /*toastr popup function*/
    function toastrPopUp() {
        toastr.options = {
            "closeButton": true,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "3000",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
            // toastr.success('Success messages');
        }
    }

    /*toastr popup function*/
    toastrPopUp();

    if (window.toastr && !toastr.__spWrapped) {
        var origSuccess = toastr.success.bind(toastr);
        toastr.success = function (msg) {
            var text = String(msg || '');
            if (/cart/i.test(text)) {
                if (toastr.__spCartLock) return;
                toastr.__spCartLock = true;
                setTimeout(function () { toastr.__spCartLock = false; }, 2500);
                toastr.clear();
            }
            return origSuccess.apply(toastr, arguments);
        };
        toastr.__spWrapped = true;
    }

</script>


</html>
