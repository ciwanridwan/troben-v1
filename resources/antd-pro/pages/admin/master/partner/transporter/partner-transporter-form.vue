<template>
  <div>
    <h2>Tanggung Jawab</h2>
    <h3>Kota / Kabupaten</h3>
    <a-row type="flex">
      <a-col :span="8">
        <responsible :regencies="geo.regencies"></responsible>
      </a-col>
    </a-row>
  </div>
</template>
<script>
import responsible from "./responsible.vue";
export default {
  props: {
    geo: {
      type: Object
    }
  },
  components: { responsible }
};
</script>
