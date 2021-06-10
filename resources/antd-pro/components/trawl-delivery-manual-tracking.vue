<template>
  <trawl-modal-split>
    <template slot="trigger">
      <a-button class="trawl-button-success">Ubah posisi</a-button>
    </template>
    <template slot="title">
      Ubah detail posisi
    </template>
    <template slot="left">
      <a-space direction="vertical">
        <span class="trawl-text-bold">No Manifest : MNF000202</span>
        <a-space>
          <car-icon />
          <span>Budi Pratiwi - Xenia</span>
        </a-space>

        <order-modal-row-layout :afterLine="false" :iconPadding="false">
          <template slot="icon">
            <send-icon class="trawl-icon-full-width" />
          </template>
          <template slot="content">
            <a-space direction="vertical" :size="1">
              <span>Pengirim</span>
              <span class="trawl-text-bold">MB-001-11</span>
              <p class="trawl-text-normal">
                Lorem ipsum, dolor sit amet consectetur adipisicing elit. In
                omnis esse delectus modi, necessitatibus laborum alias ipsum,
                totam optio deleniti officiis perferendis! Atque et dolorem
                laborum animi eaque aspernatur a.
              </p>
              <span>
                Kode Pos : 12210
              </span>
            </a-space>
          </template>
        </order-modal-row-layout>
        <order-modal-row-layout :afterLine="false" :iconPadding="false">
          <template slot="icon">
            <receive-icon class="trawl-icon-full-width" />
          </template>
          <template slot="content">
            <a-space direction="vertical" :size="1">
              <span>Penerima</span>
              <span class="trawl-text-bold">MB-001-11</span>
              <p class="trawl-text-normal">
                Lorem ipsum, dolor sit amet consectetur adipisicing elit. In
                omnis esse delectus modi, necessitatibus laborum alias ipsum,
                totam optio deleniti officiis perferendis! Atque et dolorem
                laborum animi eaque aspernatur a.
              </p>
              <span>
                Kode Pos : 12210
              </span>
            </a-space>
          </template>
        </order-modal-row-layout>

        <a-card>
          <a-row type="flex" :gutter="[12, 12]">
            <a-col :span="8">
              <a-row type="flex">
                <a-col :span="24">
                  <a-space>
                    <span>
                      <package-icon />
                    </span>
                    <span>
                      Berat :
                    </span>
                  </a-space>
                </a-col>
                <a-col :span="24" class="trawl-text-center">
                  <span class="trawl-text-bold">
                    20 Kg
                  </span>
                </a-col>
              </a-row>
            </a-col>
            <a-col :span="8">
              <a-row type="flex">
                <a-col :span="24">
                  <a-space>
                    <span>
                      <weight-machine-icon />
                    </span>
                    <span>
                      Jml. koli :
                    </span>
                  </a-space>
                </a-col>
                <a-col :span="24" class="trawl-text-center">
                  <span class="trawl-text-bold">
                    1 Koli
                  </span>
                </a-col>
              </a-row>
            </a-col>
            <a-col :span="8">
              <a-row type="flex">
                <a-col :span="24">
                  <a-space>
                    <span>
                      <term-condition-icon />
                    </span>
                    <span>
                      Jml. Resi :
                    </span>
                  </a-space>
                </a-col>
                <a-col :span="24" class="trawl-text-center">
                  <span class="trawl-text-bold">
                    1 Resi
                  </span>
                </a-col>
              </a-row>
            </a-col>
          </a-row>
        </a-card>
      </a-space>
    </template>
    <template slot="rightHeader">
      <a-space direction="vertical" :style="{ width: '100%' }">
        <span class="trawl-text-bold">Posisi Terakhir</span>
        <a-input-search placeholder="Posisi Terakhir" />
      </a-space>
    </template>
    <template slot="rightContent">
      <a-timeline>
        <a-timeline-item>Create a services site 2015-09-01</a-timeline-item>
        <a-timeline-item
          >Solve initial network problems 2015-09-01</a-timeline-item
        >
        <a-timeline-item>Technical testing 2015-09-01</a-timeline-item>
        <a-timeline-item
          >Network problems being solved 2015-09-01</a-timeline-item
        >
      </a-timeline>
    </template>
    <template slot="rightFooter">
      <a-button block class="trawl-button-success">Simpan</a-button>
    </template>
  </trawl-modal-split>
</template>
<script>
import {
  CarIcon,
  SendIcon,
  ReceiveIcon,
  WeightMachineIcon,
  PackageIcon,
  TermConditionIcon
} from "./icons";

import TrawlModalSplit from "./trawl-modal-split.vue";

export default {
  components: {
    TrawlModalSplit,
    CarIcon,
    SendIcon,
    ReceiveIcon,
    WeightMachineIcon,
    PackageIcon,
    TermConditionIcon
  }
};
</script>
, S, PackageIconendIcon
