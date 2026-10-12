<form method="post" enctype="multipart/form-data" class="compact-form" id="payment-form">
    <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
    <input type="hidden" name="booking_id" value="<?=$id?>">

    <label>Metode Pembayaran
        <select name="method" id="payment-method" required>
            <option value="transfer">Transfer Bank</option>
            <option value="qris">QRIS</option>
            <option value="cash">Cash</option>
        </select>
    </label>

    <div id="bank-field" class="payment-method-box">
        <div class="payment-box-title">Pilih Bank Tujuan</div>
        <div class="bank-grid">
            <?php foreach($bankAccounts as $bank): ?>
                <label class="bank-card">
                    <input type="radio" name="bank" value="<?=e($bank['code'])?>">
                    <span>
                        <strong><?=e($bank['name'])?></strong>
                        <span class="bank-number"><?=e($bank['number'])?></span>
                        <small>a.n. <?=e($bank['holder'])?></small>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <small class="demo-note">Nomor rekening di atas adalah data DEMO untuk pengujian dan harus diganti sebelum digunakan sungguhan.</small>
    </div>

    <div id="qris-box" class="payment-method-box qris-box">
        <div class="payment-box-title">Bayar dengan QRIS</div>
        <div class="qris-demo-layout">
            <img src="../assets/img/qris-demo.png" alt="QRIS demo ServisKu">
            <div>
                <strong>Scan QRIS dengan aplikasi pembayaran</strong>
                <p>Gunakan gambar ini hanya untuk simulasi. QRIS ini <strong>tidak dapat digunakan untuk transaksi nyata</strong>.</p>
            </div>
        </div>
    </div>

    <div class="payment-instructions"><strong>Instruksi pembayaran</strong><p>Pilih metode di atas, lakukan pembayaran sesuai total invoice, lalu upload bukti pembayaran. Admin akan memeriksa bukti sebelum status menjadi Lunas.</p></div>

    <label id="proof-field">Bukti Pembayaran
        <input type="file" name="proof" accept="image/jpeg,image/png,image/webp">
        <small>Format JPG, PNG, WEBP · maksimal 3 MB.</small>
    </label>
    <button type="submit"><?=isset($payment) && $payment && $payment['status']==='failed'?'Kirim Ulang untuk Verifikasi':'Kirim Pembayaran untuk Verifikasi'?></button>
</form>
