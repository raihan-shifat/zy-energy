@extends('admin.layouts.admin')

@section('css')
<style>
    /* Quick-access cards (Quotation Maker + Enquiries) */
    .quick-card {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 1.75rem 2rem;
        border-radius: 12px;
        text-decoration: none;
        color: #fff;
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
    }
    .quick-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, .18);
        filter: brightness(1.05);
        color: #fff;
    }
    .quick-card--quotation { background: var(--brand-700, #0e7a4f); }
    .quick-card--enquiry   { background: var(--accent-600, #2563eb); }
    .quick-card__icon {
        font-size: 2.5rem;
        line-height: 1;
        opacity: .9;
    }
    .quick-card__title {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0;
    }
    .quick-card__hint {
        margin: 0;
        font-size: .875rem;
        opacity: .85;
    }
    .quick-card__badge {
        margin-left: auto;
        background: #fff;
        color: #111;
        font-weight: 700;
        border-radius: 999px;
        padding: .45rem .9rem;
        font-size: 1rem;
    }

    /* Metric cards */
    .metric-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        height: 100%;
    }
    .metric-card h6 {
        margin: 0;
        font-size: .8rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .metric-card p.value {
        margin: .35rem 0 0;
        font-size: 1.6rem;
        font-weight: 700;
        color: #212529;
    }
    .metric-card i {
        color: var(--brand-700, #0e7a4f);
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- Quick-access cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <a href="{{ route('admin.quotations.create') }}" class="quick-card quick-card--quotation">
                <i class="fas fa-file-invoice-dollar quick-card__icon"></i>
                <div>
                    <p class="quick-card__title">Create a New Quotation</p>
                    <p class="quick-card__hint">Open the Quotation Maker and start building a quote in one click.</p>
                </div>
                <i class="fas fa-arrow-right ms-auto"></i>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('admin.enquiries.index') }}" class="quick-card quick-card--enquiry">
                <i class="fas fa-envelope-open-text quick-card__icon"></i>
                <div>
                    <p class="quick-card__title">View Enquiries</p>
                    <p class="quick-card__hint">Review RFQ leads submitted from the storefront.</p>
                </div>
                @if ($data['newEnquiries'] > 0)
                    <span class="quick-card__badge">{{ $data['newEnquiries'] }} new</span>
                @endif
            </a>
        </div>
    </div>

    {{-- Metric cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="metric-card">
                <h6><i class="fas fa-users me-1"></i> Total Visitors</h6>
                <p class="value">{{ number_format($data['totalVisitors']) }}</p>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="metric-card">
                <h6><i class="fas fa-envelope me-1"></i> Total Enquiries</h6>
                <p class="value">{{ number_format($data['totalEnquiries']) }}</p>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="metric-card">
                <h6><i class="fas fa-file-invoice me-1"></i> Total Quotations</h6>
                <p class="value">{{ number_format($data['totalQuotations']) }}</p>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="metric-card">
                <h6><i class="fas fa-box me-1"></i> Total Products</h6>
                <p class="value">{{ number_format($data['totalProducts']) }}</p>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="metric-card">
                <h6><i class="fas fa-sitemap me-1"></i> Total Categories</h6>
                <p class="value">{{ number_format($data['totalCategories']) }}</p>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="metric-card">
                <h6><i class="fas fa-fire me-1"></i> Top Enquired Category</h6>
                <p class="value" style="font-size: 1.1rem;">
                    {{ $data['topEnquiredCategory']->category_name ?? '—' }}
                </p>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Recent Enquiries (latest 5) --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
                    <span>Recent Enquiries</span>
                    <a href="{{ route('admin.enquiries.index') }}" class="text-white small">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Country</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['recentEnquiries'] as $enquiry)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.enquiries.show', $enquiry->id) }}">
                                            {{ $enquiry->name }}
                                        </a>
                                    </td>
                                    <td>{{ $enquiry->country ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $enquiry->status == 'new' ? 'warning' : ($enquiry->status == 'contacted' ? 'info' : 'secondary') }}">
                                            {{ ucfirst($enquiry->status) }}
                                        </span>
                                    </td>
                                    <td>{{ \format_date($enquiry->created_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted text-center py-3">No enquiries yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Quotations (latest 5) --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
                    <span>Recent Quotations</span>
                    <a href="{{ route('admin.quotations.index') }}" class="text-white small">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Invoice No.</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['recentQuotations'] as $quotation)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.quotations.show', $quotation) }}">
                                            {{ $quotation->invoice_number }}
                                        </a>
                                    </td>
                                    <td>{{ $quotation->customer_name }}</td>
                                    <td>{{ $quotation->currency_symbol }}{{ number_format($quotation->total, 2) }}</td>
                                    <td>{{ \format_date($quotation->date) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted text-center py-3">No quotations yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
