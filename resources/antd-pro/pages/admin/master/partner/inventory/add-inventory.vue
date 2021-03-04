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
      <a-form-model ref="ruleForm" :rules="rules" :model="form">
        <a-row type="flex" :gutter="[10, 10]">
          <a-col :span="6">
            <trawl-input label="Nama Alat">
              <template slot="input">
                <a-form-model-item ref="name" prop="name">
                  <a-input
                    v-model="form.name"
                    @blur="
                      () => {
                        $refs.name.onFieldBlur();
                      }
                    "
                  ></a-input>
                </a-form-model-item>
              </template>
            </trawl-input>
          </a-col>
          <a-col :span="6">
            <trawl-input label="Berat (Kg)">
              <template slot="input">
                <a-form-model-item ref="capacity" prop="capacity">
                  <a-input-number v-model="form.capacity"></a-input-number>
                </a-form-model-item>
              </template>
            </trawl-input>
          </a-col>
          <a-col :span="6">
            <trawl-input label="Tinggi (cm)">
              <template slot="input">
                <a-form-model-item ref="height" prop="height">
                  <a-input-number v-model="form.height"></a-input-number>
                </a-form-model-item>
              </template>
            </trawl-input>
          </a-col>
          <a-col :span="6">
            <trawl-input label="Jumlah">
              <template slot="input">
                <a-form-model-item ref="qty" prop="qty">
                  <a-input-number v-model="form.qty"></a-input-number>
                </a-form-model-item>
              </template>
            </trawl-input>
          </a-col>
        </a-row>
      </a-form-model>
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
        capacity: null,
        height: null,
        qty: null
      },
      rules: {
        name: [{ required: true }],
        capacity: [{ required: true }],
        height: [{ required: true }],
        qty: [{ required: true }]
      }
    };
  },
  methods: {
    clearForm() {
      this.form = {
        name: null,
        capacity: null,
        height: null,
        qty: null
      };
    },
    closeForm() {
      this.visible = false;
    },
    onOk() {
      this.$refs.ruleForm.validate(valid => {
        if (valid) {
          this.inventories.push({ ...this.form });
          this.$refs.ruleForm.resetFields();
          this.closeForm();
        }
      });
    }
  }
};
</script>
