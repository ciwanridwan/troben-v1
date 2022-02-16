<template>
  <content-chat-layout>
    <!-- chat list -->
    <template slot="sider-left">
      <a-spin
        :spinning="loadingRoom"
        tip="Loading..."
        style="min-height: 500px"
      >
        <a-empty
          :description="'nggak ada chat nih...'"
          v-if="!roomChat && !loadingRoom"
        />
        <a-list
          item-layout="horizontal"
          :data-source="roomChat"
          class="spin-content"
        >
          <a-list-item
            slot="renderItem"
            slot-scope="item, index"
            :class="[roomId == item.room_id ? 'trawl-bg-gray' : '']"
            style="padding-left: 10px"
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
                    'https://ui-avatars.com/api/?name=' +
                    `${item.customer.name}`
                  "
              /></a-badge>
            </a-list-item-meta>
          </a-list-item>
        </a-list>
      </a-spin>
    </template>

    <template slot="content-head">
      <!-- <a-button @click="loading">test loading</a-button> -->
      <a-list-item v-if="nickname" class="trawl-bg-white">
        <a-list-item-meta :description="'active now'">
          <a slot="title">{{ nickname }}</a>
          <a-badge slot="avatar"
            ><a-avatar :src="`https://ui-avatars.com/api/?name=${nickname}`"
          /></a-badge>
        </a-list-item-meta>
      </a-list-item>
    </template>

    <!-- chat box -->
    <template slot="content">
      <!-- chat body -->
      <a-spin
        v-if="nickname"
        :spinning="loadingChat"
        tip="Loading..."
        style="min-height: 900px"
      >
        <div class="spin-content" style="position: relative">
          <a-row
            v-for="(data, index) in dataChat"
            :key="index"
            type="flex"
            :justify="data.user_chat ? 'end' : 'start'"
            align="bottom"
            :class="[
              'trawl-chat--next',
              index == dataChat.length - 1 ? 'scrollingContainer' : '',
            ]"
            :style="[
              'overflow-y: scroll;',
              data.user_chat ? 'padding-left: 15px' : '',
            ]"
          >
            <a-col v-if="data.customer_chat" :md="1">
              <a-avatar
                :src="'https://ui-avatars.com/api/?name=A+D'"
              ></a-avatar>
            </a-col>
            <a-col
              :md="14"
              :class="[
                'trawl-chat trawl-chat-message',
                data.user_chat
                  ? 'trawl-chat-message--receive trawl-bg-green--darken'
                  : 'trawl-chat-message--send trawl-bg-white',
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
                  style="width: 403px"
                  :src="
                    data.attachments[0].customer_file
                      ? data.attachments[0].customer_file
                      : data.attachments[0].user_file
                  "
                  alt="image"
                />
              </a>
              {{ data.user_chat ? data.user_chat : data.customer_chat }}
              <a-space
                align="baseline"
                style="position: absolute; bottom: 0px; right: 0px"
              >
                <img
                  v-if="data.user_chat"
                  :src="
                    data.customer_read_at && data.user_chat
                      ? '/assets/Check_Double_Blue.png'
                      : !data.customer_read_at && data.user_chat
                      ? '/assets/Check_Double_White.png'
                      : '/assets/Clock_Pending.png'
                  "
                  alt="logo"
                />
                <p>{{ moment(data.created_at).format("LT") }}</p>
              </a-space>
            </a-col>
            <a-col v-if="data.user_chat" :md="1" style="margin-left: 20px">
              <a-avatar
                :src="`https://ui-avatars.com/api/?name=${nickname}`"
              ></a-avatar>
            </a-col>
          </a-row>
        </div>
      </a-spin>
      <!-- chat send -->
      <div v-if="nickname" class="trawl-chat-send trawl-bg-white">
        <img v-if="tempUrl" :src="tempUrl" width="200" height="200" />
        <p v-if="tempUrl">{{ imageName }}</p>
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
              style="border: none; background: none; margin: 0; padding: 0"
              @click="$refs.fileInput.click()"
            >
              <img
                style="width: 24px"
                src="/assets/paperclip.png"
                alt="paperclip"
              />
            </button>
            <a-button
              style="margin: 0; padding: 0px 10px"
              type="primary"
              @click="producerSocket"
              :disabled="loadingChat"
              >Send</a-button
            >
            <!-- <a-button @click="scrollToElement({ behavior: 'smooth' })"
              >test scroll down from top</a-button
            > -->
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

