@extends('admin.layout.app')
@section('title', 'Business Settings')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <div class="card">
                <div class="card-header"><h4>Business Settings</h4></div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Google Time Zone API time: {{ $status['now_display'] ?? '' }} ({{ $status['timezone'] ?? '' }})
                        — {{ !empty($status['is_open']) ? 'Open' : 'Closed' }}
                    </div>
                    <form method="POST" action="{{ route('business-settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Business timezone</label>
                                <input class="form-control" name="business_timezone" value="{{ $settings['business_timezone'] ?? 'Europe/London' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Google timezone lat</label>
                                <input class="form-control" name="google_timezone_lat" value="{{ $settings['google_timezone_lat'] ?? '53.4808' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Google timezone lng</label>
                                <input class="form-control" name="google_timezone_lng" value="{{ $settings['google_timezone_lng'] ?? '-2.2426' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Add-to-order minutes</label>
                                <input type="number" class="form-control" name="add_to_order_minutes" value="{{ $settings['add_to_order_minutes'] ?? 10 }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Drive-In discount %</label>
                                <input type="number" class="form-control" name="drive_in_discount_percent" value="{{ $settings['drive_in_discount_percent'] ?? 20 }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>WhatsApp number</label>
                                <input class="form-control" name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '447727412922' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Wholesale window start</label>
                                <input class="form-control" name="wholesale_delivery_start" value="{{ $settings['wholesale_delivery_start'] ?? '19:00' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Wholesale window end</label>
                                <input class="form-control" name="wholesale_delivery_end" value="{{ $settings['wholesale_delivery_end'] ?? '22:00' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Wholesale cutoff</label>
                                <input class="form-control" name="wholesale_cutoff_time" value="{{ $settings['wholesale_cutoff_time'] ?? '19:00' }}">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Wholesale delivery days</label>
                                @foreach(['monday','thursday','saturday','tuesday','wednesday','friday','sunday'] as $day)
                                    <label class="mr-3">
                                        <input type="checkbox" name="wholesale_delivery_days[]" value="{{ $day }}" {{ in_array($day, $days) ? 'checked' : '' }}> {{ ucfirst($day) }}
                                    </label>
                                @endforeach
                            </div>
                            @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                            <div class="form-group col-md-6">
                                <label>{{ ucfirst($day) }} open</label>
                                <input class="form-control" name="open_{{ $day }}" value="{{ $hours[$day]['open'] ?? ($day==='sunday' ? '14:00' : '16:00') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ ucfirst($day) }} close (next morning)</label>
                                <input class="form-control" name="close_{{ $day }}" value="{{ $hours[$day]['close'] ?? '06:00' }}">
                            </div>
                            @endforeach
                            <div class="form-group col-md-12">
                                <label>App download banner text</label>
                                <input class="form-control" name="app_download_text" value="{{ $settings['app_download_text'] ?? '' }}">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Private booking title</label>
                                <input class="form-control" name="private_booking_title" value="{{ $settings['private_booking_title'] ?? '' }}">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Private booking description</label>
                                <textarea class="form-control" name="private_booking_description" rows="4">{{ $settings['private_booking_description'] ?? '' }}</textarea>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Private booking image</label>
                                <input type="file" class="form-control" name="private_booking_image">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Hero video</label>
                                <input type="file" class="form-control" name="hero_video" accept="video/mp4">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Hero poster</label>
                                <input type="file" class="form-control" name="hero_poster">
                            </div>
                        </div>
                        <button class="btn btn-primary">Save settings</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@section('js')
@if (\Illuminate\Support\Facades\Session::has('message'))
<script>toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');</script>
@endif
@endsection
