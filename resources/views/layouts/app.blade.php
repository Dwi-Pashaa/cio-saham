<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<meta http-equiv="X-UA-Compatible" content="ie=edge" />
	<title>@yield('title') &mdash; {{ config('app.name') }}</title>

	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

	<!-- CSS files -->
	<link href="{{asset('css/tabler.min.css?1738096685')}}" rel="stylesheet" />
	<link href="{{asset('css/tabler-vendors.min.css?1738096685')}}" rel="stylesheet" />
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
	<link href="{{asset('css/custom-theme.css')}}?v={{ time() }}" rel="stylesheet" />
	<link href="{{asset('css/cio-saham.css')}}?v={{ time() }}" rel="stylesheet" />
	@stack('css')
</head>

<body>
	<div class="page">
		<!-- Header & Navigation Bar -->
		<div class="sticky-top">
			@include('components.header')
			@include('components.navbar')
		</div>

		<div class="page-wrapper">
			<!-- Page header -->
			<div class="page-header d-print-none">
				<div class="container-xl">
					<div class="row g-2 align-items-center justify-content-between">
						<div class="col">
							@hasSection('pretitle')
								<div class="page-pretitle">
									@yield('pretitle')
								</div>
							@endif
							<h2 class="page-title">
								@yield('title')
							</h2>
							@hasSection('subtitle')
								<div class="text-muted small mt-1">
									@yield('subtitle')
								</div>
							@endif
						</div>
						@hasSection('actions')
							<div class="col-auto ms-auto d-print-none">
								<div class="btn-list">
									@yield('actions')
								</div>
							</div>
						@endif
					</div>
				</div>
			</div>

			<!-- Page body -->
			<div class="page-body">
				<div class="container-xl">
					@yield('content')
				</div>
			</div>

			<!-- Footer (Hidden on Android/Mobile: d-none d-md-block) -->
			<footer class="footer footer-transparent d-print-none d-none d-md-block">
				<div class="container-xl">
					<div class="row text-center align-items-center flex-row-reverse">
						<div class="col-12 col-lg-auto mt-3 mt-lg-0">
							<ul class="list-inline list-inline-dots mb-0">
								<li class="list-inline-item">
									&copy; {{ date('Y') }} <strong class="text-dark">{{ config('app.name') }}</strong>. All rights reserved.
								</li>
							</ul>
						</div>
					</div>
				</div>
			</footer>
		</div>
	</div>

	<!-- Native Android Bottom Navigation Dock (Mobile Only) -->
	@include('components.android-bottom-nav')

	@stack('modal')

	<!-- Libs JS -->
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
	<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
	<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
	<script src="{{asset('libs/apexcharts/dist/apexcharts.min.js?1738096685')}}" defer></script>
	<!-- Tabler Core -->
	<script src="{{asset('js/tabler.min.js?1738096685')}}" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		// Global DataTables Default Settings
		if ($.fn.dataTable) {
			$.extend(true, $.fn.dataTable.defaults, {
				language: {
					processing: '<div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Memuat data...',
					search: "_INPUT_",
					searchPlaceholder: "Pencarian cepat...",
					lengthMenu: "Tampilkan _MENU_ data",
					info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
					infoEmpty: "Menampilkan 0 s/d 0 dari 0 data",
					infoFiltered: "(disaring dari _MAX_ total data)",
					zeroRecords: "Tidak ada data yang cocok ditemukan",
					emptyTable: "Belum ada data yang tersedia di tabel ini",
					paginate: {
						first: "Awal",
						previous: "Sebelumnya",
						next: "Berikutnya",
						last: "Akhir"
					}
				}
			});
		}
	</script>
	@stack('js')
	@stack('scripts')
</body>

</html>