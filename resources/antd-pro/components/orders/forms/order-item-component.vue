<template>
  <a-form-model
    ref="formRules"
    :hideRequiredMark="true"
    :model="value"
    :rules="rules"
  >
    <a-row type="flex" :gutter="[12, 12]">
      <a-col :span="6">
        <a-form-model-item label="Deskripsi Barang" prop="desc">
          <a-select
            v-model="value.name"
            size="large"
            placeholder="Deskripsi Barang"
          >
            <a-select-option
              v-for="(item, index) in items"
              :key="index"
              :value="item.toLowerCase()"
            >
              {{ item }}
            </a-select-option>
          </a-select>
          <!-- <a-input
            size="large"
            v-model="value.desc"
            placeholder="Deskripsi Barang"
          ></a-input> -->
        </a-form-model-item>
      </a-col>
      <a-col v-show="value.name == 'lainnya'" :span="6">
        <a-form-model-item label="Detail Barang" prop="desc">
          <a-input
            type="text"
            size="large"
            v-model="item_desc"
            placeholder="Detail Barang"
          ></a-input>
        </a-form-model-item>
      </a-col>
    </a-row>

    <a-row v-show="value.name != 'motor'" type="flex" :gutter="[12, 12]">
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
    <a-row v-show="value.name != 'motor'" type="flex" :gutter="[12, 12]">
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
    <!-- form motor -->

    <a-row v-show="value.name == 'motor'" type="flex" :gutter="[12, 12]">
      <a-col :span="9">
        <a-form-model-item label="Jenis Motor" prop="moto_type">
          <a-select
            v-model="value.moto_type"
            size="large"
            placeholder="Deskripsi Barang"
          >
            <a-select-option
              v-for="(item, index) in motorType"
              :key="index"
              :value="item.toLowerCase()"
            >
              {{ item }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
      </a-col>
      <a-col :span="9">
        <a-form-model-item label="Merek Motor" prop="moto_merk">
          <a-input
            v-model="value.moto_merk"
            type="text"
            size="large"
            placeholder="Merek Motor"
          ></a-input>
        </a-form-model-item>
      </a-col>
    </a-row>
    <a-row v-show="value.name == 'motor'" type="flex" :gutter="[12, 12]">
      <a-col :span="9">
        <a-form-model-item label="CC Motor" prop="moto_cc">
          <a-select
            v-model="value.moto_cc"
            size="large"
            placeholder="Deskripsi Barang"
          >
            <a-select-option
              v-for="(item, index) in ccMotor"
              :key="index"
              :value="item.type"
            >
              {{ item.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
      </a-col>
      <a-col :span="9">
        <a-form-model-item label="Tahun Motor" prop="moto_year">
          <a-input
            v-model="value.moto_year"
            type="number"
            size="large"
            placeholder="Tahun Motor"
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
        name: [{ required: true }],
        desc: [{ required: true }],
        length: [{ required: !this.isMotor }],
        width: [{ required: !this.isMotor }],
        height: [{ required: !this.isMotor }],
        weight: [{ required: !this.isMotor }],
        qty: [{ required: !this.isMotor }],
        price: [{ required: !this.isMotor }],
        moto_cc: [{ required: this.isMotor }],
        moto_type: [{ required: this.isMotor }],
        moto_merk: [{ required: this.isMotor }],
        moto_year: [{ required: this.isMotor }],
      },
      items: [
        "Pakaian",
        "Kosmetik",
        "Aksesoris",
        "Makanan Non Frozen",
        "Motor",
        "Elektronik",
        "Parabotan",
        "Lainnya",
      ],
      ccMotor: [
        {
          name: "100 CC - 150 CC",
          type: 150,
        },
        {
          name: "151 CC - 250 CC",
          type: 250,
        },
        {
          name: ">250 CC",
          type: 999,
        },
      ],
      motorType: ["Matic", "Kopling", "Gigi"],
      item_desc: "",
      isMotor: false,
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
          name: null,
          desc: null,
          length: null,
          width: null,
          height: null,
          weight: null,
          qty: null,
          price: null,
          moto_cc: null,
          moto_type: null,
          moto_merk: null,
          moto_year: null,
        };
      },
    },
  },
  watch: {
    value: {
      handler: function (value) {
        if (value.name == "lainnya") {
          value.desc = this.item_desc;
        } else {
          value.desc = value.name;
        }
        if (value.name == "motor") {
          this.isMotor = true;
        } else {
          this.isMotor = false;
        }
        this.onChange(value);
        this.$emit("change", value);
      },
      deep: true,
    },
  },
};
</script>
