<?php

namespace App\Http\Controllers\Pages;

use App\Exports\AssetExport;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetImage;
use App\Models\Shareholder;
use App\Services\CashNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class AssetController extends Controller
{
    /**
     * Unduh daftar aset inventaris ke format file Excel (.xlsx).
     */
    public function export(Request $request)
    {
        $fileName = 'inventaris_aset_pt_cio_' . date('Ymd_His') . '.xlsx';
        return Excel::download(
            new AssetExport(
                $request->type,
                $request->owner_type,
                $request->start_date,
                $request->end_date
            ),
            $fileName
        );
    }
    /**
     * Tampilkan daftar dan ringkasan metrik inventaris aset.
     */
    public function index(Request $request)
    {
        $query = Asset::with(['shareholder', 'creator', 'images'])->orderBy('id', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('owner_type')) {
            $query->where('owner_type', $request->owner_type);
        }

        if ($request->filled('shareholder_id')) {
            $query->where('shareholder_id', $request->shareholder_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
        }

        // Summary KPI Metrics
        $totalCount        = (clone $query)->count();
        $totalPriceSum     = (clone $query)->sum('price');
        $ptPriceSum        = (clone $query)->where('owner_type', 'pt')->sum('price');
        $investorPriceSum  = (clone $query)->where('owner_type', 'shareholder')->sum('price');

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filter(function ($q) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = trim($request->input('search.value'));
                        $q->where(function ($sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%")
                                ->orWhere('type', 'like', "%{$search}%")
                                ->orWhere('serial_number', 'like', "%{$search}%")
                                ->orWhere('mac_address', 'like', "%{$search}%")
                                ->orWhere('owner_name', 'like', "%{$search}%")
                                ->orWhere('notes', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('name_and_type', function ($item) {
                    $name = e($item->name);
                    $type = e($item->type);
                    $notes = $item->notes ? e($item->notes) : '';
                    $notesHtml = $notes ? '<div class="text-muted small text-truncate mt-0.5" style="max-width: 260px; font-size: 0.72rem;" title="' . $notes . '">' . $notes . '</div>' : '';

                    return '<div class="py-0.5">
                                <strong class="text-dark d-block fs-4 text-truncate">' . $name . '</strong>
                                <div class="mt-1">
                                    <span class="badge bg-secondary-lt fw-semibold" style="font-size: 0.7rem;">' . $type . '</span>
                                </div>
                                ' . $notesHtml . '
                            </div>';
                })
                ->addColumn('price_formatted', function ($item) {
                    return '<span class="font-monospace fw-bold text-dark fs-4" style="white-space: nowrap;">Rp ' . number_format((float) $item->price, 0, ',', '.') . '</span>';
                })
                ->addColumn('serial_and_mac', function ($item) {
                    $sn = $item->serial_number ? e($item->serial_number) : null;
                    $mac = $item->mac_address ? e($item->mac_address) : null;

                    if (!$sn && !$mac) {
                        return '<span class="text-muted small">-</span>';
                    }

                    $html = '<div class="d-flex flex-column gap-1">';
                    if ($sn) {
                        $html .= '<div class="d-flex align-items-center gap-1 font-monospace" style="font-size: 0.75rem;">
                                    <span class="badge bg-azure-lt px-1.5 py-0.5">SN</span>
                                    <span>' . $sn . '</span>
                                    <button type="button" class="btn btn-ghost-secondary p-0 btn-copy-sn" data-clipboard-text="' . $sn . '" title="Salin Serial Number" style="width: 16px; height: 16px; line-height: 1;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /><path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" /></svg>
                                    </button>
                                  </div>';
                    }
                    if ($mac) {
                        $html .= '<div class="d-flex align-items-center gap-1 font-monospace" style="font-size: 0.75rem;">
                                    <span class="badge bg-teal-lt px-1.5 py-0.5">MAC</span>
                                    <span>' . $mac . '</span>
                                    <button type="button" class="btn btn-ghost-secondary p-0 btn-copy-mac" data-clipboard-text="' . $mac . '" title="Salin MAC Address" style="width: 16px; height: 16px; line-height: 1;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /><path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" /></svg>
                                    </button>
                                  </div>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('owner_info', function ($item) {
                    if ($item->owner_type === 'shareholder') {
                        $ownerName = e($item->shareholder?->name ?? $item->owner_name ?? 'Investor');
                        return '<span class="badge bg-purple-lt fw-bold font-monospace d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                    ' . $ownerName . '
                                </span>';
                    }

                    return '<span class="badge bg-blue-lt fw-bold font-monospace d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M5 21v-14l8 -4v18" /><path d="M19 21v-10l-6 -4" /></svg>
                                PT CIO NETWORK SOLUTION
                            </span>';
                })
                ->addColumn('purchase_date_creator', function ($item) {
                    $dateStr = $item->purchase_date ? $item->purchase_date->format('d M Y') : '-';
                    $creator = e($item->creator?->name ?? 'Sistem');
                    return '<div class="font-monospace text-dark" style="font-size: 0.76rem;">' . $dateStr . '</div>
                            <div class="text-muted small" style="font-size: 0.72rem;">Oleh: ' . $creator . '</div>';
                })
                ->addColumn('action', function ($item) {
                    $user = auth()->user();
                    $btnShow = '<button type="button" class="btn-action btn-action-info btn-show-asset" data-id="' . $item->id . '" title="Rincian Spesifikasi Aset">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                </button>';

                    $btnGallery = '';
                    if ($item->images->count() > 0) {
                        $btnGallery = '<button type="button" class="btn-action btn-action-purple btn-view-gallery" data-id="' . $item->id . '" title="Lihat Galeri Foto (' . $item->images->count() . ' Foto)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                                    </button>';
                    }

                    $btnEdit = '';
                    if ($user && $user->can('ubah aset')) {
                        $btnEdit = '<button type="button" class="btn-action btn-action-warning btn-edit-asset" data-id="' . $item->id . '" title="Ubah Data Aset">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                    </button>';
                    }

                    $btnDelete = '';
                    if ($user && $user->can('hapus aset')) {
                        $btnDelete = '<button type="button" class="btn-action btn-action-danger btn-delete-asset" data-id="' . $item->id . '" data-name="' . e($item->name) . '" title="Hapus Aset">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    </button>';
                    }

                    return '<div class="action-btn-group d-flex justify-content-center align-items-center gap-1">' . $btnShow . $btnGallery . $btnEdit . $btnDelete . '</div>';
                })
                ->rawColumns(['name_and_type', 'price_formatted', 'serial_and_mac', 'owner_info', 'purchase_date_creator', 'action'])
                ->with([
                    'totalCount'        => $totalCount,
                    'totalPriceSum'     => number_format($totalPriceSum, 0, ',', '.'),
                    'ptPriceSum'        => number_format($ptPriceSum, 0, ',', '.'),
                    'investorPriceSum'  => number_format($investorPriceSum, 0, ',', '.'),
                ])
                ->make(true);
        }

        $assetTypes = [
            'Perangkat Jaringan',
            'Server & Komputer',
            'Elektronik & Gadget',
            'Kendaraan Operasional',
            'Perlengkapan Kantor',
            'Lainnya'
        ];

        $shareholders = Shareholder::where('status', 'active')->orderBy('name')->get();

        return view('pages.asset.index', compact(
            'totalCount',
            'totalPriceSum',
            'ptPriceSum',
            'investorPriceSum',
            'assetTypes',
            'shareholders'
        ));
    }

    /**
     * Form tambah aset.
     */
    public function create()
    {
        $assetTypes = [
            'Perangkat Jaringan',
            'Server & Komputer',
            'Elektronik & Gadget',
            'Kendaraan Operasional',
            'Perlengkapan Kantor',
            'Lainnya'
        ];

        $shareholders = Shareholder::where('status', 'active')->orderBy('name')->get();

        return view('pages.asset.create', compact('assetTypes', 'shareholders'));
    }

    /**
     * Simpan data aset baru beserta upload multiple image.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|string|max:100',
            'price'          => 'required',
            'serial_number'  => 'nullable|string|max:100',
            'mac_address'    => 'nullable|string|max:50',
            'owner_type'     => 'nullable|in:pt,shareholder',
            'shareholder_id' => 'nullable|exists:shareholders,id',
            'purchase_date'  => 'nullable|date',
            'notes'          => 'nullable|string',
            'images'         => 'nullable|array',
            'images.*'       => 'file|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'images.*.uploaded' => 'Salah satu berkas foto aset gagal diunggah karena melebihi batas upload server. Silakan kompres foto atau gunakan file berukuran maksimal 5MB.',
            'images.*.mimes'    => 'Format foto aset harus berupa JPG, JPEG, PNG, atau WEBP.',
            'images.*.max'      => 'Ukuran masing-masing foto aset tidak boleh melebihi 5MB.',
        ]);

        $rawPrice = (float) str_replace(['.', ','], ['', '.'], str_replace(['Rp', ' ', '.'], '', (string) $request->price));

        $canManageOwner = $request->user()->can('atur pemilik aset');

        if ($canManageOwner && $request->owner_type === 'shareholder' && $request->filled('shareholder_id')) {
            $shareholder = Shareholder::find($request->shareholder_id);
            $ownerType = 'shareholder';
            $shareholderId = $shareholder?->id;
            $ownerName = $shareholder ? $shareholder->name : 'Investor';
        } else {
            // Default mutlak jika tidak memiliki hak akses atau memilih PT
            $ownerType = 'pt';
            $shareholderId = null;
            $ownerName = 'PT CIO NETWORK SOLUTION';
        }

        $asset = Asset::create([
            'name'           => $request->name,
            'type'           => $request->type,
            'price'          => $rawPrice,
            'serial_number'  => $request->serial_number ?: null,
            'mac_address'    => $request->mac_address ?: null,
            'owner_type'     => $ownerType,
            'shareholder_id' => $shareholderId,
            'owner_name'     => $ownerName,
            'purchase_date'  => $request->purchase_date ?: null,
            'notes'          => $request->notes ?: null,
            'created_by'     => auth()->id(),
        ]);

        // Simpan multiple images jika ada diupload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('assets/images', 'public');
                $asset->images()->create([
                    'image_path' => $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        // Kirim Notifikasi WhatsApp Otomatis ke Manajemen & Investor
        try {
            app(CashNotificationService::class)->notifyAssetCreated($asset);
        } catch (\Throwable $e) {
            Log::error('[AssetController] Gagal mengirim notifikasi WA penambahan aset: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data aset berhasil ditambahkan ke inventaris.',
                'data'    => $asset->load('images'),
            ]);
        }

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil ditambahkan ke inventaris.');
    }

    /**
     * Tampilkan rincian data aset (JSON untuk modal atau Blade view).
     */
    public function show(Request $request, $id)
    {
        $asset = Asset::with(['shareholder', 'creator', 'images'])->findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'id'                        => $asset->id,
                    'name'                      => $asset->name,
                    'type'                      => $asset->type,
                    'price'                     => (float) $asset->price,
                    'price_formatted'           => number_format((float) $asset->price, 0, ',', '.'),
                    'serial_number'             => $asset->serial_number ?: '-',
                    'mac_address'               => $asset->mac_address ?: '-',
                    'owner_type'                => $asset->owner_type,
                    'owner_label'               => $asset->owner_label,
                    'shareholder_id'            => $asset->shareholder_id,
                    'shareholder_name'          => $asset->shareholder?->name,
                    'purchase_date'             => $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : null,
                    'purchase_date_formatted'   => $asset->purchase_date ? $asset->purchase_date->format('d F Y') : '-',
                    'notes'                     => $asset->notes ?: '-',
                    'creator_name'              => $asset->creator?->name ?? 'Sistem',
                    'created_at_formatted'      => $asset->created_at ? $asset->created_at->format('d M Y, H:i') . ' WIB' : '-',
                    'images'                    => $asset->images->map(fn($img) => [
                        'id'         => $img->id,
                        'url'        => $img->url,
                        'is_primary' => $img->is_primary,
                    ]),
                    'thumbnail_url'             => $asset->thumbnail_url,
                ]
            ]);
        }

        return view('pages.asset.show', compact('asset'));
    }

    /**
     * Form edit aset (JSON untuk AJAX modal atau Blade view).
     */
    public function edit(Request $request, $id)
    {
        $asset = Asset::with(['shareholder', 'images'])->findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'id'             => $asset->id,
                    'name'           => $asset->name,
                    'type'           => $asset->type,
                    'price'          => number_format((float) $asset->price, 0, ',', '.'),
                    'serial_number'  => $asset->serial_number,
                    'mac_address'    => $asset->mac_address,
                    'owner_type'     => $asset->owner_type,
                    'shareholder_id' => $asset->shareholder_id,
                    'purchase_date'  => $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : null,
                    'notes'          => $asset->notes,
                    'update_url'     => route('assets.update', $asset->id),
                    'images'         => $asset->images->map(fn($img) => [
                        'id'         => $img->id,
                        'url'        => $img->url,
                        'is_primary' => $img->is_primary,
                    ]),
                ]
            ]);
        }

        $assetTypes = [
            'Perangkat Jaringan',
            'Server & Komputer',
            'Elektronik & Gadget',
            'Kendaraan Operasional',
            'Perlengkapan Kantor',
            'Lainnya'
        ];

        $shareholders = Shareholder::where('status', 'active')->orderBy('name')->get();

        return view('pages.asset.edit', compact('asset', 'assetTypes', 'shareholders'));
    }

    /**
     * Perbarui data aset beserta penambahan/penghapusan multiple images.
     */
    public function update(Request $request, $id)
    {
        $asset = Asset::with('images')->findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|string|max:100',
            'price'          => 'required',
            'serial_number'  => 'nullable|string|max:100',
            'mac_address'    => 'nullable|string|max:50',
            'owner_type'     => 'nullable|in:pt,shareholder',
            'shareholder_id' => 'nullable|exists:shareholders,id',
            'purchase_date'  => 'nullable|date',
            'notes'          => 'nullable|string',
            'images'         => 'nullable|array',
            'images.*'       => 'file|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'images.*.uploaded' => 'Salah satu berkas foto aset gagal diunggah karena melebihi batas upload server. Silakan kompres foto atau gunakan file berukuran maksimal 5MB.',
            'images.*.mimes'    => 'Format foto aset harus berupa JPG, JPEG, PNG, atau WEBP.',
            'images.*.max'      => 'Ukuran masing-masing foto aset tidak boleh melebihi 5MB.',
        ]);

        $rawPrice = (float) str_replace(['.', ','], ['', '.'], str_replace(['Rp', ' ', '.'], '', (string) $request->price));

        $canManageOwner = $request->user()->can('atur pemilik aset');

        if ($canManageOwner && $request->owner_type === 'shareholder' && $request->filled('shareholder_id')) {
            $shareholder = Shareholder::find($request->shareholder_id);
            $ownerType = 'shareholder';
            $shareholderId = $shareholder?->id;
            $ownerName = $shareholder ? $shareholder->name : 'Investor';
        } else {
            // Default mutlak jika tidak memiliki izin atur pemilik aset atau memilih PT
            $ownerType = 'pt';
            $shareholderId = null;
            $ownerName = 'PT CIO NETWORK SOLUTION';
        }

        $asset->update([
            'name'           => $request->name,
            'type'           => $request->type,
            'price'          => $rawPrice,
            'serial_number'  => $request->serial_number ?: null,
            'mac_address'    => $request->mac_address ?: null,
            'owner_type'     => $ownerType,
            'shareholder_id' => $shareholderId,
            'owner_name'     => $ownerName,
            'purchase_date'  => $request->purchase_date ?: null,
            'notes'          => $request->notes ?: null,
        ]);

        // Hapus foto yang ditandai untuk dihapus
        if ($request->filled('deleted_image_ids')) {
            $deletedIds = is_array($request->deleted_image_ids) 
                ? $request->deleted_image_ids 
                : explode(',', (string) $request->deleted_image_ids);

            foreach ($asset->images()->whereIn('id', $deletedIds)->get() as $img) {
                $img->delete();
            }
        }

        // Upload foto baru jika ada
        if ($request->hasFile('images')) {
            $hasPrimary = $asset->images()->where('is_primary', true)->exists();
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('assets/images', 'public');
                $asset->images()->create([
                    'image_path' => $path,
                    'is_primary' => !$hasPrimary && $index === 0,
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data aset berhasil diperbarui.',
                'data'    => $asset->load('images'),
            ]);
        }

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diperbarui.');
    }

    /**
     * Hapus sebuah foto aset secara spesifik.
     */
    public function destroyImage($imageId)
    {
        $image = AssetImage::findOrFail($imageId);
        $image->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Foto aset berhasil dihapus.'
        ]);
    }

    /**
     * Hapus data aset beserta seluruh fotonya.
     */
    public function destroy(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);
        $asset->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Data aset berhasil dihapus dari inventaris.'
            ]);
        }

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil dihapus dari inventaris.');
    }
}
