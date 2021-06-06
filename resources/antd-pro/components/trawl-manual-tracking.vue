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
import { CarIcon } from "./icons";
import trawlModalSplit from "./trawl-modal-split.vue";
export default {
  components: { trawlModalSplit, CarIcon }
};
</script>
