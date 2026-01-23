@extends('home.layout.app')
@section('title', 'Notifications')
@section('content')

<style>
    /* Notification Page Styles */
    .notifications-page {
        min-height: 70vh;
        padding: 30px 0;
    }
    
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .notification-header h1 {
        font-size: 28px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }
    
    .notification-card {
        border: none;
        border-radius: 10px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        background: #fff;
    }
    
    .notification-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    
    .notification-card.unread {
        background: #f8f9ff;
        border-left: 4px solid #007bff;
    }
    
    .notification-card.read {
        background: #fff;
        border-left: 4px solid #ddd;
    }
    
    .notification-body {
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    
    .notification-content {
        flex: 1;
        padding-right: 15px;
    }
    
    .notification-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .notification-desc {
        font-size: 14px;
        color: #666;
        line-height: 1.5;
        margin-bottom: 8px;
    }
    
    .notification-time {
        font-size: 12px;
        color: #888;
    }
    
    .notification-status {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        min-width: 100px;
    }
    
    .mark-read-btn {
        background: #007bff;
        color: white;
        border: none;
        padding: 6px 15px;
        border-radius: 5px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: 8px;
    }
    
    .mark-read-btn.read {
        background: #6c757d;
        cursor: default;
    }
    
    .mark-read-btn:hover:not(.read) {
        background: #0056b3;
        transform: translateY(-1px);
    }
    
    .clear-all-btn {
        background: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .clear-all-btn:hover {
        background: #218838;
        transform: translateY(-1px);
    }
    
    .status-badge {
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 12px;
        font-weight: 500;
    }
    
    .status-unread {
        background: #ffebe6;
        color: #d63939;
    }
    
    .status-read {
        background: #e6ffed;
        color: #28a745;
    }
    
    .no-notifications {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    
    .no-notifications i {
        font-size: 60px;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    .no-notifications h3 {
        font-size: 22px;
        margin-bottom: 10px;
        color: #333;
    }
    
    .pagination-container {
        margin-top: 30px;
        text-align: center;
    }
</style>

<section class="section">
    <div class="container-xxl position-relative p-0">
        <div class="container-xxl py-5 bg-primary hero-header mb-md-5 mb-3">
            <div class="container text-center my-lg-5 pt-lg-5 pb-lg-4">
                <h1 class="display-3 text-dark mb-3 animated slideInDown">Notifications</h1>
            </div>
        </div>
    </div>

    <div class="container-xxl notifications-page wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="notification-header">
                <h1>
                    All Notifications 
                    @if($unreadCount > 0)
                        <span class="badge bg-danger ms-2">{{ $unreadCount }}</span>
                    @endif
                </h1>

                <!-- Clear All Notifications button -->
                <button class="clear-all-btn" id="clearAllBtn"
                    {{ $notifications->count() == 0 ? 'disabled' : '' }}>
                    Clear All Notifications
                </button>
            </div>

            @if($notifications->count() > 0)
                @foreach($notifications as $notification)
                    <div class="notification-card {{ $notification->seenByUser == '0' ? 'unread' : 'read' }}">
                        <div class="notification-body">
                            <div class="notification-content">
                                <div class="notification-title">
                                    {{ $notification->title }}
                                </div>
                                <div class="notification-desc">
                                    {{ $notification->description }}
                                </div>
                                <div class="notification-time">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $notification->created_at->format('d M Y, h:i A') }}
                                </div>
                            </div>
                            
                            <div class="notification-status">
                                <button class="mark-read-btn {{ $notification->seenByUser == '1' ? 'read' : '' }}"
                                        data-notification-id="{{ $notification->id }}"
                                        {{ $notification->seenByUser == '1' ? 'disabled' : '' }}>
                                    {{ $notification->seenByUser == '1' ? 'Read' : 'Mark as Read' }}
                                </button>
                                
                                <span class="status-badge {{ $notification->seenByUser == '0' ? 'status-unread' : 'status-read' }}">
                                    {{ $notification->seenByUser == '0' ? 'Unread' : 'Read' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($notifications->hasPages())
                <div class="pagination-container">
                    {{ $notifications->links() }}
                </div>
                @endif

            @else
                <div class="no-notifications">
                    <i class="far fa-bell-slash"></i>
                    <h3>No Notifications Yet</h3>
                    <p>You don't have any notifications at the moment.</p>
                </div>
            @endif
        </div>
    </div>
</section>

@endsection

@section('js')
<script>
$(document).ready(function() {
    // Set toastr options
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",  // <-- 5 seconds
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // Mark single notification as read
    $(document).on('click', '.mark-read-btn:not(.read)', function() {
        const button = $(this);
        const notificationId = button.data('notification-id');
        const card = button.closest('.notification-card');
        
        $.ajax({
            url: '{{ route("notifications.mark.read") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: notificationId
            },
            beforeSend: function() {
                button.html('<i class="fas fa-spinner fa-spin"></i> Processing');
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    button.text('Read');
                    button.addClass('read');
                    button.prop('disabled', true);
                    card.removeClass('unread').addClass('read');
                    card.find('.status-badge')
                        .removeClass('status-unread')
                        .addClass('status-read')
                        .text('Read');

                    const unreadCountElement = $('.notification-header .badge');
                    let currentCount = parseInt(unreadCountElement.text());
                    if (currentCount > 1) {
                        unreadCountElement.text(currentCount - 1);
                    } else {
                        unreadCountElement.remove();
                    }

                    toastr.success('Notification marked as read'); // <-- 5 sec me hide
                }
            },
            error: function() {
                button.text('Mark as Read');
                button.prop('disabled', false);
                toastr.error('Failed to mark notification as read'); // <-- 5 sec me hide
            }
        });
    });

    // Clear All Notifications
    $('#clearAllBtn').click(function() {
        const button = $(this);
        if (button.prop('disabled')) return;

        $.ajax({
            url: '{{ route("notifications.clear") }}',
            type: 'GET',
            beforeSend: function() {
                button.html('<i class="fas fa-spinner fa-spin"></i>');
                button.prop('disabled', true);
            },
            success: function() {
                $('.notification-card').slideUp(300, function() {
                    $(this).remove();
                });
                toastr.success('All notifications cleared successfully'); // <-- 5 sec me hide
                button.html('Clear All Notifications');
				setTimeout(function() {
        location.reload();
    }, 5000);
            },
            error: function() {
                button.prop('disabled', false);
                button.html('Clear All Notifications');
                toastr.error('Failed to clear notifications'); // <-- 5 sec me hide
            }
        });
    });

});

</script>
@endsection
