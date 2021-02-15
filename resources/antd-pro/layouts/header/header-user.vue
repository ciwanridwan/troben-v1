<script>
import {userDropdown} from "../../navigation"

export default {
  data: () => ({
    userDropdown
  })
}
</script>
<template>
  <a-dropdown>
    <div class="header-user" style="cursor: pointer;">
      <a-avatar class="avatar" size="small" shape="circle">{{ initial(user().name) }}</a-avatar>
      <span class="name">{{ user().name }}</span>
    </div>
    <a-menu :class="['avatar-menu']" slot="overlay">
      <a-menu-item v-for="item in userDropdown" :key="item.route">
        <a :href="routeUri(item.route)">
          <a-icon :type="item.icon"/>
          <span>{{ item.text }}</span>
        </a>
      </a-menu-item>
    </a-menu>
  </a-dropdown>
</template>
