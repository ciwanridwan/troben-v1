<template>
  <a-form-model ref="formRules" :rules="rules" :model="value">
    <a-row type="flex" :gutter="[12, 12]">
      <a-col :span="18">
        <a-form-model-item prop="is_protection">
          <a-space>
            <a-checkbox v-model="value.handling">
              Perlindungan tambahan diberikan untuk menambah keamanan barang di
              saat...
            </a-checkbox>
            <a-icon
              :component="InformationCircleIcon"
              :style="{ cursor: 'pointer' }"
            />
          </a-space>
        </a-form-model-item>
      </a-col>
    </a-row>
    <a-row type="flex" :gutter="[12, 12]">
      <a-col :span="6" v-if="value.handling">
        <a-form-model-item label="Perlindungan Tambahan">
          <a-select
            v-model="form.handling_type"
            size="large"
            placeholder="Perlindungan Tambahan"
          >
            <a-select-option
              v-for="(item, index) in items"
              :key="index"
              :value="item.code"
            >
              {{ item.name }}
            </a-select-option>
          </a-select>
          <!-- <a-input
                type="number"
                size="large"
                placeholder="Masukan harga motor anda"
              ></a-input> -->
        </a-form-model-item>
      </a-col>
      <a-col v-if="form.handling_type == 'wood'" :span="4">
        <a-form-model-item label="Panjang (cm)" prop="length">
          <a-input
            type="number"
            size="large"
            v-model.number="value.length"
            placeholder="Panjang (cm)"
          ></a-input>
        </a-form-model-item>
      </a-col>
      <a-col v-if="form.handling_type == 'wood'" :span="4">
        <a-form-model-item label="Lebar (cm)" prop="width">
          <a-input
            type="number"
            size="large"
            v-model.number="value.width"
            placeholder="Lebar (cm)"
          ></a-input>
        </a-form-model-item>
      </a-col>
      <a-col v-if="form.handling_type == 'wood'" :span="4">
        <a-form-model-item label="Tinggi (cm)" prop="height">
          <a-input
            type="number"
            size="large"
            v-model.number="value.height"
            placeholder="Tinggi (cm)"
          ></a-input>
        </a-form-model-item>
      </a-col>
    </a-row>
  </a-form-model>
</template>
<script>
import { InformationCircleIcon } from "../../icons";
export default {
  data() {
    return {
      InformationCircleIcon,
      form: {
        handling: false,
        handling_type: null,
      },
      rules: {
        is_protection: [{ required: false }],
      },
      items: [
        {
          name: "Packing Kayu",
          code: "wood",
        },
      ],
    };
  },
  props: {
    onChange: {
      type: Function,
      default: () => {},
    },
    defaultValue: {
      type: Object,
      default: () => {},
    },
    value: {
      type: Object,
      default: () => {
        return {
          handling: false,
          handling_type: null,
          length: null,
          width: null,
          height: null,
        };
      },
    },
  },
  methods: {
    setDefaultValue() {
      Object.keys(this.form).forEach((k) => {
        this.form[k] = this.defaultValue[k];
      });
    },
  },
  watch: {
    value: {
      handler: function (value) {
        this.onChange(value);
        this.$emit("change", value);
      },
      deep: true,
    },
  },
};
</script>
