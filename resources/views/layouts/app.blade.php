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
	<link href="{{asset('')}}css/tabler.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-flags.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-socials.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-payments.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-vendors.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-marketing.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/demo.min.css?1738096685" rel="stylesheet" />
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
	<script src="{{asset('')}}libs/apexcharts/dist/apexcharts.min.js?1738096685" defer></script>
	<script src="{{asset('')}}libs/jsvectormap/dist/jsvectormap.min.js?1738096685" defer></script>
	<script src="{{asset('')}}libs/jsvectormap/dist/maps/world.js?1738096685" defer></script>
	<script src="{{asset('')}}libs/jsvectormap/dist/maps/world-merc.js?1738096685" defer></script>
	<!-- Tabler Core -->
	<script src="{{asset('')}}js/tabler.min.js?1738096685" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		}); 
	</script>
	@stack('js')
</body>

</html>