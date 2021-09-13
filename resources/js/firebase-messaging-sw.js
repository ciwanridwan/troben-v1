importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js');

const firebaseConfig = {
  apiKey: "AIzaSyBcQAHU7NDH90vHM7RycPu8t2PNsMHrpUc",
  authDomain: "trawlbens-fb.firebaseapp.com",
  projectId: "trawlbens-fb",
  storageBucket: "trawlbens-fb.appspot.com",
  messagingSenderId: "1019509689561",
  appId: "1:1019509689561:web:9928df986d23258e1a02c8",
  measurementId: "G-9P4MRD37HV"
}

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: "/assets/tb-logo.png",
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});
