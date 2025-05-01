<x-mail::message>
<h1 style="text-align: center; font-size: 24px">
    Payment was Completed Successfully.
</h1>

@foreach ($orders as $order)

<x-mail::table>

<table>

<tbody>

<tr>
<td>Seller</td>
<td>
<a href="{{ url('/') }}">
{{ $order->vendorUser->vendor->store_name}}
</a>
</td>
</tr>

<tr>
<td>Order #</td>
<td># {{ $order->id }}</td>
</tr>

<tr>
<td>Items</td>
<td>{{ $order->orderItems->count() }}</td>
</tr>

<tr>
<td>Total</td>
<td>{{ \Illuminate\Support\Number::currency($order->total_price) }}</td>
</tr>
</tbody>

</table>

</x-mail::table>

<x-mail::table>

<table>

<thead>
<tr>
<th>Item</th>
<th>Quantity</th>
<th>Price</th>
</tr>
</thead>

<tbody>
@foreach ( $order->orderItems as $orderItem )
<tr>
<td>
<table>
<tbody>
<tr>
<td padding="5" style="padding: 5px">
<img style="min-width: 60px; max-width: 60px" src="{{ $orderItem->product->getImageForOptions($orderItem->variation_type_option_ids) }}" alt="">
</td>

<td style="font-size: 13px; padding: 5px">
{{ $orderItem->product->title }}

</td>

</tr>
</tbody>
</table>
</td>
<td>
{{ $orderItem->quantity }}
</td>
<td>
{{ \Illuminate\Support\Number::currency($orderItem->price) }}
</td>

</tr>
@endforeach

</tbody>
</table>

</x-mail::table>

<x-mail::button :url="$order->id">
View Order Details
</x-mail::button>

@endforeach



<x-mail::subcopy>
    Lorem ipsum dolor sit amet consectetur adipisicing elit. Quae dignissimos debitis natus unde nobis. Maxime, pariatur? Eaque consequuntur enim odit fugit, accusantium nemo eveniet voluptatem non minima.
</x-mail::subcopy>

<x-mail::panel>
    Lorem ipsum dolor sit amet consectetur adipisicing elit. Asperiores omnis tempore commodi aperiam quis culpa accusantium dolorem amet. Ut nesciunt dignissimos ab voluptate doloremque velit debitis harum tempore blanditiis nihil.
</x-mail::panel>


Thanks, <br>
{{ config('app.name')}}




</x-mail::message>