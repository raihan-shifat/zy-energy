@extends('admin.layouts.admin')

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="mb-0 dt-heading">Exchange Rates</h6>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Currency Code</th>
                    <th>Name</th>
                    <th>Rate (vs RMB)</th>
                    <th>Base</th>
                    <th>Active</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rates as $rate)
                <tr>
                    <td><strong>{{ $rate->code }}</strong></td>
                    <td>{{ $rate->name }}</td>
                    <td>
                        <form action="{{ route('admin.exchange-rates.update', $rate) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="number" name="rate" value="{{ $rate->rate }}" step="0.000001" class="form-control form-control-sm d-inline-block" style="width:140px;" {{ $rate->is_base ? 'disabled' : '' }}>
                            @unless($rate->is_base)
                                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i></button>
                            @endunless
                        </form>
                    </td>
                    <td>{!! $rate->is_base ? '<span class="badge bg-primary">Base</span>' : '' !!}</td>
                    <td>
                        @unless($rate->is_base)
                        <form action="{{ route('admin.exchange-rates.update', $rate) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="rate" value="{{ $rate->rate }}">
                            <input type="hidden" name="is_active" value="{{ $rate->is_active ? 0 : 1 }}">
                            <button type="submit" class="btn btn-sm {{ $rate->is_active ? 'btn-success' : 'btn-secondary' }}">
                                {{ $rate->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                        @endunless
                    </td>
                    <td>{{ $rate->last_updated_at ? $rate->last_updated_at->format('Y-m-d H:i') : '-' }}</td>
                    <td>
                        @unless($rate->is_base)
                        <form action="{{ route('admin.exchange-rates.destroy', $rate) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this currency?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endunless
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr>
        <h6>Add New Currency</h6>
        <form action="{{ route('admin.exchange-rates.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-2">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-control" required maxlength="10" placeholder="e.g. EUR">
            </div>
            <div class="col-md-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Euro">
            </div>
            <div class="col-md-2">
                <label class="form-label">Rate (vs RMB)</label>
                <input type="number" name="rate" class="form-control" required step="0.000001" value="1">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success">Add Currency</button>
            </div>
        </form>
    </div>
</div>
@endsection
