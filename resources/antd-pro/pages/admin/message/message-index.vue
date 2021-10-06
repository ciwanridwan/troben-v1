<template>
  <content-chat-layout>
    <!-- chat list -->
    <template slot="sider-left">
      <a-list item-layout="horizontal" :data-source="data">
        <a-list-item slot="renderItem" slot-scope="item, index" :class="[ item.is_active ? 'trawl-bg-gray' : '' ]" style="padding-left: 10px;">
          <a-list-item-meta :description="item.description" @click="alert('oke')">
            <a slot="title">{{ item.title }}</a>
            <a-badge :dot="item.is_online" slot="avatar"><a-avatar :src="'https://ui-avatars.com/api/?name='+item.title"/></a-badge>
          </a-list-item-meta>
        </a-list-item>
      </a-list>
    </template>

    <template slot="content-head">
      <a-list-item class="trawl-bg-white">
        <a-list-item-meta :description="'active now'">
          <a slot="title">Faris Ferdian Akbar</a>
          <a-badge dot slot="avatar"><a-avatar :src="'https://ui-avatars.com/api/?name=Faris+Ferdian+Akbar'"/></a-badge>
        </a-list-item-meta>
      </a-list-item>
    </template>

    <!-- chat box -->
    <template slot="content">
      <!-- chat body -->
      <div>
        <a-row type="flex" justify="start" align="bottom" class="trawl-chat--first" style="padding-left: 15px">
          <a-col :md="1">
            <a-avatar :src="'https://ui-avatars.com/api/?name=Faris+Ferdian+Akbar'"></a-avatar>
          </a-col>
          <a-col :md="14" class="trawl-chat trawl-chat-message trawl-chat-message--send trawl-bg-white">
            lorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem ip
          </a-col>
        </a-row>

        <a-row type="flex" justify="end" align="bottom" class="trawl-chat--next">
          <a-col :md="14" class="trawl-chat trawl-chat-message trawl-chat-message--receive trawl-bg-green--darken">
            lorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem ip
          </a-col>
          <a-col :md="1" style="margin-left: 20px">
            <a-avatar :src="'https://ui-avatars.com/api/?name=Faris+Ferdian+Akbar'"></a-avatar>
          </a-col>
        </a-row>

        <a-row type="flex" justify="end" align="bottom" class="trawl-chat--next">
          <a-col :md="14" class="trawl-chat trawl-chat-message trawl-chat-message--receive trawl-bg-green--darken">
            lorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem ip
          </a-col>
          <a-col :md="1" style="margin-left: 20px">
            <a-avatar :src="'https://ui-avatars.com/api/?name=Faris+Ferdian+Akbar'"></a-avatar>
          </a-col>
        </a-row>

        <a-row type="flex" justify="start" align="bottom" class="trawl-chat--next" style="padding-left: 15px">
          <a-col :md="1">
            <a-avatar :src="'https://ui-avatars.com/api/?name=Faris+Ferdian+Akbar'"></a-avatar>
          </a-col>
          <a-col :md="14" class="trawl-chat trawl-chat-message trawl-chat-message--send trawl-bg-white">
            lorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem ip
          </a-col>
        </a-row>

        <a-row type="flex" justify="start" align="bottom" class="trawl-chat--next" style="padding-left: 15px">
          <a-col :md="1">
            <a-avatar :src="'https://ui-avatars.com/api/?name=Faris+Ferdian+Akbar'"></a-avatar>
          </a-col>
          <a-col :md="14" class="trawl-chat trawl-chat-message trawl-chat-message--send trawl-bg-white">
            lorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem iplorem ip
          </a-col>
        </a-row>
      </div>

      <!-- chat send -->
      <div class="trawl-chat-send trawl-bg-white">
        <a-row>
          <a-col :md="22">
            <a-input :placeholder="'Ketikkan sesuatu'" style="display: inline-block"></a-input>
          </a-col>
          <a-col :md="1" :offset="1">
            <a-button type="primary">Send</a-button>
          </a-col>
        </a-row>
      </div>
    </template>

    <!-- notification -->
    <template slot="sider-right">
      <trawl-notification></trawl-notification>
    </template>
  </content-chat-layout>
</template>
<script>
import ContentChatLayout from "../../../layouts/content-chat-layout";
const data = [
  {
    title: 'Ant Design Title 1',
    description: "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: false,
    is_online: true,
  },
  {
    title: 'Ant Design Title 2',
    description: "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: true,
    is_online: true,
  },
  {
    title: 'Ant Design Title 3',
    description: "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: false,
    is_online: false,
  },
  {
    title: 'Ant Design Title 4',
    description: "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: false,
    is_online: false,
  },
  {
    title: 'Ant Design Title 4',
    description: "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: false,
    is_online: false,
  },
];
export default {
  components: {ContentChatLayout},
  data() {
    return {
      data,
    };
  },
}
</script>
