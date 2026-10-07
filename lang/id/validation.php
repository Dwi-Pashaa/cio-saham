<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi
    |--------------------------------------------------------------------------
    */

    'accepted'             => ':attribute harus diterima.',
    'accepted_if'          => ':attribute harus diterima ketika :other bernilai :value.',
    'active_url'           => ':attribute bukan URL yang valid.',
    'after'                => ':attribute harus berupa tanggal setelah :date.',
    'after_or_equal'       => ':attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha'                => ':attribute hanya boleh berisi huruf.',
    'alpha_dash'           => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num'            => ':attribute hanya boleh berisi huruf dan angka.',
    'array'                => ':attribute harus berupa array.',
    'before'               => ':attribute harus berupa tanggal sebelum :date.',
    'before_or_equal'      => ':attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between'              => [
        'numeric' => ':attribute harus antara :min dan :max.',
        'file'    => ':attribute harus antara :min dan :max kilobita.',
        'string'  => ':attribute harus antara :min dan :max karakter.',
        'array'   => ':attribute harus memiliki antara :min dan :max item.',
    ],
    'boolean'              => ':attribute harus berupa true atau false.',
    'confirmed'            => 'Konfirmasi :attribute tidak cocok.',
    'current_password'     => 'Kata sandi saat ini salah.',
    'date'                 => ':attribute bukan tanggal yang valid.',
    'date_equals'          => ':attribute harus berupa tanggal yang sama dengan :date.',
    'date_format'          => ':attribute tidak cocok dengan format :format.',
    'declined'             => ':attribute harus ditolak.',
    'declined_if'          => ':attribute harus ditolak ketika :other bernilai :value.',
    'different'            => ':attribute dan :other harus berbeda.',
    'digits'               => ':attribute harus terdiri dari :digits digit.',
    'digits_between'       => ':attribute harus antara :min dan :max digit.',
    'dimensions'           => ':attribute memiliki dimensi gambar yang tidak valid.',
    'distinct'             => ':attribute memiliki nilai duplikat.',
    'email'                => ':attribute harus berupa alamat email yang valid.',
    'ends_with'            => ':attribute harus diakhiri dengan salah satu dari: :values.',
    'enum'                 => ':attribute yang dipilih tidak valid.',
    'exists'               => ':attribute yang dipilih tidak valid.',
    'file'                 => ':attribute harus berupa berkas / file.',
    'filled'               => ':attribute wajib diisi.',
    'gt'                   => [
        'numeric' => ':attribute harus lebih besar dari :value.',
        'file'    => ':attribute harus lebih besar dari :value kilobita.',
        'string'  => ':attribute harus lebih besar dari :value karakter.',
        'array'   => ':attribute harus memiliki lebih dari :value item.',
    ],
    'gte'                  => [
        'numeric' => ':attribute harus lebih besar dari atau sama dengan :value.',
        'file'    => ':attribute harus lebih besar dari atau sama dengan :value kilobita.',
        'string'  => ':attribute harus lebih besar dari atau sama dengan :value karakter.',
        'array'   => ':attribute harus memiliki :value item atau lebih.',
    ],
    'image'                => ':attribute harus berupa gambar.',
    'in'                   => ':attribute yang dipilih tidak valid.',
    'in_array'             => ':attribute tidak ada dalam :other.',
    'integer'              => ':attribute harus berupa angka bulat.',
    'ip'                   => ':attribute harus berupa alamat IP yang valid.',
    'ipv4'                 => ':attribute harus berupa alamat IPv4 yang valid.',
    'ipv6'                 => ':attribute harus berupa alamat IPv6 yang valid.',
    'json'                 => ':attribute harus berupa JSON string yang valid.',
    'lt'                   => [
        'numeric' => ':attribute harus kurang dari :value.',
        'file'    => ':attribute harus kurang dari :value kilobita.',
        'string'  => ':attribute harus kurang dari :value karakter.',
        'array'   => ':attribute harus memiliki kurang dari :value item.',
    ],
    'lte'                  => [
        'numeric' => ':attribute harus kurang dari atau sama dengan :value.',
        'file'    => ':attribute harus kurang dari atau sama dengan :value kilobita.',
        'string'  => ':attribute harus kurang dari atau sama dengan :value karakter.',
        'array'   => ':attribute tidak boleh memiliki lebih dari :value item.',
    ],
    'mac_address'          => ':attribute harus berupa alamat MAC yang valid.',
    'max'                  => [
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'file'    => ':attribute tidak boleh lebih dari :max kilobita (maksimal :max KB).',
        'string'  => ':attribute tidak boleh lebih dari :max karakter.',
        'array'   => ':attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'mimes'                => ':attribute harus berupa berkas bertipe: :values.',
    'mimetypes'            => ':attribute harus berupa berkas bertipe: :values.',
    'min'                  => [
        'numeric' => ':attribute minimal bernilai :min.',
        'file'    => ':attribute minimal berukuran :min kilobita.',
        'string'  => ':attribute minimal terdiri dari :min karakter.',
        'array'   => ':attribute minimal harus memiliki :min item.',
    ],
    'multiple_of'          => ':attribute harus merupakan kelipatan dari :value.',
    'not_in'               => ':attribute yang dipilih tidak valid.',
    'not_regex'            => 'Format :attribute tidak valid.',
    'numeric'              => ':attribute harus berupa angka.',
    'password'             => [
        'letters'          => ':attribute harus mengandung setidaknya satu huruf.',
        'mixed'            => ':attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers'          => ':attribute harus mengandung setidaknya satu angka.',
        'symbols'          => ':attribute harus mengandung setidaknya satu simbol.',
        'uncompromised'    => ':attribute yang dimasukkan telah bocor dalam kebocoran data. Silakan pilih kata sandi lain.',
    ],
    'present'              => ':attribute harus ada.',
    'prohibited'           => ':attribute tidak diizinkan.',
    'prohibited_if'        => ':attribute tidak diizinkan ketika :other bernilai :value.',
    'prohibited_unless'    => ':attribute tidak diizinkan kecuali :other bernilai salah satu dari :values.',
    'prohibits'            => ':attribute melarang :other untuk disertakan.',
    'regex'                => 'Format :attribute tidak valid.',
    'required'             => ':attribute wajib diisi.',
    'required_array_keys'  => ':attribute harus berisi entri untuk: :values.',
    'required_if'          => ':attribute wajib diisi ketika :other bernilai :value.',
    'required_unless'      => ':attribute wajib diisi kecuali :other bernilai salah satu dari :values.',
    'required_with'        => ':attribute wajib diisi ketika terdapat :values.',
    'required_with_all'    => ':attribute wajib diisi ketika terdapat :values.',
    'required_without'     => ':attribute wajib diisi ketika :values tidak ada.',
    'required_without_all' => ':attribute wajib diisi ketika tidak ada satu pun dari :values yang terisi.',
    'same'                 => ':attribute dan :other harus cocok.',
    'size'                 => [
        'numeric' => ':attribute harus berukuran :size.',
        'file'    => ':attribute harus berukuran :size kilobita.',
        'string'  => ':attribute harus berukuran :size karakter.',
        'array'   => ':attribute harus berisi :size item.',
    ],
    'starts_with'          => ':attribute harus diawali dengan salah satu dari: :values.',
    'string'               => ':attribute harus berupa teks.',
    'timezone'             => ':attribute harus berupa zona waktu yang valid.',
    'unique'               => ':attribute sudah digunakan / terdaftar.',
    'uploaded'             => ':attribute gagal diunggah. Ukuran berkas mungkin melebihi batas upload maksimal server (25 MB) atau koneksi terputus.',
    'url'                  => ':attribute harus berupa URL yang valid.',
    'uuid'                 => ':attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Pesan Validasi Kustom
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'proof_file' => [
            'uploaded' => 'File bukti transaksi gagal diunggah karena melebihi batas maksimal server. Silakan gunakan file di bawah 5MB.',
        ],
        'receipt_file' => [
            'uploaded' => 'File nota/kuitansi gagal diunggah karena melebihi batas maksimal server. Silakan gunakan file di bawah 5MB.',
        ],
        'images.*' => [
            'uploaded' => 'Salah satu foto aset gagal diunggah karena melebihi batas maksimal server. Silakan kompres foto sebelum diunggah.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama Atribut Kustom
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'transaction_date'       => 'tanggal transaksi',
        'sender_name'            => 'nama pengirim',
        'recipient_name'         => 'nama penerima',
        'bank_name'              => 'nama bank',
        'account_number'         => 'nomor rekening',
        'amount'                 => 'nominal transaksi',
        'has_admin_fee'          => 'biaya admin',
        'admin_fee'              => 'biaya admin',
        'proof_file'             => 'bukti transaksi / pembayaran',
        'receipt_file'           => 'nota / kuitansi',
        'notes'                  => 'keterangan / catatan',
        'name'                   => 'nama barang / aset',
        'type'                   => 'tipe aset',
        'price'                  => 'harga barang',
        'serial_number'          => 'serial number',
        'mac_address'            => 'MAC address',
        'owner_type'             => 'kepemilikan aset',
        'shareholder_id'         => 'pemilik investor',
        'purchase_date'          => 'tanggal perolehan',
        'images'                 => 'foto aset',
        'images.*'               => 'berkas foto aset',
    ],

];
