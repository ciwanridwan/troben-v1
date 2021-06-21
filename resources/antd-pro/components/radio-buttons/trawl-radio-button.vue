<template>
  <a-radio-button :class="['trawl-radio-button', alignClass]" :value="radioValue">
    <div v-if="hasIconSlot" class="trawl-radio-button--icon">
      <slot name="icon"></slot>
    </div>
    <slot></slot>
  </a-radio-button>
</template>
<script>
export default {
  props: {
    radioValue: null,
    align: {
      type: String,
      default: "center",
    },
  },
  data() {
    return {
      radioChecked: false,
    };
  },

  computed: {
    hasIconSlot() {
      return !!this.$slots.icon;
    },
    alignClass() {
      switch (this.align) {
        case "center":
          return "trawl-text-center";
          break;
        case "left":
          return "trawl-text-left";
          break;
        default:
          break;
      }
    },
  },
};
</script>
