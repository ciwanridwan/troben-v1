<?php
if ( ! function_exists('to_rp_rvs')) {
	function to_rp_rvs($value) {
		return number_format( $value, 0 , ',' , '.' );
	}
}
if ( ! function_exists('date_parse_rvs')) {
	function date_parse_rvs($value) {
        try {
            $p = \Carbon\Carbon::parse($value);
            return $p->format('Y/m/d H:i:s');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return $value;
        }
	}
}
?>

<table border="1">
    <thead>
        <tr>
            <th>No.</th>
            <th>Kode resi</th>
            <th>Kota asal</th>
            <th>Provinsi tujuan</th>
            <th>Kota/Kab tujuan</th>
            <th>Kecamatan tujuan</th>
            <th>Kelurahan tujuan</th>
            <th>Kodepos tujuan</th>
            <th>Tipe order</th>
            <th>Tipe kendaraan pickup</th>
            <th>Tanggal unload</th>
            <th>Partner asal</th>
            <th>No. transaksi nicepay</th>
            <th>Status transaksi nicepay</th>
            <th>Tanggal verifikasi pembayaran</th>
            <th>Tanggal permohonan pembayaran</th>
            <th>Berat total</th>
            <th>Harga Barang</th>
            <th>Total Biaya Kirim</th>
            <th>Diskon</th>
            {{-- <th>Total Komisi</th> --}}
            <th>Biaya packing</th>
            <th>Biaya asuransi</th>
            <th>Biaya pickup</th>
            <th>Biaya total</th>
            <th>Komisi Mitra Asal (Service)</th>
            <th>Komisi Tambahan Mitra Asal</th>
        </tr>
    </thead>
    <tbody>
    @foreach($result as $i => $d)
    <tr>
        <td>{{$i+1}}</td>
        <td>{{$d->receipt_code}}</td>
        <td>{{$d->origin_city}}</td>
        <td>{{$d->destination_province}}</td>
        <td>{{$d->destination_city}}</td>
        <td>{{$d->destination_district}}</td>
        <td>{{$d->destination_sub_district}}</td>
        <td>{{$d->zip_code}}</td>
        <td>{{$d->type_order}}</td>
        <td>{{$d->transporter_pickup_type}}</td>
        <td>{{date_parse_rvs($d->unloaded_at)}}</td>
        <td>{{$d->origin_partner}}</td>
        <td>{{$d->nicepay_trx_id}}</td>
        <td>{{$d->nicepay_status}}</td>
        <td>{{date_parse_rvs($d->payment_verified_at)}}</td>
        <td>{{date_parse_rvs($d->payment_request_at)}}</td>
        <td>{{$d->total_weight}}</td>
        <td>{{to_rp_rvs($d->item_price)}}</td>
        <td>{{to_rp_rvs($d->total_delivery_price)}}</td>
        <td>{{to_rp_rvs($d->discount_delivery)}}</td>
        {{-- <td>{{to_rp_rvs($d->total_commission)}}</td> --}}
        <td>{{to_rp_rvs($d->receipt_total_packing_price)}}</td>
        <td>{{to_rp_rvs($d->receipt_insurance_price)}}</td>
        <td>{{to_rp_rvs($d->receipt_pickup_price)}}</td>
        <td>{{to_rp_rvs($d->receipt_total_amount)}}</td>
        <td>{{to_rp_rvs($d->commission_manual)}}</td>
        <td>{{to_rp_rvs($d->extra_commission)}}</td>
    </tr>
    @endforeach
    </tbody>
</table>
