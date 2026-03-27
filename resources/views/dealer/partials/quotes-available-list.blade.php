<div class="card" style="padding:0;">
    <table class="table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Postcode</th>
                <th>Interests</th>
                <th>Price</th>
                <th>Purchased</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
            @php $cnt = (int) ($counts[$it->id] ?? 0); $iBought = in_array($it->id, $mine ?? []); @endphp
            <tr data-lead-id="{{ $it->id }}">
                <td>
                    <div class="fw-700 text-dark">{{ $iBought ? $it->name : 'Name Hidden' }}</div>
                    <div class="text-sm text-muted">{{ $iBought ? $it->email : 'Email Hidden' }}</div>
                </td>
                <td>{{ $it->postcode }}</td>
                <td>
                    @if(is_array($it->interests) && count($it->interests))
                        @foreach($it->interests as $tag)
                            <span class="badge">{{ ucwords(str_replace('_',' ',$tag)) }}</span>
                        @endforeach
                    @else
                        —
                    @endif
                </td>
                <td>@if(!is_null($it->price)) £{{ number_format($it->price, 2) }} @else — @endif</td>
                <td><span class="text-sm">{{ $cnt }} dealer{{ $cnt===1?'':'s' }} purchased</span></td>
                <td>
                    @if($iBought)
                        <a class="btn btn--ghost btn--sm" href="{{ route('dealer.leads.view', $it) }}">View</a>
                    @elseif($cnt >= 3)
                        <button class="btn btn--ghost btn--sm" disabled>Sold Out</button>
                    @else
                        <button class="btn btn--primary btn--sm js-buy-lead" data-id="{{ $it->id }}">Buy</button>
                        <form action="{{ route('dealer.leads.decline', $it->id) }}" method="POST" style="display:inline-block; margin-left: 6px;">
                            @csrf
                            <button class="btn btn--danger-soft btn--sm">Decline</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:1rem">No leads available.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($items->hasPages())
    <div class="mt-4 quotes-available-pagination" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
@endif
