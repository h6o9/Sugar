@component('mail::message')
    <div style="text-align:center;">
        <img src="{{ asset('public/img/logo.png') }}" alt="Sugar-Papi Logo"
            style="vertical-align: middle;margin-bottom: -3px;height: 125px;margin-bottom: 30px">
        <h3>Welcome to Sugar-Papi</h3>
    </div>
    <div style="padding-left:0; border-radius: 10px;">
        <h2 style="color: #333; font-size: 24px; font-weight: bold; margin-bottom: 10px;">Order Confirmation</h2>
        <p style="color: #666; font-size: 16px;">Your order has been successfully placed. Below is your order code:</p>
    </div>
    <div style="text-align: center;padding-left: 0">
        <p style="color: #666; font-size: 16px; margin-bottom: 10px;"><strong>Order Code:</strong> {{ $orderCode }}
        </p>
        <a href="https://www.a-znutritionandsmoothies.com/user/my-order"
            style="background-color: #dc3838; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; display: inline-block; margin-bottom: 10px;">View
            Order</a>
    </div>
    <div style="text-align: center">
        <p style="color: #666; font-size: 16px;">Thank you for shopping with us!</p>
        <p style="color: #888; font-size: 14px;">Sugar-Papi</p>
    </div>
@endcomponent
