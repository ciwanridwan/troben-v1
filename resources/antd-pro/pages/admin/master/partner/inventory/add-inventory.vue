<template>
  <div>
    <a-button @click="visible = true" icon="plus">
      Tambah Alat
    </a-button>
    <a-modal
      v-model="visible"
      :width="720"
      title="Tambah Inventaris"
      @ok="onOk"
    >
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="6">
          <trawl-input label="Nama Alat">
            <template slot="input">
              <a-input v-model="form.name"></a-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="6">
          <trawl-input label="Berat (Kg)">
            <template slot="input">
              <a-input-number v-model="form.weight"></a-input-number>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="6">
          <trawl-input label="Tinggi (cm)">
            <template slot="input">
              <a-input-number v-model="form.height"></a-input-number>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="6">
          <trawl-input label="Jumlah">
            <template slot="input">
              <a-input-number v-model="form.count"></a-input-number>
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
  props: ["inventories"],
  components: { trawlInput },
  data() {
    return {
      visible: false,
      form: {
        name: null,
        weight: null,
        height: null,
        count: null
      }
    };
  },
  methods: {
    clearForm() {
      this.form = {
        name: null,
        weight: null,
        height: null,
        count: null
      };
    },
    closeForm() {
      this.visible = false;
    },
    onOk() {
      this.inventories.push(this.form);
      this.clearForm();
      this.closeForm();
    }
  }
};
</script>
