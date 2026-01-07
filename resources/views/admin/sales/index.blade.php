@extends('admin.layout.app')
@section('title', 'index')
@section('content')
<div class="main-content" style="min-height: 562px;">
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-header" style="padding-bottom: 0!important;border-bottom: 0">
                            <div class="col-12">
                                <h4>Orders</h4>

                                <ul class="nav nav-tabs col-12" id="myTab2" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="daily-tab2" data-toggle="tab" href="#daily"
                                            role="tab" aria-selected="false">Daily Sales</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="weekly-tab2" data-toggle="tab" href="#weekly" role="tab"
                                            aria-selected="true">Weekly Sales</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="monthly-tab2" data-toggle="tab" href="#monthly"
                                            role="tab" aria-selected="true">Monthly Sales</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="yearly-tab2" data-toggle="tab" href="#yearly" role="tab"
                                            aria-selected="true">Yearly Sales</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="branch-tab2" data-toggle="tab" href="#branches"
                                            role="tab" aria-selected="true">Branches Sales</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="daily" role="tabpanel"
                                aria-labelledby="daily-tab2">
                                <!-- Content for Daily Sales goes here -->
                                <div class="card-body table-responsive">
                                    <div class="row text-center">
                                        <div>
                                            <h3>
                                                Total Earnings:
                                            </h3>
                                        </div>
                                        <div>
                                            <h4>
                                                £ {{ $data['totalDailyAmount'] }}
                                            </h4>
                                        </div>
                                    </div>
                                    <table class="table table-striped table-bordered text-center tableExport">
                                        <thead>
                                            <tr>
                                                <th>Order Id</th>
                                                <th>Branch Locationn</th>
                                                <th>User</th>
                                                <th>Product</th>
                                                <th>Toppings</th>
                                                <th>Product Price</th>
                                                <th>Product Size</th>
                                                <th>Quantity</th>
                                                <th>Sub Total</th>
                                                <th>Tip Amount</th>
                                                <th>Branch Tax</th>
                                                <th>Order Total</th>
                                                <th>After Redeemed</th>
                                                <th>Redeemed</th>
                                                <th>Day</th>
                                                <th>Order Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data['dailySales'] as $index => $dailySale)
                                            <tr>
                                                <td>#{{ $dailySale->code }}</td>
                                                <td>
                                                    @foreach ($dailySale->orderItem as $dailySaleItem)
                                                    {{ $dailySaleItem->branch->location }}
                                                    @endforeach
                                                </td>
                                                <th>{{ $dailySale->user->name }}</th>
                                                <td>
                                                    @foreach ($dailySale->orderItem as $dailySaleItem)
                                                    {{ $dailySaleItem->product_name }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>

                                                <td>
                                                    @foreach ($dailySale->orderItem as $dailySaleItem)
                                                    @if (!$dailySaleItem->orderToppings->isEmpty())
                                                    @foreach ($dailySaleItem->orderToppings as $topping)
                                                    @if ($loop->first)
                                                    <h6 class="small m-0">
                                                        {{ $topping->category->name }}:
                                                    </h6>
                                                    @endif
                                                    <p class="small m-0">
                                                        {{ $topping->toppings->name }}
                                                        (£{{ $topping->toppings->price }})
                                                    </p>
                                                    @endforeach
                                                    @else
                                                    <div class="badge p-2 badge-shadow btn-danger text-white">
                                                        No Topping
                                                    </div>
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($dailySale->orderItem as $dailySaleItem)
                                                    {{ $dailySaleItem->product_size ? $dailySaleItem->product_size : 'No
                                                    Size Found' }}
                                                    @break
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($dailySale->orderItem as $dailySaleItem)
                                                    £{{ $dailySaleItem->product_price }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($dailySale->orderItem as $dailySaleItem)
                                                    {{ $dailySaleItem->quantity }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($dailySale->orderItem as $dailySaleItem)
                                                    £{{ $dailySaleItem->sub_total }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @php
                                                    $tips = [];
                                                    @endphp

                                                    @foreach ($dailySale->orderItem as $dailySaleItem)
                                                    @php
                                                    $tip = $dailySaleItem->tip;
                                                    @endphp

                                                    @if (!in_array($tip, $tips))
                                                    £ {{ $tip }}
                                                    @php
                                                    $tips[] = $tip;
                                                    @endphp
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @php
                                                    $branchTaxs = [];
                                                    @endphp

                                                    @foreach ($dailySale->orderItem as $dailySaleItem)
                                                    @php
                                                    $branchTax = $dailySaleItem->branch->tax;
                                                    @endphp

                                                    @if (!in_array($branchTax, $branchTaxs))
                                                    £ {{ $branchTax }}
                                                    @php
                                                    $branchTaxs[] = $branchTax;
                                                    @endphp
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>£{{ $dailySale->total_amount }}</td>

                                                @php
                                                $redeemedAmount = is_numeric($dailySale->redeemed) ? $dailySale->redeemed : 0;
                                                $afterRedeemed = $dailySale->total_amount - $redeemedAmount;
                                                @endphp
                                                @if($afterRedeemed < $dailySale->total_amount)
                                                <td>£{{ $afterRedeemed }}</td>
                                                @else
                                                <td>-</td>
                                                @endif
                                                @if($redeemedAmount > 0)
                                                <td>£{{ $redeemedAmount }}</td>
                                                @else
                                                <td>-</td>
                                                @endif

                                                <td>{{ $dailySale->day_name }}</td>
                                                <td>{{ $dailySale->order_date }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Content for Weekly Sales goes here -->
                            <div class="tab-pane fade" id="weekly" role="tabpanel" aria-labelledby="weekly-tab2">
                                <div class="card-body table-responsive">
                                    <div class="row text-center">
                                        <div>
                                            <h3>
                                                Total Earnings:
                                            </h3>
                                        </div>
                                        <div>
                                            <h4>
                                                £ {{ $data['totalWeeklyAmount'] }}
                                            </h4>
                                        </div>
                                    </div>
                                    <table class="table table-striped table-bordered text-center tableExport">
                                        <thead>
                                            <tr>
                                                <th>Order Id</th>
                                                <th>Branch Location</th>
                                                <th>User</th>
                                                <th>Product</th>
                                                <th>Toppings</th>
                                                <th>Product Price</th>
                                                <th>Product Size</th>
                                                <th>Quantity</th>
                                                <th>Sub Total</th>
                                                <th>Tip Amount</th>
                                                <th>Branch Tax</th>
                                                <th>Order Total</th>
                                                <th>After Redeemed</th>
                                                <th>Redeemed</th>
                                                <th>Day</th>
                                                <th>Order Date</th>
                                                {{-- <th>Action</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data['weeklySales'] as $index => $weeklySale)
                                            <tr>
                                                <td>#{{ $weeklySale->code }}</td>
                                                <td>
                                                    @foreach ($weeklySale->orderItem as $weeklySaleItem)
                                                    {{ $weeklySaleItem->branch->location }}
                                                    @endforeach
                                                </td>
                                                <th>{{ $weeklySale->user->name }}</th>
                                                <td>
                                                    @foreach ($weeklySale->orderItem as $weeklySaleItem)
                                                    {{ $weeklySaleItem->product_name }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>

                                                <td>
                                                    @foreach ($weeklySale->orderItem as $weeklySaleItem)
                                                    @if (!$weeklySaleItem->orderToppings->isEmpty())
                                                    @foreach ($weeklySaleItem->orderToppings as $topping)
                                                    @if ($loop->first)
                                                    <h6 class="small m-0">
                                                        {{ $topping->category->name }}:
                                                    </h6>
                                                    @endif
                                                    <p class="small m-0">
                                                        {{ $topping->toppings->name }}
                                                        (£{{ $topping->toppings->price }})
                                                    </p>
                                                    @endforeach
                                                    @else
                                                    <div class="badge p-2 badge-shadow btn-danger text-white">
                                                        No Topping
                                                    </div>
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($weeklySale->orderItem as $weeklySaleItem)
                                                    {{ $weeklySaleItem->product_size ? $weeklySaleItem->product_size :
                                                    'No Size Found' }}
                                                    @break
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($weeklySale->orderItem as $weeklySaleItem)
                                                    £{{ $weeklySaleItem->product_price }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($weeklySale->orderItem as $weeklySaleItem)
                                                    {{ $weeklySaleItem->quantity }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($weeklySale->orderItem as $weeklySaleItem)
                                                    £{{ $weeklySaleItem->sub_total }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @php
                                                    $tips = [];
                                                    @endphp

                                                    @foreach ($weeklySale->orderItem as $weeklySaleItem)
                                                    @php
                                                    $tip = $weeklySaleItem->tip;
                                                    @endphp

                                                    @if (!in_array($tip, $tips))
                                                    £ {{ $tip }}
                                                    @php
                                                    $tips[] = $tip;
                                                    @endphp
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @php
                                                    $branchTaxs = [];
                                                    @endphp

                                                    @foreach ($weeklySale->orderItem as $weeklySaleItem)
                                                    @php
                                                    $branchTax = $weeklySaleItem->branch->tax;
                                                    @endphp

                                                    @if (!in_array($branchTax, $branchTaxs))
                                                    £ {{ $branchTax }}
                                                    @php
                                                    $branchTaxs[] = $branchTax;
                                                    @endphp
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>£{{ $weeklySale->total_amount }}</td>

                                                @php
                                                $redeemedAmount = is_numeric($weeklySale->redeemed) ? $weeklySale->redeemed : 0;
                                                $afterRedeemed = $weeklySale->total_amount - $redeemedAmount;
                                                @endphp
                                                @if($afterRedeemed < $weeklySale->total_amount)
                                                <td>£{{ $afterRedeemed }}</td>
                                                @else
                                                <td>-</td>
                                                @endif
                                                @if($redeemedAmount > 0)
                                                <td>£{{ $redeemedAmount }}</td>
                                                @else
                                                <td>-</td>
                                                @endif

                                                <td>{{ $weeklySale->day_name }}</td>
                                                <td>{{ $weeklySale->order_date }}</td>
                                            </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Content for Monthly Sales goes here -->
                            <div class="tab-pane fade" id="monthly" role="tabpanel" aria-labelledby="monthly-tab2">
                                <div class="card-body table-responsive">
                                    <div class="row text-center">
                                        <div>
                                            <h3>
                                                Total Earnings:
                                            </h3>
                                        </div>
                                        <div>
                                            <h4>
                                                £ {{ $data['totalMonthlyEarnings'] }}

                                            </h4>
                                        </div>
                                    </div>
                                    <table
                                        class="table table-striped table-responsive table-bordered text-center tableExport">
                                        <thead>
                                            <tr>
                                                <th>Order Code</th>
                                                <th>Branch Location</th>
                                                <th>User Name</th>
                                                <th>Product Name</th>
                                                <th>Topping</th>
                                                <th>Product Price</th>
                                                <th>Product Size</th>
                                                <th>Quantity</th>
                                                <th>Sub Total</th>
                                                <th>Tip amount</th>
                                                <th>Branch Tax</th>
                                                <th>Order Total</th>
                                                <th>After Redeemed</th>
                                                <th>Redeemed</th>
                                                <th>Day Name</th>
                                                <th>Order Date</th>
                                                {{-- <th>Action</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data['monthlySales'] as $index => $monthlySale)
                                            <tr>
                                                <td>#{{ $monthlySale->code }}</td>
                                                <td>
                                                    @foreach ($monthlySale->orderItem as $monthlySaleItem)
                                                    {{ $monthlySaleItem->branch->location }}
                                                    @endforeach
                                                </td>
                                                <th>{{ $monthlySale->user->name }}</th>
                                                <td>
                                                    @foreach ($monthlySale->orderItem as $monthlySaleItem)
                                                    {{ $monthlySaleItem->product_name }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>

                                                <td>
                                                    @foreach ($monthlySale->orderItem as $monthlySaleItem)
                                                    @if (!$monthlySaleItem->orderToppings->isEmpty())
                                                    @foreach ($monthlySaleItem->orderToppings as $topping)
                                                    @if ($loop->first)
                                                    <h6 class="small m-0">
                                                        {{ $topping->category->name }}:
                                                    </h6>
                                                    @endif
                                                    <p class="small m-0">
                                                        {{ $topping->toppings->name }}
                                                        (${{ $topping->toppings->price }})
                                                    </p>
                                                    @endforeach
                                                    @else
                                                    <div class="badge p-2 badge-shadow btn-danger text-white">
                                                        No Topping
                                                    </div>
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($monthlySale->orderItem as $monthlySaleItem)
                                                    {{ $monthlySaleItem->product_size ? $monthlySaleItem->product_size :
                                                    'No Size Found' }}
                                                    @break
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($monthlySale->orderItem as $monthlySaleItem)
                                                    £{{ $monthlySaleItem->product_price }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($monthlySale->orderItem as $monthlySaleItem)
                                                    {{ $monthlySaleItem->quantity }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($monthlySale->orderItem as $monthlySaleItem)
                                                    £{{ $monthlySaleItem->sub_total }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @php
                                                    $tips = [];
                                                    @endphp

                                                    @foreach ($monthlySale->orderItem as $monthlySaleItem)
                                                    @php
                                                    $tip = $monthlySaleItem->tip;
                                                    @endphp

                                                    @if (!in_array($tip, $tips))
                                                    £ {{ $tip }}
                                                    @php
                                                    $tips[] = $tip;
                                                    @endphp
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @php
                                                    $branchTaxs = [];
                                                    @endphp

                                                    @foreach ($monthlySale->orderItem as $monthlySaleItem)
                                                    @php
                                                    $branchTax = $monthlySaleItem->branch->tax;
                                                    @endphp

                                                    @if (!in_array($branchTax, $branchTaxs))
                                                    £ {{ $branchTax }}
                                                    @php
                                                    $branchTaxs[] = $branchTax;
                                                    @endphp
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>£{{ $monthlySale->total_amount }}</td>

                                                @php
                                                $redeemedAmount = is_numeric($monthlySale->redeemed) ? $monthlySale->redeemed : 0;
                                                $afterRedeemed = $monthlySale->total_amount - $redeemedAmount;
                                                @endphp
                                                @if($afterRedeemed < $monthlySale->total_amount)
                                                <td>£{{ $afterRedeemed }}</td>
                                                @else
                                                <td>-</td>
                                                @endif
                                                @if($redeemedAmount > 0)
                                                <td>£{{ $redeemedAmount }}</td>
                                                @else
                                                <td>-</td>
                                                @endif

                                                <td>{{ $monthlySale->day_name }}</td>
                                                <td>{{ $monthlySale->order_date }}</td>
                                            </tr>
                                            @endforeach
                                            <!-- Your table content for weekly sales here -->

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Content for Yearly Sales goes here -->
                            <div class="tab-pane fade" id="yearly" role="tabpanel" aria-labelledby="yearly-tab2">
                                <div class="card-body table-responsive">
                                    <div class="row text-center">
                                        <div>
                                            <h3>
                                                Total Earnings:
                                            </h3>
                                        </div>
                                        <div>
                                            <h4>
                                                £ {{ $data['totalYearlyAmount'] }}
                                            </h4>
                                        </div>
                                    </div>
                                    <table class="table table-striped table-bordered text-center tableExport">
                                        <thead>
                                            <tr>
                                                <th>Order Code</th>
                                                <th>Branch Location</th>
                                                <th>User Name</th>
                                                <th>Product Name</th>
                                                <th>Topping</th>
                                                <th>Product Price</th>
                                                <th>Product Size</th>
                                                <th>Quantity</th>
                                                <th>Sub Total</th>
                                                <th>Tip Amount</th>
                                                <th>Branch Tax</th>
                                                <th>Order Total</th>
                                                <th>After Redeemed</th>
                                                <th>Redeemed</th>
                                                <th>Day Name</th>
                                                <th>Order Date</th>
                                                {{-- <th>Action</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data['yearlySales'] as $index => $yearlySale)
                                            <tr>
                                                <td>#{{ $yearlySale->code }}</td>
                                                <td>
                                                    @foreach ($yearlySale->orderItem as $yearlySaleItem)
                                                    {{ $yearlySaleItem->branch->location }}
                                                    @endforeach
                                                </td>
                                                <th>{{ $yearlySale->user->name }}</th>
                                                <td>
                                                    @foreach ($yearlySale->orderItem as $yearlySaleItem)
                                                    {{ $yearlySaleItem->product_name }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>

                                                <td>
                                                    @foreach ($yearlySale->orderItem as $yearlySaleItem)
                                                    @if (!$yearlySaleItem->orderToppings->isEmpty())
                                                    @foreach ($yearlySaleItem->orderToppings as $topping)
                                                    @if ($loop->first)
                                                    <h6 class="small m-0">
                                                        {{ $topping->category->name }}:
                                                    </h6>
                                                    @endif
                                                    <p class="small m-0">
                                                        {{ $topping->toppings->name }}
                                                        (£{{ $topping->toppings->price }})
                                                    </p>
                                                    @endforeach
                                                    @else
                                                    <div class="badge p-2 badge-shadow btn-danger text-white">
                                                        No Topping
                                                    </div>
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($yearlySale->orderItem as $yearlySaleItem)
                                                    {{ $yearlySaleItem->product_size ? $yearlySaleItem->product_size :
                                                    'No Size Found' }}
                                                    @break
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($yearlySale->orderItem as $yearlySaleItem)
                                                    £{{ $yearlySaleItem->product_price }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($yearlySale->orderItem as $yearlySaleItem)
                                                    {{ $yearlySaleItem->quantity }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($yearlySale->orderItem as $yearlySaleItem)
                                                    £{{ $yearlySaleItem->sub_total }}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @php
                                                    $tips = [];
                                                    @endphp

                                                    @foreach ($yearlySale->orderItem as $yearlySaleItem)
                                                    @php
                                                    $tip = $yearlySaleItem->tip;
                                                    @endphp

                                                    @if (!in_array($tip, $tips))
                                                    £ {{ $tip }}
                                                    @php
                                                    $tips[] = $tip;
                                                    @endphp
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @php
                                                    $branchTaxs = [];
                                                    @endphp

                                                    @foreach ($yearlySale->orderItem as $yearlySaleItem)
                                                    @php
                                                    $branchTax = $yearlySaleItem->branch->tax;
                                                    @endphp

                                                    @if (!in_array($branchTax, $branchTaxs))
                                                    £ {{ $branchTax }}
                                                    @php
                                                    $branchTaxs[] = $branchTax;
                                                    @endphp
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td>£{{ $yearlySale->total_amount }}</td>

                                                @php
                                                $redeemedAmount = is_numeric($yearlySale->redeemed) ? $yearlySale->redeemed : 0;
                                                $afterRedeemed = $yearlySale->total_amount - $redeemedAmount;
                                                @endphp
                                                @if($afterRedeemed < $yearlySale->total_amount)
                                                <td>£{{ $afterRedeemed }}</td>
                                                @else
                                                <td>-</td>
                                                @endif
                                                @if($redeemedAmount > 0)
                                                <td>£{{ $redeemedAmount }}</td>
                                                @else
                                                <td>-</td>
                                                @endif

                                                <td>{{ $yearlySale->day_name }}</td>
                                                <td>{{ $yearlySale->order_date }}</td>
                                            </tr>
                                            @endforeach
                                            <!-- Your table content for weekly sales here -->

                                        </tbody>

                                    </table>
                                </div>
                            </div>
                            {{-- Branches Sales --}}
                            <div class="tab-pane fade" id="branches" role="tabpanel" aria-labelledby="branches-tab2">
                                <div class="card-body table-responsive">
                                    <table class="table table-striped table-bordered text-center tableExport">
                                        <thead>
                                            <tr>
                                                <th>Branch No</th>
                                                <th>Branch Location</th>
                                                <th>Branch Name</th>
                                                <th>Total Daily Earnings</th>
                                                <th>Total Weekly Earnings</th>
                                                <th>Total Monthly Earnings</th>
                                                <th>Total Yearly Earnings</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data['branchSales'] as $branchData)
                                            <tr>
                                                <td>{{ $branchData['branch']->branch_number }}</td>
                                                <td>{{ $branchData['branch']->location }}</td>
                                                <td>{{ $branchData['branch']->name }}</td>
                                                <td>
                                                    £{{ isset($branchData['totalDailyAmount']) ?
                                                    $branchData['totalDailyAmount'] : '0' }}
                                                </td>
                                                <td>
                                                    £{{ isset($branchData['totalWeeklyAmount']) ?
                                                    $branchData['totalWeeklyAmount'] : '0' }}
                                                </td>
                                                <td>
                                                    £{{ isset($branchData['totalMonthlyAmount']) ?
                                                    $branchData['totalMonthlyAmount'] : '0' }}
                                                </td>
                                                <td>
                                                    £{{ isset($branchData['totalYearlyAmount']) ?
                                                    $branchData['totalYearlyAmount'] : '0' }}
                                                </td>

                                            </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
</section>
</div>
@endsection
@section('js')
@if (\Illuminate\Support\Facades\Session::has('message'))
<script>
    toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
</script>
@endif
<script>
    $('.tableExport').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
    });
</script>
@endsection

@section('js')

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<!-- Add this to your layout file, before the Bootstrap JavaScript file -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection
