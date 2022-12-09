<template>
  <div>
    <content-layout>
      <template slot="content">
        <div>
          <h2 class="text-danger">
            <a
              :href="
                routeUri('admin.master.trawltruck.dashboard.driver.account')
              "
            >
              <a-icon class="mr-1 gray-color" type="arrow-left" />
            </a>
            Detail Akun Driver
          </h2>
        </div>
        <a-space align="start" :size="50">
          <a-avatar class="avatar" :size="150" icon="user" />
          <div>
            <a-row type="flex" :gutter="[24, 24]">
              <a-col :span="12">
                <a-space direction="vertical" size="large">
                  <div>
                    <h2>Nama Lengkap</h2>
                    <div class="form-driver">Hendra Kurniawan Sucipto</div>
                  </div>
                  <div>
                    <h2>Email</h2>
                    <div class="form-driver">Hendrakurniawan12@gmail.com</div>
                  </div>
                  <div>
                    <h2>Merek Kendaraan</h2>
                    <div class="form-driver">Suzuki</div>
                  </div>
                  <div>
                    <h2>Rekening Bank</h2>
                    <div class="form-driver">7563 6468 6472 644</div>
                  </div>
                </a-space>
              </a-col>
              <a-col :span="12">
                <a-space direction="vertical" size="large">
                  <div>
                    <h2>Nomor Telepone</h2>
                    <div class="form-driver">0897 6678 0989</div>
                  </div>
                  <div>
                    <h2>Jenis Mobil</h2>
                    <div class="form-driver">Mobil Box</div>
                  </div>
                  <div>
                    <h2>Model Kendaraan</h2>
                    <div class="form-driver">Odong-Odong</div>
                  </div>
                </a-space>
              </a-col>
            </a-row>

            <div style="margin-top: 33px">
              <h2>Alamat Mendaftar</h2>
              <div
                class="form-driver"
                style="width: 667px; height: 153px; display: block"
              >
                Jln.Rasamala No.35, Menteng Dalam, Tebet, Jakarta Selatan
              </div>
            </div>
            <div class="form-doc">
              <div>
                <h3>Foto KTP</h3>
                <img src="https://i.postimg.cc/W30P2QDw/Rectangle-8531.png" />
              </div>
              <div>
                <h3>Foto SIM</h3>
                <img src="https://i.postimg.cc/MHD2BVmc/Rectangle-8532.png" />
              </div>
              <div>
                <h3>Foto STNK</h3>
                <img src="https://i.postimg.cc/0jx1rcDR/Rectangle-8533.png" />
              </div>
            </div>
          </div>
        </a-space>
      </template>
    </content-layout>
  </div>
</template>

<style scoped>
.avatar {
  margin-left: 25px;
  margin-top: 20px;
}
.form-driver {
  border: 1px solid #dbdbdb;
  border-radius: 5px;
  padding: 10px;
  width: 322px;
  height: 50px;
  display: flex;
  align-items: center;
}
.form-doc {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
}
</style>
