<template>
  <a-form-model ref="formRules" :hideRequiredMark="true" :model="value" :rules="rules">
    <a-row type="flex" :gutter="[12, 12]">
      <a-col :span="6">
        <a-form-model-item label="Deskripsi Barang" prop="description">
          <a-input
            size="large"
            v-model="value.description"
            placeholder="Deskripsi Barang"
          ></a-input>
        </a-form-model-item>
      </a-col>
    </a-row>
    <a-row type="flex" :gutter="[12, 12]">
      <a-col :span="6">
        <a-form-model-item label="Panjang (cm)" prop="length">
          <a-input
            type="number"
            size="large"
            v-model.number="value.length"
            placeholder="Panjang (cm)"
          ></a-input>
        </a-form-model-item>
      </a-col>
      <a-col :span="6">
        <a-form-model-item label="Lebar (cm)" prop="width">
          <a-input
            type="number"
            size="large"
            v-model.number="value.width"
            placeholder="Lebar (cm)"
          ></a-input>
        </a-form-model-item>
      </a-col>
      <a-col :span="6">
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
    <a-row type="flex" :gutter="[12, 12]">
      <a-col :span="6">
        <a-form-model-item label="Berat (kg)" prop="weight">
          <a-input
            type="number"
            size="large"
            v-model.number="value.weight"
            placeholder="Berat (kg)"
          ></a-input>
        </a-form-model-item>
      </a-col>
      <a-col :span="6">
        <a-form-model-item label="Jumlah Paket" prop="qty">
          <a-input
            type="number"
            size="large"
            v-model.number="value.qty"
            placeholder="Jumlah Paket"
          ></a-input>
        </a-form-model-item>
      </a-col>
      <a-col :span="6">
        <a-form-model-item label="Perkiraan Harga Barang" prop="price">
          <a-input
            type="number"
            size="large"
            v-model.number="value.price"
            placeholder="Perkiraan Harga Barang"
          ></a-input>
        </a-form-model-item>
      </a-col>
    </a-row>
  </a-form-model>
</template>
<script>
export default {
  data() {
    return {
      rules: {
        description: [{ required: true }],
        length: [{ required: true }],
        width: [{ required: true }],
        height: [{ required: true }],
        weight: [{ required: true }],
        qty: [{ required: true }],
        price: [{ required: true }],
      },
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
          description: null,
          length: null,
          width: null,
          height: null,
          weight: null,
          qty: null,
          price: null,
        };
      },
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
