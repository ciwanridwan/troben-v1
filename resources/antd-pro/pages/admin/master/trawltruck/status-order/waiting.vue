<template>
  <a-row type="flex" :gutter="[24, 24]">
    <a-col :span="12" style="margin-bottom: 5em">
      <a-layout-content :style="{ margin: '24px 16px 0' }">
        <div
          :style="{ padding: '24px', background: '#fff', minHeight: '360px' }"
        >
          <div
            :style="{
              display: 'flex',
              justifyContent: 'space-between',
              marginBottom: '10px',
            }"
          >
            <div class="title">Detail Order</div>
            <div class="timer">00:09:45</div>
          </div>
          <a-row type="flex" :gutter="[24, 24]">
            <a-col :span="12">
              <a-space direction="vertical" size="large">
                <div>
                  <h3>Id Order</h3>
                  <div class="form-order">TRB45658799045</div>
                </div>
                <div>
                  <h3>Jenis Orderan</h3>
                  <div class="form-order">Terjadwal</div>
                </div>
                <div>
                  <h3>Nomor Telepon</h3>
                  <div class="form-order">0897 6564 5567</div>
                </div>
              </a-space>
            </a-col>
            <a-col :span="12">
              <a-space direction="vertical" size="large">
                <div>
                  <h3>Nama Customer</h3>
                  <div class="form-order">Andi Setiawan</div>
                </div>
                <div>
                  <h3>Jenis Armada</h3>
                  <div class="form-order">Pick Up Box</div>
                </div>
                <div>
                  <h3>Pembayaran</h3>
                  <div class="form-order">Cash</div>
                </div>
              </a-space>
            </a-col>
          </a-row>
          <div style="margin-top: 33px">
            <h3>Tanggal Order</h3>
            <div class="form-order" style="width: 699px; display: block">
              12 Januari 2022 - 15 Januari 2022
            </div>
          </div>
          <div style="margin-top: 33px">
            <h3>Alamat Penjemputan</h3>
            <div
              class="form-order"
              style="width: 699px; height: 153px; display: block"
            >
              Jln.Rasamala no.35, Menteng Dalam, Tebet, Jakarta Selatan
            </div>
          </div>
          <div>
            <h3 style="margin-top: 33px">Attachment Barang</h3>
            <div class="attach">
              <div>
                <img src="https://i.postimg.cc/mgKCWHBj/Rectangle-8540.png" />
              </div>
              <div>
                <img src="https://i.postimg.cc/9fTTmQfh/Rectangle-8541.png" />
              </div>
              <div>
                <img src="https://i.postimg.cc/Tw6bpvyW/Rectangle-8542.png" />
              </div>
              <div>
                <img src="https://i.postimg.cc/Tw6bpvyW/Rectangle-8542.png" />
              </div>
              <div>
                <img src="https://i.postimg.cc/Tw6bpvyW/Rectangle-8542.png" />
              </div>
              <div>
                <img src="https://i.postimg.cc/Tw6bpvyW/Rectangle-8542.png" />
              </div>
            </div>
          </div>
        </div>
      </a-layout-content>
    </a-col>
    <a-col :span="12">
      <div
        style="display: flex; justify-content: flex-end; margin: 24px 16px 0"
      >
        <div class="status">
          <span class="dot" style="margin-right: 10px"></span> Waiting
        </div>
      </div>
      <a-layout-content :style="{ margin: '11px 16px 0' }">
        <div
          :style="{ padding: '24px', background: '#fff', minHeight: '360px' }"
        >
          <div
            :style="{
              display: 'flex',
              justifyContent: 'space-between',
              marginBottom: '10px',
            }"
          >
            <div class="title">Detail Penerima</div>
          </div>
          <a-row type="flex" :gutter="[24, 24]">
            <a-col :span="12">
              <a-space direction="vertical" size="large">
                <div>
                  <h3>Nama Lengkap</h3>
                  <div class="form-order">Hendra Kurniawan</div>
                </div>
              </a-space>
            </a-col>
            <a-col :span="12">
              <a-space direction="vertical" size="large">
                <div>
                  <h3>Nomor Telepone</h3>
                  <div class="form-order">0897 6657 4569</div>
                </div>
              </a-space>
            </a-col>
          </a-row>
          <div style="margin-top: 33px">
            <h3>Alamat Penjemputan</h3>
            <div class="form-order" style="width: 699px; display: block">
              Jln.Rasamala no.35, Menteng Dalam, Tebet, Jakarta Selatan
            </div>
          </div>
        </div>
      </a-layout-content>
      <a-layout-content :style="{ margin: '24px 16px 0' }">
        <div
          :style="{ padding: '24px', background: '#fff', minHeight: '360px' }"
        >
          <div
            :style="{
              display: 'flex',
              justifyContent: 'space-between',
              marginBottom: '10px',
            }"
          >
            <div class="title">Detail Driver</div>
          </div>
          <a-row type="flex" :gutter="[24, 24]">
            <a-col :span="12">
              <a-space direction="vertical" size="large">
                <div>
                  <h3>Id Driver</h3>
                  <div class="form-order">DRV5667856456</div>
                </div>
              </a-space>
            </a-col>
            <a-col :span="12">
              <a-space direction="vertical" size="large">
                <div>
                  <h3>Nama Driver</h3>
                  <div class="form-order">Samuel Suhendro</div>
                </div>
              </a-space>
            </a-col>
          </a-row>
        </div>
      </a-layout-content>
    </a-col>
  </a-row>
</template>

<script>
export default {};
</script>

<style scoped>
.status {
  font-family: "Poppins";
  font-style: normal;
  font-weight: 400;
  font-size: 15px;
  line-height: 22px;
  color: #8e16ec;
  background: rgba(142, 22, 236, 0.15);
  border: 1px solid #8e16ec;
  border-radius: 10px;
  width: 194px;
  height: 51px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.dot {
  height: 15px;
  width: 15px;
  background-color: #8e16ec;
  border-radius: 50%;
  display: inline-block;
}
.form-order {
  border: 1px solid #dbdbdb;
  border-radius: 5px;
  padding: 10px;
  width: 322px;
  height: 50px;
  display: flex;
  align-items: center;
}
.attach {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  justify-content: flex-start;
}
.title {
  font-family: "Poppins";
  font-style: normal;
  font-weight: 700;
  font-size: 18px;
  line-height: 27px;
  color: #000000;
}
.timer {
  font-family: "Poppins";
  font-style: normal;
  font-weight: 400;
  font-size: 15px;
  line-height: 22px;
  color: #e60013;
  background: rgba(230, 0, 19, 0.15);
  border: 1px solid #e60013;
  border-radius: 5px;
  box-sizing: border-box;
  padding: 3px 20px;
}
</style>
