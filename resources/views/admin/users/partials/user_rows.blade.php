@foreach ($users as $user)
<tr>

<td>{{ ($users->currentPage()-1)*$users->perPage()+$loop->iteration }}</td>

<td>{{ $user->name }}</td>

<td>
@if($user->email)
<a href="mailto:{{ $user->email }}" style="text-decoration: underline; color:#007bff">
{{ $user->email }}
</a>
@else
<span class="text-muted">No Email</span>
@endif
</td>

<td>{{ $user->phone ?? 'No Phone' }}</td>

<td>{{ $user->postcode ?? 'No Postcode' }}</td>

<td>{{ $user->address ?? 'No Address' }}</td>

<td>£{{ number_format($user->average_spend,2) }}</td>

<td>

<form method="POST" action="{{ route('users-delete',$user->id) }}" class="deleteForm">
@csrf
@method('DELETE')

<button class="btn btn-danger btn-sm show_confirm">
<i class="fa fa-trash"></i>
</button>

</form>

</td>

</tr>
@endforeach