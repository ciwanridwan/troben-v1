<template>
  <div>
    <a-space direction="vertical" size="large">
      <a-space direction="vertical" :size="1">
        <span class="trawl-text-bolder trawl-text-danger"> Dari </span>
        <h2 class="trawl-text-bolder trawl-text-normal">
          {{ origin_province_name }}
        </h2>
        <span class="trawl-text-bolder">
          {{ origin_regency_name }}
        </span>
      </a-space>

      <a-space direction="vertical" :size="1">
        <span class="trawl-text-bolder trawl-text-danger"> Ke </span>
        <h2 class="trawl-text-bolder trawl-text-normal">
          {{ destination_province_name }}
        </h2>
        <span class="trawl-text-bolder">
          {{ destination_regency_name }}
        </span>
      </a-space>
    </a-space>
  </div>
</template>
<script>
export default {
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
