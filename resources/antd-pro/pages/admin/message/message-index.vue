<template>
  <content-chat-layout>
    <!-- chat list -->
    <template slot="sider-left">
      <a-list item-layout="horizontal" :data-source="roomChat">
        <a-list-item
          slot="renderItem"
          slot-scope="item, index"
          :class="[roomId == item.room_id ? 'trawl-bg-gray' : '']"
          style="padding-left: 10px;"
        >
          <a-list-item-meta :description="description(item)">
            <!-- <button class="trawl-chat--buttonSideBar"> -->
            <div
              class="trawl-chat--buttonSideBar"
              @click="listChat(item, getListChat)"
              slot="title"
            >
              {{ item.customer.name }}
            </div>
            <!-- </button> -->
            <a-badge :dot="item.is_online" slot="avatar"
              ><a-avatar
                :src="
                  'https://ui-avatars.com/api/?name=' + `${item.customer.name}`
                "
            /></a-badge>
          </a-list-item-meta>
        </a-list-item>
      </a-list>
    </template>

    <template slot="content-head">
      <a-list-item v-if="nickname" class="trawl-bg-white">
        <a-list-item-meta :description="'active now'">
          <a slot="title">{{ nickname }}</a>
          <a-badge dot slot="avatar"
            ><a-avatar :src="`https://ui-avatars.com/api/?name=${nickname}`"
          /></a-badge>
        </a-list-item-meta>
      </a-list-item>
    </template>

    <!-- chat box -->
    <template slot="content">
      <!-- chat body -->
      <div v-if="nickname">
        <a-row
          v-for="(data, index) in dataChat"
          :key="index"
          type="flex"
          :justify="data.customer_chat ? 'end' : 'start'"
          align="bottom"
          :class="[
            'trawl-chat--next',
            index == dataChat.length - 1 ? 'scrollingContainer' : ''
          ]"
          :style="data.customer_chat ? 'padding-left: 15px' : ''"
        >
          <a-col v-if="data.user_chat" :md="1">
            <a-avatar :src="'https://ui-avatars.com/api/?name=A+D'"></a-avatar>
          </a-col>
          <a-col
            :md="14"
            :class="[
              'trawl-chat trawl-chat-message',
              data.customer_chat
                ? 'trawl-chat-message--receive trawl-bg-green--darken'
                : 'trawl-chat-message--send trawl-bg-white'
            ]"
          >
            <a
              v-if="data.attachments.length"
              :href="
                data.attachments[0].customer_file
                  ? data.attachments[0].customer_file
                  : data.attachments[0].user_file
              "
              target="_blank"
            >
              <img
                v-if="data.attachments.length"
                style="width: 403px;"
                :src="
                  data.attachments[0].customer_file
                    ? data.attachments[0].customer_file
                    : data.attachments[0].user_file
                "
                alt="image"
              />
            </a>
            {{ data.customer_chat ? data.customer_chat : data.user_chat }}
          </a-col>
          <a-col v-if="data.customer_chat" :md="1" style="margin-left: 20px">
            <a-avatar
              :src="`https://ui-avatars.com/api/?name=${nickname}`"
            ></a-avatar>
          </a-col>
        </a-row>
      </div>

      <!-- chat send -->
      <div v-if="nickname" class="trawl-chat-send trawl-bg-white">
        <a-row>
          <a-col :md="20">
            <a-input
              :placeholder="'Ketikkan sesuatu'"
              style="display: inline-block"
              v-model="textInput"
            ></a-input>
          </a-col>
          <a-col :md="3" :offset="1">
            <input
              type="file"
              style="display: none"
              @change="onFileSelected"
              ref="fileInput"
            />
            <button
              style="border: none; background: none; margin: 0; padding: 0;"
              @click="$refs.fileInput.click()"
            >
              <img
                style="width: 24px;"
                src="/assets/paperclip.png"
                alt="paperclip"
              />
            </button>
            <a-button
              style="margin: 0; padding: 0px 10px;"
              type="primary"
              @click="producerSocket"
              >Send</a-button
            >
            <!-- <a-button @click="scrollToElement({ behavior: 'smooth' })"
              >test scroll down from top</a-button
            >
            <a-button @click="loading">test loading</a-button> -->
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

