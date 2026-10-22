@extends('layouts.guest')
@section('title', 'Terma Mata Ganjaran')
@section('meta_description', 'Terma penggunaan mata ganjaran BonusHub: cara mata diperoleh, ditebus dan tamat tempoh.')
@section('content')
@php
    $months = (int) config('points.default_months', 12);
    $warn = (int) config('points.warn_days', 30);
    $min = (int) config('points.min_months', 6);
    $max = (int) config('points.max_months', 36);
    $on = (bool) config('points.expiry_enabled');
@endphp
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10" style="max-width:820px">
    <h1 class="text-2xl sm:text-3xl font-bold text-surface-900 mb-2">Terma Mata Ganjaran</h1>
    <p class="text-sm text-surface-500 mb-8">Dikemas kini: 21 Oktober 2026</p>

    <div class="space-y-6 text-sm sm:text-base text-surface-700 leading-relaxed">
        <section>
            <h2 class="font-semibold text-surface-900 mb-1">1. Mata ganjaran</h2>
            <p>Mata diberikan oleh merchant (kedai) yang anda sertai melalui BonusHub apabila anda membeli, menyiapkan tugasan atau merujuk rakan mengikut peraturan merchant itu. Mata disimpan <strong>berasingan bagi setiap merchant</strong>. Mata ialah ganjaran kesetiaan dan <strong>tidak mempunyai nilai tunai</strong> melainkan merchant menyatakan sebaliknya dengan jelas pada ganjaran yang ditawarkan.</p>
        </section>

        <section>
            <h2 class="font-semibold text-surface-900 mb-1">2. Menebus mata</h2>
            <p>Anda boleh menebus mata dengan ganjaran yang disenaraikan oleh merchant, tertakluk kepada baki mata, stok dan syarat ganjaran itu. Setiap tebusan mendapat kod tuntutan yang diserahkan kepada merchant untuk menerima ganjaran. Merchant boleh membatalkan tebusan yang belum diserahkan; mata anda akan dipulangkan.</p>
        </section>

        <section>
            <h2 class="font-semibold text-surface-900 mb-1">3. Tamat tempoh mata</h2>
            @if($on)
                <p>Mata anda dengan sesuatu merchant akan <strong>tamat tempoh jika tiada aktiviti selama {{ $months }} bulan</strong>. Aktiviti bermaksud transaksi kumpul atau tebus mata yang telah diluluskan. Merchant boleh menetapkan tempoh sendiri antara {{ $min }} hingga {{ $max }} bulan, atau memilih supaya mata tidak tamat; tempoh yang terpakai kepada anda dipaparkan melalui tarikh tamat pada halaman <em>My Points</em>.</p>
                <p class="mt-2">Kami akan memberi <strong>amaran {{ $warn }} hari sebelum</strong> mata tamat melalui notifikasi dalam aplikasi. Sebarang aktiviti baharu yang diluluskan akan mengira semula tempoh tersebut. Mata yang telah tamat dikeluarkan daripada baki anda dan direkodkan dalam sejarah mata; ia tidak dipulihkan melainkan merchant memutuskan sebaliknya.</p>
            @else
                <p><strong>Tamat tempoh mata belum berkuat kuasa.</strong> Apabila ia dimulakan, mata akan tamat jika tiada aktiviti selama tempoh yang ditetapkan (lalai {{ $months }} bulan; merchant boleh memilih {{ $min }}–{{ $max }} bulan atau tiada tamat), dengan amaran {{ $warn }} hari sebelum. Kami akan memaklumkan pengguna dan mengemas kini terma ini <strong>sebelum</strong> ia dimulakan.</p>
            @endif
        </section>

        <section>
            <h2 class="font-semibold text-surface-900 mb-1">4. Pembetulan dan pembatalan transaksi</h2>
            <p>Merchant boleh membatalkan (void) transaksi yang tersilap. Pembatalan direkodkan dalam sejarah mata anda dan baki dilaraskan sewajarnya.</p>
        </section>

        <section>
            <h2 class="font-semibold text-surface-900 mb-1">5. Tier keahlian</h2>
            <p>Tier anda dengan sesuatu merchant ditentukan oleh jumlah mata yang anda peroleh dalam 12 bulan terakhir mengikut ambang yang ditetapkan merchant itu. Menebus mata tidak menurunkan tier anda.</p>
        </section>

        <section>
            <h2 class="font-semibold text-surface-900 mb-1">6. Penyalahgunaan</h2>
            <p>Mata yang diperoleh melalui penipuan, akaun palsu atau penyalahgunaan sistem rujukan boleh dibatalkan oleh merchant atau BonusHub.</p>
        </section>

        <section>
            <h2 class="font-semibold text-surface-900 mb-1">7. Pertanyaan</h2>
            <p>Untuk soalan tentang baki atau ganjaran, hubungi merchant berkenaan. Untuk soalan tentang platform, hubungi Marz Technology melalui <a class="text-bonus-600 underline" href="https://marztechnology.com.my/" target="_blank" rel="noopener">marztechnology.com.my</a>.</p>
        </section>
    </div>
</div>
@endsection
