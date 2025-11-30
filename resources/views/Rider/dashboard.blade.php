<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rider Dashboard</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Leaflet --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />

    {{-- SweetAlert + Icons --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Vite assets --}}
    @vite(['resources/css/rider-dashboard.css', 'resources/js/rider-dashboard.js', 'resources/css/history.css'])

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

    {{-- SIDEBAR --}}
    <div class="sidebar">
        {{-- Theme toggle --}}
        <button id="themeToggle" class="btn btn-outline-light ms-2">
            <i class="bi bi-sun-fill" id="themeIcon"></i>
        </button>

        {{-- Nav links --}}
        <ul class="nav nav-pills flex-md-column flex-row align-items-center w-100" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#dashboard" type="button" role="tab">
                    📊 Dashboard
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#history" type="button" role="tab">
                    📜 History
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#transaction" type="button" role="tab">
                    💳 Transactions
                </button>
            </li>
        </ul>

        {{-- PROFILE SECTION (desktop) --}}
        <div class="profile-section dropdown text-center mt-2">
            <img
                src="{{ asset('videos/phpunit.webp') }}"
                alt="Profile Picture"
                class="profile-pic dropdown-toggle"
                id="profileDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                style="width: 70px; height: 70px; border-radius: 50%;">

            <h5 class="mt-2">Welcome Back</h5>
            <h6>{{ $rider->name ?? 'Rider' }}</h6>

            <form id="logoutForm" method="POST" action="{{ route('rider.logout') }}">
                @csrf
                <button type="button" class="btn btn-danger w-100 mt-2 logout-btn">🚪 Logout</button>
            </form>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="main-content">
        <div class="tab-content">

            {{-- PROFILE TAB (mobile) --}}
            <div class="tab-pane fade" id="profile">
                <div class="p-3 text-center">

                    <div class="d-flex justify-content-end mb-2 d-md-none">
                        <button id="mobileThemeToggle" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-sun-fill" id="mobileThemeIcon"></i>
                        </button>
                    </div>

                    <img
                        src="{{ asset('videos/phpunit.webp') }}"
                        alt="Profile Picture"
                        style="width:80px; height:80px; border-radius:50%; margin-bottom:10px;">
                    <h5>{{ $rider->name ?? 'Rider' }}</h5>
                    <p class="text-muted">Welcome Back!</p>

                    <form id="logoutFormMobile" method="POST" action="{{ route('rider.logout') }}">
                        @csrf
                        <button type="button" class="btn btn-danger w-100 logout-btn">🚪 Logout</button>
                    </form>
                </div>
            </div>

            {{-- DASHBOARD TAB --}}
            <div class="tab-pane fade" id="dashboard">
                <div class="dashboard-header">
                    <img src="{{ asset('videos/header.png') }}" alt="Header Left">
                </div>

                <div class="container py-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="card route-card shadow-sm p-3">
                                <h5 class="text-center mb-3">🗺️ Set Your Route</h5>

                                <div class="mb-2">
                                    <label for="fromPlace" class="form-label">From:</label>
                                    <input type="text" id="fromPlace" class="form-control route-input" placeholder="Starting point">
                                </div>

                                <div class="mb-2">
                                    <label for="toPlace" class="form-label">To:</label>
                                    <input type="text" id="toPlace" class="form-control route-input" placeholder="Destination">
                                </div>

                                <div class="text-center">
                                    <button id="calculateRouteBtn" class="btn btn-danger w-100 route-btn">
                                        Calculate Route
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Map card --}}
                        <div class="col-12">
                            <div class="card map-card shadow-sm p-2 position-relative">
                                <div id="mapContainer">
                                    <div id="map"></div>
                                </div>

                                <button id="fullscreenBtn"
                                        class="btn fullscreen-toggle-btn shadow rounded-circle"
                                        style="z-index: 10000;">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Route info --}}
                        <div class="col-12">
                            <div id="routeInfo" class="alert route-alert d-none"></div>
                            <div id="loadingSpinner" class="text-center" style="display:none;">
                                <div class="spinner-border text-danger" role="status"></div>
                                <p>Calculating route...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- HISTORY TAB --}}
            <div class="tab-pane fade" id="history">
                <div class="content-card">
                    <h3>📜 Delivery History</h3>

                    <div class="history-controls mb-3 d-flex flex-wrap gap-2">
                        <input type="text" id="historySearch" class="form-control w-auto"
                               placeholder="Search history..." style="min-width: 290px; color: white;">

                        {{-- Filter dropdown --}}
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button"
                                    id="historyFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="historyFilterDropdown">
                                <li><a class="dropdown-item history-filter" href="#" data-filter="all">All</a></li>
                                <li><a class="dropdown-item history-filter" href="#" data-filter="today">Today</a></li>
                                <li><a class="dropdown-item history-filter" href="#" data-filter="week">Week</a></li>
                                <li><a class="dropdown-item history-filter" href="#" data-filter="month">Month</a></li>
                            </ul>
                        </div>

                        {{-- Sort dropdown --}}
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button"
                                    id="historySortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Latest First
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="historySortDropdown">
                                <li><a class="dropdown-item sort-option active" href="#" data-value="latest">Latest First</a></li>
                                <li><a class="dropdown-item sort-option" href="#" data-value="oldest">Oldest First</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Scrollable accordion --}}
                <div class="history-accordion-wrapper">
                    <div class="accordion" id="historyAccordion">
                        @forelse ($history as $item)
                            <div class="accordion-item" data-date="{{ $item->updated_at }}">
                                <h2 class="accordion-header" id="headingHistory{{ $item->transaction_id }}">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseHistory{{ $item->transaction_id }}"
                                            aria-expanded="false"
                                            aria-controls="collapseHistory{{ $item->transaction_id }}">
                                        #{{ $item->user_id }} — {{ $item->user->name }}
                                        <span class="badge bg-success ms-2">Completed</span>
                                    </button>
                                </h2>

                                <div id="collapseHistory{{ $item->transaction_id }}"
                                     class="accordion-collapse collapse"
                                     aria-labelledby="headingHistory{{ $item->transaction_id }}"
                                     data-bs-parent="#historyAccordion">
                                    <div class="accordion-body">
                                        <p><strong>Date Delivered:</strong>
                                            {{ $item->updated_at ? $item->updated_at->format('Y-m-d H:i') : 'N/A' }}
                                        </p>

                                        <p><strong>Customer:</strong>
                                            {{ $item->user->name ?? 'N/A' }}
                                        </p>

                                        <p class="customer-phone"><strong>Contact:</strong> 
                                            {{ $item->user->telephone_number ?? 'N/A' }} 💬 📞
                                        </p>

                                        <p><strong>Address:</strong>
                                            {{ $item->address ?? 'N/A' }}
                                        </p>

                                        <p><strong>Proof of Delivery:</strong></p>

                                        @if($item->proof_of_delivery)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $item->proof_of_delivery) }}"
                                                     class="img-thumbnail"
                                                     style="width: 120px; cursor:pointer;"
                                                     data-bs-toggle="modal"
                                                     data-bs-target="#proofModal{{ $item->transaction_id }}">
                                            </div>

                                            {{-- Modal for this history item --}}
                                            <div class="modal fade proofmodal"
                                                 id="proofModal{{ $item->transaction_id }}"
                                                 tabindex="-1" aria-hidden="true"
                                                 data-bs-backdrop="true">
                                        
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content bg-dark">
                                                        <div class="modal-header border-0">
                                                            <button type="button"
                                                                    class="btn-close btn-close-white"
                                                                    data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-center">
                                                            <img src="{{ asset('storage/' . $item->proof_of_delivery) }}"
                                                                 class="img-fluid rounded">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">No proof uploaded</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted">No completed deliveries yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- TRANSACTIONS TAB --}}
            <div class="tab-pane fade" id="transaction">
                <div class="content-card">
                    <h3>💳 Transactions</h3>
                    </div>


                    <div class="accordion" id="transactionsAccordion" style="margin-top: 25px;">
                        @foreach ($orders as $order)
                            <div class="accordion-item"  style="margin-top: 15px;">
                                <h2 class="accordion-header" id="heading{{ $order->id }}">
                                    <button class="accordion-button collapsed d-flex justify-content-between align-items-center"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $order->id }}"
                                            aria-expanded="false"
                                            aria-controls="collapse{{ $order->id }}" >

                                        <span class="d-flex flex-nowrap align-items-center flex-grow-1 text-truncate">
                                            <span class="text-truncate me-2">{{ $order->user->name  ?? 'N/A'}}</span>
                                            <span class="badge bg-info flex-shrink-0">{{ $order->status }}</span>
                                        </span>
                                    </button>
                                </h2>

                                <div id="collapse{{ $order->id }}" class="accordion-collapse collapse"
                                     aria-labelledby="heading{{ $order->id }}"
                                     data-bs-parent="#transactionsAccordion">
                                    <div class="accordion-body" style="margin-left: 15px; margin-right: 15px;">
                                        <p><strong>Contact:</strong>
                                            <span class="customer-phone link" style="color:#0d6efd;cursor:pointer;"
                                                  data-phone="{{ $order->user->telephone_number ?? 'N/A' }}">
                                                {{ $order->user->telephone_number  ?? 'N/A'}} 💬 📞
                                            </span>
                                        </p>

                                        <p><strong>Location</strong>
                                            <span style="color:#0d6efd;cursor:pointer;">
                                                {{ $order->location }}
                                            </span>
                                        </p>

                                        <p class="address-text"><strong>Address:</strong> {{ $order->address }}</p>
                                        <p><strong>Total:</strong> ₱{{ $order->total }}</p>

                                        @php
                                            if ($order->location) {
                                                [$lat, $lng] = explode(',', $order->location);
                                            } else {
                                                $lat = $lng = null;
                                            }
                                        @endphp

                                        <button class="btn btn-primary set-route-btn"
                                                data-transaction-id="{{ $order->id }}"
                                                data-name="{{ $order->user->name ?? 'N/A' }}"
                                                data-address="{{ $order->address }}"
                                                data-contact="{{ $order->user->telephone_number ?? 'N/A' }}"
                                                data-lat="{{ $lat }}"
                                                data-lng="{{ $lng }}">
                                            Set as Destination
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- MOBILE BOTTOM NAV --}}
    <div class="mobile-bottom-nav d-md-none">
        <button class="nav-btn active" data-bs-toggle="pill" data-bs-target="#dashboard" type="button">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </button>
        <button class="nav-btn" data-bs-toggle="pill" data-bs-target="#history" type="button">
            <i class="bi bi-clock-history"></i>
            <span>History</span>
        </button>
        <button class="nav-btn" data-bs-toggle="pill" data-bs-target="#transaction" type="button">
            <i class="bi bi-credit-card"></i>
            <span>Transaction</span>
        </button>
        <button class="nav-btn" data-bs-toggle="pill" data-bs-target="#profile" type="button">
            <i class="bi bi-person-circle"></i>
            <span>Profile</span>
        </button>
    </div>

    {{-- CUSTOMER MODAL --}}
    <div class="modal fade" id="customerModal" tabindex="-1"
         aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content customer-modal">
                <div class="modal-header customer-modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Customer Details</h5>
                    <button type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body customer-modal-body">
                    <p><strong>Name:</strong> <span id="modalCustomerName"></span></p>
                    <p class="customer-phone"><strong >Contact:</strong> <span id="modalCustomerContact"></span></p>
                    <p><strong>Address:</strong> <span id="modalCustomerAddress"></span></p>

                    <input type="hidden" id="modalCustomerLat">
                    <input type="hidden" id="modalCustomerLng">
                    <input type="hidden" id="modalTransactionId">

                    <div class="mb-2">
                        <label for="proofInput" class="form-label">Proof of Delivery (photo)</label>
                        <input type="file" id="proofInput" accept="image/*" class="form-control">
                        <small class="form-text">Required when marking as delivered.</small>
                    </div>
                </div>

                <div class="modal-footer customer-modal-footer">
                    <button type="button" class="btn btn-success" id="confirmSetInTransitBtn">
                        Start Delivery
                    </button>
                    <button type="button" class="btn btn-primary" id="uploadDeliverBtn">
                        Mark Delivered (upload proof)
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- PHONE MODAL --}}
    <div class="modal fade" id="phoneModal" tabindex="-1" aria-labelledby="phoneModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content contact-modal">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="phoneModalLabel">Contact Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="contact-icon-wrapper mb-3">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>
                    <p id="modalPhoneNumber" class="fs-4 fw-semibold mb-3"></p>
                    <p class="contact-subtitle mb-3">Choose how you want to reach the customer</p>

                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mt-2">
                        <a id="callBtn" class="btn contact-btn-call d-flex align-items-center justify-content-center gap-2 px-4" href="">
                            <i class="bi bi-telephone-fill"></i>
                            <span>Call</span>
                        </a>
                        <a id="messageBtn" class="btn contact-btn-message d-flex align-items-center justify-content-center gap-2 px-4" href="">
                            <i class="bi bi-chat-dots-fill"></i>
                            <span>Message</span>
                        </a>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0">
                    <small class="contact-note">Make sure to confirm the order details with the customer.</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
    <script src="https://unpkg.com/lrm-openrouteservice/dist/lrm-openrouteservice.js"></script>
</body>
</html>
