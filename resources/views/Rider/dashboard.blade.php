<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />

  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rider Dashboard</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  @vite(['resources/css/rider-dashboard.css', 'resources/js/rider-dashboard.js', 'resources/css/history.css'])

  <meta name="csrf-token" content="{{ csrf_token() }}">

  

</head>

<body>
  <!-- === SIDEBAR (turns horizontal on mobile) === -->
  <div class="sidebar">
    <!-- NAV LINKS -->

    <!-- 🌗 Theme Toggle Button -->
    <button id="themeToggle" class="btn btn-outline-light ms-2">
      <i class="bi bi-sun-fill" id="themeIcon"></i>
    </button>

    <ul class="nav nav-pills flex-md-column flex-row align-items-center w-100" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#dashboard" type="button" role="tab">
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



    <!-- PROFILE SECTION -->
    <div class="profile-section dropdown text-center mt-2">
      <img
        src="{{ asset('videos/A952D051-BA1D-47D0-B69D-08AC9EB2ADEE_1_201_a.jpeg') }}"
        alt="Profile Picture"
        class="profile-pic dropdown-toggle"
        id="profileDropdown"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        style="width: 70px; height: 70px; border-radius: 50%;">

      <h5 class="mt-2">Welcome Back</h5>
      <h6>{{ $rider->name ?? 'Rider' }}</h6>

      <!-- SINGLE LOGOUT FORM -->
      <form id="logoutForm" method="POST" action="{{ route('rider.logout') }}">
        @csrf
        <button type="button" class="btn btn-danger w-100 mt-2 logout-btn">🚪 Logout</button>
      </form>
    </div>



  </div>

  <!-- === MAIN CONTENT === -->
  <div class="main-content">
    <div class="tab-content">
      <!-- MOBILE PROFILE TAB -->
      <div class="tab-pane fade" id="profile">
        <div class="p-3 text-center">
          <img
            src="{{ asset('videos/A952D051-BA1D-47D0-B69D-08AC9EB2ADEE_1_201_a.jpeg') }}"
            alt="Profile Picture"
            style="width:80px; height:80px; border-radius:50%; margin-bottom:10px;">
          <h5>{{ $rider->name ?? 'Rider' }}</h5>
          <p class="text-muted">Welcome Back!</p>

          <!-- Logout Form -->
          <form id="logoutFormMobile" method="POST" action="{{ route('rider.logout') }}">
            @csrf
            <button type="button" class="btn btn-danger w-100 logout-btn">🚪 Logout</button>
          </form>
        </div>
      </div>



      <!-- DASHBOARD -->
      <div class="tab-pane fade show active" id="dashboard">
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

    <!-- Map Card -->
    <div class="col-12">
      <div class="card map-card shadow-sm p-2 position-relative">
        <div id="mapContainer">
          <div id="map"></div>
        </div>

        <!-- Fullscreen Button -->
        <button id="fullscreenBtn"
          class="btn fullscreen-toggle-btn shadow rounded-circle"
          style="z-index: 10000;">
          <i class="bi bi-arrows-fullscreen"></i>
        </button>
      </div>
    </div>

    <!-- Route Info -->
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

<div class="tab-pane fade" id="history">
    <div class="content-card">
        <h3>📜 Delivery History</h3>

        <!-- Controls: Search, Filters, Sort -->
        <div class="history-controls mb-3 d-flex flex-wrap gap-2">
            <input type="text" id="historySearch" class="form-control w-auto" placeholder="Search history..." style="min-width: 290px; color: white;">

            <!-- Filter Dropdown -->
<div class="dropdown">
  <button class="btn btn-outline-light dropdown-toggle" type="button" id="historyFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="bi bi-funnel"></i> Filter
  </button>
  <ul class="dropdown-menu" aria-labelledby="historyFilterDropdown">
    <li><a class="dropdown-item history-filter" href="#" data-filter="all">All</a></li>
    <li><a class="dropdown-item history-filter" href="#" data-filter="today">Today</a></li>
    <li><a class="dropdown-item history-filter" href="#" data-filter="week">Week</a></li>
    <li><a class="dropdown-item history-filter" href="#" data-filter="month">Month</a></li>
  </ul>
</div>


           <!-- Dropdown Sort -->
<div class="dropdown">
  <button class="btn btn-outline-light dropdown-toggle" type="button" id="historySortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
    Latest First
  </button>
  <ul class="dropdown-menu" aria-labelledby="historySortDropdown">
    <li><a class="dropdown-item sort-option active" href="#" data-value="latest">Latest First</a></li>
    <li><a class="dropdown-item sort-option" href="#" data-value="oldest">Oldest First</a></li>
  </ul>
