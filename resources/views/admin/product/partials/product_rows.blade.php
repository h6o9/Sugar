@foreach ($products as $index => $product)
<tr>
    <td>{{ ($products->currentPage()-1)*$products->perPage() + $loop->iteration }}</td>
    <td>{{ $product->menu->name ?? '' }}</td>
    <td>{{ $product->name }}</td>
    <td><img src="{{ asset($product->image) }}" height="50" width="50"></td>

    <td>
        @if ($product->variants->isNotEmpty())
            £{{ $product->variants->pluck('original_price')->implode(', £') }}
        @else
            £{{ $product->original_price }}
        @endif
    </td>

    <td>
        @if ($product->variants->isNotEmpty())
            £{{ $product->variants->pluck('price')->implode(', £') }}
        @else
            £{{ $product->price }}
        @endif
    </td>

    <td>
        @if ($product->variants->isNotEmpty())
            {{ $product->variants->pluck('size')->implode(', ') }}
        @else
            <div class="badge badge-danger">No size</div>
        @endif
    </td>

    <td>
        @php
            $ruleValue = $product->rule ?? null;
            $rule = $ruleValue == 'Priority' ? 'Individual' : ucfirst($ruleValue);
        @endphp

        @if(empty($ruleValue))
            <span class="badge badge-primary">No Settings Applied</span>
        @elseif(strtolower($ruleValue) == 'bulk')
            <span class="badge badge-success">{{ ucfirst($ruleValue) }}</span>
        @elseif($ruleValue == 'Priority')
            <span class="badge badge-danger">{{ $rule }}</span>
        @else
            <span class="badge badge-secondary">{{ $rule }}</span>
        @endif
    </td>

    <td>
        @if ($product->is_featured)
            <a href="{{ route('admin.featured',$product->id) }}" class="btn btn-success"><i class="fas fa-star"></i></a>
        @else
            <a href="{{ route('admin.featured',$product->id) }}" class="btn btn-secondary"><i class="far fa-star"></i></a>
        @endif
    </td>

    <td>
        @if ($product->status)
            <span class="badge badge-success">Active</span>
        @else
            <span class="badge badge-danger">DeActive</span>
        @endif
    </td>

    <td style="display:flex;gap:5px;justify-content:center">
        <div>
          <a class="btn btn-info" href="{{ route('product.edit',$product->id) }}">Edit</a>
        </div>

       <form method="POST" action="{{ route('product.destroy', $product->id) }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger show_confirm">Delete</button>
</form>
    </td>
</tr>
@endforeach