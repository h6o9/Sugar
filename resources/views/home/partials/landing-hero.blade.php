<section class="sp-hero">
    <div class="sp-app-banner">
        <i class="fa fa-mobile-alt"></i>
        <span>DOWNLOAD THE APP FOR SAVINGS & DISCOUNTS</span>
    </div>
    <video id="spHeroVideo" autoplay muted loop playsinline preload="auto" poster="{{ asset('public/img/hero-poster.jpg') }}">
        <source src="{{ asset('public/videos/hero.mp4') }}" type="video/mp4">
    </video>
    <div class="sp-hero-overlay"></div>
    <div class="sp-hero-content">
        <h1 class="sp-brand">SUGAR PAPPI</h1>
        <div class="sp-rating">
            4.6
            <span class="text-warning">★</span>
            (2000+)
        </div>
        <div class="sp-locations-head">
            <span class="line"></span>
            4 LOCATIONS
            <span class="line"></span>
        </div>
        <div class="text-center mb-1" style="color:var(--sp-pink)">♥</div>
        <div class="sp-cities">London • Manchester • Birmingham • Leicester</div>
        <div class="sp-cta">
            <button class="btn sp-btn sp-btn-pink" data-bs-toggle="modal" data-bs-target="#storeSelectModal">ORDER NOW</button>
            <a href="#menuContainer" class="btn sp-btn sp-btn-ghost" id="jumpToMenu">VIEW MENU</a>
        </div>
    </div>
</section>
<script>
(function () {
    var v = document.getElementById('spHeroVideo');
    if (!v) return;
    v.muted = true;
    v.defaultMuted = true;
    v.setAttribute('muted', '');
    v.playsInline = true;
    var started = Date.now();
    function ping(msg, extra) {
        // #region agent log
        fetch('http://127.0.0.1:7335/ingest/7b869b6d-0737-4031-abfa-725419739f94',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'1796d5'},body:JSON.stringify({sessionId:'1796d5',hypothesisId:'V1',location:'landing-hero.blade.php',message:msg,data:Object.assign({readyState:v.readyState,paused:v.paused,ms:Date.now()-started}, extra||{}),timestamp:Date.now()})}).catch(function(){});
        // #endregion
    }
    function tryPlay() {
        var p = v.play();
        if (p && p.then) p.then(function () { ping('hero play ok'); }).catch(function (err) { ping('hero play fail', {err: String(err)}); });
    }
    tryPlay();
    v.addEventListener('loadeddata', function () { ping('hero loadeddata'); tryPlay(); });
    v.addEventListener('canplay', function () { ping('hero canplay'); tryPlay(); });
    document.addEventListener('DOMContentLoaded', tryPlay);
})();
</script>

<div class="modal fade" id="storeSelectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background:#111;color:#fff;border-radius:18px;">
            <div class="modal-header border-0">
                <h5 class="modal-title text-uppercase">Select a store</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 justify-content-center">
                    @forelse(($landingStores ?? []) as $store)
                        @if(empty($store['is_orderable']) || empty($store['branch']))
                            @continue
                        @endif
                        <div class="col-md-8 col-lg-6">
                            <div class="store-card">
                                <h5 class="mb-1 text-white">{{ $store['label'] }}</h5>
                                <p class="small mb-3 text-white">{{ optional($store['branch'])->location }}</p>
                                <button class="btn sp-btn-pink w-100 select-store-btn" data-branch="{{ $store['branch']->id }}">Order from this store</button>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-white text-center mb-0">No store is available for ordering yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
