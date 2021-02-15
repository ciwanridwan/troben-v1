<template>
  <div class="footer">
    <div class="links">
      <a target="_blank" :key="index" :href="item.link ? item.link : 'javascript: void(0)'"
         v-for="(item, index) in links">
        <a-icon v-if="item.icon" :type="item.icon"/>
        {{ item.text }}
      </a>
    </div>
    <div class="copyright">
      Copyright &copy; {{ copytext }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'PageFooter',
  props: {
    copyright: String,
    linkList: Array
  },
  computed: {
    copytext() {
      return typeof this.copyright === 'undefined' ? this.config.footer.copyright : this.copyright
    },
    links() {
      return typeof this.linkList === 'undefined' ? this.config.footer.links : this.linkList
    }
  }
}
</script>
