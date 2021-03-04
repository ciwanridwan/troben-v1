<template>
  <div>
    <a-button @click="visible = true" icon="plus">Tambah Armada</a-button>
    <a-modal
      :visible="visible"
      @cancel="handleCancel"
      :width="720"
      title="Tambah Armada"
      @ok="onOk"
    >
      <a-form-model
        ref="ruleForm"
        :model="form"
        layout="vertical"
        :rules="rules"
        hideRequiredMark
      >
        <a-row type="flex" :gutter="[10, 10]">
          <a-col :span="6">
            <a-form-model-item ref="type" prop="type">
              <template slot="label">
                <h3>Jenis Kendaraan</h3>
              </template>
              <a-select
                show-search
                :filter-option="filterOption"
                v-model="form.type"
                placeholder="- Jenis Kendaraan -"
              >
                <a-select-option
                  v-for="type in types"
                  :key="type"
                  :value="type"
                >
                  {{ type }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col :span="6">
            <a-form-model-item
              ref="registrationNumber"
              prop="registration_number"
            >
              <template slot="label">
                <h3>No. Polisi</h3>
              </template>
              <a-input v-model="form.registration_number"></a-input>
            </a-form-model-item>
          </a-col>
          <a-col :span="6">
            <a-form-model-item ref="weight" prop="weight">
              <template slot="label">
                <h3>Kapasitas (Kg)</h3>
              </template>
              <a-input-number v-model="form.weight"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="6">
            <a-form-model-item ref="productionYear" prop="production_year">
              <template slot="label">
                <h3>Tahun Pembuatan</h3>
              </template>
              <a-input v-model="form.production_year"></a-input>
            </a-form-model-item>
          </a-col>
          <a-col :span="6">
            <a-form-model-item ref="length" prop="length">
              <template slot="label">
                <h3>Panjang (cm)</h3>
              </template>
              <a-input-number v-model="form.length"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="6">
            <a-form-model-item ref="width" prop="width">
              <template slot="label">
                <h3>Width (cm)</h3>
              </template>
              <a-input-number v-model="form.width"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="6">
            <a-form-model-item ref="height" prop="height">
              <template slot="label">
                <h3>Tinggi (cm)</h3>
              </template>
              <a-input-number v-model="form.height"></a-input-number>
            </a-form-model-item>
          </a-col>
        </a-row>
        <a-row type="flex" :gutter="[10, 10]">
          <a-col :span="6">
            <a-form-model-item ref="registration_name" prop="registration_name">
              <template slot="label">
                <h3>Nama STNK</h3>
              </template>
              <a-input v-model="form.registration_name"></a-input>
            </a-form-model-item>
          </a-col>
          <a-col :span="6">
            <a-form-model-item ref="registration_year" prop="registration_year">
              <template slot="label">
                <h3>Tahun Berlaku STNK</h3>
              </template>
              <a-input v-model="form.registration_year"></a-input>
            </a-form-model-item>
          </a-col>
        </a-row>
      </a-form-model>
    </a-modal>
  </div>
</template>
<script>
import trawlInput from "../../../../../components/trawl-input.vue";
export default {
  components: { trawlInput },
  data() {
    return {
      visible: false,

      form: {
        type: null,
        production_year: null,
        registration_name: null,
        registration_year: null,
        registration_number: null,
        length: null,
        width: null,
        height: null,
        weight: null
      },
      rules: {
        type: [{ required: true }],
        production_year: [{ required: true }],
        registration_name: [{ required: true }],
        registration_year: [{ required: true }],
        registration_number: [{ required: true }],
        length: [{ required: true }],
        width: [{ required: true }],
        height: [{ required: true }],
        weight: [{ required: true }]
      }
    };
  },
  props: {
    types: {
      type: Array,
      default: []
    },
    transporters: {
      type: Array,
      default: []
    }
  },
  methods: {
    closeForm() {
      this.visible = false;
    },
    handleCancel() {
      this.visible = false;
    },
    onOk() {
      this.$refs.ruleForm.validate(valid => {
        if (valid) {
          this.transporters.push({ ...this.form });
          this.$refs.ruleForm.resetFields();
          this.closeForm();
        }
      });
    }
  }
};
</script>
<style lang="scss"></style>
