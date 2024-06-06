<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                {{ __('Orders') }}
            </h3>
        </div>

        <div class="card-actions">
            <x-action.create route="{{ route('orders.create') }}" />
        </div>
    </div>

    <div class="card-body border-bottom py-3">
        <div class="d-flex">
            <div class="text-secondary">
                Show
                <div class="mx-2 d-inline-block">
                    <select wire:model.live="perPage" class="form-select form-select-sm" aria-label="result per page">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                    </select>
                </div>
                entries
            </div>
            <div class="ms-auto text-secondary">
                Search:
                <div class="ms-2 d-inline-block">
                    <input type="text" wire:model.live="search" class="form-control form-control-sm"
                        aria-label="Search invoice">
                </div>
            </div>
        </div>
    </div>

    <x-spinner.loading-spinner />

    <div class="table-responsive">
        <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
            <thead class="thead-light">
                <tr>
                    <th class="align-middle text-center w-1">
                        {{ __('No.') }}
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('invoice_no')" href="#" role="button">
                            {{ __('Invoice No.') }}
                            @include('includes._sort-icon', ['field' => 'invoice_no', 'sortField' => $sortField, 'sortAsc' => $sortAsc])
                        </a>
                    </th>
                    <!-- Kolom lainnya -->
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    @foreach ($order->details as $detail)
                        <tr>
                            <td class="align-middle text-center">
                                {{ $order->id }}
                            </td>
                            <td class="align-middle text-center">
                                {{ $order->invoice_no }}
                            </td>
                            <td class="align-middle text-center">
                                {{ $order->customer->name }}
                            </td>
                            <td class="align-middle text-center">
                                {{ $detail->product->name }}
                            </td>
                            <td class="align-middle text-center">
                                {{ $detail->quantity }}
                            </td>
                            <td class="align-middle text-center">
                                {{ $detail->price }}
                            </td>
                            <td class="align-middle text-center">
                                <x-status dot
                                    color="{{ $order->order_status === \App\Enums\OrderStatus::COMPLETE ? 'green' : ($order->order_status === \App\Enums\OrderStatus::PENDING ? 'orange' : '') }}"
                                    class="text-uppercase">
                                    {{ $order->order_status->label() }}
                                </x-status>
                            </td>
                            <td class="align-middle text-center">
                                <x-button.show class="btn-icon" route="{{ route('orders.show', $order->uuid) }}" />
                                <x-button.print class="btn-icon"
                                    route="{{ route('order.downloadInvoice', $order->uuid) }}" />
                                @if ($order->order_status === \App\Enums\OrderStatus::PENDING)
                                    <x-button.delete class="btn-icon" route="{{ route('orders.cancel', $order) }}"
                                        onclick="return confirm('Are you sure to cancel invoice no. {{ $order->invoice_no }} ?')" />
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">
            Showing <span>{{ $orders->firstItem() }}</span> to <span>{{ $orders->lastItem() }}</span> of
            <span>{{ $orders->total() }}</span> entries
        </p>

        <ul class="pagination m-0 ms-auto">
            {{ $orders->links() }}
        </ul>
    </div>
</div>