export default {
  components: { ContentChatLayout },
  data() {
    return {
      temp: [],
      imageName: "",
      loadingRoom: true,
      loadingChat: true,
      roomChat: null,
      dataChat: null,
      textInput: "",
      nickname: "",
      descriptionText: "",
      roomId: null,
      selectedFile: null,
      jwt_token: "",
      activeLoading: 0,
      tempUrl: null,
    };
  },
  methods: {
    switchLoading(type) {
      this.loadingRoom = type;
    },

    tempChat() {
      let data = {
        attachments: this.selectedFile
          ? [{ customer_file: null, user_file: this.tempUrl }]
          : [],
        created_at: new Date(),
        customer_chat: null,
        customer_read_at: null,
        deleted_at: null,
        id: null,
        room_id: this.roomId,
        updated_at: null,
        user_chat: this.textInput,
        user_read_at: null,
      };

      this.dataChat.push(data);
      //console.log("tempChat data", data);
      //console.log("pushh", this.dataChat);
    },

    scrollToElement(options) {
      // let el = this.$el.getElementsByClassName("scrollingContainer")[0];
      // if (el) {
      //   el.scrollIntoView(options);
      // }
      let el = document.getElementById("scrollingContainer");
      el.scrollTo(0, el.scrollHeight);
    },

    onFileSelected(event) {
      //console.log("cel ref", event.target.files.length > 0);
      //console.log(event);
      if (event.target.files.length > 0) {
        //console.log("ada file");
        this.selectedFile = event.target.files[0];
        this.tempUrl = URL.createObjectURL(this.selectedFile);
        // let output = document.getElementById("blah");
        // output.src = src;
      }
      //console.log("lolos");

      // var reader = new FileReader();
      // reader.onload = function (e) {
      //   // $("#blah").attr("src", e.target.result).width(150).height(200);
      //   let output = document.getElementById("blah");
      //   output.src = reader.result;
      // };

      // reader.readAsDataURL(event.files[0]);

      this.imageName = event.target.files[0].name;
      //console.log("upload", event);
      //console.log("uploadData", event.target.files[0]);
    },

    description(item) {
      if (item.chats.length == 0) return "";
      else
        return `${
          item.chats[0].customer_chat
            ? item.chats[0].customer_chat
            : item.chats[0].user_chat
        }`;
      //  ${
      //   item.chats[1]
      //     ? item.chats[1].customer_chat
      //       ? item.chats[1].customer_chat
      //       : item.chats[1].user_chat
      //     : ""
      // } ${
      //   item.chats[2]
      //     ? item.chats[2].customer_chat
      //       ? item.chats[2].customer_chat
      //       : item.chats[2].user_chat
      //     : ""
      // }`;
    },

    getListRoom(value) {
      if (value === "reconnect")
        return this.consumeSocket(this.getListRoom, this.switchLoading);
      this.roomChat = value;
      //console.log("onmessage", this.roomChat);
      this.loadingRoom = false;
    },

    getListChat(value) {
      //console.log("masuk pak ekooo", value);
      this.dataChat = value;
      this.loadingChat = false;
      if (!this.loadingChat) {
        this.scrollToElement({ behavior: "smooth" });
      }
    },

    consumeSocket(callback, switchLoading) {
      let chatBaseUrl = this.chatBaseUrl;
      let jwtToken = this.jwt_token;
      let consumer = new WebSocket(
        `${this.socketBaseUrl}/ws/v2/consumer/non-persistent/public/default/${
          this.user().id
        }-partner/room`
      );

      consumer.onopen = function (e) {
        //console.log("isOpen", consumer);

        fetch(`${chatBaseUrl}/chat/list/partner/room`, {
          method: "GET",
          headers: {
            Authorization: `bearer ${jwtToken}`,
          },
        })
          .then((response) => {
            // console.log("success", response);
            if (response.status == 404) {
              switchLoading(false);
            }
          })
          .catch((err) => {
            console.error("failure error", err);
          });
      };

      consumer.onmessage = function (event) {
        let getData = null;
        let message = JSON.parse(event.data);
        let payload = atob(message.payload);

        getData = JSON.parse(payload);
        getData.forEach((element) => {
          element.is_active = false;
          element.is_online = false;
        });
        // console.log("getdata", getData);
        callback(getData);
      };

      consumer.onclose = function (event) {
        // //console.log("on close....", event);
        if (event.wasClean) {
          // alert(
          //   `[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`
          // );
          //console.log(`[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`);
        } else {
          // e.g. server process killed or network down
          // event.code is usually 1006 in this case
          // alert("[close] Connection consumer died");
          //console.log("[close] Connection consumer died");
          callback("reconnect");
        }
      };

      consumer.onerror = function (error) {
        // alert(`[error] ${error.message}`);
        //console.log("consumer error", error.message);
      };

      consumer.connect;
    },

    listChat(item, callback) {
      let chatBaseUrl = this.chatBaseUrl;
      this.loadingChat = true;
      this.dataChat = null;
      this.roomId = item.room_id;
      let jwtToken = this.jwt_token;
      let consumer = new WebSocket(
        `${this.socketBaseUrl}/ws/v2/consumer/non-persistent/public/default/${
          this.user().id
        }-partner-room-${item.room_id}/chat`
      );
      consumer.onclose = function (event) {
        // //console.log("listchat on close....", event);
        if (event.wasClean) {
          //console.log(`listchat [close] Connection closed cleanly, code=${event.code} reason=${event.reason}`);
        } else {
          // e.g. server process killed or network down
          // event.code is usually 1006 in this case
          //console.log("listchat [close] Connection list chat died");
          listChat(item, callback);
        }
      };

      consumer.onopen = function (e) {
        // //console.log("list chat isOpenChat", consumer);

        fetch(`${chatBaseUrl}/chat/list/partner/room/${item.room_id}`, {
          method: "GET",
          headers: {
            Authorization: `bearer ${jwtToken}`,
          },
        })
          .then((response) => {
            // //console.log("list chat success", response);
          })
          .then(() => {
            let el = document.getElementById("scrollingContainer");
            el.scrollTo(0, el.scrollHeight);
          })
          .catch((err) => {
            console.error("list chat failure error", err);
          });
      };

      // //console.log("roomChat", this.roomChat);

      consumer.onmessage = function (event) {
        var myHeaders = new Headers();
        myHeaders.append("Authorization", `bearer ${jwtToken}`);

        var requestOptions = {
          method: "PATCH",
          headers: myHeaders,
          redirect: "follow",
        };

        fetch(
          `${chatBaseUrl}/chat/partner/read/partner/room/${item.room_id}`,
          requestOptions
        )
          .then((response) => response.text())
          .then((result) => {
            //console.log("patch", result)
          })
          .catch((error) => {
            //console.log("error patch", error)
          });

        let message = JSON.parse(event.data);
        let payload = atob(message.payload);
        callback(JSON.parse(payload));
        // //console.log("onmessagechat", JSON.parse(payload));
      };

      this.nickname = item.customer.name;

      consumer.onerror = function (error) {
        //console.log("error", error.message);
      };

      consumer.connect;
    },

    // closeSocket() {
    //   let socket = new WebSocket(
    //     "ws://127.0.0.1:8080/ws/v2/consumer/persistent/public/default/my-topic/my-sub"
    //   );

    //   socket.onclose = function(event) {
    //     //console.log("on close....", event);
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
      // //console.log("produce", this.roomId, this.textInput);

      // var myHeaders = new Headers();
      // myHeaders.append("Authorization", `bearer ${this.jwt_token}`);

      // var formdata = new FormData();
      // formdata.append("room_id", this.roomId);
      // formdata.append("message", this.textInput);
      // if (this.selectedFile) {
      //   //console.log("masuk upload file", this.selectedFile.name);
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
      //   `${chatBaseUrl}/chat/trawlbens/to/customer",
      //   requestOptions
      // )
      //   .then(response => response.text())
      //   .then(result => //console.log(result))
      //   .catch(error => //console.log("error", error));

      let chatBaseUrl = this.chatBaseUrl;

      var myHeaders = new Headers();
      myHeaders.append("Authorization", `bearer ${this.jwt_token}`);

      var formdata = new FormData();
      formdata.append("room_id", this.roomId);
      formdata.append("message", this.textInput);
      if (this.selectedFile) {
        // //console.log("masuk upload file", this.selectedFile.name);

        formdata.append("attachments", this.selectedFile);
      }

      var requestOptions = {
        method: "POST",
        headers: myHeaders,
        body: formdata,
        // redirect: "follow"
      };

      fetch(`${chatBaseUrl}/chat/trawlbens/to/customer`, requestOptions)
        .then((response) => {
          // //console.log("response upload", response);
          this.tempChat();
          response.text();
        })
        .then((result) => {
          this.textInput = "";
          this.selectedFile = null;
          this.tempUrl = null;
          this.scrollToElement();
          // //console.log("success produce", result);
        })
        .catch((error) => {
          //console.log("error", error)
        });
    },

    // consumeNotif() {
    //   let consumer = new WebSocket(
    //     "${this.socketBaseUrl}/ws/v2/consumer/non-persistent/public/default/1-admin-chat-notification/notification"
    //   );

    //   consumer.onopen = function(e) {
    //     //console.log("notif isOpen", consumer);
    //   };

    //   consumer.onmessage = function(event) {
    //     //console.log("onmessage notif", event);
    //   };

    //   consumer.onclose = function(event) {
    //     //console.log("on close....", event);
    //     if (event.wasClean) {
    //       // alert(
    //       //   `[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`
    //       // );
    //       //console.log(
    //         `[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`
    //       );
    //     } else {
    //       // e.g. server process killed or network down
    //       // event.code is usually 1006 in this case
    //       // alert("[close] Connection consumer died");
    //       //console.log("[close] Connection consumer died");
    //       callback("reconnect");
    //     }
    //   };

    //   consumer.onerror = function(error) {
    //     // alert(`[error] ${error.message}`);
    //     //console.log("consumer error notif", error.message);
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
    //     //console.log("onmessage....", event.data);
    //   };
    // }
  },
  created() {
    this.jwt_token = this.$laravel.jwt_token;
    this.consumeSocket(this.getListRoom, this.switchLoading);
  },
  watch: {
    // $route: "consumeSocket",
    $route: "listChat",
    // $route: "consumeNotif"
  },
  mounted() {
    // //console.log("user", this.user());
    // //console.log("token", this.$laravel.jwt_token);
  },
};
</script>
