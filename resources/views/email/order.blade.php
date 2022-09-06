<h3>USADHA BHAKTI</h3>
<p>------------------------------------------------------------</p>
<p>To: {{ $agent->name }}</p>
<p>Tanggal:       {{ $order->register }}<p>
<p>Order Number:  {{ $order->code }}<p>
<p>Nama Member:   {{ $order->customers->code }} - {{ $order->customers->name }}<p>
<p>------------------------------------------------------------<p>
@foreach ($order->products as $id => $product)
<p>{{ $product->code }} - {{ $product->name }}<p>
<p>{{ $product->pivot->quantity }} @ Rp.{{ number_format($product->price, 2) }} (Rp.{{ number_format($product->pivot->quantity*$product->price, 2) }})<p>
@endforeach
<p>------------------------------------------------------------<p>
<p>TOTAL AMOUNT:                                      Rp.{{ number_format($order->total, 2) }}<p>

<p>Selamat datang di <a href="http://usadhabhakti.com/">www.usadhabhakti.com</a></p>
<p>Order Confirmation.</p>