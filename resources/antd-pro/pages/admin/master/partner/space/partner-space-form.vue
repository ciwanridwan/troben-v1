<template>
  <div>
    <h2>Tempat Penyimpanan Barang</h2>
    <a-form-model ref="ruleForm" :rules="rules" :model="form">
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="8">
          <trawl-input label="Panjang (m)">
            <template slot="input">
              <a-form-model-item ref="length" prop="length">
                <a-input-number v-model="form.length"></a-input-number>
              </a-form-model-item>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Lebar (m)">
            <template slot="input">
              <a-form-model-item ref="width" prop="width">
                <a-input-number v-model="form.width"></a-input-number>
              </a-form-model-item>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Tinggi">
            <template slot="input">
              <a-form-model-item ref="height" prop="height">
                <a-input-number v-model="form.height"></a-input-number>
              </a-form-model-item>
            </template>
          </trawl-input>
        </a-col>
      </a-row>
    </a-form-model>
  </div>
</template>
<script>
import trawlInput from "../../../../../components/trawl-input.vue";
export default {
  components: { trawlInput },
  data() {
    return {
      form: {
        length: null,
        width: null,
        height: null
      },
      rules: {
        length: [{ required: true }],
        width: [{ required: true }],
        height: [{ required: true }]
      }
    };
  }
};
</script>
