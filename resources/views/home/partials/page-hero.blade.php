@php
    $heroCandidates = [
        'img/page-hero.jpg',
        'img/food-gallery.jpg',
        'img/hero-poster.jpg',
        'img/pic-top.jpg',
        'img/logo.png',
    ];
    $pageHeroSrc = asset('public/img/logo.png');
    foreach ($heroCandidates as $rel) {
        if (file_exists(public_path($rel))) {
            $pageHeroSrc = asset('public/' . $rel);
            break;
        }
    }
@endphp
<div class="sp-page-hero" style="background-image:url('{{ $pageHeroSrc }}')">
    <h1>{{ $title ?? 'Sugar Pappi' }}</h1>
</div>
