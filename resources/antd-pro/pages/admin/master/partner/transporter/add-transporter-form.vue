<template>
  <div>
    <a-button @click="visible = true" icon="plus">Tambah Armada</a-button>
    <a-modal
      :visible="visible"
      @cancel="handleCancel"
      :width="720"
      title="Tambah Armada"
    >
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="6">
          <trawl-input label="Jenis Kendaraan">
            <template slot="input">
              <a-input v-model="form.type"></a-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="6">
          <trawl-input label="No. Polisi">
            <template slot="input">
              <a-input v-model="form.registration.number"></a-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="6">
          <trawl-input label="Kapasitas (Kg)">
            <template slot="input">
              <a-input-number v-model="form.capacity"></a-input-number>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="6">
          <trawl-input label="Tahun pembuatan">
            <template slot="input">
              <a-input v-model="form.production_year"></a-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="6">
          <trawl-input label="Panjang (cm)">
            <template slot="input">
              <a-input-number v-model="form.dimension.length"></a-input-number>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="6">
          <trawl-input label="Lebar (cm)">
            <template slot="input">
              <a-input-number v-model="form.dimension.width"></a-input-number>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="6">
          <trawl-input label="Tinggi (cm)">
            <template slot="input">
              <a-input-number v-model="form.dimension.height"></a-input-number>
            </template>
          </trawl-input>
        </a-col>
      </a-row>
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="6">
          <trawl-input label="Nama STNK">
            <template slot="input">
              <a-input-number v-model="form.registration.name"></a-input-number>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="6">
          <trawl-input label="Tahun Berlaku STNK">
            <template slot="input">
              <a-input-number
                v-model="form.registration.valid"
              ></a-input-number>
            </template>
          </trawl-input>
        </a-col>
      </a-row>
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
        capacity: null,
        production_year: null,
        registration: {
          name: null,
          valid: null,
          number: null
        },
        dimension: {
          length: null,
          width: null,
          height: null
        }
      }
    };
  },
  methods: {
    handleCancel() {
      this.visible = false;
    }
  },
  watch: {
    "form.type": function(value) {
      console.log(value);
    }
  }
};
</script>
<style lang="scss"></style>
