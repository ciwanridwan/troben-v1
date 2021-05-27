<template>
  <div>
    <a-row type="flex" class="trawl-order-row-layout--container">
      <a-col
        v-if="hasSlot('icon')"
        :span="iconSize"
        class="trawl-order-row-layout--icon trawl-text-center"
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
        <a-divider v-if="afterLine === true" />
      </a-col>
    </a-row>
  </div>
</template>
<script>
export default {
  props: {
    afterLine: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      iconSize: 4,
      addonSize: 4
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
    }
  },
  methods: {
    hasSlot(value) {
      return !!this.$slots[value];
    }
  }
};
</script>
