<template>
  <div>
    <a-form-model ref="formRules" :model="form" :rules="rules">
      <a-form-model-item prop="items">
        <template v-for="(item, index) in form.items">
          <trawl-divider
            :key="`destroyer_${index}`"
            v-if="form.items.length > 1"
            orientation="right"
          >
            <a-icon
              :component="MinusCircleIcon"
              :style="{ cursor: 'pointer' }"
              @click="removeItem"
            />
          </trawl-divider>
          <order-item-form :key="index" ref="itemForm" v-model="form.items[index]" />
        </template>

        <trawl-divider orientation="right">
          <a-icon
            :component="PlusCircleIcon"
            :style="{ cursor: 'pointer' }"
            @click="addItem"
          />
        </trawl-divider>
      </a-form-model-item>
      <a-form-model-item prop="photos">
        <h3>Foto Keseluruhan Barang</h3>
        <trawl-upload-list v-model="form.photos" />
      </a-form-model-item>
    </a-form-model>
  </div>
</template>
<script>
import { PlusCircleIcon, MinusCircleIcon } from "../../../icons";
import TrawlDivider from "../../../trawl-divider.vue";
import TrawlUploadList from "../../../trawl-upload-list.vue";
import OrderHandlingsComponent from "../../forms/order-handlings-component.vue";
import OrderItemForm from "../../forms/order-item-form.vue";
export default {
  data() {
    return {
      form: {
        items: [{}],
        photos: [],
      },
      valid: true,
      rules: {
        items: [{ required: true }],
        photos: [{ required: true }],
      },
      PlusCircleIcon,
      MinusCircleIcon,
    };
  },
  methods: {
    async validate() {
      let form = this.$refs.itemForm;

      let valid = true;

      for (const component of form) {
        valid = await component.validate();
        if (!valid) {
          return false;
        }
      }

      await this.$refs.formRules
        .validate()
        ?.then((value) => {
          if (!value) {
            valid = false;
          }
        })
        .catch((error) => {
          valid = false;
        });

      return valid;
    },

    addItem() {
      this.form.items.push({});
    },
    removeItem(index) {
      this.form.items.splice(index, 1);
    },
  },
  computed: {
    items() {
      let items = [];

      this.form?.items?.forEach((item) => {
        console.log(item);
        let tempItem = {};
        Object.keys(item).forEach((k) => {
          tempItem = { ...tempItem, ...item[k] };
        });
        items.push(tempItem);
      });
      return items;
    },
  },
  components: {
    OrderHandlingsComponent,
    TrawlDivider,
    OrderItemForm,
    TrawlUploadList,
  },
  watch: {
    form: {
      handler: function (value) {
        this.$emit("change", {
          ...value,
          items: this.items,
        });
        this.$emit("input", {
          ...value,
          items: this.items,
        });
      },
      deep: true,
    },
  },
};
</script>
