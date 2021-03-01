<template>
  <div>
    <h2>Tempat Penyimpanan Barang</h2>
    <a-row type="flex" :gutter="[10, 10]">
      <a-col :span="8">
        <trawl-input label="Panjang (m)">
          <template slot="input">
            <a-input-number v-model="form.dimension.length"></a-input-number>
          </template>
        </trawl-input>
      </a-col>
      <a-col :span="8">
        <trawl-input label="Lebar (m)">
          <template slot="input">
            <a-input-number v-model="form.dimension.width"></a-input-number>
          </template>
        </trawl-input>
      </a-col>
      <a-col :span="8">
        <trawl-input label="Tinggi">
          <template slot="input">
            <a-input-number v-model="form.dimension.height"></a-input-number>
          </template>
        </trawl-input>
      </a-col>
    </a-row>
  </div>
</template>
<script>
import trawlInput from "../../../../../components/trawl-input.vue";
export default {
  components: { trawlInput },
  data() {
    return {
      form: {
        dimension: {
          length: null,
          width: null,
          height: null
        }
      }
    };
  }
};
</script>
