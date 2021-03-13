<template>
  <div>
    <a-button :class="['trawl-button-success']" @click="visible = true">
      Print
    </a-button>
    <a-modal v-model="visible" :width="640" centered>
      <template slot="title">
        <h3>
          <a-space align="center">
            <b>ID Resi</b>
            <print-icon></print-icon>
          </a-space>
        </h3>
      </template>
      <a-card>
        <!-- Nama Mitra -->
        <a-row type="flex">
          <a-col :span="12">
            <trawl-pack-with-text></trawl-pack-with-text>
            <h2 :style="{ margin: 0 }"><b>Mitra Bisnis</b></h2>
            <h4 style="font-weight:normal">Lubuk Kilangan, Padang</h4>
            <h4 style="font-weight:normal" :class="['trawl-text-mute']">
              {{ currentDate }}
            </h4>
          </a-col>
          <a-col :span="12" class="trawl-card-white-label">
            <trawl-red-icon></trawl-red-icon>
          </a-col>
        </a-row>

        <!-- From To Region -->
        <a-row type="flex" align="middle">
          <a-col :span="12">
            <a-space direction="vertical">
              <div>
                <h4 class="trawl-text-danger">Dari</h4>
                <h2 :style="{ margin: 0 }"><b>Bandung</b></h2>
                <h3 class="trawl-text-normal">Jawa Barat</h3>
              </div>

              <div>
                <h4 class="trawl-text-danger">Dari</h4>
                <h2 :style="{ margin: 0 }"><b>Padang</b></h2>
                <h3 class="trawl-text-normal">Sumatera Barat</h3>
              </div>
            </a-space>
          </a-col>
          <a-col :span="12" class="trawl-text-center">
            <qr-code-icon></qr-code-icon>
            <h3 class="trawl-text-normal">No Barang: 1-2-1234-0</h3>
          </a-col>
        </a-row>

        <a-divider></a-divider>

        <a-row type="flex" :gutter="[24, 24]">
          <!-- sender -->
          <a-col :span="12">
            <a-space direction="vertical" size="middle">
              <div>
                <h3 class="trawl-text-normal">
                  <a-space align="center">
                    <send-icon class="trawl-icon-scoped"></send-icon>
                    Pengirim
                  </a-space>
                </h3>
                <h3><b>Faris Ferdian Akbar</b></h3>
                <h3 class="trawl-text-normal">085694455935</h3>
                <h3 class="trawl-text-normal">
                  Jl. Soekarno No.1, Kel. Indarung, Kec. Lubuk Kilangan, Padang,
                  Sumatera Barat.
                </h3>
              </div>

              <h3 class="trawl-text-normal">Kode pos : 40256</h3>

              <a-space direction="vertical" class="trawl-text-center">
                <qr-code-icon
                  :style="{ width: '91px', height: '91px' }"
                ></qr-code-icon>
                <h3 class="trawl-text-normal">No Resi: 1-2-1234-0</h3>
              </a-space>
            </a-space>
          </a-col>

          <!-- receiver -->
          <a-col :span="12">
            <a-space direction="vertical" size="middle">
              <div>
                <h3 class="trawl-text-normal">
                  <a-space align="center">
                    <receive-icon class="trawl-icon-scoped"></receive-icon>
                    Penerima
                  </a-space>
                </h3>
                <h3><b>Agus Andriatmo</b></h3>
                <h3 class="trawl-text-normal">085694455935</h3>
                <h3 class="trawl-text-normal">
                  Jl. Soekarno No.1, Kel. Indarung, Kec. Lubuk Kilangan, Padang,
                  Sumatera Barat.
                </h3>
              </div>

              <h3 class="trawl-text-normal">Kode pos : 52312</h3>

              <a-space direction="vertical">
                <h3 class="trawl-text-normal">
                  Ket Barang :
                  <b>Kulkas 2 Pintu</b>
                </h3>
                <h3 class="trawl-text-normal">
                  <a-space>
                    <package-icon></package-icon>
                    <span>
                      Berat :
                      <b>20 Kg</b>
                    </span>
                  </a-space>
                </h3>
                <h3 class="trawl-text-normal">
                  <a-space>
                    <weight-machine-icon></weight-machine-icon>
                    <span>
                      Jml. Koli :
                      <b>20 Kg</b>
                    </span>
                  </a-space>
                </h3>
                <h3 :class="['trawl-text-normal', 'trawl-text-mute']">
                  www.trawlbens.id
                </h3>
              </a-space>
            </a-space>
          </a-col>
        </a-row>
      </a-card>
    </a-modal>
  </div>
</template>
<script>
import {
  SendIcon,
  GpsIcon,
  TrawlPackIcon,
  TrawlPackWithText,
  TrawlRedIcon,
  QrCodeIcon,
  PackageIcon,
  WeightMachineIcon,
  PrintIcon
} from "../../../../components/icons";
import ReceiveIcon from "../../../../components/icons/receiveIcon.vue";

export default {
  data() {
    return {
      visible: false
    };
  }
};
</script>
<style lang="less" scoped>
svg.trawl-icon-scoped {
  height: 27px !important;
  width: 27px !important;
}
</style>
