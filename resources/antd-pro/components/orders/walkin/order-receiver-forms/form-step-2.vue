<template>
  <a-form-model
    ref="formRules"
    :model="form"
    :rules="rules"
    :hideRequiredMark="true"
    layout="vertical"
  >
    <a-form-model-item prop="items">
      <order-item-form ref="itemForm" v-bind:form.sync="form" />
      <!-- <a-space :key="index" direction="vertical" :style="{ width: '100%' }" ref="items">
        <trawl-divider v-if="numberItem > 1" orientation="right">
          <a-icon
            :component="MinusCircleIcon"
            :style="{ cursor: 'pointer' }"
            @click="takeItem(index - 1)"
          />
        </trawl-divider>
        <div>
          <h3 class="trawl-text-bolder">Daftar Barang</h3>
          <order-item-component :ref="`itemComponent${index}`" :onChange="onItemChange" />
        </div>

        <div>
          <h3 class="trawl-text-bolder">Rekomendasi Packing</h3>
          <order-handlings-component
            :ref="`handlingComponent${index}`"
            :onChange="onPackagingChange"
          />
        </div>
        <div>
          <h3 class="trawl-text-bolder">Asuransi</h3>
          <order-insurance-component :ref="`insuranceComponent${index}`" />
        </div>
      </a-space> -->
      <trawl-divider orientation="right">
        <a-icon
          :component="PlusCircleIcon"
          :style="{ cursor: 'pointer' }"
          @click="addItem"
        />
      </trawl-divider>
    </a-form-model-item>
  </a-form-model>
</template>
<script>
import { PlusCircleIcon, MinusCircleIcon } from "../../../icons";
import trawlRadioButton from "../../../trawl-radio-button.vue";
import { services } from "../../../../data/services";
import OrderHandlingsComponent from "../../forms/order-handlings-component.vue";
import OrderItemComponent from "../../forms/order-item-component.vue";
import OrderInsuranceComponent from "../../forms/order-insurance-component.vue";
import TrawlDivider from "../../../trawl-divider.vue";
import OrderItemForm from "../../forms/order-item-form.vue";
export default {
  components: {
    trawlRadioButton,
    OrderHandlingsComponent,
    OrderItemComponent,
    OrderInsuranceComponent,
    TrawlDivider,
    OrderItemForm,
  },
  data() {
    return {
      PlusCircleIcon,
      MinusCircleIcon,
      services,
      numberItem: 1,
      formRefs: ["itemForm"],
      form: {
        items: [],
      },
      rules: {
        items: [{ required: true }],
      },
    };
  },
  methods: {
    async validate() {
      if (this.$refs.itemForm.valid) {
        console.log(this.$refs.itemForm.getFormData);
      }
    },
    addItem() {
      this.numberItem++;
    },
    takeItem(index) {
      this.$refs.items[index].remove();
      this.numberItem--;
      console.log(this.numberItem);
    },
  },
  watch: {
    form: {
      handler: function (value) {
        console.log(value);
      },
      deep: true,
    },
  },
};
</script>