const data2 = [
  {
    titl: "Ant Design Title 1",
    descriptio:
      "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: false,
    is_online: true
  },
  {
    titl: "Ant Design Title 2",
    descriptio:
      "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: true,
    is_online: true
  },
  {
    titl: "Ant Design Title 3",
    descriptio:
      "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: false,
    is_online: false
  },
  {
    titl: "Ant Design Title 4",
    descriptio:
      "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: false,
    is_online: false
  },
  {
    titl: "Ant Design Title 4",
    descriptio:
      "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: false,
    is_online: false
  },
  {
    titl: "Ant Design Title 4",
    descriptio:
      "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: false,
    is_online: false
  },
  {
    titl: "Ant Design Title 4",
    descriptio:
      "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: false,
    is_online: false
  },
  {
    titl: "Ant Design Title 4",
    descriptio:
      "Ant Design, a design language for background applications, is refined by Ant UED Team",
    is_active: false,
    is_online: false
  }
];
export default {
  components: { ContentChatLayout },
  data() {
    return {
      data2,
      fals: false,
      roomChat: null,
      dataChat: null,
      textInput: "",
      nickname: "",
      descriptionText: "",
      roomId: null,
      newMessage: [],
      selectedFile: null,
      jwt_token: "",
      activeLoading: 0
    };
  },
  methods: {
    loading() {
      this.$message.loading("Action in rogress...", this.activeLoading);
      // setTimeout(hide, 3000);
    },

    scrollToElement(options) {
      const el = this.$el.getElementsByClassName("scrollingContainer")[0];
      if (el) {
        el.scrollIntoView(options);
      }
    },

    onFileSelected(event) {
      this.selectedFile = event.target.files[0];
      console.log("upload", event);
      console.log("uploadData", event.target.files[0]);
    },

    description(item) {
      if (item.chats.length == 0) return "";
      else
        return `${
          item.chats[0].customer_chat
            ? item.chats[0].customer_chat
            : item.chats[0].user_chat
        } ${
          item.chats[1]
            ? item.chats[1].customer_chat
              ? item.chats[1].customer_chat
              : item.chats[1].user_chat
            : ""
        } ${
          item.chats[2]
            ? item.chats[2].customer_chat
              ? item.chats[2].customer_chat
              : item.chats[2].user_chat
            : ""
        }`;
    },

    getListRoom(value) {
      if (value === "reconnect") return this.consumeSocket(this.getListRoom);
      this.roomChat = value;
      console.log("onmessage", this.roomChat);
      this.activeLoading = 1;
    },

    getListChat(value) {
      console.log("masuk pak ekooo", value);
      this.dataChat = value;
      this.scrollToElement({ behavior: "smooth" });
    },

    consumeSocket(callback) {
      this.loading();
      let jwtToken = this.jwt_token;
      let consumer = new WebSocket(
        "wss://staging-ws.trawlbens.com/ws/v2/consumer/non-persistent/public/default/1-admin/room"
      );

      consumer.onopen = function(e) {
        console.log("isOpen", consumer);

        fetch("https://staging-chat.trawlbens.com/chat/list/admin/room", {
          method: "GET",
          headers: {
            Authorization: `bearer ${jwtToken}`
          }
        })
          .then(response => {
            console.log("success", response);
          })
          .catch(err => {
            console.error("failure error", err);
          });
      };

      consumer.onmessage = function(event) {
        let getData = null;
        let message = JSON.parse(event.data);
        let payload = atob(message.payload);

        getData = JSON.parse(payload);
        getData.forEach(element => {
          element.is_active = false;
          element.is_online = false;
        });
        callback(getData);
      };

      // consumer.onclose = function(event) {
      //   console.log("on close....", event);
      //   if (event.wasClean) {
      //     // alert(
      //     //   `[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`
      //     // );
      //     console.log(
      //       `[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`
      //     );
      //   } else {
      //     // e.g. server process killed or network down
      //     // event.code is usually 1006 in this case
      //     // alert("[close] Connection consumer died");
      //     console.log("[close] Connection consumer died");
      //     callback("reconnect");
      //   }
      // };

      consumer.onerror = function(error) {
        // alert(`[error] ${error.message}`);
        console.log("consumer error", error.message);
      };

      consumer.connect;
    },

    listChat(item, callback) {
      this.roomId = item.room_id;
      let jwtToken = this.jwt_token;
      let consumer = new WebSocket(
        `wss://staging-ws.trawlbens.com/ws/v2/consumer/non-persistent/public/default/1-admin-room-${item.room_id}/chat`
      );
      consumer.onclose = function(event) {
        console.log("listchat on close....", event);
        if (event.wasClean) {
          console.log(
            `listchat [close] Connection closed cleanly, code=${event.code} reason=${event.reason}`
          );
        } else {
          // e.g. server process killed or network down
          // event.code is usually 1006 in this case
          console.log("listchat [close] Connection list chat died");
          listChat(item, callback);
        }
      };

      consumer.onopen = function(e) {
        console.log("list chat isOpenChat", consumer);

        fetch(
          `https://staging-chat.trawlbens.com/chat/list/admin/room/${item.room_id}`,
          {
            method: "GET",
            headers: {
              Authorization: `bearer ${jwtToken}`
            }
          }
        )
          .then(response => {
            console.log("list chat success", response);
          })
          .catch(err => {
            console.error("list chat failure error", err);
          });
      };

      consumer.onmessage = function(event) {
        var myHeaders = new Headers();
        myHeaders.append("Authorization", `bearer ${jwtToken}`);

        var requestOptions = {
          method: "PATCH",
          headers: myHeaders,
          redirect: "follow"
        };

        fetch(
          `https://staging-chat.trawlbens.com/chat/admin/read/trawlbens/room/${item.room_id}`,
          requestOptions
        )
          .then(response => response.text())
          .then(result => console.log("patch", result))
          .catch(error => console.log("error patch", error));

        let message = JSON.parse(event.data);
        let payload = atob(message.payload);
        callback(JSON.parse(payload));
        console.log("onmessagechat", JSON.parse(payload));
      };

      this.nickname = item.customer.name;

      consumer.onerror = function(error) {
        console.log("error", error.message);
      };

      consumer.connect;
    },

    // closeSocket() {
    //   let socket = new WebSocket(
    //     "ws://127.0.0.1:8080/ws/v2/consumer/persistent/public/default/my-topic/my-sub"
    //   );

    //   socket.onclose = function(event) {
    //     console.log("on close....", event);
    //     if (event.wasClean) {
    //       alert(
    //         `[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`
    //       );
    //     } else {
    //       // e.g. server process killed or network down
    //       // event.code is usually 1006 in this case
    //       alert("[close] Connection died");
    //     }
    //   };
    //   socket.onerror = function(error) {
    //     alert(`[error] ${error.message}`);
    //   };
    // },

    producerSocket() {
      console.log("produce", this.roomId, this.textInput);

      // var myHeaders = new Headers();
      // myHeaders.append("Authorization", `bearer ${this.jwt_token}`);

      // var formdata = new FormData();
      // formdata.append("room_id", this.roomId);
      // formdata.append("message", this.textInput);
      // if (this.selectedFile) {
      //   console.log("masuk upload file", this.selectedFile.name);
      //   formdata.append("attachments", this.selectedFile);
      // }
      // // formdata.append("attachments", fileInput.files[0]);

      // var requestOptions = {
      //   method: "POST",
      //   headers: myHeaders,
      //   body: formdata,
      //   redirect: "follow"
      // };

      // fetch(
      //   "https://staging-chat.trawlbens.com/chat/trawlbens/to/customer",
      //   requestOptions
      // )
      //   .then(response => response.text())
      //   .then(result => console.log(result))
      //   .catch(error => console.log("error", error));

      var myHeaders = new Headers();
      myHeaders.append("Authorization", `bearer ${this.jwt_token}`);

      var formdata = new FormData();
      formdata.append("room_id", this.roomId);
      formdata.append("message", this.textInput);
      if (this.selectedFile) {
        console.log("masuk upload file", this.selectedFile.name);
        formdata.append("attachments", this.selectedFile);
      }

      var requestOptions = {
        method: "POST",
        headers: myHeaders,
        body: formdata
        // redirect: "follow"
      };

      fetch(
        "https://staging-chat.trawlbens.com/chat/trawlbens/to/customer",
        requestOptions
      )
        .then(response => response.text())
        .then(result => {
          this.textInput = "";
          this.selectedFile = null;
          console.log("success produce", result);
        })
        .catch(error => console.log("error", error));
    }

    // consumeNotif() {
    //   let consumer = new WebSocket(
    //     "wss://staging-ws.trawlbens.com/ws/v2/consumer/non-persistent/public/default/1-admin-chat-notification/notification"
    //   );

    //   consumer.onopen = function(e) {
    //     console.log("notif isOpen", consumer);
    //   };

    //   consumer.onmessage = function(event) {
    //     console.log("onmessage notif", event);
    //   };

    //   consumer.onclose = function(event) {
    //     console.log("on close....", event);
    //     if (event.wasClean) {
    //       // alert(
    //       //   `[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`
    //       // );
    //       console.log(
    //         `[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`
    //       );
    //     } else {
    //       // e.g. server process killed or network down
    //       // event.code is usually 1006 in this case
    //       // alert("[close] Connection consumer died");
    //       console.log("[close] Connection consumer died");
    //       callback("reconnect");
    //     }
    //   };

    //   consumer.onerror = function(error) {
    //     // alert(`[error] ${error.message}`);
    //     console.log("consumer error notif", error.message);
    //   };

    //   consumer.connect;
    // }
  },

  computed: {
    // onSocket() {
    //   let socket = new WebSocket(
    //     "ws://127.0.0.1:8080/ws/v2/consumer/persistent/public/default/my-topic/my-sub"
    //   );
    //   socket.onopen = function(e) {
    //     alert("[open] Connection established");
    //     // alert("Sending to server");
    //     // socket.send("My name is John");
    //   };
    // }
    // consumerSocket() {
    //   let socket = new WebSocket(
    //     "ws://127.0.0.1:8080/ws/v2/consumer/persistent/public/default/my-topic/my-sub"
    //   );
    //   socket.onmessage = function(event) {
    //     this.newMessage.push(event.data);
    //     console.log("onmessage....", event.data);
    //   };
    // }
  },
  created() {
    this.jwt_token = this.$laravel.jwt_token;
    this.consumeSocket(this.getListRoom);
    // this.consumeNotif();
  },
  watch: {
    // $route: "consumeSocket",
    $route: "listChat"
    // $route: "consumeNotif"
  },
  mounted() {
    console.log("user", this.$laravel.user);
  }
};
</script>
