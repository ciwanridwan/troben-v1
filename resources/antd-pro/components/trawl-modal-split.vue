<template>
  <div>
    <span class="trawl-modal-split--trigger" @click="showModal">
      <slot name="trigger"></slot>
    </span>
    <a-modal
      class="trawl-modal-split--modal"
      :visible="visible"
      :footer="null"
      width="60%"
      centered
    >
      <template slot="closeIcon">
        <a-icon type="close" @click="hideModal"></a-icon>
      </template>
      <template slot="title">
        <h3
          class="trawl-modal-split--title trawl-text-normal trawl-text-bolder"
        >
          <slot v-if="hasSlot('title')" name="title"></slot>
          <span v-else>Trawl Modal Split Title</span>
        </h3>
      </template>
      <a-row type="flex" justify="space-between">
        <a-col
          class="trawl-modal-split--left-container"
          :xs="24"
          :sm="24"
          :md="12"
          :lg="12"
          :xl="12"
        >
          <slot name="left"></slot>
          <a-divider />
          <slot name="leftBottom"></slot>
        </a-col>
        <a-col
          class="trawl-modal-split--right-container"
          :xs="24"
          :sm="24"
          :md="12"
          :lg="12"
          :xl="12"
        >
          <a-layout
            class="trawl-modal-split--right-layout trawl-bg-transparent"
          >
            <a-layout-header
              v-if="hasSlot('rightHeader')"
              class="trawl-bg-transparent trawl-modal-split--right-layout--header"
            >
              <slot name="rightHeader"></slot>
            </a-layout-header>
            <a-layout-content
              class="trawl-bg-transparent trawl-modal-split--right-layout--content"
            >
              <slot name="rightContent"></slot>
            </a-layout-content>
            <a-layout-footer
              v-if="hasSlot('rightFooter')"
              class="trawl-bg-transparent trawl-modal-split--right-layout--footer"
            >
              <slot name="rightFooter"></slot>
            </a-layout-footer>
          </a-layout>
        </a-col>
      </a-row>
    </a-modal>
  </div>
</template>
<script>
export default {
  props: ["value"],
  data() {
    return {
      visible: false,
    };
  },
  methods: {
    hideModal() {
      return (this.visible = false);
    },
    showModal() {
      return (this.visible = true);
    },
    hasSlot(slotName) {
      return !!this.$slots[slotName];
    },
  },
  watch: {
    value: function (value) {
      this.visible = value;
      this.$emit("input", value);
    },
    visible: function (value) {
      this.$emit("input", value);
    },
  },
};
</script>
