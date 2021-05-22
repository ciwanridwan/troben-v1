<template>
  <div class="trawl-modal-confirm--container">
    <div class="trawl-modal-confirm--trigger" @click="showModal">
      <slot name="trigger"> </slot>
    </div>
    <a-modal v-model="visible" :footer="null" :closable="false">
      <template v-if="hasSlot('title')" slot="title">
        <div class="trawl-modal-confirm--title">
          <slot name="title"></slot>
        </div>
      </template>

      <span class="trawl-modal-confirm--text">
        <slot name="text"></slot>
      </span>
      <a-row type="flex" :gutter="[12, 12]" class="trawl-modal-confirm--action">
        <a-col :span="12">
          <a-button
            @click="onCancel"
            class="trawl-button-success--ghost"
            ghost
            block
            size="large"
          >
            Batal
          </a-button>
        </a-col>
        <a-col :span="12">
          <a-button
            @click="onOk"
            class="trawl-button-success"
            block
            size="large"
          >
            Ya
          </a-button>
        </a-col>
      </a-row>
    </a-modal>
  </div>
</template>
<script>
export default {
  data() {
    return {
      visible: false
    };
  },
  props: {
    ok: {
      type: Function,
      default: () => {}
    },
    cancel: {
      type: Function,
      default: () => {}
    }
  },
  methods: {
    hasSlot(slotName) {
      return !!this.$slots[slotName];
    },
    showModal() {
      this.visible = true;
    },
    hideModal() {
      this.visible = false;
    },
    onCancel() {
      this.cancel();
      this.hideModal();
    },
    onOk() {
      this.ok();
      this.hideModal();
    }
  }
};
</script>
