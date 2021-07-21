<template>
  <div>
    <a-space direction="vertical" size="small">
      <a-space direction="vertical" :size="1">
        <span
          class="trawl-text-bolder trawl-text-danger"
          :style="textStyle"
        >
          Dari
        </span>
        <h2
          class="trawl-text-bolder trawl-text-normal"
          :style="textStyle"
        >
          {{ origin_province_name }}
        </h2>
        <span
          class="trawl-text-bolder"
          :style="textStyle"
        >
          {{ origin_regency_name }}
        </span>
      </a-space>

      <a-space direction="vertical" :size="1">
        <span
          class="trawl-text-bolder trawl-text-danger"
          :style="textStyle"
        >
          Ke
        </span>
        <h2
          class="trawl-text-bolder trawl-text-normal"
          :style="textStyle"
        >
          {{ destination_province_name }}
        </h2>
        <span
          class="trawl-text-bolder"
          :style="textStyle"
        >
          {{ destination_regency_name }}
        </span>
      </a-space>
    </a-space>
  </div>
</template>
<script>
export default {
  data() {
    return {
      textStyle: { 'font-size': '.65rem' }
    }
  },
  props: ["package"],
  computed: {
    origin_regency_name() {
      return this.package?.origin_regency?.name;
    },
    origin_province_name() {
      return this.package?.origin_regency?.province?.name;
    },

    destination_regency_name() {
      return this.package?.destination_regency?.name;
    },
    destination_province_name() {
      return this.package?.destination_regency?.province?.name;
    },
  },
};
</script>
