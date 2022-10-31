<template>
  <div>
    <a-row
      type="flex"
      class="trawl-order-row-layout--container"
      :style="alignItem"
    >
      <a-col
        v-if="hasSlot('icon')"
        :span="iconSize"
        :class="[
          'trawl-order-row-layout--icon',
          !iconPadding ? 'trawl-order-row-layout--icon-0' : null,
          alignClass,
        ]"
      >
        <slot name="icon"></slot>
      </a-col>
      <a-col :span="24 - iconSize" class="trawl-order-row-layout--content">
        <a-row type="flex">
          <a-col :span="contentSize">
            <slot name="content"></slot>
          </a-col>
          <a-col
            v-if="hasSlot('addon')"
            :span="addonSize"
            class="trawl-order-row-layout--addon"
          >
            <slot name="addon"></slot>
          </a-col>
        </a-row>
      </a-col>
    </a-row>
    <a-divider v-if="afterLine === true" />
  </div>
</template>
<script>
export default {
  props: {
    afterLine: {
      type: Boolean,
      default: true,
    },
    iconPadding: {
      type: Boolean,
      default: true,
    },
    align: {
      type: String,
      default: "center",
    },
    alignItem: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      iconSize: 4,
      addonSize: 10,
    };
  },
  computed: {
    containerContentSize() {
      let contentSize = 24;
      if (this.hasSlot("addon")) {
        contentSize -= this.addonSize;
      }
      return contentSize;
    },
    contentSize() {
      let contentSize = 24;
      if (this.hasSlot("addon")) {
        contentSize -= this.addonSize;
      }
      return contentSize;
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
  methods: {
    hasSlot(value) {
      return !!this.$slots[value];
    },
  },
};
</script>