</div>


        </div>
                </div>



        <!-- Scrollable Accordion -->
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
                            #{{ $item->transaction_id }} — {{ $item->customer_name }}
                            <span class="badge bg-success ms-2">Completed</span>
                        </button>
                    </h2>

                    <div id="collapseHistory{{ $item->transaction_id }}"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingHistory{{ $item->transaction_id }}"
                         data-bs-parent="#historyAccordion">
                        <div class="accordion-body">
                            <p><strong>Date Delivered:</strong> {{ $item->updated_at ? $item->updated_at->format('Y-m-d H:i') : 'N/A' }}</p>
                            <p><strong>Customer:</strong> {{ $item->customer_name }}</p>
                            <p><strong>Contact:</strong> {{ $item->customer_contact }}</p>
                            <p><strong>Address:</strong> {{ $item->customer_address }}</p>

                            <p><strong>Proof of Delivery:</strong></p>
                            @if($item->proof_of_delivery)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $item->proof_of_delivery) }}"
                                     class="img-thumbnail"
                                     style="width: 120px; cursor:pointer;"
                                     data-bs-toggle="modal"
                                     data-bs-target="#proofModal{{ $item->transaction_id }}">
                                <!-- Modal -->
                                <div class="modal fade" id="proofModal{{ $item->transaction_id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content bg-dark">
                                            <div class="modal-header border-0">
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="{{ asset('storage/' . $item->proof_of_delivery) }}" class="img-fluid rounded">
                                            </div>
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



      <div class="tab-pane fade" id="transaction">
        <div class="content-card">
          <h3>💳 Transactions</h3>
          <div class="accordion" id="transactionsAccordion">
  @foreach ($transactions as $transaction)
  <div class="accordion-item">
    <h2 class="accordion-header" id="heading{{ $transaction->transaction_id }}">
      <button class="accordion-button collapsed d-flex justify-content-between align-items-center"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapse{{ $transaction->transaction_id }}"
              aria-expanded="false"
              aria-controls="collapse{{ $transaction->transaction_id }}">
        <span class="d-flex flex-nowrap align-items-center flex-grow-1 text-truncate">
          <span class="text-truncate me-2">{{ $transaction->customer_name }}</span>
          <span class="badge bg-info flex-shrink-0">{{ $transaction->delivery_status }}</span>
        </span>
      </button>
    </h2>

    <div id="collapse{{ $transaction->transaction_id }}" class="accordion-collapse collapse"
         aria-labelledby="heading{{ $transaction->transaction_id }}" data-bs-parent="#transactionsAccordion">
      <div class="accordion-body">
        <p class="address-text"><strong>Address:</strong> {{ $transaction->customer_address }}</p>
        <p>
          <strong>Contact:</strong> 
          <span class="customer-phone" 
                style="color:#0d6efd;cursor:pointer;" 
                data-phone="{{ $transaction->customer_contact }}">
            {{ $transaction->customer_contact }}
          </span>
        </p>

        <!-- Set as Destination Button -->
        <button class="btn btn-primary set-route-btn"
                data-transaction-id="{{ $transaction->transaction_id }}"
                data-name="{{ $transaction->customer_name }}"
                data-address="{{ $transaction->customer_address }}"
                data-contact="{{ $transaction->customer_contact }}">
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

  <!-- === MOBILE BOTTOM NAVBAR === -->
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


  <!-- Customer Details Modal -->
  <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="customerModalLabel">Customer Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <p><strong>Name:</strong> <span id="modalCustomerName"></span></p>
          <p><strong>Contact:</strong> <span id="modalCustomerContact"></span></p>
          <p><strong>Address:</strong> <span id="modalCustomerAddress"></span></p>
          <input type="hidden" id="modalCustomerLat">
          <input type="hidden" id="modalCustomerLng">

          <!-- Hidden Transaction ID -->
          <input type="hidden" id="modalTransactionId">

          <!-- Proof of Delivery (photo) -->
          <div class="mb-2">
            <label for="proofInput" class="form-label">Proof of Delivery (photo)</label>
            <input type="file" id="proofInput" accept="image/*" class="form-control">
            <small class="form-text text-muted">Required when marking as delivered.</small>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="confirmSetInTransitBtn">Start Delivery</button>
          <button type="button" class="btn btn-primary" id="uploadDeliverBtn">Mark Delivered (upload proof)</button>
        </div>
      </div>
    </div>
  </div>


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

  <!-- === SCRIPTS === -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
  <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
  <script src="https://unpkg.com/lrm-openrouteservice/dist/lrm-openrouteservice.js"></script>


</body>

</html>