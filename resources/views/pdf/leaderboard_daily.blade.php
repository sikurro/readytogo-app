<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan K3 Petugas - Resume Kuis Harian</title>
    <style>
        body {
            font-family: 'Pragmatica', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            color: #334155;
            font-size: 9pt;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        @page {
            size: A4 landscape;
            margin: 0.8cm 1cm;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header-title-container {
            padding-left: 12px;
            vertical-align: middle;
        }
        .company-name {
            font-size: 9pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        .report-title {
            font-size: 14pt;
            font-weight: 800;
            color: #0f172a;
            margin: 2px 0 0 0;
            line-height: 1.2;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 8pt;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
        }
        .meta-label {
            color: #64748b;
            font-weight: bold;
            width: 100px;
        }
        .meta-val {
            color: #0f172a;
        }
        
        .layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .layout-td-left {
            vertical-align: top;
            width: 62%;
            padding-right: 15px;
        }
        .layout-td-right {
            vertical-align: top;
            width: 38%;
            padding-left: 15px;
        }

        .summary-section {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 8px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .summary-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #115e59;
            margin-top: 0;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-text {
            margin: 0;
            font-size: 8.5pt;
            color: #1e3f20;
            text-align: justify;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 3px;
            margin-top: 10px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin: 0 -6px 10px -6px;
        }
        .stat-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
            width: 25%;
        }
        .stat-card.primary { border-top: 3px solid #0f172a; }
        .stat-card.success { border-top: 3px solid #10b981; }
        .stat-card.warning { border-top: 3px solid #f59e0b; }
        .stat-card.danger { border-top: 3px solid #ef4444; }
        .stat-num {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }
        .stat-label {
            font-size: 7pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        /* Chart container */
        .chart-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #ffffff;
            padding: 10px;
            margin-bottom: 10px;
        }
        .chart-box-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #475569;
            margin-top: 0;
            margin-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 4px;
        }
        
        /* CSS Column Chart for Tren Perkembangan Pengetahuan */
        .trend-chart-table {
            width: 100%;
            height: 110px;
            border-collapse: collapse;
            margin-top: 3px;
        }
        .trend-chart-bar-td {
            vertical-align: bottom;
            text-align: center;
            padding: 0 1px;
        }
        .trend-bar-label {
            font-size: 6pt;
            color: #64748b;
            margin-top: 3px;
            display: block;
        }
        
        /* CSS Stacked Bar Chart for Persentase Tingkat Pemahaman */
        .chart-legend {
            margin-top: 10px;
            font-size: 8pt;
            text-align: center;
        }
        .legend-item {
            display: inline-block;
            margin-right: 12px;
        }
        .legend-color {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 2px;
            margin-right: 4px;
            vertical-align: middle;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8.5pt;
        }
        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
            padding: 6px 8px;
            border: 1px solid #0f172a;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
        }
        .data-table tr.even {
            background-color: #f8fafc;
        }
        .rank-badge {
            display: inline-block;
            width: 16px;
            height: 16px;
            line-height: 16px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 7.5pt;
        }
        .rank-1 { background-color: #f59e0b; color: #0f172a; }
        .rank-2 { background-color: #cbd5e1; color: #0f172a; }
        .rank-3 { background-color: #b45309; color: #ffffff; }
        .rank-other { color: #64748b; }
        
        .location-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 5px;
        }
        .location-table td {
            padding: 4px 0;
            vertical-align: middle;
        }
        .location-bar-bg {
            background-color: #e2e8f0;
            border-radius: 4px;
            height: 8px;
            width: 100%;
            display: block;
        }
        .location-bar-fill {
            background-color: #3b82f6;
            height: 100%;
            border-radius: 4px;
            display: block;
        }
        .location-bar-fill.success { background-color: #10b981; }
        .location-bar-fill.warning { background-color: #f59e0b; }
        .location-bar-fill.danger { background-color: #ef4444; }

        .footer-table {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .signature-box {
            text-align: right;
            padding-right: 10px;
            font-size: 8.5pt;
        }
        .signature-line {
            margin-top: 40px;
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }
        .signature-title {
            color: #64748b;
            font-size: 8pt;
            margin-top: 1px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 160px; vertical-align: middle;">
                <img src="{{ public_path('images/logo-pelindo.png') }}" style="width: 150px; height: auto; display: block;" alt="Logo Pelindo">
            </td>
            <td class="header-title-container">
                <div class="company-name">DIVISI HSE & KESELAMATAN KERJA PELABUHAN</div>
                <h1 class="report-title">LAPORAN EVALUASI PENGETAHUAN K3 PETUGAS (KUIS HARIAN)</h1>
            </td>
        </tr>
    </table>

    <!-- Metadata Info -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Bulan Evaluasi</td>
            <td style="width: 10px;">:</td>
            <td class="meta-val">{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</td>
            <td class="meta-label" style="padding-left: 40px;">Tanggal Cetak</td>
            <td style="width: 10px;">:</td>
            <td class="meta-val">{{ now()->translatedFormat('d F Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="meta-label">Jenis Evaluasi</td>
            <td>:</td>
            <td class="meta-val">Kuis Pengetahuan K3 Harian (Daily Test)</td>
            <td class="meta-label" style="padding-left: 40px;">Status Laporan</td>
            <td>:</td>
            <td class="meta-val" style="color: #10b981; font-weight: bold;">Selesai Terverifikasi</td>
        </tr>
    </table>

    <table class="layout-table">
        <tr>
            <!-- Left Side: Executive Summary, KPIs & Location Accuracy -->
            <td class="layout-td-left">
                <div class="summary-section">
                    <h3 class="summary-title">Ringkasan Eksekutif</h3>
                    <p class="summary-text">
                        Berdasarkan data pengerjaan Kuis K3 Harian pada periode <strong>{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</strong>, tercatat sebanyak <strong>{{ number_format($totalPetugas, 0, ',', '.') }} petugas</strong> pelabuhan telah berpartisipasi aktif dalam evaluasi mandiri ini. Rata-rata akurasi jawaban dari seluruh petugas mencapai <strong>{{ str_replace('.', ',', $avgAccuracy) }}%</strong> dengan perolehan skor rata-rata akumulatif sebesar <strong>{{ number_format($avgScore, strpos((string)$avgScore, '.') !== false ? 2 : 0, ',', '.') }} poin</strong>. Hasil ini mencerminkan komitmen tinggi petugas pelabuhan dalam memahami regulasi keselamatan, protokol tanggap darurat pelabuhan, serta penggunaan alat pelindung diri (APD) demi mewujudkan lingkungan kerja <i>Zero Accident</i> di seluruh area operasional pelabuhan.
                    </p>
                </div>

                <h3 class="section-title" style="margin-top: 0;">Indikator Kinerja Utama (KPI)</h3>
                <table class="stats-table">
                    <tr>
                        <td class="stat-card primary">
                            <div class="stat-label">Petugas Aktif</div>
                            <div class="stat-num">{{ number_format($totalPetugas, 0, ',', '.') }}</div>
                        </td>
                        <td class="stat-card success">
                            <div class="stat-label">Rata-rata Akurasi</div>
                            <div class="stat-num">{{ str_replace('.', ',', $avgAccuracy) }}%</div>
                        </td>
                        <td class="stat-card warning">
                            <div class="stat-label">Jawaban Benar</div>
                            <div class="stat-num">{{ number_format($totalCorrect, 0, ',', '.') }}</div>
                        </td>
                        <td class="stat-card danger">
                            <div class="stat-label">Jawaban Salah</div>
                            <div class="stat-num">{{ number_format($totalWrong, 0, ',', '.') }}</div>
                        </td>
                    </tr>
                </table>

                <h3 class="section-title" style="margin-top: 5px;">Akurasi per Lokasi Kerja</h3>
                <table class="location-table">
                    @foreach($accuracyPerLocation as $locationName => $accuracy)
                    <tr>
                        <td style="font-weight: bold; color: #334155; width: 140px;">{{ $locationName }}</td>
                        <td style="text-align: right; padding-right: 10px; font-weight: bold; width: 45px;">{{ str_replace('.', ',', $accuracy) }}%</td>
                        <td>
                            <div class="location-bar-bg">
                                <div class="location-bar-fill {{ $accuracy >= 75 ? 'success' : ($accuracy >= 50 ? 'warning' : 'danger') }}" style="width: {{ $accuracy }}%;"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @if(count($accuracyPerLocation) == 0)
                    <tr>
                        <td colspan="3" style="text-align: center; color: #94a3b8; padding: 10px 0;">Tidak ada data per lokasi.</td>
                    </tr>
                    @endif
                </table>
            </td>

            <!-- Right Side: Visualization Charts -->
            <td class="layout-td-right">
                <!-- Chart 1: Tren Perkembangan K3 Harian -->
                <div class="chart-box">
                    <h4 class="chart-box-title">Tren Perkembangan Pengetahuan K3 Harian (%)</h4>
                    <table class="trend-chart-table">
                        <tr>
                            @foreach($dailyProgressData as $data)
                            <td class="trend-chart-bar-td">
                                <div style="width: 100%; height: 85px; background-color: #f1f5f9; border-radius: 2px; overflow: hidden;">
                                    @if($data['avg_accuracy'] !== null)
                                        @php
                                            $barHeight = round(($data['avg_accuracy'] / 100) * 85);
                                            $emptyHeight = 85 - $barHeight;
                                        @endphp
                                        @if($emptyHeight > 0)
                                            <div style="height: {{ $emptyHeight }}px; background-color: #f1f5f9; width: 100%;"></div>
                                        @endif
                                        @if($barHeight > 0)
                                            <div style="background-color: {{ $data['avg_accuracy'] >= 75 ? '#10b981' : ($data['avg_accuracy'] >= 50 ? '#f59e0b' : '#ef4444') }}; height: {{ $barHeight }}px; width: 100%; border-radius: 2px; text-align: center; color: #ffffff; font-size: 5pt; font-weight: bold; line-height: {{ $barHeight }}px; overflow: hidden;">
                                                @if($barHeight > 10)
                                                    {{ round($data['avg_accuracy']) }}
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                <span class="trend-bar-label">{{ $data['day'] }}</span>
                            </td>
                            @endforeach
                        </tr>
                    </table>
                </div>

                <!-- Chart 2: Persentase Pemahaman -->
                <div class="chart-box">
                    <h4 class="chart-box-title">Persentase Tingkat Pemahaman</h4>
                    @php
                        $totalAll = $totalCorrect + $totalWrong;
                        $percentCorrect = $totalAll > 0 ? round(($totalCorrect / $totalAll) * 100, 1) : 0;
                        $percentWrong = $totalAll > 0 ? round(($totalWrong / $totalAll) * 100, 1) : 0;
                    @endphp
                    <table style="width: 100%; border-collapse: collapse; height: 22px; border-radius: 4px; overflow: hidden; background-color: #e2e8f0;">
                        <tr>
                            @if($percentCorrect > 0)
                            <td style="width: {{ $percentCorrect }}%; background-color: #10b981; color: #ffffff; text-align: center; font-weight: bold; font-size: 8pt; padding: 0;">
                                {{ str_replace('.', ',', $percentCorrect) }}%
                            </td>
                            @endif
                            @if($percentWrong > 0)
                            <td style="width: {{ $percentWrong }}%; background-color: #ef4444; color: #ffffff; text-align: center; font-weight: bold; font-size: 8pt; padding: 0;">
                                {{ str_replace('.', ',', $percentWrong) }}%
                            </td>
                            @endif
                        </tr>
                    </table>
                    <div class="chart-legend">
                        <span class="legend-item">
                            <span class="legend-color" style="background-color: #10b981;"></span>
                            Jawaban Benar ({{ number_format($totalCorrect, 0, ',', '.') }})
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background-color: #ef4444;"></span>
                            Jawaban Salah ({{ number_format($totalWrong, 0, ',', '.') }})
                        </span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- Second Page: Leaderboard Table (Fit to page) -->
    <h3 class="section-title" style="margin-top: 0;">Peringkat Kinerja Pengetahuan K3 Petugas</h3>
    <table class="data-table" style="width: 100%;">
        <thead>
            <tr>
                <th style="text-align: center; width: 35px;">Pos</th>
                <th style="text-align: left;">Nama Petugas</th>
                <th style="text-align: center; width: 90px;">NIP</th>
                <th style="text-align: left; width: 150px;">Jabatan</th>
                <th style="text-align: left; width: 150px;">Lokasi Kerja</th>
                <th style="text-align: center; width: 60px;">Akurasi</th>
                <th style="text-align: center; width: 50px;">Skor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($filteredLeaderboard as $index => $row)
            <tr class="{{ $index % 2 == 1 ? 'even' : '' }}">
                <td style="text-align: center; font-weight: bold;">
                    @if($row['rank'] == 1)
                        <span class="rank-badge rank-1">1</span>
                    @elseif($row['rank'] == 2)
                        <span class="rank-badge rank-2">2</span>
                    @elseif($row['rank'] == 3)
                        <span class="rank-badge rank-3">3</span>
                    @else
                        <span class="rank-badge rank-other">{{ $row['rank'] }}</span>
                    @endif
                </td>
                <td style="font-weight: bold; color: #0f172a;">{{ $row['name'] }}</td>
                <td style="text-align: center; font-family: monospace; font-size: 8pt;">{{ $row['nip'] ?: '-' }}</td>
                <td style="font-size: 8pt;">{{ $row['position'] ?: '-' }}</td>
                <td style="color: #475569; font-weight: bold; font-size: 8pt;">{{ $row['location'] }}</td>
                <td style="text-align: center; font-weight: bold; color: {{ $row['percentage_correct'] >= 75 ? '#10b981' : ($row['percentage_correct'] >= 50 ? '#f59e0b' : '#ef4444') }};">
                    {{ str_replace('.', ',', $row['percentage_correct']) }}%
                </td>
                <td style="text-align: center; font-weight: bold; color: #b45309;">{{ number_format($row['total_score'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @if(count($filteredLeaderboard) == 0)
            <tr>
                <td colspan="7" style="text-align: center; color: #94a3b8; padding: 20px;">
                    Tidak ada data pengerjaan kuis untuk periode/filter ini.
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Signature Section under the table, aligned right -->
    <table class="footer-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%;">
                <div class="signature-box">
                    <div>Makassar, {{ now()->translatedFormat('d F Y') }}</div>
                    <div class="signature-title">Mengetahui/Menyetujui,</div>
                    <div class="signature-line" style="margin-top: 35px;">(Nama Penanggung Jawab)</div>
                    <div class="signature-title">Divisi HSSE PT Pelindo Jasa Maritim</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
