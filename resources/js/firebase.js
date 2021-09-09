import fcm from "firebase/app";
import "firebase/messaging";

const firebaseConfig = window.Laravel.fcm.cloud_messaging.web_app.firebaseConfig;
const audio = new Audio('/assets/notification-audio.mp3');

const firebase = {
  data() {
    return {
      messaging: undefined,
      sound: undefined,
    }
  },
  methods: {
    init() {
      fcm.initializeApp(firebaseConfig);
    },
    setMessaging() {
      this.messaging = fcm.messaging();
    },
    runServiceWorker() {
      navigator.serviceWorker
        .register(window.Laravel.fcm.service_worker_url)
        .then((registration) => {
          this.messaging.useServiceWorker(registration);
          this.messaging
            .getToken({vapidKey: window.Laravel.fcm.cloud_messaging.web_app.vapidKey})
            .then(async (currentToken) => {
              if (currentToken) {
                await this.subscribeTokenToTopic(currentToken, window.Laravel.user.fcm_token);
                this.receiveMessage();
              }
            })
            .catch((err) => {
              console.log('Error to get token', err);
            });

          if (Notification.permission !== 'granted') {
            Notification.requestPermission();
          }
        })
      .catch((err) => {
        console.log(err);
      })
    },
    async subscribeTokenToTopic(token, topic) {
      fetch('https://iid.googleapis.com/iid/v1/'+token+'/rel/topics/'+topic, {
        method: 'POST',
        headers: new Headers({
          'Authorization': `key=${window.Laravel.fcm.cloud_messaging.serverkey}`
        })
      }).then(response => {
        if (response.status < 200 || response.status >= 400) {
          throw 'Error subscribing to topic: '+response.status + ' - ' + response.text();
        }
      }).catch(error => {
        console.error(error);
      })
    },
    receiveMessage() {
      try {
        this.messaging.onMessage((payload) => {
          this.$notification.info({
            message: payload.notification.title,
            description: payload.notification.body,
          })
          this.audioNotification();
        });
      } catch (e) {
        console.log(e);
      }
    },
    audioNotification() {
      if (this.sound !== undefined) {
        clearTimeout(this.sound);
      }
      audio.currentTime = 1;
      audio.play()
        .then(() => {
          this.sound = setTimeout(() => { audio.pause(); audio.currentTime = 1; this.sound = undefined },5500)
        })
        .catch((e) => {
          console.log(e)
        })
    }
  }
}

export default firebase;
